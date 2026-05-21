<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['matricula'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Sesion no valida.']);
    exit();
}

require_once __DIR__ . '/notificaciones_helpers.php';

$conexion = new mysqli("localhost", "root", "", "portal_estadias");
if ($conexion->connect_error) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'No se pudo conectar a la base de datos.']);
    exit();
}

limpiarNotificacionesVistas($conexion);

$matricula = (int)$_SESSION['matricula'];
$ids = isset($_POST['ids']) ? json_decode($_POST['ids'], true) : [];

if (!is_array($ids) || count($ids) === 0) {
    echo json_encode(['status' => 'success']);
    exit();
}

$ids = array_values(array_filter(array_map('intval', $ids), fn($id) => $id > 0));
if (count($ids) === 0) {
    echo json_encode(['status' => 'success']);
    exit();
}

$placeholders = implode(',', array_fill(0, count($ids), '?'));
$tipos = str_repeat('i', count($ids) + 1);
$parametros = array_merge([$matricula], $ids);

$stmt = $conexion->prepare("UPDATE notificaciones SET leida = 1, fecha_vista = COALESCE(fecha_vista, NOW()), fecha_expira = COALESCE(fecha_expira, DATE_ADD(NOW(), INTERVAL 3 DAY)) WHERE matricula_alumno = ? AND id_notificacion IN ($placeholders)");
$stmt->bind_param($tipos, ...$parametros);
$stmt->execute();
$stmt->close();

echo json_encode(['status' => 'success']);
?>
