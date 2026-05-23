<?php
// api/subir_excel.php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['admin_logged'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesión no válida.']);
    exit();
}

require_once __DIR__ . '/../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

if (!isset($_FILES['archivo_excel']) || $_FILES['archivo_excel']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'No se recibió un archivo válido.']);
    exit();
}

$archivo = $_FILES['archivo_excel']['tmp_name'];

try {
    $spreadsheet = IOFactory::load($archivo);
    $data = $spreadsheet->getActiveSheet()->toArray();
    $conexion = new mysqli("localhost", "root", "", "portal_estadias");
    $registros_procesados = 0;

    foreach ($data as $index => $row) {
        if ($index == 0) continue; // Omitir la fila de encabezados
        
        // Ajusta los índices [0, 1, 2, 3] dependiendo de las columnas de tu Excel real
        $matricula = (int)($row[0] ?? 0);
        $nombre = trim($row[1] ?? '');
        $correo = trim($row[2] ?? '');
        
        if ($matricula > 0 && $nombre !== '') {
            // Unificación: Inserta o actualiza para evitar duplicados en el dashboard
            $stmt = $conexion->prepare("
                INSERT INTO alumnos (matricula, nombre_completo, correo) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                nombre_completo = VALUES(nombre_completo), 
                correo = VALUES(correo)
            ");
            $stmt->bind_param("iss", $matricula, $nombre, $correo);
            if ($stmt->execute()) {
                $registros_procesados++;
            }
            $stmt->close();
        }
    }

    echo json_encode(['status' => 'success', 'message' => "Se procesaron y unificaron $registros_procesados alumnos exitosamente."]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error al leer el archivo Excel: ' . $e->getMessage()]);
}
?>