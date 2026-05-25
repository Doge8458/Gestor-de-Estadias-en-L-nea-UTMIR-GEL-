<?php
// admin/panel.php
session_start();

if (!isset($_SESSION['admin_logged'])) {
    header('Location: ../login_auth.php');
    exit();
}

$conexion = new mysqli("localhost", "root", "", "portal_estadias");
if ($conexion->connect_error) {
    die("Error crítico: No se pudo conectar a la base de datos.");
}

$busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';

if (!empty($busqueda)) {
    $busqueda_param = "%" . $busqueda . "%";
    // BÚSQUEDA PROFUNDA: Revisa texto del PDF (contenido_texto)
    $query = "
        SELECT a.*, e.id_entrega, e.fecha_subida, e.cuatrimestre_subido, e.programa_educativo_subido, e.nombre_archivo_subido, e.link_google_drive, e.contenido_texto 
        FROM alumnos a 
        LEFT JOIN entregas e ON a.matricula = e.matricula_alumno 
        WHERE a.matricula LIKE ? 
           OR a.nombre_completo LIKE ? 
           OR e.nombre_archivo_subido LIKE ?
           OR e.contenido_texto LIKE ?
        ORDER BY a.matricula DESC";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("ssss", $busqueda_param, $busqueda_param, $busqueda_param, $busqueda_param);
    $stmt->execute();
    $resultado = $stmt->get_result();
} else {
    $query = "
        SELECT a.*, e.id_entrega, e.fecha_subida, e.cuatrimestre_subido, e.programa_educativo_subido, e.nombre_archivo_subido, e.link_google_drive, e.contenido_texto 
        FROM alumnos a 
        LEFT JOIN entregas e ON a.matricula = e.matricula_alumno 
        ORDER BY a.matricula DESC";
    $resultado = $conexion->query($query);
}

require_once __DIR__ . '/views/panel.view.php';
?>