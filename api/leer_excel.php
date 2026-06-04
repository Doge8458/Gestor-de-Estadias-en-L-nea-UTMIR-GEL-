<?php
// api/leer_excel.php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['admin_logged'])) {
    echo json_encode(['status' => 'error', 'success' => false, 'message' => 'Sesion caducada. Recarga el panel e inicia sesion nuevamente.']);
    exit();
}

require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (!class_exists('ZipArchive')) {
    echo json_encode(['status' => 'error', 'success' => false, 'message' => 'La extension ZIP de PHP no esta habilitada. Activa extension=zip en C:\\xampp\\php\\php.ini y reinicia Apache para leer archivos .xlsx.']);
    exit();
}

if (!isset($_FILES['archivo_excel']) || $_FILES['archivo_excel']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'success' => false, 'message' => 'No se recibio un archivo valido.']);
    exit();
}

function normalizarEncabezado($texto) {
    $texto = mb_strtolower(trim((string)$texto), 'UTF-8');
    $texto = str_replace(["\n", "\r"], ' ', $texto);
    $sinAcentos = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
    if ($sinAcentos !== false) {
        $texto = $sinAcentos;
    }
    $texto = str_replace(["'", "`", "^", "~", '"'], '', $texto);
    return preg_replace('/\s+/', ' ', $texto);
}

function buscarColumna($headers, $candidatos) {
    foreach ($candidatos as $candidato) {
        foreach ($headers as $index => $header) {
            if ($header !== '' && strpos($header, $candidato) !== false) {
                return $index;
            }
        }
    }
    return null;
}

function valorFila($row, $index) {
    if ($index === null || !array_key_exists($index, $row) || $row[$index] === null) {
        return '';
    }
    return trim((string)$row[$index]);
}

function inferirNivelDocumento($programa, $cuatrimestre) {
    $texto = normalizarEncabezado($programa . ' ' . $cuatrimestre);
    if (strpos($texto, 'tecnico superior universitario') !== false || strpos($texto, 't.s.u') !== false || strpos($texto, 'tsu') !== false || strpos($texto, '6') !== false) {
        return '6o cuatrimestre (Tecnico Superior Universitario)';
    }
    if (strpos($texto, 'licenciatura') !== false || strpos($texto, 'ingenieria') !== false || strpos($texto, '10') !== false || strpos($texto, '11') !== false) {
        return '10o cuatrimestre (Ingenieria/Licenciatura)';
    }
    return '6o cuatrimestre (Tecnico Superior Universitario)';
}

try {
    $spreadsheet = IOFactory::load($_FILES['archivo_excel']['tmp_name']);
    $alumnosPorMatricula = [];
    $hojasLeidas = 0;

    foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
        $rows = $sheet->toArray(null, true, true, false);
        if (count($rows) < 2) {
            continue;
        }

        $headers = array_map('normalizarEncabezado', $rows[0]);
        $idxMatricula = buscarColumna($headers, ['numero de matricula', 'matricula']);
        $idxNombre = buscarColumna($headers, ['nombre del alumn', 'nombre completo', 'alumno']);
        $idxCarrera = buscarColumna($headers, ['carrera', 'programa educativo', 'programa']);
        $idxCuatrimestre = buscarColumna($headers, ['cuatrimestre en el que realiza su estadia', 'cuatrimestre']);
        $idxCorreoInstitucional = buscarColumna($headers, ['correo institucional']);
        $idxCorreo = buscarColumna($headers, ['direccion de correo electronico', 'correo electronico', 'correo de respaldo']);
        $idxCurp = buscarColumna($headers, ['curp']);

        if ($idxMatricula === null || $idxNombre === null || $idxCarrera === null) {
            continue;
        }

        $hojasLeidas++;

        foreach (array_slice($rows, 1) as $row) {
            $matricula = preg_replace('/\D+/', '', valorFila($row, $idxMatricula));
            $nombre = valorFila($row, $idxNombre);
            $programa = valorFila($row, $idxCarrera);
            $cuatrimestre = valorFila($row, $idxCuatrimestre);
            $correo = valorFila($row, $idxCorreoInstitucional);
            if ($correo === '') {
                $correo = valorFila($row, $idxCorreo);
            }
            $curp = strtoupper(valorFila($row, $idxCurp));

            if ($matricula === '' || $nombre === '' || $programa === '') {
                continue;
            }

            if ($curp === '') {
                $curp = $matricula;
            }

            $alumnosPorMatricula[$matricula] = [
                'curp' => $curp,
                'clave_temporal' => $idxCurp === null,
                'matricula' => $matricula,
                'nombre' => $nombre,
                'nombre_completo' => $nombre,
                'programa' => $programa,
                'programa_educativo' => $programa,
                'cuatrimestre' => $cuatrimestre,
                'nivel_documento' => inferirNivelDocumento($programa, $cuatrimestre),
                'correo' => $correo,
                'correo_institucional' => $correo
            ];
        }
    }

    $alumnos = array_values($alumnosPorMatricula);
    usort($alumnos, fn($a, $b) => strcmp($a['programa'] . $a['nombre'], $b['programa'] . $b['nombre']));

    echo json_encode([
        'status' => 'success',
        'success' => true,
        'data' => $alumnos,
        'message' => count($alumnos) . " alumnos encontrados en $hojasLeidas hoja(s) del Excel."
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'success' => false, 'message' => 'Error al procesar el Excel. Verifica que sea un archivo .xlsx valido.']);
}
?>
