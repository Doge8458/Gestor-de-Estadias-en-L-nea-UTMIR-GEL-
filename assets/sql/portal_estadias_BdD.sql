-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 05-05-2026 a las 17:57:03
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `portal_estadias`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alumnos`
--

CREATE TABLE `alumnos` (
  `matricula` int(7) UNSIGNED NOT NULL,
  `curp` varchar(500) NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `acreditado` tinyint(1) DEFAULT 0,
  `video_visto` tinyint(1) DEFAULT 0,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `programa_educativo` varchar(180) DEFAULT NULL,
  `cuatrimestre` varchar(80) DEFAULT NULL,
  `correo` varchar(160) DEFAULT NULL,
  `estatus` varchar(40) DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `alumnos`
--

INSERT INTO `alumnos` (`matricula`, `curp`, `nombre_completo`, `acreditado`, `video_visto`) VALUES
(2102244, '$2y$10$4JLsj3akqSixC5cJmnk31e20uvatUdckgjBTgQRbtTAIqAZa7Yg8W', 'Ingeniero de Prueba', 1, 0),
(2403322, '$2y$10$IcEU4cxHcPBBkeD09uemdeO5J7ei/7Z6Ri0dT111C2P.dR4mGV.Pm', 'Alumno de Prueba', 1, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion_periodo`
--

CREATE TABLE `configuracion_periodo` (
  `id` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `configuracion_periodo`
--

INSERT INTO `configuracion_periodo` (`id`, `fecha_inicio`, `fecha_fin`) VALUES
(1, '2026-05-04', '2026-08-31');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entregas`
--

CREATE TABLE `entregas` (
  `id_entrega` int(11) NOT NULL,
  `matricula_alumno` int(7) UNSIGNED NOT NULL,
  `nombre_archivo_subido` varchar(255) NOT NULL,
  `cuatrimestre_subido` varchar(20) NOT NULL,
  `programa_educativo_subido` varchar(100) NOT NULL,
  `link_google_drive` varchar(512) DEFAULT NULL,
  `fecha_subida` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `alumnos`
--
ALTER TABLE `alumnos`
  ADD PRIMARY KEY (`matricula`);

--
-- Indices de la tabla `configuracion_periodo`
--
ALTER TABLE `configuracion_periodo`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `entregas`
--
ALTER TABLE `entregas`
  ADD PRIMARY KEY (`id_entrega`),
  ADD KEY `matricula_alumno` (`matricula_alumno`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `configuracion_periodo`
--
ALTER TABLE `configuracion_periodo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `entregas`
--
ALTER TABLE `entregas`
  MODIFY `id_entrega` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `entregas`
--
ALTER TABLE `entregas`
  ADD CONSTRAINT `entregas_ibfk_1` FOREIGN KEY (`matricula_alumno`) REFERENCES `alumnos` (`matricula`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
