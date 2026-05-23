<?php
// api/guardar_alumnos.php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['admin_logged'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesión no válida.']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['alumnos'])) {
    echo json_encode(['status' => 'error', 'message' => 'No se recibieron alumnos para habilitar.']);
    exit();
}

$conexion = new mysqli("localhost", "root", "", "portal_estadias");
if ($conexion->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Error de base de datos.']);
    exit();
}

// Asegurarnos de que la columna de acreditación exista
$conexion->query("ALTER TABLE alumnos ADD COLUMN IF NOT EXISTS acreditado TINYINT(1) DEFAULT 0");

// Inserción con UNIFICACIÓN: Ahora incluye "acreditado = 1" para desbloquear la subida de memorias
$query = "INSERT INTO alumnos (matricula, curp, nombre_completo, programa_educativo, cuatrimestre, correo, estatus, acreditado) 
          VALUES (?, ?, ?, ?, ?, ?, 'activo', 1) 
          ON DUPLICATE KEY UPDATE 
          curp = VALUES(curp),
          nombre_completo = VALUES(nombre_completo), 
          programa_educativo = VALUES(programa_educativo),
          cuatrimestre = VALUES(cuatrimestre),
          correo = VALUES(correo), 
          estatus = 'activo',
          acreditado = 1";

$stmt = $conexion->prepare($query);
$procesados = 0;

foreach ($input['alumnos'] as $al) {
    $mat = (int)$al['matricula'];
    // Encriptamos el CURP para que el Login funcione
    $curp_hash = password_hash($al['curp'], PASSWORD_DEFAULT);
    
    $stmt->bind_param("isssss", $mat, $curp_hash, $al['nombre'], $al['programa'], $al['cuatrimestre'], $al['correo']);
    if ($stmt->execute()) {
        $procesados++;
    }
}
$stmt->close();

echo json_encode(['status' => 'success', 'message' => "Se habilitaron $procesados alumnos exitosamente con permisos de subida."]);
?>