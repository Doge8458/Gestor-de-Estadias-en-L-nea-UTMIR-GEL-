USE portal_estadias;

CREATE TABLE IF NOT EXISTS notificaciones (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
