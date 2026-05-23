<?php
// api/guardar_periodo_notif.php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Medida de seguridad: Validar que el admin haya iniciado sesión
if (!isset($_SESSION['admin_logged'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Sesion de administrador no valida.']);
    exit();
}

// Recibir el rango de fechas enviado por JavaScript (Fetch API)
$rango = trim($_POST['rango_fechas_notif'] ?? '');

// Flatpickr devuelve las fechas separadas por " a ", validamos que exista este formato
if (empty($rango) || strpos($rango, ' a ') === false) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Por favor, selecciona un rango de fechas completo.']);
    exit();
}

// Separamos la fecha de inicio y la fecha de fin
list($fecha_inicio, $fecha_fin) = explode(' a ', $rango);

// Agregamos las horas de inicio de día y fin de día para que sea un DateTime correcto en MySQL
$inicio_db = $fecha_inicio . " 00:00:00";
$fin_db = $fecha_fin . " 23:59:59";

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "portal_estadias");
if ($conexion->connect_error) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'No se pudo conectar a la base de datos.']);
    exit();
}

// Actualizamos siempre el primer registro (id = 1) de nuestra configuración
$stmt = $conexion->prepare("UPDATE configuracion_notificaciones SET fecha_inicio = ?, fecha_fin = ? LIMIT 1");

if ($stmt) {
    $stmt->bind_param("ss", $inicio_db, $fin_db);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'El periodo se programó exitosamente.']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error al guardar en la base de datos.']);
    }
    $stmt->close();
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error al preparar la consulta.']);
}

$conexion->close();
?>