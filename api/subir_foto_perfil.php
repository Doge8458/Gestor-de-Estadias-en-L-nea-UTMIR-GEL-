<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['matricula'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesion expirada.']);
    exit();
}

if (!isset($_FILES['foto_perfil']) || $_FILES['foto_perfil']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'Selecciona una imagen valida.']);
    exit();
}

$archivo = $_FILES['foto_perfil'];
$maxBytes = 3 * 1024 * 1024;

if ($archivo['size'] > $maxBytes) {
    echo json_encode(['status' => 'error', 'message' => 'La imagen no debe superar 3MB.']);
    exit();
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($archivo['tmp_name']);
$extensiones = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp'
];

if (!isset($extensiones[$mime])) {
    echo json_encode(['status' => 'error', 'message' => 'Solo se permiten imagenes JPG, PNG o WEBP.']);
    exit();
}

$matricula = (int)$_SESSION['matricula'];
$directorio = __DIR__ . '/../uploads/perfiles';

if (!is_dir($directorio) && !mkdir($directorio, 0755, true)) {
    echo json_encode(['status' => 'error', 'message' => 'No se pudo preparar la carpeta de perfiles.']);
    exit();
}

$extension = $extensiones[$mime];
$nombreArchivo = 'perfil_' . $matricula . '_' . time() . '.' . $extension;
$rutaDestino = $directorio . '/' . $nombreArchivo;
$rutaPublica = 'uploads/perfiles/' . $nombreArchivo;

if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
    echo json_encode(['status' => 'error', 'message' => 'No se pudo guardar la imagen.']);
    exit();
}

$conexion = new mysqli("localhost", "root", "", "portal_estadias");
if ($conexion->connect_error) {
    @unlink($rutaDestino);
    echo json_encode(['status' => 'error', 'message' => 'Error de conexion a la base de datos.']);
    exit();
}

$columnaFoto = $conexion->query("SHOW COLUMNS FROM alumnos LIKE 'foto_perfil'");
if ($columnaFoto && $columnaFoto->num_rows === 0) {
    $conexion->query("ALTER TABLE alumnos ADD COLUMN foto_perfil VARCHAR(255) DEFAULT NULL");
}

$fotoAnterior = null;
$stmtAnterior = $conexion->prepare("SELECT foto_perfil FROM alumnos WHERE matricula = ?");
$stmtAnterior->bind_param("i", $matricula);
$stmtAnterior->execute();
$stmtAnterior->bind_result($fotoAnterior);
$stmtAnterior->fetch();
$stmtAnterior->close();

$stmt = $conexion->prepare("UPDATE alumnos SET foto_perfil = ? WHERE matricula = ?");
$stmt->bind_param("si", $rutaPublica, $matricula);
$ok = $stmt->execute();
$stmt->close();
$conexion->close();

if (!$ok) {
    @unlink($rutaDestino);
    echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar tu perfil.']);
    exit();
}

if ($fotoAnterior && strpos($fotoAnterior, 'uploads/perfiles/') === 0) {
    $rutaAnterior = __DIR__ . '/../' . $fotoAnterior;
    if (is_file($rutaAnterior)) {
        @unlink($rutaAnterior);
    }
}

echo json_encode([
    'status' => 'success',
    'message' => 'Foto de perfil actualizada.',
    'foto' => $rutaPublica
]);
