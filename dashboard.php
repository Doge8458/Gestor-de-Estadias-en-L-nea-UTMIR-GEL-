<?php
// 1. SESIÓN Y CONEXIÓN 
session_start();
if (!isset($_SESSION['matricula'])) { header("Location: index.html"); exit(); }
$matricula_alumno = $_SESSION['matricula'];
$nombre_alumno = $_SESSION['nombre'];
$servidor = "localhost"; $usuario_db = "root"; $password_db = ""; $nombre_db = "portal_estadias";
$conexion = new mysqli($servidor, $usuario_db, $password_db, $nombre_db);
$columnaFoto = $conexion->query("SHOW COLUMNS FROM alumnos LIKE 'foto_perfil'");
if ($columnaFoto && $columnaFoto->num_rows === 0) {
    $conexion->query("ALTER TABLE alumnos ADD COLUMN foto_perfil VARCHAR(255) DEFAULT NULL");
}
foreach ([
    'programa_educativo' => 'VARCHAR(180) DEFAULT NULL',
    'cuatrimestre' => 'VARCHAR(80) DEFAULT NULL',
    'correo' => 'VARCHAR(160) DEFAULT NULL',
    'acreditado' => 'TINYINT(1) DEFAULT 0',
    'video_visto' => 'TINYINT(1) DEFAULT 0'
] as $columna => $definicion) {
    $columnaSegura = $conexion->real_escape_string($columna);
    $existeColumna = $conexion->query("SHOW COLUMNS FROM alumnos LIKE '$columnaSegura'");
    if ($existeColumna && $existeColumna->num_rows === 0) {
        $conexion->query("ALTER TABLE alumnos ADD COLUMN $columna $definicion");
    }
}
require_once __DIR__ . '/api/notificaciones_helpers.php';
limpiarNotificacionesVistas($conexion);
$entrega_tsu = null; $entrega_ing = null;
$stmt = $conexion->prepare("SELECT * FROM entregas WHERE matricula_alumno = ?");
$stmt->bind_param("i", $matricula_alumno);
$stmt->execute();
$resultado = $stmt->get_result();
while ($fila = $resultado->fetch_assoc()) {
    if (strpos($fila['cuatrimestre_subido'], '6º') !== false) { $entrega_tsu = $fila; }
    if (strpos($fila['cuatrimestre_subido'], '10º') !== false) { $entrega_ing = $fila; }
}
$stmt->close();

// NUEVO CODIGO: Verificar si el alumno ya esta acredita para la subida de archivos.
$acreditado = 0;
$stmt_acred = $conexion->prepare("SELECT acreditado FROM alumnos WHERE matricula = ?");
$stmt_acred->bind_param("i", $matricula_alumno); // <-- Aquí estaba el detalle
$stmt_acred->execute();
$stmt_acred->bind_result($acreditado);
$stmt_acred->fetch();
$stmt_acred->close();

// NUEVO CODIGO: Verificar si el alumno ya esta acreditado y si ya vio el video
$acreditado = 0;
$video_visto = 0;
$foto_perfil = null;
$programa_educativo_alumno = '';
$cuatrimestre_alumno = '';
$stmt_acred = $conexion->prepare("SELECT acreditado, video_visto, foto_perfil, programa_educativo, cuatrimestre FROM alumnos WHERE matricula = ?");
$stmt_acred->bind_param("i", $matricula_alumno); 
$stmt_acred->execute();
$stmt_acred->bind_result($acreditado, $video_visto, $foto_perfil, $programa_educativo_alumno, $cuatrimestre_alumno);
$stmt_acred->fetch();
$stmt_acred->close();

// 2. OBTENCIÓN DEL PERIODO CONFIGURADO
$res_periodo = $conexion->query("SELECT * FROM configuracion_periodo LIMIT 1");
$periodo_actual = $res_periodo ? $res_periodo->fetch_assoc() : null;
$fecha_inicio = $periodo_actual ? $periodo_actual['fecha_inicio'] : '';
$fecha_fin = $periodo_actual ? $periodo_actual['fecha_fin'] : '';
$ahora = time();
$inicio_ts = strtotime($fecha_inicio);
$fin_ts = strtotime($fecha_fin);

// Variable que dicta si el estudiante puede subir archivos
$periodo_activo = ($ahora >= $inicio_ts && $ahora <= $fin_ts);

$notificaciones_alumno = [];
$notificaciones_sin_leer = 0;
$stmt_notif = $conexion->prepare("SELECT id_notificacion, tipo, asunto, motivo, comentario, detalle, mensaje, leida, fecha_creacion, fecha_vista FROM notificaciones WHERE matricula_alumno = ? AND (leida = 0 OR fecha_vista >= DATE_SUB(NOW(), INTERVAL 3 DAY)) ORDER BY leida ASC, fecha_creacion DESC");
$stmt_notif->bind_param("i", $matricula_alumno);
$stmt_notif->execute();
$resultado_notif = $stmt_notif->get_result();
while ($notificacion = $resultado_notif->fetch_assoc()) {
    if ((int)$notificacion['leida'] === 0) {
        $notificaciones_sin_leer++;
    }
    $notificaciones_alumno[] = $notificacion;
}
$stmt_notif->close();

$conexion->close();

$yaSubioTSU = ($entrega_tsu != null);
$yaSubioING = ($entrega_ing != null);

function inferirProcesoDocumento($programa, $cuatrimestre) {
    $texto = mb_strtolower(($programa ?? '') . ' ' . ($cuatrimestre ?? ''), 'UTF-8');
    if (strpos($texto, 'licenciatura') !== false || strpos($texto, 'ingenier') !== false || strpos($texto, '10') !== false || strpos($texto, '11') !== false) {
        return '10º cuatrimestre (Ingeniería/Licenciatura)';
    }
    return '6º cuatrimestre (Técnico Superior Universitario)';
}

$proceso_subida = inferirProcesoDocumento($programa_educativo_alumno, $cuatrimestre_alumno);
$yaSubioDocumentoRequerido = (strpos($proceso_subida, '10') !== false) ? $yaSubioING : $yaSubioTSU;
$haTerminadoTodo = $yaSubioDocumentoRequerido;
require __DIR__ . '/views/dashboard.view.php';
