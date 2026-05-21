<?php
function asegurarTablaNotificaciones(mysqli $conexion): void {
    $sql = "CREATE TABLE IF NOT EXISTS notificaciones (
        id_notificacion INT AUTO_INCREMENT PRIMARY KEY,
        matricula_alumno INT(7) UNSIGNED NOT NULL,
        tipo ENUM('eliminacion', 'general') NOT NULL DEFAULT 'general',
        asunto VARCHAR(150) NOT NULL,
        motivo VARCHAR(150) NULL,
        comentario TEXT NULL,
        detalle TEXT NULL,
        mensaje TEXT NOT NULL,
        leida TINYINT(1) NOT NULL DEFAULT 0,
        fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        fecha_vista DATETIME NULL,
        fecha_expira DATETIME NULL,
        INDEX idx_notificaciones_alumno (matricula_alumno, leida, fecha_creacion),
        INDEX idx_notificaciones_limpieza (leida, fecha_vista),
        CONSTRAINT fk_notificaciones_alumnos
            FOREIGN KEY (matricula_alumno) REFERENCES alumnos(matricula)
            ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $conexion->query($sql);
}

function limpiarNotificacionesVistas(mysqli $conexion): void {
    asegurarTablaNotificaciones($conexion);
    $conexion->query("DELETE FROM notificaciones WHERE leida = 1 AND fecha_vista IS NOT NULL AND fecha_vista <= DATE_SUB(NOW(), INTERVAL 3 DAY)");
}

function crearNotificacion(
    mysqli $conexion,
    int $matricula,
    string $tipo,
    string $asunto,
    ?string $motivo,
    ?string $comentario,
    ?string $detalle,
    string $mensaje
): bool {
    asegurarTablaNotificaciones($conexion);

    $stmt = $conexion->prepare("INSERT INTO notificaciones (matricula_alumno, tipo, asunto, motivo, comentario, detalle, mensaje) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("issssss", $matricula, $tipo, $asunto, $motivo, $comentario, $detalle, $mensaje);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}
?>
