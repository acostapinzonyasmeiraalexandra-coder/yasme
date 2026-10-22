-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 14-10-2026 a las 02:47:10
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
-- Base de datos: `supermercado`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `cedula_cliente` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `direccion` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`cedula_cliente`, `nombre`, `telefono`, `email`, `direccion`) VALUES
(12345, 'yakis', '3434555', 'yakelinequintero2707@gmail.com', 'ttttt'),
(309499, 'andres', '2333333', 'andres@gmail.com', 'mz23patios');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_salida`
--

CREATE TABLE `detalle_salida` (
  `id_detalle` int(11) NOT NULL,
  `cod_movimiento` int(10) UNSIGNED NOT NULL,
  `cod_producto` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `nombre_producto` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `factura_salida`
--

CREATE TABLE `factura_salida` (
  `cod_movimiento` int(10) UNSIGNED NOT NULL,
  `fecha` date NOT NULL DEFAULT curdate(),
  `nota` varchar(255) DEFAULT NULL,
  `cedula_cliente` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `factura_salida`
--

INSERT INTO `factura_salida` (`cod_movimiento`, `fecha`, `nota`, `cedula_cliente`) VALUES
(21, '2026-10-09', '', 12345),
(22, '2026-10-09', '', 12345),
(23, '2026-10-09', '', 12345),
(25, '2026-10-09', '', 12345),
(26, '2026-10-09', '', 12345),
(27, '2026-10-12', '', 12345);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario`
--

