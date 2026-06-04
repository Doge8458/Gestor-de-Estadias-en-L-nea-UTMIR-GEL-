CREATE DATABASE IF NOT EXISTS portal_estadias CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE portal_estadias;

CREATE TABLE IF NOT EXISTS alumnos (
    matricula INT(7) UNSIGNED NOT NULL,
    curp VARCHAR(255) NOT NULL,
    nombre_completo VARCHAR(100) NOT NULL,
    foto_perfil VARCHAR(255) DEFAULT NULL,
    programa_educativo VARCHAR(180) DEFAULT NULL,
    cuatrimestre VARCHAR(80) DEFAULT NULL,
    correo VARCHAR(160) DEFAULT NULL,
    estatus VARCHAR(40) DEFAULT 'activo',
    acreditado TINYINT(1) DEFAULT 0,
    video_visto TINYINT(1) DEFAULT 0,
    PRIMARY KEY (matricula)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS entregas (
    id_entrega INT AUTO_INCREMENT,
    matricula_alumno INT(7) UNSIGNED NOT NULL,
    nombre_archivo_subido VARCHAR(255) NOT NULL,
    cuatrimestre_subido VARCHAR(20) NOT NULL,
    programa_educativo_subido VARCHAR(100) NOT NULL,
    link_google_drive VARCHAR(512) NULL,
    fecha_subida DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_entrega),
    FOREIGN KEY (matricula_alumno)
    REFERENCES alumnos(matricula)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS configuracion_periodo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL
);

INSERT INTO configuracion_periodo (fecha_inicio, fecha_fin) 
VALUES ('2024-01-01', '2026-12-31');
