<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['admin_logged'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Sesion de administrador no valida.']);
    exit();
}

require_once __DIR__ . '/notificaciones_helpers.php';

$matricula = isset($_POST['matricula']) ? (int)$_POST['matricula'] : 0;
$asunto = trim($_POST['asunto'] ?? '');
$motivo = trim($_POST['motivo'] ?? '');
$mensaje = trim($_POST['mensaje'] ?? '');

if ($matricula <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'La matricula es obligatoria para buscar al alumno.']);
    exit();
}

if ($asunto === '' || $mensaje === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'El asunto y el texto del mensaje son obligatorios.']);
    exit();
}

$conexion = new mysqli("localhost", "root", "", "portal_estadias");
if ($conexion->connect_error) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'No se pudo conectar a la base de datos.']);
    exit();
}

asegurarTablaNotificaciones($conexion);

$stmtAlumno = $conexion->prepare("SELECT nombre_completo FROM alumnos WHERE matricula = ?");
$stmtAlumno->bind_param("i", $matricula);
$stmtAlumno->execute();
$resAlumno = $stmtAlumno->get_result();
$alumno = $resAlumno->fetch_assoc();
$stmtAlumno->close();

if (!$alumno) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'No se encontro ningun alumno con esa matricula.']);
    exit();
}

$motivoFinal = $motivo !== '' ? $motivo : null;
if (!crearNotificacion($conexion, $matricula, 'general', $asunto, $motivoFinal, null, null, $mensaje)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'No se pudo enviar la notificacion.']);
    exit();
}

echo json_encode([
    'status' => 'success',
    'message' => 'Mensaje enviado correctamente.',
    'alumno' => $alumno['nombre_completo']
]);
?>
