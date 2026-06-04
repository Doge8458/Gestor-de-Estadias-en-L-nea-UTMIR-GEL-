<?php
// ----- MOSTRAR ERRORES -----
ini_set('display_errors', 1);
error_reporting(E_ALL);
// ---------------------------

// --- HERRAMIENTA TEMPORAL PARA BORRAR UN USUARIO DE PRUEBA ---

// 1. Datos de conexión (los mismos de XAMPP)
$servidor = "localhost";
$usuario_db = "root";
$password_db = "";
$nombre_db = "portal_estadias";

$conexion = new mysqli($servidor, $usuario_db, $password_db, $nombre_db);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// 2. --- DATOS DEL ALUMNO A BORRAR ---
// Solo necesitamos la matrícula para identificarlo
$matricula_prueba = 2403322;

// 3. Preparamos la consulta SQL para ELIMINAR (DELETE)
$stmt = $conexion->prepare("DELETE FROM alumnos WHERE matricula = ?");

if ($stmt === false) {
    die("Error al preparar la consulta: " . $conexion->error);
}

// Vinculamos el parámetro ("i" de Integer porque la matrícula es un número)
$stmt->bind_param("i", $matricula_prueba);

// 4. Ejecutamos y verificamos
if ($stmt->execute()) {
    // affected_rows nos dice cuántas filas se borraron realmente
    if ($stmt->affected_rows > 0) {
        echo "<h1>¡Éxito!</h1>";
        echo "Usuario de prueba con matrícula <b>" . $matricula_prueba . "</b> eliminado correctamente de la base de datos.<br>";
    } else {
        echo "<h1>Aviso</h1>";
        echo "La consulta se ejecutó bien, pero <b>no se encontró</b> la matrícula " . $matricula_prueba . " en la tabla. Es posible que ya haya sido borrada antes.<br>";
    }
} else {
    echo "Error al eliminar el usuario: " . $stmt->error;
}

$stmt->close();
$conexion->close();

?>