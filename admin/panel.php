<?php
// admin/panel.php
session_start();

if (!isset($_SESSION['admin_logged'])) {
    header('Location: ../login_auth.php');
    exit();
}

$conexion = new mysqli("localhost", "root", "", "portal_estadias");
if ($conexion->connect_error) {
    die("Error critico: No se pudo conectar a la base de datos.");
}

function asegurarColumnaPanel($conexion, $tabla, $columna, $definicion) {
    $columnaSegura = $conexion->real_escape_string($columna);
    $tablaSegura = $conexion->real_escape_string($tabla);
    $resultado = $conexion->query("SHOW COLUMNS FROM `$tablaSegura` LIKE '$columnaSegura'");
    if ($resultado && $resultado->num_rows === 0) {
        $conexion->query("ALTER TABLE `$tablaSegura` ADD COLUMN `$columnaSegura` $definicion");
    }
}

asegurarColumnaPanel($conexion, 'alumnos', 'programa_educativo', 'VARCHAR(180) DEFAULT NULL');
asegurarColumnaPanel($conexion, 'alumnos', 'cuatrimestre', 'VARCHAR(80) DEFAULT NULL');
asegurarColumnaPanel($conexion, 'alumnos', 'correo', 'VARCHAR(160) DEFAULT NULL');
asegurarColumnaPanel($conexion, 'alumnos', 'estatus', "VARCHAR(40) DEFAULT 'activo'");
asegurarColumnaPanel($conexion, 'alumnos', 'acreditado', 'TINYINT(1) DEFAULT 0');

$busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';
$filtros_busqueda = [];

if ($busqueda !== '') {
    preg_match_all('/#[\p{L}\p{N}_-]+|[^\s#]+/u', $busqueda, $matches);
    $filtros_busqueda = array_values(array_filter(array_map(function($term) {
        return trim(ltrim($term, '#'));
    }, $matches[0] ?? [])));
}

$selectBase = "
    SELECT a.*, e.id_entrega, e.fecha_subida, e.cuatrimestre_subido, e.programa_educativo_subido,
           e.nombre_archivo_subido, e.link_google_drive, e.contenido_texto
    FROM alumnos a
    LEFT JOIN entregas e ON a.matricula = e.matricula_alumno";

if (!empty($filtros_busqueda)) {
    $whereParts = [];
    $params = [];

    foreach ($filtros_busqueda as $term) {
        $whereParts[] = "(CAST(a.matricula AS CHAR) LIKE ? OR a.nombre_completo LIKE ? OR a.programa_educativo LIKE ? OR a.cuatrimestre LIKE ? OR e.programa_educativo_subido LIKE ? OR e.cuatrimestre_subido LIKE ? OR e.nombre_archivo_subido LIKE ? OR e.contenido_texto LIKE ?)";
        for ($i = 0; $i < 8; $i++) {
            $params[] = '%' . $term . '%';
        }
    }

    $query = $selectBase . " WHERE " . implode(" AND ", $whereParts) . " ORDER BY COALESCE(a.programa_educativo, 'Sin carrera'), a.nombre_completo ASC";
    $stmt = $conexion->prepare($query);
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $resultado = $stmt->get_result();
} else {
    $query = $selectBase . " ORDER BY COALESCE(a.programa_educativo, 'Sin carrera'), a.nombre_completo ASC";
    $resultado = $conexion->query($query);
}

require_once __DIR__ . '/views/panel.view.php';
?>
