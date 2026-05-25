<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['matricula'])) { 
    echo json_encode(['status'=>'error', 'message'=>'No autorizado']); exit; 
}

$conexion = new mysqli("localhost", "root", "", "portal_estadias");
$accion = $_POST['accion'] ?? '';
$matricula = $_SESSION['matricula'];

if ($accion === 'marcar_una') {
    $id = (int)($_POST['id_notificacion'] ?? 0);
    $stmt = $conexion->prepare("UPDATE notificaciones SET leida = 1 WHERE id_notificacion = ? AND matricula_alumno = ?");
    $stmt->bind_param("ii", $id, $matricula);
    $stmt->execute();
    echo json_encode(['status'=>'success']);
} 
elseif ($accion === 'limpiar') {
    $stmt = $conexion->prepare("DELETE FROM notificaciones WHERE matricula_alumno = ?");
    $stmt->bind_param("i", $matricula);
    $stmt->execute();
    echo json_encode(['status'=>'success']);
} else {
    echo json_encode(['status'=>'error']);
}
?>