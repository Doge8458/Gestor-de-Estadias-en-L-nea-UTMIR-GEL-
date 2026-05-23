<?php
// 1. SESIÓN Y CONEXIÓN 
session_start();
if (!isset($_SESSION['matricula'])) { header("Location: index.html"); exit(); }
$matricula_alumno = $_SESSION['matricula'];
$nombre_alumno = $_SESSION['nombre'];
$servidor = "localhost"; $usuario_db = "root"; $password_db = ""; $nombre_db = "portal_estadias";
$conexion = new mysqli($servidor, $usuario_db, $password_db, $nombre_db);
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
$stmt_acred = $conexion->prepare("SELECT acreditado, video_visto FROM alumnos WHERE matricula = ?");
$stmt_acred->bind_param("i", $matricula_alumno); 
$stmt_acred->execute();
$stmt_acred->bind_result($acreditado, $video_visto);
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
$haTerminadoTodo = ($yaSubioTSU && $yaSubioING);
require __DIR__ . '/views/dashboard.view.php';
