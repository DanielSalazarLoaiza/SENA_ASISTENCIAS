-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 18-03-2025 a las 10:20:47
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
-- Base de datos: `sena_asistencias`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ambientes`
--

CREATE TABLE `ambientes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `centro_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ambientes`
--

INSERT INTO `ambientes` (`id`, `nombre`, `centro_id`) VALUES
(1, 'Sistemas 1', 2),
(2, 'Apoyo 3', 2),
(3, 'Ejemplo', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aprendices`
--

CREATE TABLE `aprendices` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `ficha_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `aprendices`
--

INSERT INTO `aprendices` (`id`, `nombre`, `ficha_id`) VALUES
(1, 'Daniel Salazar Loaiza', 2),
(2, 'Miguel Angel Jaramillo Garzon', 2),
(3, 'Andres Felipe Rivera Sanchez', 2),
(4, 'Miguel Angel Higuita', 2),
(5, 'Jhoan Sebastian Zuluaga ', 2),
(6, 'Mateo Garcia ', 2),
(7, 'Mario ', 2),
(8, 'Ferney Arias', 2),
(9, 'Julian David Marulanda Leon', 2),
(10, 'Brandon Restrepo', 2),
(11, 'Mateito', 2),
(12, 'Geraldine', 2),
(13, 'Alejo', 2),
(14, 'Miguel Higuita', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencias`
--

CREATE TABLE `asistencias` (
  `id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `aprendiz_id` int(11) DEFAULT NULL,
  `asistio` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `asistencias`
--

INSERT INTO `asistencias` (`id`, `fecha`, `aprendiz_id`, `asistio`) VALUES
(1, '2025-03-15', 1, 0),
(2, '2025-03-16', 1, 0),
(3, '2025-03-17', 1, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `centros`
--

CREATE TABLE `centros` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `regional_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `centros`
--

INSERT INTO `centros` (`id`, `nombre`, `regional_id`) VALUES
(1, 'Llave inglesa', 1),
(2, 'Centro de Procesos Industriales', 2),
(3, 'Centros Medallo', 3),
(4, 'Cafetera', 2),
(5, 'Automatizacion', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fichas`
--

CREATE TABLE `fichas` (
  `id` int(11) NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `programa_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `fichas`
--

INSERT INTO `fichas` (`id`, `codigo`, `programa_id`) VALUES
(1, '32434334', 1),
(2, '2873707', 2),
(3, '2873711', 3),
(4, '1234567', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instructores_programas`
--

CREATE TABLE `instructores_programas` (
  `id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `programa_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `instructores_programas`
--

INSERT INTO `instructores_programas` (`id`, `instructor_id`, `programa_id`) VALUES
(1, 13, 3),
(2, 14, 2),
(3, 15, 4),
(4, 16, 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instructores_programas_backup`
--

CREATE TABLE `instructores_programas_backup` (
  `id` int(11) NOT NULL DEFAULT 0,
  `instructor_id` int(11) NOT NULL,
  `programa_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `programas_formacion`
--

CREATE TABLE `programas_formacion` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `centro_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `programas_formacion`
--

INSERT INTO `programas_formacion` (`id`, `nombre`, `centro_id`) VALUES
(1, 'Llave inglesa', 1),
(2, 'Analisis y Desarrollo de Software', 2),
(3, 'Ingles', 2),
(4, 'Ejemplo', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `regionales`
--

CREATE TABLE `regionales` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `regionales`
--

INSERT INTO `regionales` (`id`, `nombre`) VALUES
(1, 'Martillo'),
(2, 'Caldas'),
(3, 'Antioquia'),
(4, 'La Dorada'),
(5, 'Cundinamarca');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','coordinator','instructor') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'miguel13', '$2y$10$dy4WoqATHFkEui2JHm7/ye6XZLCxf/zfGh9D9P1OZSoQ3PFXaqU36', 'super_admin'),
(2, 'miguel12', '$2y$10$8wwVKlqpIBQ/Sd6ttSOJAey6AnxCozLhEJJerNjSB71JrtQaNSjAe', 'super_admin'),
(3, 'miguel13', '$2y$10$YA6OUB.cwAjOIPbbcO3oROoXUjA9Qz.On12dtmbsSGQHm2dgbBDdC', 'coordinator'),
(4, 'miguel13', '$2y$10$ByvrrbfgF6E9cu.5oqtSZeV2rD3M/HImTrwNfKzAGW3bscnEzJyLu', 'coordinator'),
(5, 'miguel123', '$2y$10$SZy.8sKI0MFEJ0PF3wQ93OEsBRRw1PTs1Xa7re8hneEpkgEAVDs2u', 'instructor'),
(6, 'hola12', '$2y$10$Kr/j4PBcd0AQQ0P7oGN2OePTlFZgxe2Twxxqin.NFFSSHsmPtzUfO', 'coordinator'),
(7, 'ramon', '$2y$10$j9zDk8MwC1QzsY9l14tfb.6qLpHh9N/g9pKuVIMURUBIrURx8KGlm', 'instructor'),
(8, 'carlos12', '$2y$10$Ni8OxkWtK9vnvBc8g8/vjOZIh.C5mf9aBLJ.TDzm7jZ4lrjDb56Hq', 'instructor'),
(9, 'sasa', '$2y$10$dSuCWiUMpXrAlokyt/UejOs8iWAPheVp2m/gWM.nQCuvPtvXtudCW', 'coordinator'),
(10, 'slzr', '$2y$10$BcSe8bo8yKsS6yiwCUzsTOIHr2wos7xeLTPmfEfObhurm0wctdli2', 'super_admin'),
(11, 'coordinador', '$2y$10$/4mGVAbhLjs1v5aF9QT2huf5ZHv7xYs.Q/v8FQDgToGFSac63kXQS', 'coordinator'),
(12, 'instructor', '$2y$10$qDB.BiiGmWY4kHpMIwvjVuGlpqyvmedO7LVwCxWyVFyRUBkq19YuC', 'instructor'),
(13, 'instru', '$2y$10$NnchV.Egm6VrgRBvt3LPje55aJnIukc1t0NnAXDYlBjy6HSbtp422', 'instructor'),
(14, 'jusapi', '$2y$10$wOuh0lucWsc4yOwqruwr/OLj7e0RfMDqHY0Roquj4aeBRYLR8kNPi', 'instructor'),
(15, 'higuita', '$2y$10$bZT2VKycRrsoStQmU1GJXOkicacJT9IXDrtGAZyHROkOU/GL7Rdaq', 'instructor'),
(16, 'chinchi', '$2y$10$bW8lsOmZxavJ5SPaxxsgXu4iAz9g9KzSxCqgsOYpYzhso3MLj9n3.', 'instructor');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `ambientes`
--
ALTER TABLE `ambientes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `centro_id` (`centro_id`);

--
-- Indices de la tabla `aprendices`
--
ALTER TABLE `aprendices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ficha_id` (`ficha_id`);

--
-- Indices de la tabla `asistencias`
--
ALTER TABLE `asistencias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `aprendiz_id` (`aprendiz_id`);

--
-- Indices de la tabla `centros`
--
ALTER TABLE `centros`
  ADD PRIMARY KEY (`id`),
  ADD KEY `regional_id` (`regional_id`);

--
-- Indices de la tabla `fichas`
--
ALTER TABLE `fichas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `programa_id` (`programa_id`);

--
-- Indices de la tabla `instructores_programas`
--
ALTER TABLE `instructores_programas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `instructor_id` (`instructor_id`),
  ADD KEY `programa_id` (`programa_id`);

--
-- Indices de la tabla `programas_formacion`
--
ALTER TABLE `programas_formacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `centro_id` (`centro_id`);

--
-- Indices de la tabla `regionales`
--
ALTER TABLE `regionales`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `ambientes`
--
ALTER TABLE `ambientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `aprendices`
--
ALTER TABLE `aprendices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `asistencias`
--
ALTER TABLE `asistencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `centros`
--
ALTER TABLE `centros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `fichas`
--
ALTER TABLE `fichas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `instructores_programas`
--
ALTER TABLE `instructores_programas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `programas_formacion`
--
ALTER TABLE `programas_formacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `regionales`
--
ALTER TABLE `regionales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `ambientes`
--
ALTER TABLE `ambientes`
  ADD CONSTRAINT `ambientes_ibfk_1` FOREIGN KEY (`centro_id`) REFERENCES `centros` (`id`);

--
-- Filtros para la tabla `aprendices`
--
ALTER TABLE `aprendices`
  ADD CONSTRAINT `aprendices_ibfk_1` FOREIGN KEY (`ficha_id`) REFERENCES `fichas` (`id`);

--
-- Filtros para la tabla `asistencias`
--
ALTER TABLE `asistencias`
  ADD CONSTRAINT `asistencias_ibfk_1` FOREIGN KEY (`aprendiz_id`) REFERENCES `aprendices` (`id`);

--
-- Filtros para la tabla `centros`
--
ALTER TABLE `centros`
  ADD CONSTRAINT `centros_ibfk_1` FOREIGN KEY (`regional_id`) REFERENCES `regionales` (`id`);

--
-- Filtros para la tabla `fichas`
--
ALTER TABLE `fichas`
  ADD CONSTRAINT `fichas_ibfk_1` FOREIGN KEY (`programa_id`) REFERENCES `programas_formacion` (`id`);

--
-- Filtros para la tabla `instructores_programas`
--
ALTER TABLE `instructores_programas`
  ADD CONSTRAINT `instructores_programas_ibfk_1` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `instructores_programas_ibfk_2` FOREIGN KEY (`programa_id`) REFERENCES `programas_formacion` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `programas_formacion`
--
ALTER TABLE `programas_formacion`
  ADD CONSTRAINT `programas_formacion_ibfk_1` FOREIGN KEY (`centro_id`) REFERENCES `centros` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
