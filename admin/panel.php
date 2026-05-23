<?php
session_start();
// Redirige al index.html de la carpeta ADMIN si no hay sesión
if (!isset($_SESSION['admin_logged'])) { header("Location: index.html"); exit(); }

// Conexión a BD
$conexion = new mysqli("localhost", "root", "", "portal_estadias");

// ==========================================
// CREACIÓN AUTOMÁTICA Y GUARDADO DE PERIODO
// ==========================================
$crear_tabla = "CREATE TABLE IF NOT EXISTS configuracion_periodo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha_inicio DATETIME NOT NULL,
    fecha_fin DATETIME NOT NULL
)";
$conexion->query($crear_tabla);

$check_vacia = $conexion->query("SELECT id FROM configuracion_periodo LIMIT 1");
if ($check_vacia && $check_vacia->num_rows == 0) {
    $conexion->query("INSERT INTO configuracion_periodo (fecha_inicio, fecha_fin) VALUES (CURRENT_TIMESTAMP, DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 30 DAY))");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rango_fechas'])) {
    $fechas = explode(" a ", $_POST['rango_fechas']);
    if (count($fechas) == 2) {
        $inicio = $fechas[0] . " 00:00:00";
        $fin = $fechas[1] . " 23:59:59";
        $conexion->query("UPDATE configuracion_periodo SET fecha_inicio='$inicio', fecha_fin='$fin'");
        header("Location: panel.php");
        exit();
    }
}

// Obtener periodo actual
$res_periodo = $conexion->query("SELECT * FROM configuracion_periodo LIMIT 1");
$periodo_actual = $res_periodo->fetch_assoc();

// Lógica del Buscador
$busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';

if (!empty($busqueda)) {
    // FILTRO AVANZADO: Busca por Matrícula, Nombre o el Título/Nombre del archivo de proyecto.
    $busqueda_param = "%" . $busqueda . "%";
    $query = "
        SELECT a.*, e.id_entrega, e.fecha_subida, e.cuatrimestre_subido, e.programa_educativo_subido, e.nombre_archivo_subido, e.link_google_drive 
        FROM alumnos a 
        LEFT JOIN entregas e ON a.matricula = e.matricula_alumno 
        WHERE a.matricula LIKE ? 
           OR a.nombre_completo LIKE ? 
           OR e.nombre_archivo_subido LIKE ?
        ORDER BY a.matricula DESC";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("sss", $busqueda_param, $busqueda_param, $busqueda_param);
    $stmt->execute();
    $resultado = $stmt->get_result();
} else {
    $query = "SELECT a.*, e.id_entrega, e.fecha_subida, e.cuatrimestre_subido, e.programa_educativo_subido, e.nombre_archivo_subido, e.link_google_drive FROM alumnos a LEFT JOIN entregas e ON a.matricula = e.matricula_alumno ORDER BY a.matricula DESC";
    $resultado = $conexion->query($query);
}
