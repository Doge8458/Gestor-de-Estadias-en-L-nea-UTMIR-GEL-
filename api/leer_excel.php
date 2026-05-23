<?php
// api/leer_excel.php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['admin_logged'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesión caducada. Por favor, recarga la página de administrador e inicia sesión nuevamente.']);
    exit();
}

require_once __DIR__ . '/../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

if (!isset($_FILES['archivo_excel']) || $_FILES['archivo_excel']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'No se recibió un archivo válido.']);
    exit();
}

try {
    $archivo = $_FILES['archivo_excel']['tmp_name'];
    $spreadsheet = IOFactory::load($archivo);
    $data = $spreadsheet->getActiveSheet()->toArray();
    
    $alumnos = [];
    foreach ($data as $index => $row) {
        if ($index == 0) continue; // Omitir la fila 1 (Encabezados)
        
        // Mapeo EXACTO según la estructura de tu archivo Excel
        $curp = trim($row[0] ?? '');
        $matricula = trim($row[1] ?? '');
        $nombre = trim($row[2] ?? '');
        $programa = trim($row[3] ?? '');
        $cuatrimestre = trim($row[4] ?? '');
        $correo = trim($row[5] ?? '');
        
        if (!empty($matricula) && !empty($nombre) && !empty($curp)) {
            $alumnos[] = [
                'curp' => $curp,
                'matricula' => $matricula,
                'nombre' => $nombre,
                'programa' => $programa,
                'cuatrimestre' => $cuatrimestre,
                'correo' => $correo
            ];
        }
    }

    echo json_encode(['status' => 'success', 'data' => $alumnos]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error al procesar el Excel. Verifica las columnas.']);
}
?>