CREATE TABLE `inventario` (
  `cod_inventario` int(11) NOT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `cod_producto` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mov_entrada`
--

CREATE TABLE `mov_entrada` (
  `cod_movimiento` int(11) NOT NULL,
  `fecha` date DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `nota` varchar(100) DEFAULT NULL,
  `cod_proveedor` int(11) DEFAULT NULL,
  `cod_producto` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mov_entrada`
--

INSERT INTO `mov_entrada` (`cod_movimiento`, `fecha`, `precio`, `cantidad`, `nota`, `cod_proveedor`, `cod_producto`) VALUES
(1, '2026-10-09', 20000.00, 82, 'ee', 11, 11),
(15, '2026-10-12', 1000000.00, 20, 'unidades', 15, 15);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mov_salida`
--

CREATE TABLE `mov_salida` (
  `cod_movimiento` int(11) NOT NULL,
  `fecha` date DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `nota` varchar(100) DEFAULT NULL,
  `cod_producto` int(11) DEFAULT NULL,
  `cedula_cliente` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `cod_producto` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `categoria` varchar(500) DEFAULT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  `cod_proveedor` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`cod_producto`, `nombre`, `precio`, `categoria`, `descripcion`, `cod_proveedor`) VALUES
(11, 'arroz', 30000.00, 'abarrotes', 'en buen estado', 11),
(12, 'higado', 40000.00, 'carnes', 'en buen estado', 11),
(13, 'fresas', 50000.00, 'frutas', 'en buen estado', 11),
(15, 'coca-cola', 3500.00, 'carnes', 'coacola', 15);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedor`
--

CREATE TABLE `proveedor` (
  `cod_proveedor` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `direccion` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proveedor`
--

INSERT INTO `proveedor` (`cod_proveedor`, `nombre`, `telefono`, `email`, `direccion`) VALUES
(11, 'yasme', '3239887653', 'yasme@gmail.com', 'mz lote 34 san juanito'),
(15, 'coca-cola', '3118776225', 'cocacola@gmail.com', 'patios');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_usuario`
--

CREATE TABLE `tipo_usuario` (
  `id_tipo_usuario` int(11) NOT NULL,
  `tipo_usu` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_usuario`
--

INSERT INTO `tipo_usuario` (`id_tipo_usuario`, `tipo_usu`) VALUES
(1, 'administrador'),
(2, 'empleado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `cedula` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `apellido` varchar(50) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `direccion` varchar(50) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `usuario` varchar(50) DEFAULT NULL,
  `contra` varchar(50) DEFAULT NULL,
  `id_tipo_usuario` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`cedula`, `nombre`, `apellido`, `telefono`, `direccion`, `email`, `usuario`, `contra`, `id_tipo_usuario`) VALUES
(0, NULL, NULL, NULL, NULL, NULL, 'yakis', 'y12345', 1),
(1094049616, 'YASMEIRA', 'ACOSTA', '12345', 'mz4lote1', 'yasme@gmail.com', 'yasmeira', 'y1234', 2);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`cedula_cliente`);

--
-- Indices de la tabla `detalle_salida`
--
ALTER TABLE `detalle_salida`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `fk_detsal_producto` (`cod_producto`),
  ADD KEY `fk_detsal_factura` (`cod_movimiento`);

--
-- Indices de la tabla `factura_salida`
--
ALTER TABLE `factura_salida`
  ADD PRIMARY KEY (`cod_movimiento`),
  ADD KEY `cedula_cliente` (`cedula_cliente`);

--
-- Indices de la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD PRIMARY KEY (`cod_inventario`),
  ADD KEY `cod_producto` (`cod_producto`);

--
-- Indices de la tabla `mov_entrada`
--
ALTER TABLE `mov_entrada`
  ADD PRIMARY KEY (`cod_movimiento`),
  ADD KEY `cod_proveedor` (`cod_proveedor`),
  ADD KEY `cod_producto` (`cod_producto`);

--
-- Indices de la tabla `mov_salida`
--
ALTER TABLE `mov_salida`
  ADD PRIMARY KEY (`cod_movimiento`),
  ADD KEY `cod_producto` (`cod_producto`),
  ADD KEY `fk_cliente_mov_salida` (`cedula_cliente`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`cod_producto`),
  ADD KEY `fk_proveedor_producto` (`cod_proveedor`);

--
-- Indices de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  ADD PRIMARY KEY (`cod_proveedor`);

--
-- Indices de la tabla `tipo_usuario`
--
ALTER TABLE `tipo_usuario`
  ADD PRIMARY KEY (`id_tipo_usuario`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`cedula`),
  ADD KEY `id_tipo_usuario` (`id_tipo_usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `detalle_salida`
--
ALTER TABLE `detalle_salida`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `factura_salida`
--
ALTER TABLE `factura_salida`
  MODIFY `cod_movimiento` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalle_salida`
--
ALTER TABLE `detalle_salida`
  ADD CONSTRAINT `fk_detsal_factura` FOREIGN KEY (`cod_movimiento`) REFERENCES `factura_salida` (`cod_movimiento`),
  ADD CONSTRAINT `fk_detsal_producto` FOREIGN KEY (`cod_producto`) REFERENCES `producto` (`cod_producto`);

--
-- Filtros para la tabla `factura_salida`
--
ALTER TABLE `factura_salida`
  ADD CONSTRAINT `factura_salida_ibfk_1` FOREIGN KEY (`cedula_cliente`) REFERENCES `cliente` (`cedula_cliente`);

--
-- Filtros para la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD CONSTRAINT `inventario_ibfk_1` FOREIGN KEY (`cod_producto`) REFERENCES `producto` (`cod_producto`);

--
-- Filtros para la tabla `mov_entrada`
--
ALTER TABLE `mov_entrada`
  ADD CONSTRAINT `mov_entrada_ibfk_1` FOREIGN KEY (`cod_proveedor`) REFERENCES `proveedor` (`cod_proveedor`),
  ADD CONSTRAINT `mov_entrada_ibfk_2` FOREIGN KEY (`cod_producto`) REFERENCES `producto` (`cod_producto`);

--
-- Filtros para la tabla `mov_salida`
--
ALTER TABLE `mov_salida`
  ADD CONSTRAINT `fk_cliente_mov_salida` FOREIGN KEY (`cedula_cliente`) REFERENCES `cliente` (`cedula_cliente`),
  ADD CONSTRAINT `mov_salida_ibfk_2` FOREIGN KEY (`cod_producto`) REFERENCES `producto` (`cod_producto`);

--
-- Filtros para la tabla `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `fk_proveedor_producto` FOREIGN KEY (`cod_proveedor`) REFERENCES `proveedor` (`cod_proveedor`);

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`id_tipo_usuario`) REFERENCES `tipo_usuario` (`id_tipo_usuario`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
