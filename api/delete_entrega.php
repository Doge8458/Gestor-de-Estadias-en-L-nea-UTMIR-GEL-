<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_DEPRECATED);

if (!isset($_SESSION['admin_logged'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Sesion de administrador no valida.']);
    exit();
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/notificaciones_helpers.php';

$motivosPermitidos = [
    'paginas_no_enumeradas' => 'Paginas no enumeradas',
    'problemas_indice' => 'Problemas con el indice',
    'indice_no_coincide' => 'El indice no coincide con las paginas',
    'falta_documento_autorizacion' => 'Falta del documento de autorizacion',
    'documento_autorizacion_no_agregado' => 'Documento de autorizacion no agregado',
    'orden_paginas' => 'Orden de las paginas',
    'otro' => 'Otro'
];

$idEntrega = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$motivoSeleccionado = trim($_POST['motivo_select'] ?? '');
$motivoOtro = trim($_POST['motivo_otro'] ?? '');
$comentario = trim($_POST['comentario'] ?? '');
$detalleOtro = trim($_POST['detalle_otro'] ?? '');

if ($idEntrega <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID de entrega no valido.']);
    exit();
}

if (!array_key_exists($motivoSeleccionado, $motivosPermitidos)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Selecciona un motivo para eliminar el archivo.']);
    exit();
}

if ($motivoSeleccionado === 'otro' && ($motivoOtro === '' || $detalleOtro === '')) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Cuando selecciones Otro debes escribir el motivo y explicar el problema.']);
    exit();
}

$motivoFinal = $motivoSeleccionado === 'otro' ? $motivoOtro : $motivosPermitidos[$motivoSeleccionado];
$detalleFinal = $motivoSeleccionado === 'otro' ? $detalleOtro : null;
$comentarioFinal = $comentario !== '' ? $comentario : null;

$conexion = new mysqli("localhost", "root", "", "portal_estadias");
if ($conexion->connect_error) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'No se pudo conectar a la base de datos.']);
    exit();
}

asegurarTablaNotificaciones($conexion);

$stmt = $conexion->prepare("SELECT id_entrega, matricula_alumno, nombre_archivo_subido, link_google_drive FROM entregas WHERE id_entrega = ?");
$stmt->bind_param("i", $idEntrega);
$stmt->execute();
$res = $stmt->get_result();
$entrega = $res->fetch_assoc();
$stmt->close();

if (!$entrega) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'No se encontro la entrega solicitada.']);
    exit();
}

$link = $entrega['link_google_drive'];
if (!empty($link) && preg_match('/\/d\/(.*?)\//', $link, $coincidencias) && isset($coincidencias[1])) {
    try {
        $client = new Google\Client();
        $client->setApplicationName('ProyectoUSB Uploader');
        $client->setScopes(Google\Service\Drive::DRIVE_FILE);
        $client->setAuthConfig(__DIR__ . '/../client_secret.json');
        $client->setAccessType('offline');

        $tokenPath = __DIR__ . '/token.json';
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
            if ($client->isAccessTokenExpired()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                file_put_contents($tokenPath, json_encode($client->getAccessToken()));
            }

            $driveService = new Google\Service\Drive($client);
            $driveService->files->delete($coincidencias[1]);
        }
    } catch (Exception $e) {
        // Si Drive falla, de todos modos se elimina el registro local y se avisa al alumno.
    }
}

$matricula = (int)$entrega['matricula_alumno'];
$asunto = 'Archivo eliminado por administracion';
$mensaje = "Tu archivo {$entrega['nombre_archivo_subido']} fue eliminado por administracion. Motivo: {$motivoFinal}.";
if ($comentarioFinal) {
    $mensaje .= " Comentario: {$comentarioFinal}.";
}
if ($detalleFinal) {
    $mensaje .= " Detalle: {$detalleFinal}.";
}

$conexion->begin_transaction();

try {
    if (!crearNotificacion($conexion, $matricula, 'eliminacion', $asunto, $motivoFinal, $comentarioFinal, $detalleFinal, $mensaje)) {
        throw new Exception('No se pudo crear la notificacion.');
    }

    $stmtDelete = $conexion->prepare("DELETE FROM entregas WHERE id_entrega = ?");
    $stmtDelete->bind_param("i", $idEntrega);
    if (!$stmtDelete->execute()) {
        throw new Exception('No se pudo eliminar la entrega.');
    }
    $stmtDelete->close();

    $conexion->commit();
    echo json_encode(['status' => 'success', 'message' => 'Entrega eliminada y notificacion enviada al alumno.']);
} catch (Exception $e) {
    $conexion->rollback();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conexion->close();
?>
