<?php
// Mostrar errores
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. Conexión a la Base de Datos
$servidor = "localhost";
$usuario_db = "root";
$password_db = "";
$nombre_db = "portal_estadias";

$conexion = new mysqli($servidor, $usuario_db, $password_db, $nombre_db);
if ($conexion->connect_error) { die("Fallo la conexión: " . $conexion->connect_error); }

// 2. DATOS DEL NUEVO USUARIO (INGENIERÍA)
$matricula = 2102244; 
$nombre = "Ingeniero de Prueba";
$curp_real = "ABC123456DEF789012";


$curp_hash = password_hash($curp_real, PASSWORD_DEFAULT);


$sql = "INSERT INTO alumnos (matricula, curp, nombre_completo) VALUES (?, ?, ?)";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("iss", $matricula, $curp_hash, $nombre);

if ($stmt->execute()) {
    echo "<h1>¡Usuario Creado con Éxito!</h1>";
    echo "<p>Ahora puedes probar el sistema con estos datos:</p>";
    echo "<ul>";
    echo "<li><b>Usuario:</b> $matricula</li>";
    echo "<li><b>Contraseña:</b> $curp_real</li>";
    echo "</ul>";
    echo "<a href='/ProyectoUSB/index.html'><button>Ir a Iniciar Sesión</button></a>";
} else {
    echo "Error al crear usuario (quizás la matrícula ya existe): " . $stmt->error;
}

$stmt->close();
$conexion->close();
?>