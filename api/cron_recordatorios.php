<?php
// api/cron_recordatorios.php
// SCRIPT DE PRODUCCIÓN: Envío automatizado basado en periodo

require_once __DIR__ . '/mailer_helpers.php';

$conexion = new mysqli("localhost", "root", "", "portal_estadias");
if ($conexion->connect_error) {
    die("Error de conexión a la BD.");
}

// 1. Verificar si estamos dentro del periodo de NOTIFICACIONES
$queryNotif = "SELECT fecha_inicio, fecha_fin FROM configuracion_notificaciones LIMIT 1";
$resNotif = $conexion->query($queryNotif);

if (!$resNotif || $resNotif->num_rows === 0) {
    die("No hay configuración de notificaciones.");
}

$perNotif = $resNotif->fetch_assoc();
$ahora = time();

// Si no estamos en el periodo habilitado por el administrador, el Cron se detiene silenciosamente
if ($ahora < strtotime($perNotif['fecha_inicio']) || $ahora > strtotime($perNotif['fecha_fin'])) {
    die("Fuera de periodo de notificaciones. Ejecución cancelada.");
}

// 2. Calcular tiempo restante de entregas para el contador visual
$queryEntregas = "SELECT fecha_fin FROM configuracion_periodo LIMIT 1";
$resEntregas = $conexion->query($queryEntregas);
$dias = $horas = $mins = 0;

if ($resEntregas && $resEntregas->num_rows > 0) {
    $perEntregas = $resEntregas->fetch_assoc();
    $finEstadias = strtotime($perEntregas['fecha_fin']);
    $diferencia = $finEstadias - $ahora;

    if ($diferencia > 0) {
        $dias = floor($diferencia / (60 * 60 * 24));
        $horas = floor(($diferencia % (60 * 60 * 24)) / (60 * 60));
        $mins = floor(($diferencia % (60 * 60)) / 60);
    }
}

$htmlContador = "
<div style='display: table; margin: 20px auto; text-align: center; border-spacing: 10px;'>
    <div style='display: table-cell; background: #1a202c; color: #48bb78; padding: 15px 20px; border-radius: 8px; width: 60px;'>
        <div style='font-size: 28px; font-weight: 800;'>{$dias}</div><div style='font-size: 11px;'>DÍAS</div>
    </div>
    <div style='display: table-cell; background: #1a202c; color: #48bb78; padding: 15px 20px; border-radius: 8px; width: 60px;'>
        <div style='font-size: 28px; font-weight: 800;'>{$horas}</div><div style='font-size: 11px;'>HRS</div>
    </div>
    <div style='display: table-cell; background: #1a202c; color: #48bb78; padding: 15px 20px; border-radius: 8px; width: 60px;'>
        <div style='font-size: 28px; font-weight: 800;'>{$mins}</div><div style='font-size: 11px;'>MINS</div>
    </div>
</div>";

// 3. Buscar a todos los alumnos que NO han entregado su memoria
$queryAlumnos = "
    SELECT a.matricula, a.nombre_completo, a.correo 
    FROM alumnos a
    LEFT JOIN entregas e ON a.matricula = e.matricula
    WHERE e.id_entrega IS NULL AND a.correo IS NOT NULL AND a.correo != ''
";
$resultado = $conexion->query($queryAlumnos);
$contadorEnvios = 0;

while ($alumno = $resultado->fetch_assoc()) {
    $asunto = "⚠️ Urgente: Cierre de Plataforma GEL en $dias días";
    $cuerpoHtml = "
    <div style='font-family: Montserrat, Arial, sans-serif; padding: 25px; color: #2d3748; background-color: #f7fafc;'>
        <div style='background-color: #ffffff; padding: 20px; border-radius: 8px; border-top: 5px solid #e53e3e; box-shadow: 0 4px 6px rgba(0,0,0,0.05); max-width: 600px; margin: 0 auto; text-align: center;'>
            <h2 style='color: #e53e3e;'>Aviso de Cierre Próximo</h2>
            <p style='text-align: left;'>Hola <strong>" . htmlspecialchars($alumno['nombre_completo']) . "</strong>,</p>
            <p style='text-align: left;'>Este es un recordatorio automático. Aún no has subido tu Memoria Técnica de Estadías. Si no realizas la carga antes del cierre, tu acceso se bloqueará permanentemente.</p>
            <h3 style='color: #2d3748; margin-top: 30px;'>Tiempo Restante:</h3>
            {$htmlContador}
            <a href='http://localhost/Proyecto_GEL/login' style='display: inline-block; background-color: #e53e3e; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-weight: bold; margin-top: 20px;'>Ingresar a la Plataforma</a>
        </div>
    </div>";

    if (enviarCorreoGmail($alumno['correo'], $asunto, $cuerpoHtml)) {
        $contadorEnvios++;
    }
}
echo "Proceso finalizado. Correos despachados: $contadorEnvios.";
?>