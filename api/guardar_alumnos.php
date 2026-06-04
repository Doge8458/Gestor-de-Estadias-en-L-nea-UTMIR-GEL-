<?php
// api/guardar_alumnos.php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['admin_logged'])) {
    echo json_encode(['status' => 'error', 'success' => false, 'message' => 'Sesion no valida.']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$alumnos = $input['alumnos'] ?? $input;

if (!is_array($alumnos) || empty($alumnos)) {
    echo json_encode(['status' => 'error', 'success' => false, 'message' => 'No se recibieron alumnos para habilitar.']);
    exit();
}

$conexion = new mysqli("localhost", "root", "", "portal_estadias");
if ($conexion->connect_error) {
    echo json_encode(['status' => 'error', 'success' => false, 'message' => 'Error de base de datos.']);
    exit();
}

function asegurarColumna($conexion, $tabla, $columna, $definicion) {
    $columnaSegura = $conexion->real_escape_string($columna);
    $tablaSegura = $conexion->real_escape_string($tabla);
    $resultado = $conexion->query("SHOW COLUMNS FROM `$tablaSegura` LIKE '$columnaSegura'");
    if ($resultado && $resultado->num_rows === 0) {
        $conexion->query("ALTER TABLE `$tablaSegura` ADD COLUMN `$columnaSegura` $definicion");
    }
}

asegurarColumna($conexion, 'alumnos', 'programa_educativo', 'VARCHAR(180) DEFAULT NULL');
asegurarColumna($conexion, 'alumnos', 'cuatrimestre', 'VARCHAR(80) DEFAULT NULL');
asegurarColumna($conexion, 'alumnos', 'correo', 'VARCHAR(160) DEFAULT NULL');
asegurarColumna($conexion, 'alumnos', 'estatus', "VARCHAR(40) DEFAULT 'activo'");
asegurarColumna($conexion, 'alumnos', 'acreditado', 'TINYINT(1) DEFAULT 0');
asegurarColumna($conexion, 'alumnos', 'video_visto', 'TINYINT(1) DEFAULT 0');

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
if (!$stmt) {
    echo json_encode(['status' => 'error', 'success' => false, 'message' => 'No se pudo preparar el guardado de alumnos.']);
    exit();
}

$procesados = 0;
$credencialesTemporales = 0;

foreach ($alumnos as $al) {
    $mat = (int)($al['matricula'] ?? 0);
    $nombre = trim((string)($al['nombre'] ?? $al['nombre_completo'] ?? ''));
    $programa = trim((string)($al['programa'] ?? $al['programa_educativo'] ?? ''));
    $cuatrimestre = trim((string)($al['cuatrimestre'] ?? ''));
    $correo = trim((string)($al['correo'] ?? $al['correo_institucional'] ?? ''));
    $curpPlano = trim((string)($al['curp'] ?? ''));

    if ($mat <= 0 || $nombre === '' || $programa === '') {
        continue;
    }

    if ($curpPlano === '') {
        $curpPlano = (string)$mat;
    }
    if (!empty($al['clave_temporal']) || $curpPlano === (string)$mat) {
        $credencialesTemporales++;
    }

    $curpHash = password_hash($curpPlano, PASSWORD_DEFAULT);
    $stmt->bind_param("isssss", $mat, $curpHash, $nombre, $programa, $cuatrimestre, $correo);
    if ($stmt->execute()) {
        $procesados++;
    }
}

$stmt->close();
$conexion->close();

$mensaje = "Se habilitaron $procesados alumnos con carrera y periodo detectados.";
if ($credencialesTemporales > 0) {
    $mensaje .= " $credencialesTemporales registro(s) no traian CURP en el Excel; se uso la matricula como clave temporal.";
}

echo json_encode(['status' => 'success', 'success' => true, 'message' => $mensaje]);
