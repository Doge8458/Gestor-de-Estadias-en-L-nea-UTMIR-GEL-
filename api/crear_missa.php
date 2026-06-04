<?php
// ----- AÑADE ESTAS DOS LÍNEAS AL INICIO -----
ini_set('display_errors', 1);
error_reporting(E_ALL);
// ---------------------------------------------

// --- HERRAMIENTA TEMPORAL PARA CREAR UN USUARIO DE PRUEBA ---

// 1. Datos de conexión
$servidor = "localhost";
$usuario_db = "root";
$password_db = "";
$nombre_db = "portal_estadias";

$conexion = new mysqli($servidor, $usuario_db, $password_db, $nombre_db);

if ($conexion->connect_error) {
    // Si la conexión falla, esto SÍ se mostrará
    die("Error de conexión: " . $conexion->connect_error);
}

// 2. --- DATOS DEL ALUMNO DE PRUEBA ---
$matricula_prueba = 2403017;
$curp_prueba = "RIBM050427HHGVSRA2"; // El CURP en texto plano
$nombre_prueba = "Missael";

// 3. ¡Importante! Ciframos el CURP (la contraseña)
$curp_hasheado = password_hash($curp_prueba, PASSWORD_DEFAULT);

// 4. Preparamos la consulta SQL para insertar
$stmt = $conexion->prepare("INSERT INTO alumnos (matricula, curp, nombre_completo) VALUES (?, ?, ?)");

// Verificamos si la preparación de la consulta falló (común si la tabla no existe)
if ($stmt === false) {
    die("Error al preparar la consulta: " . $conexion->error);
}

$stmt->bind_param("iss", $matricula_prueba, $curp_hasheado, $nombre_prueba); // "iss" = Integer, String, String

// 5. Ejecutamos y verificamos
if ($stmt->execute()) {
    echo "<h1>¡Éxito!</h1>";
    echo "Usuario de prueba creado correctamente.<br>";
    echo "<b>Matrícula:</b> " . $matricula_prueba . "<br>";
    echo "<b>Contraseña (CURP):</b> " . $curp_prueba . "<br>";
    echo "<p>Ya puedes ir a tu página de inicio e iniciar sesión.</p>";
} else {
    // Si hay un error en la ejecución, AHORA SÍ lo veremos
    echo "Error al crear el usuario: " . $stmt->error;
}

$stmt->close();
$conexion->close();

?>