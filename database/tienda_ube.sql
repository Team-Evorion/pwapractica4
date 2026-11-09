-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 06-11-2026 a las 00:33:54
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
-- Base de datos: `tienda_ube`
--
CREATE DATABASE IF NOT EXISTS `tienda_ube` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `tienda_ube`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

DROP TABLE IF EXISTS `clientes`;
CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `direccion` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

DROP TABLE IF EXISTS `pedidos`;
CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

DROP TABLE IF EXISTS `productos`;
CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `imagen` varchar(255) NOT NULL,
  `vendedor_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `descripcion`, `precio`, `imagen`, `vendedor_id`) VALUES
(1, 'Laptop Lenovo IdeaPad', 'Computadora portátil para estudio, trabajo y navegación diaria.', 649.99, 'img/productos/laptop-lenovo-ideapad.jpeg', 1),
(2, 'Monitor Full HD', 'Monitor de 24 pulgadas con resolución Full HD y entrada HDMI.', 179.99, 'img/productos/monitor-full-hd.jpeg', 1),
(3, 'Audífonos Bluetooth', 'Audífonos inalámbricos con micrófono y batería recargable.', 44.99, 'img/productos/audifonos-bluetooth.jpeg', 2),
(4, 'Teclado mecánico', 'Teclado mecánico compacto con iluminación y conexión USB.', 59.99, 'img/productos/teclado-mecanico.jpeg', 2),
(5, 'Mouse gamer', 'Mouse óptico con iluminación RGB, diseño ergonómico y botones adicionales.', 34.99, 'img/productos/mouse-gamer.jfif', 2),
(6, 'Webcam Full HD', 'Cámara web Full HD 1080p con micrófono integrado y conexión USB.', 49.99, 'img/productos/webcam-full-hd.jfif', 1),
(7, 'Altavoz Bluetooth', 'Altavoz portátil inalámbrico con batería recargable y sonido de alta calidad.', 39.99, 'img/productos/altavoz-bluetooth.png', 2),
(8, 'Smartwatch', 'Reloj inteligente con notificaciones y seguimiento de actividad física.', 79.99, 'img/productos/smartwatch.jfif', 2),
(9, 'SSD de 1 TB', 'Unidad SSD M.2 NVMe de 1 TB con transferencia de datos de alta velocidad.', 89.99, 'img/productos/ssd-1tb.jpg', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('usuario','admin') NOT NULL DEFAULT 'usuario',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Cuentas académicas de demostración para los integrantes y el profesor
-- Las contraseñas se almacenan mediante password_hash() de PHP
--

INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `password`, `rol`) VALUES
(1, 'Jesús Rafael Fernández Pérez', 'jesus.fernandez@ube.edu.ec', '$2y$10$X5nOmD4juvQ3wE1ftY1ITeXRgfIS3dj8R/uDgiu28vSzHlO9PSJlS', 'usuario'),
(2, 'José Andrés Merlin Gamboa', 'jose.merlin@ube.edu.ec', '$2y$10$dn1/69qsMos20Dle2fdjbe2gTE8yMhb0Um4daaD.klD8xU7fUoDQq', 'usuario'),
(3, 'José Alberto Rosado Dominguez', 'jose.rosado@ube.edu.ec', '$2y$10$Ip6QG66nj72SN.9kkUONGO.I.CWRvPjSH7Hj51u4x.QX2eDrx4fMO', 'usuario'),
(4, 'Terry Steven Ponce Farías', 'terry.ponce@ube.edu.ec', '$2y$10$cN7ifz6dF9oIX6ld7LUtLOxtJoOW17BGwcSS.EKYWLQgH8ppZXj0q', 'usuario'),
(5, 'Elkin Daniel Velastegui Zambrano', 'elkin.velastegui@ube.edu.ec', '$2y$10$KYo2x9D1n5VB6My/515SKOqKUAwteBnBYBsu3x5VH60KMonESET7C', 'usuario'),
(6, 'Zahid Joel Zambrano Moreira', 'zahid.zambrano@ube.edu.ec', '$2y$10$M3FHIh.sr.Yo/ANWsem1SeqrEke4fL00EV.NjjXtq8O6fB.FG6SIW', 'usuario'),
(7, 'Administrador UBE', 'profesor.admin@ube.edu.ec', '$2y$10$Qc8xc9uNIN1IDQsmTVlx/u.rGSOuO5e1ONxAi.8/9jtRwi.lSlp5m', 'admin');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vendedores`
--

DROP TABLE IF EXISTS `vendedores`;
CREATE TABLE `vendedores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `telefono` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `vendedores`
--

INSERT INTO `vendedores` (`id`, `nombre`, `correo`, `telefono`) VALUES
(1, 'Tecnologia UBE', 'ventas@tecnologiaube.test', '0990000001'),
(2, 'Accesorios Digitales', 'ventas@accesoriosdigitales.test', '0990000002');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_clientes_correo` (`correo`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendedor_id` (`vendedor_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- Indices de la tabla `vendedores`
--
ALTER TABLE `vendedores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_vendedores_correo` (`correo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `vendedores`
--
ALTER TABLE `vendedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  ADD CONSTRAINT `pedidos_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`vendedor_id`) REFERENCES `vendedores` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
