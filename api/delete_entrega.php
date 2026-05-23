<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['admin_logged'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesión caducada. Recarga la página.']); exit();
}

require_once __DIR__ . '/notificaciones_helpers.php';
require_once __DIR__ . '/mailer_helpers.php';

$id_entrega = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$motivo_select = trim($_POST['motivo_select'] ?? '');
$motivo_otro = trim($_POST['motivo_otro'] ?? '');
$comentario = trim($_POST['comentario'] ?? '');

if ($id_entrega <= 0 || empty($motivo_select)) {
    echo json_encode(['status' => 'error', 'message' => 'Faltan datos.']); exit();
}

$conexion = new mysqli("localhost", "root", "", "portal_estadias");

$stmtInfo = $conexion->prepare("SELECT e.matricula_alumno, e.cuatrimestre_subido, a.nombre_completo, a.correo FROM entregas e JOIN alumnos a ON e.matricula_alumno = a.matricula WHERE e.id_entrega = ?");
$stmtInfo->bind_param("i", $id_entrega);
$stmtInfo->execute();
$info = $stmtInfo->get_result()->fetch_assoc();
$stmtInfo->close();

if (!$info) {
    echo json_encode(['status' => 'error', 'message' => 'Archivo no encontrado.']); exit();
}

// Convertir nomenclatura al correo para el alumno
$cuatrimestreFormat = $info['cuatrimestre_subido'];
if (strpos($cuatrimestreFormat, '6to') !== false || strpos($cuatrimestreFormat, '6º') !== false) {
    $cuatrimestreFormat = '6º cuatrimestre (Técnico Superior Universitario)';
} elseif (strpos($cuatrimestreFormat, '11vo') !== false || strpos($cuatrimestreFormat, '10º') !== false) {
    $cuatrimestreFormat = '10º cuatrimestre (Ingeniería/Licenciatura)';
}

$motivo_final = ($motivo_select === 'otro') ? $motivo_otro : str_replace('_', ' ', ucfirst($motivo_select));
$asunto = "Documento Rechazado: " . $cuatrimestreFormat;
$mensaje_db = "Tu memoria técnica ($cuatrimestreFormat) ha sido rechazada. Motivo: $motivo_final. Por favor realiza las correcciones y vuelve a subir el documento.";

$stmtDel = $conexion->prepare("DELETE FROM entregas WHERE id_entrega = ?");
$stmtDel->bind_param("i", $id_entrega);
$stmtDel->execute();
$stmtDel->close();

crearNotificacion($conexion, $info['matricula_alumno'], 'eliminacion', $asunto, $motivo_final, $comentario, null, $mensaje_db);

if (!empty($info['correo'])) {
    $cuerpoHtml = "
    <div style='font-family: Arial, sans-serif; padding: 25px; background-color: #f7fafc;'>
        <div style='background-color: #ffffff; padding: 20px; border-radius: 8px; border-top: 5px solid #e53e3e; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #e53e3e;'>Aviso Oficial de Rechazo de Documento</h2>
            <p>Hola <strong>{$info['nombre_completo']}</strong>,</p>
            <p>El Departamento de Vinculación ha revisado tu documento correspondiente al proceso de <strong>{$cuatrimestreFormat}</strong> y el estatus ha cambiado a <strong>RECHAZADO</strong> por la siguiente razón:</p>
            <div style='background-color: #fffaf0; padding: 15px; border-left: 4px solid #dd6b20; margin: 20px 0;'>
                <p><strong>Motivo de rechazo:</strong> {$motivo_final}</p>";
                if(!empty($comentario)){ $cuerpoHtml .= "<p><strong>Observaciones adicionales:</strong> {$comentario}</p>"; }
    $cuerpoHtml .= "
            </div>
            <p>Por favor, atiende las observaciones señaladas, realiza las correcciones necesarias y regresa a la plataforma para cargar la versión definitiva de tu documento.</p>
            <a href='https://utmir.edu.mx/estadias.html' target='_blank' style='display: inline-block; background-color: #00a859; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-weight: bold; margin-top: 15px;'>Ir al Portal Oficial UTMiR</a>
        </div>
    </div>";
    enviarCorreoGmail($info['correo'], "UTMiR: " . $asunto, $cuerpoHtml);
}
echo json_encode(['status' => 'success', 'message' => 'Documento eliminado y correo enviado exitosamente.']);
?>