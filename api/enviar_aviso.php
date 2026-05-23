<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['admin_logged'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesión caducada. Recarga la página.']); exit();
}

require_once __DIR__ . '/notificaciones_helpers.php';
require_once __DIR__ . '/mailer_helpers.php';

$matricula = (int)($_POST['matricula'] ?? 0);
$asunto = trim($_POST['asunto'] ?? '');
$motivo = trim($_POST['motivo'] ?? '');
$mensaje = trim($_POST['mensaje'] ?? '');

if ($matricula <= 0 || $asunto === '' || $mensaje === '') {
    echo json_encode(['status' => 'error', 'message' => 'Faltan datos obligatorios.']); exit();
}

$conexion = new mysqli("localhost", "root", "", "portal_estadias");
$stmt = $conexion->prepare("SELECT nombre_completo, correo FROM alumnos WHERE matricula = ?");
$stmt->bind_param("i", $matricula);
$stmt->execute();
$alumno = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$alumno) { echo json_encode(['status' => 'error', 'message' => 'Matrícula no encontrada.']); exit(); }

$motivoFinal = $motivo !== '' ? $motivo : null;
crearNotificacion($conexion, $matricula, 'general', $asunto, $motivoFinal, null, null, $mensaje);

if (!empty($alumno['correo'])) {
    $cuerpoHtml = "
    <div style='font-family: Arial, sans-serif; padding: 25px; background-color: #f7fafc;'>
        <div style='background-color: #ffffff; padding: 20px; border-radius: 8px; border-top: 5px solid #00a859; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #00a859;'>Aviso Oficial de Administración</h2>
            <p>Hola <strong>{$alumno['nombre_completo']}</strong>,</p>
            <p>Tienes un mensaje importante en tu panel de Estadías:</p>
            <div style='background-color: #f0fff4; padding: 15px; border-left: 4px solid #38a169; margin: 20px 0;'>
                <p><strong>Asunto:</strong> {$asunto}</p>
                <p><strong>Mensaje:</strong><br>" . nl2br(htmlspecialchars($mensaje)) . "</p>
            </div>
            <p>Por favor, ingresa a la plataforma para revisar los detalles correspondientes:</p>
            <a href='https://utmir.edu.mx/estadias.html' target='_blank' style='display: inline-block; background-color: #00a859; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-weight: bold; margin-top: 15px;'>Ir al Portal Oficial UTMiR</a>
        </div>
    </div>";
    enviarCorreoGmail($alumno['correo'], "UTMiR: " . $asunto, $cuerpoHtml);
}
echo json_encode(['status' => 'success', 'message' => 'Notificación enviada.']);
?>