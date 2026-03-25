Segunda jornada liga terminada-- ---------------------------------------------------------
--
-- SIMPLE SQL Dump
-- 
-- nawa (at) yahoo (dot) com
--
-- Host Connection Info: Localhost via UNIX socket
-- Generation Time: March 03, 2021 at 14:20 PM ( Europe/Berlin )
-- Server version: 5.6.33
-- PHP Version: 5.6.27
--
-- ---------------------------------------------------------



SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


-- ---------------------------------------------------------
--
-- Table structure for table : `categorias`
--
-- ---------------------------------------------------------

CREATE TABLE `categorias` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(30) DEFAULT NULL,
  `edad_minima` int(2) DEFAULT NULL,
  `edad_maxima` int(2) DEFAULT NULL,
  `id_competicion` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=239 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `edad_minima`, `edad_maxima`, `id_competicion`) VALUES
(234, 'Alevín I', 0, 11, ''),
(235, 'Alevín II', 12, 12, ''),
(236, 'Infantil', 13, 15, ''),
(237, 'Junior', 15, 17, ''),
(238, 'Absoluta', 18, 0, '');



-- ---------------------------------------------------------
--
-- Table structure for table : `clubes`
--
-- ---------------------------------------------------------

CREATE TABLE `clubes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8_spanish2_ci DEFAULT NULL,
  `nombre_corto` varchar(30) COLLATE utf8_spanish2_ci DEFAULT NULL,
  `codigo` int(11) DEFAULT NULL,
  `logo` varchar(50) COLLATE utf8_spanish2_ci DEFAULT NULL,
  `federacion` int(11) DEFAULT NULL,
  `creado` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `modificado` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Dumping data for table `clubes`
--

INSERT INTO `clubes` (`id`, `nombre`, `nombre_corto`, `codigo`, `logo`, `federacion`, `creado`, `modificado`) VALUES
(1, 'Club Sincro Alhama', 'CSALHAMA', 1272, 'images/clubes/logo_csalhama.png', 1, '2020-02-22 08:51:50', '2020-03-09 10:53:01'),
(2, 'Club Natación Lorca', 'CNLORCA', 10, 'images/clubes/logo_cnlorca.svg', 1, '2020-02-22 08:51:50', '2020-03-09 10:53:09'),
(3, 'Sincro Albacete', 'SALBA', 89, 'images/clubes/logo_salba.jpg', 2, '2020-02-22 08:51:50', '2020-03-09 10:53:13'),
(4, 'C.E. Sincro Inca', 'CESINCA', 1392, 'images/clubes/logo_sinca.png', 3, '2020-02-22 08:51:50', '2020-03-09 10:53:17'),
(5, 'Costablanca', 'COSTABLANCA', 1388, 'images/clubes/custom_logo.png', 4, '2020-02-22 08:51:50', '2020-03-09 10:53:21'),
(6, 'Club de Natación Formentera', 'CNFORM', 1209, 'images/clubes/logo_formentera.jpg', 3, '2020-02-22 08:51:50', '2020-03-09 10:53:25'),
(7, 'Club Deportivo Centro Educativo Los Olivos', 'CDCEOLIVOS', 0, 'images/clubes/logo_cdcelosolivos.jpg', 1, '2020-02-22 08:51:50', '2020-03-09 10:53:28'),
(8, 'Club Natación La Nucia', 'CNNUCIA', 0, 'images/clubes/logo-la-nucia.jpg', 4, '2020-02-22 08:51:50', '2020-03-09 10:53:32'),
(9, 'Club Deportivo Stadio', 'CDSTADIO', 1460, 'images/clubes/logo_cdstadio.png', 4, '2020-02-22 08:51:50', '2020-03-09 10:53:36'),
(10, 'CD Trampolin', 'CDTRAM', 1043, 'images/clubes/logo_cdtrampolin.png', 2, '2020-02-22 08:51:50', '2020-03-09 10:53:39'),
(11, 'Sincro Pulpí', 'CSPULPÍ', 0, 'images/clubes/logo_spulpi.jpg', 1, '2020-02-22 08:51:50', '2020-03-09 10:53:42'),
(12, 'Club MOVE', 'CMOVE', 0, 'images/clubes/logo_cmove.png', 1, '2020-02-22 08:51:50', '2020-03-09 10:53:45'),
(13, 'Club Natación Puerto Lumbreras', 'CNPLUM', 1286, 'images/clubes/logo_snogalte.jpg', 1, '2020-02-22 08:51:50', '2020-03-09 10:53:49'),
(14, 'Sincro Andratx', 'SANDRATX', 0, 'images/clubes/logo_andratx.jpg', 3, '2020-02-22 08:51:50', '2020-03-09 10:53:52'),
(15, 'Sincro Ibiza', 'SIBIZA', 1099, 'images/clubes/logo-sincro-ibiza.jpg', 3, '2020-02-22 08:51:50', '2020-03-09 10:53:55'),
(16, 'Sincro Mediterranea', 'CMEDITE', 15, 'images/clubes/logo_mediterranea.png', 3, '2020-02-22 08:51:50', '2020-03-09 10:53:59'),
(17, 'Club Natación Hispaocio', 'CNHISPAOC', 1567, 'images/clubes/custom_logo.png', 2, '2020-02-22 08:51:50', '2020-03-09 10:54:04'),
(18, 'Club Natación Artística Huércal-Overa', 'HUERCAL', 0, 'images/clubes/logo_huercal.png', 1, '2020-02-22 08:51:50', '2020-03-09 10:54:07'),
(19, 'Sincro Lorca', 'SLORCA', 1650, 'images/clubes/logo_slorca.jpg', 1, '2020-02-22 08:51:50', '2020-03-09 10:54:12'),
(20, 'C. Natación Sincronizada Phoenix', 'PHOENIX', 1617, 'images/clubes/logo_sphoenix.jpg', 4, '2020-02-22 08:51:50', '2020-03-09 10:54:16'),
(21, 'Artística Tomelloso', 'ATOMELLOSO', 379, 'images/clubes/logo_stomelloso.jpg', 2, '2020-02-22 08:51:50', '2020-03-09 10:52:54'),
(27, 'Sincro Retiro', 'SR', 1235, 'images/clubes/custom_logo.png', 3, '2020-03-09 10:49:11', '');



-- ---------------------------------------------------------
--
-- Table structure for table : `competiciones`
--
-- ---------------------------------------------------------

CREATE TABLE `competiciones` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) DEFAULT NULL,
  `lugar` varchar(30) DEFAULT NULL,
  `piscina` varchar(60) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `organizador_tipo` varchar(12) DEFAULT NULL,
  `organizador` int(11) DEFAULT NULL,
  `activo` varchar(2) DEFAULT NULL,
  `figuras` varchar(2) DEFAULT NULL,
  `no_federado` varchar(2) DEFAULT NULL,
  `contacto` varchar(20) DEFAULT NULL,
  `email` varchar(30) DEFAULT NULL,
  `telefono` int(11) DEFAULT NULL,
  `clave_liga` varchar(30) DEFAULT NULL,
  `nombre_corto` varchar(11) DEFAULT NULL,
  `hora_inicio` varchar(5) DEFAULT NULL,
  `hora_fin` varchar(5) DEFAULT NULL,
  `header_informe` varchar(40) DEFAULT NULL,
  `footer_informe` varchar(40) DEFAULT NULL,
  `mensaje` text,
  `color` tinytext,
  `temporada` tinytext,
  `mascara_licencia` int(2) DEFAULT NULL,
  `creado` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `modificado` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `competiciones`
--

INSERT INTO `competiciones` (`id`, `nombre`, `lugar`, `piscina`, `fecha`, `organizador_tipo`, `organizador`, `activo`, `figuras`, `no_federado`, `contacto`, `email`, `telefono`, `clave_liga`, `nombre_corto`, `hora_inicio`, `hora_fin`, `header_informe`, `footer_informe`, `mensaje`, `color`, `temporada`, `mascara_licencia`, `creado`, `modificado`) VALUES
(43, 'VII Liga de Figuras (1ª Jornada)	', 'Alhama de Murcia', 'Piscina Cubierta de Alhama de Murcia', '2019-12-14', 'Federación', 1, 'no', 'si', '', '', '', '', 'VII Liga de Figuras', 'J1', '16:00', '19:30', 'images/header_regional.jpg', 'images/footer_regional2018.jpg', '', '#FFCDD2', 'Temporada 2019-2020', '', '2020-02-03 07:35:18', '2021-03-03 08:07:35'),
(44, 'VII Liga de Figuras (2ª Jornada)', 'Puerto Lumbreras', 'Piscina Cubierta de Puerto Lumbreras', '2020-01-25', 'Federación', 1, 'no', 'si', '', '', '', '', 'VII Liga de Figuras', 'J2', '16:00', '20:00', 'images/header_regional.jpg', 'images/footer_regional2018.jpg', '', '#F8BBD0', 'Temporada 2019-2020', 4, '2020-02-03 07:35:18', '2021-03-03 08:07:37'),
(45, 'VII Liga de Figuras (3ª Jornada)', 'Lorca', 'Complejo Deportivo Felipe VI', '2020-03-07', 'Federación', 1, 'no', 'si', '', '', '', '', 'VII Liga de Figuras', 'J3', '16:00', '20:00', 'images/header_regional.jpg', 'images/footer_regional2018.jpg', '', '#E1BEE7', 'Temporada 2019-2020', 4, '2020-02-03 07:35:18', '2021-03-03 08:07:39'),
(46, 'VII Liga de Figuras (4ª Jornada)', 'Alhama de Murcia', 'Piscina Cubierta de Alhama de Murcia', '2020-06-06', 'Federación', 1, 'no', 'si', '', '', '', '', 'VII Liga de Figuras', 'J4', '16:00', '20:00', 'images/header_regional.jpg', 'images/footer_regional2018.jpg', '', '#c5e321', 'Temporada 2019-2020', 0, '2020-02-03 07:35:18', '2021-03-03 08:07:42'),
(47, 'Open penp', 'Alhamam', 'cubierta', '2020-10-10', '', '', 'si', 'si', 'si', '', '', '', 'lighicaclave', 'j6', '9:00', '20:00', 'images/header_regional.jpg', 'images/footer_regional2018.jpg', '', '#c5e321', 'Temporada 2019-2020', 4, '2020-02-03 22:43:20', '2021-03-03 08:08:43'),
(48, '', '', '', '', '', '', 'no', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '2020-03-10 23:38:34', '2021-03-03 08:07:33');



-- ---------------------------------------------------------
--
-- Table structure for table : `fases`
--
-- ---------------------------------------------------------

CREATE TABLE `fases` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_categoria` int(11) DEFAULT NULL,
  `id_modalidad` int(11) DEFAULT NULL,
  `id_figura` int(11) DEFAULT NULL,
  `orden` int(11) DEFAULT NULL,
  `id_competicion` int(11) DEFAULT NULL,
  `sorteado` varchar(2) DEFAULT NULL,
  `puntuada` varchar(2) DEFAULT 'no',
  `hora_inicio_estimada` varchar(5) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=232 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `fases`
--

INSERT INTO `fases` (`id`, `id_categoria`, `id_modalidad`, `id_figura`, `orden`, `id_competicion`, `sorteado`, `puntuada`, `hora_inicio_estimada`) VALUES
(208, 234, '', 59, 3, 46, '', 'no', ''),
(217, 234, '', 7, 2, 46, '', 'no', ''),
(218, 234, '', 6, 1, 46, '', 'no', ''),
(219, 234, '', 60, 4, 46, '', 'no', ''),
(220, 235, '', 6, 5, 46, '', 'no', ''),
(221, 235, '', 7, 6, 46, '', 'no', ''),
(222, 235, '', 59, 7, 46, '', 'no', ''),
(223, 235, '', 60, 8, 46, '', 'no', ''),
(224, 236, '', 10, 9, 46, '', 'no', ''),
(225, 236, '', 61, 10, 46, '', 'no', ''),
(226, 236, '', 62, 11, 46, '', 'no', ''),
(227, 236, '', 63, 12, 46, '', 'no', ''),
(228, 237, '', 67, 13, 46, '', 'no', ''),
(229, 237, '', 15, 14, 46, '', 'no', ''),
(230, 237, '', 68, 15, 46, '', 'no', ''),
(231, 237, '', 22, 16, 46, '', 'no', '');



-- ---------------------------------------------------------
--
-- Table structure for table : `federaciones`
--
-- ---------------------------------------------------------

CREATE TABLE `federaciones` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8_spanish2_ci DEFAULT NULL,
  `nombre_corto` varchar(30) COLLATE utf8_spanish2_ci DEFAULT NULL,
  `codigo` int(11) DEFAULT NULL,
  `logo` varchar(50) COLLATE utf8_spanish2_ci DEFAULT NULL,
  `creado` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `modificado` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Dumping data for table `federaciones`
--

INSERT INTO `federaciones` (`id`, `nombre`, `nombre_corto`, `codigo`, `logo`, `creado`, `modificado`) VALUES
(1, 'Federación de Natación de la Región de Murcia', 'FNRM', 1272, 'images/logo_csalhama.png', '2020-02-04 11:01:09', '2020-03-09 05:28:34'),
(2, 'Federación de Natación de Castilla La Mancha', 'FNCLM', 12, 'images/federaciones/logo_fnclm.jpg', '2020-02-04 11:10:20', '2020-03-09 05:27:23'),
(3, 'Federación de Natación de Castilla La Mancha', 'FNCLM', 12, 'images/federaciones/logo_fnclm.jpg', '2020-02-04 11:10:20', '2020-03-09 05:27:23'),
(4, 'Federación de Natación de Castilla La Mancha', 'FNCLM', 12, 'images/federaciones/logo_fnclm.jpg', '2020-02-04 11:10:20', '2020-03-09 05:27:23');



-- ---------------------------------------------------------
--
-- Table structure for table : `figuras`
--
-- ---------------------------------------------------------

CREATE TABLE `figuras` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `numero` varchar(6) DEFAULT NULL,
  `nombre` varchar(60) DEFAULT NULL,
  `grado_dificultad` float(10,1) DEFAULT NULL,
  `descripcion` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `figuras`
--

INSERT INTO `figuras` (`id`, `numero`, `nombre`, `grado_dificultad`, `descripcion`) VALUES
(6, 101, 'Pierna de ballet', 1.6, '#####`101 - PIERNA DE BALLET SIMPLE 1.6`Se empieza en **Posición Estirada de Espalda**. Una pierna permanece sobre la superficie durante todo el tiempo. El pie de la otra pierna se desliza a lo largo de la cara interna de la pierna extendida, hasta alcanzar la **Posición Rodilla Doblada Estirada de Espalda**. Se estira la rodilla, sin producir movimiento alguno del muslo, hasta alcanzar la **Posición Pierna de Ballet**. Se flexiona la pierna de ballet, sin movimiento del muslo, hasta alcanzar la **Posición Rodilla Doblada Estirada de Espalda**. El dedo gordo del pie de la pierna flexionada se desliza a lo largo de la cara interna de la pierna extendida, hasta que se alcanza la **Posición Estirada de Espalda**.\n\n![alt text](./images/figuras/101.png)'),
(7, 301, 'Barracuda', 1.9, '`301 - BARRACUDA 1.9`\n\nDesde la **Posición Estirada de Espalda**, las piernas se elevan a la vertical a la vez que se sumerge el cuerpo hasta la **Posición Carpa de Espalda** con el nivel del agua justo por encima de los dedos de los pies. Se ejecuta un ***Empuje*** hasta la **Posición Vertical**. Se ejecuta un ***Descenso Vertical*** al mismo ritmo que el ***Empuje***.![alt text](./images/figuras/301.png)'),
(8, 111, 'Pierna de ballet doble submarina', 2.3, ''),
(9, 360, 'Paseo de frente', 2.1, ''),
(10, 423, 'Ariana', 2.2, ''),
(12, 342, 'Garza', 2.1, ''),
(13, 115, 'Catalina', 2.3, ''),
(14, 308, 'Barracuda Espagat Aéreo', 2.8, ''),
(15, '355g', 'Marsopa giro tirabuzón', 2.5, ''),
(16, 142, 'Manta raya', 2.8, ''),
(17, 343, 'Mariposa', 2.9, ''),
(18, 439, 'Oceanita', 1.9, ''),
(19, 362, 'Prawn en superficie', 1.4, ''),
(22, '330c', 'Aurora rotación', 2.8, ''),
(23, 154, 'Londres', 2.8, ''),
(24, 349, 'Torre', 1.9, ''),
(25, 406, 'Pez espada Pierna estirada', 2.0, ''),
(26, '301e', 'Barracuda Tirabuzón 360º', 2.2, ''),
(27, '240a', 'Albatros 1/2 Giro', 2.2, ''),
(28, 346, 'Cola de pez lateral', 2.0, ''),
(29, '112f', 'IBIS Tirabuzón continuo', 2.8, ''),
(30, 325, 'Júpiter', 2.8, ''),
(31, 311, 'Kip', 1.6, ''),
(32, '355h', 'Marsopa Tirabuzón Ascendente 180º', 2.2, ''),
(33, 140, 'Flamenco rodilla doblada', 2.4, ''),
(34, 'P1', 'Posición de estirada de espalda', 1.0, ''),
(35, 'P14B', 'Posición de vela', 1.0, ''),
(36, 310, 'Mortal de espalda encongido', 1.1, ''),
(37, 'P15', 'Posición de tonel', 1.0, ''),
(38, 'P10', 'Posición de carpa de frente', 1.0, ''),
(39, 201, 'Delfín', 1.4, ''),
(40, 'P16', 'Posición espagat', 1.0, ''),
(41, 'P4A', 'Posición flamenco en superficie', 1.0, ''),
(42, 'P14C1', 'Posición vertical rodilla doblada tobillos', 1.0, ''),
(43, 'TX1', 'Transición de carpa a espagat', 1.0, ''),
(44, 'P6X1', 'Posición vertical a tobillos', 1.0, ''),
(45, 'PX1', 'Posición de mesa', 1.0, ''),
(46, 315, 'Kipnus', 1.6, ''),
(47, 303, 'Mortal de espalda en carpa', 1.5, ''),
(48, 'P7', 'Posición de grúa', 1.0, ''),
(49, 355, 'Marsopa', 1.9, ''),
(50, 'P14C', 'Posición vertical rodilla doblada', 1.0, ''),
(51, '301d', 'Barracuda Tirabuzón 180º', 2.1, ''),
(52, 306, 'Barracuda rodilla doblada', 2.0, ''),
(53, 'P13', 'Posición arqueda en superficie', 1.0, ''),
(54, 'P6X2', 'Vertical a tobillos giro 360º', 1.0, ''),
(55, 106, 'Pierna de Ballet Estirada', 1.6, ''),
(56, 420, 'Paseo de espalda', 1.9, ''),
(57, 327, 'Bailarina', 1.8, ''),
(58, 401, 'Pez Espada', 2.0, ''),
(59, 226, 'Swan', 2.1, '`226 – CISNE (SWAN) 2.1`\n\nSe ejecuta una Nova hasta la **Posición Rodilla Doblada Arqueada en Superficie**. La pierna doblada se extiende para asumir una **Posición de Caballero**. El cuerpo rota 180º para asumir la **Posición de Cola de Pez**. La pierna vertical desciende hacia la superficie para encontrarse con la pierna contraria en una **Posición de Carpa** y con un movimiento continuo el cuerpo se estira hasta la **Posición Estirada de Frente**. La cara llega a la superficie ocupando el espacio donde estaban las caderas en el inicio de esta acción.\n![alt text](./images/figuras/226.png)'),
(60, 363, 'Gota de agua', 1.5, '`363 GOTA DE AGUA 1.5`\n\nDesde una **Posición Estirada de Frente** se adopta la **Posición Carpa de Frente**. Las piernas se levantan simultáneamente hasta la **Posición Vertical Rodilla Doblada**. Se ejecuta un *Tirabuzón descendente de 180º* mientras se extiende la rodilla doblada hasta alcanzar la **Posición Vertical** antes de que los tobillos alcancen la superficie del agua.\n![alt text](./images/figuras/363.png)'),
(61, 143, 'Rio', 3.1, ''),
(62, 351, 'Jupiter', 2.8, ''),
(63, 437, 'Oceanía', 2.1, ''),
(64, 403, 'Swordtail', 2.3, ''),
(65, '355f', 'Marsopa Tirabuzón continuo 720º', 2.1, ''),
(66, 315, 'Seagull', 2.1, ''),
(67, '308i', 'Barracuda Espagat Aereo Tirabuzón ascendente 360º', 3.3, ''),
(68, '154j-2', 'London Tirabuzón Combinado 720º', 2.9, ''),
(69, 364, 'Whirlwind', 2.7, ''),
(70, 320, 'Kipswirl Espagat cerrando 180º', 2.3, ''),
(71, 440, 'Ipanema', 3.0, '');



-- ---------------------------------------------------------
--
-- Table structure for table : `jueces`
--
-- ---------------------------------------------------------

CREATE TABLE `jueces` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(40) DEFAULT NULL,
  `apellidos` varchar(40) DEFAULT NULL,
  `licencia` varchar(40) DEFAULT NULL,
  `federacion` int(11) DEFAULT NULL,
  `id_competicion` int(11) DEFAULT NULL,
  `activo` varchar(2) CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT 'si',
  `club` int(11) DEFAULT NULL,
  `creado` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `modificado` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `jueces`
--

INSERT INTO `jueces` (`id`, `nombre`, `apellidos`, `licencia`, `federacion`, `id_competicion`, `activo`, `club`, `creado`, `modificado`) VALUES
(81, 'pero', 'Diaz', 00877079, 2, '', 'si', '', '2020-03-09 11:33:07', '2021-03-02 22:44:48'),
(82, 'Pedro Francisco', 'Díaz Romero', 048426596, 1, '', 'si', '', '2020-03-09 11:34:50', '2020-03-09 11:40:39'),
(83, 'Maria Jose', 'Rubio Martinez', 045678543, 3, '', 'si', '', '2020-03-10 23:47:54', '2021-03-02 22:45:06'),
(84, 'África', 'Díaz Martínez', 038456765, 1, '', 'si', '', '2021-03-02 18:09:48', '2021-03-02 18:10:04');



-- ---------------------------------------------------------
--
-- Table structure for table : `modalidades`
--
-- ---------------------------------------------------------

CREATE TABLE `modalidades` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(15) DEFAULT NULL,
  `numero_participantes` int(11) DEFAULT NULL,
  `numero_reservas` int(11) DEFAULT NULL,
  `id_competicion` int(11) DEFAULT NULL,
  `prioridad_panel_1` int(1) DEFAULT '0',
  `prioridad_panel_2` int(1) DEFAULT '0',
  `prioridad_panel_3` int(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `modalidades`
--

INSERT INTO `modalidades` (`id`, `nombre`, `numero_participantes`, `numero_reservas`, `id_competicion`, `prioridad_panel_1`, `prioridad_panel_2`, `prioridad_panel_3`) VALUES
(1, 'Solo', 1, 1, 1, 1, 0, 0),
(2, 'Dúo', 2, 1, 1, 1, 0, 0),
(3, 'Combo', 12, 2, 1, 1, 2, 0);



-- ---------------------------------------------------------
--
-- Table structure for table : `nadadoras`
--
-- ---------------------------------------------------------

CREATE TABLE `nadadoras` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `apellidos` varchar(30) COLLATE utf8_spanish2_ci DEFAULT NULL,
  `nombre` varchar(30) COLLATE utf8_spanish2_ci DEFAULT NULL,
  `licencia` varchar(10) COLLATE utf8_spanish2_ci DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `club` int(11) DEFAULT NULL,
  `baja` char(2) COLLATE utf8_spanish2_ci DEFAULT 'no',
  `creado` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `modificado` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=785 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Dumping data for table `nadadoras`
--

INSERT INTO `nadadoras` (`id`, `apellidos`, `nombre`, `licencia`, `fecha_nacimiento`, `club`, `baja`, `creado`, `modificado`) VALUES
(1, 'GARCIA NAVARRO', 'IRATZE', 0488473, '2004-12-14', 1, 'si', '2020-02-04 11:22:06', '2020-03-09 06:50:23'),
(2, 'LOPEZ LOPEZ', 'NOELIA', 049169713, '2005-01-12', 1, 'no', '2020-02-04 11:22:06', ''),
(3, 'MERLOS FERNANDEZ', 'MARIA', 048853831, '2003-11-27', 1, 'no', '2020-02-04 11:22:06', ''),
(4, 'GALERA MORA', 'CLAUDIA', 049171039, '2004-11-17', 1, 'no', '2020-02-04 11:22:06', ''),
(5, 'CARRASCO DEL REY', 'SARA', 049247742, '2005-10-12', 1, 'no', '2020-02-04 11:22:06', ''),
(6, 'CARRASCO DEL REY', 'PAULA', 049247741, '2003-11-20', 1, 'no', '2020-02-04 11:22:06', ''),
(7, 'LORENTE PLAZAS', 'LAURA', 023334798, '2004-03-01', 1, 'no', '2020-02-04 11:22:06', ''),
(8, 'SERRANO MORA', 'ELENA', 058450115, '2004-06-03', 1, 'no', '2020-02-04 11:22:06', ''),
(9, 'MORALES SÁNCHEZ', 'LAURA', 049171914, '2002-03-25', 1, 'no', '2020-02-04 11:22:06', ''),
(10, 'QUIÑONERO GARCÍA', 'LAURA', 048129287, '2002-10-21', 1, 'no', '2020-02-04 11:22:06', ''),
(11, 'GARCÍA LARA', 'ANA', 077853635, '2002-03-25', 1, 'si', '2020-02-04 11:22:06', ''),
(15, 'SAEZ EGEA', 'NURIA', 023832726, '2000-08-31', 1, 'no', '2020-02-04 11:22:06', ''),
(16, 'BOLARÍN NAVARRO', 'LUCIA', 048704413, '2001-01-27', 1, 'no', '2020-02-04 11:22:06', ''),
(17, 'BARAZA NAVARRO', 'IRENE', 048706958, '2001-07-05', 1, 'no', '2020-02-04 11:22:06', ''),
(18, 'MANZANERA SÁNCHEZ', 'LAURA', 048853430, '2001-12-07', 1, 'no', '2020-02-04 11:22:06', ''),
(19, 'ALCON PONSODA', 'NURIA', 048854138, '2000-10-20', 1, 'no', '2020-02-04 11:22:06', ''),
(20, 'GARCIA LOPEZ', 'ANA', 049169281, '2001-06-19', 1, 'no', '2020-02-04 11:22:06', ''),
(21, 'MORALES SANCHEZ', 'ROCIO', 049171913, '2000-12-14', 1, 'si', '2020-02-04 11:22:06', ''),
(22, 'GARCIA MIÑARRO', 'LUCIA', 048840352, '2001-06-19', 1, 'no', '2020-02-04 11:22:06', ''),
(23, 'JIMENEZ GARCIA', 'MARIA', 049197219, '2001-03-03', 1, 'no', '2020-02-04 11:22:06', ''),
(24, 'GARCIA GARCIA', 'TOÑI', 049249578, '1999-03-06', 1, 'no', '2020-02-04 11:22:06', ''),
(25, 'CAVA PAGAN', 'Mª DEL AMOR', 077839692, '2001-08-31', 1, 'no', '2020-02-04 11:22:06', ''),
(26, 'SOTO SIMON', 'MARTA', 049172006, '2001-05-07', 1, 'no', '2020-02-04 11:22:06', ''),
(27, 'PEREZ MERLOS', 'LAURA', 021066804, '1998-08-19', 1, 'no', '2020-02-04 11:22:06', ''),
(28, 'LOPEZ JAVALOY', 'ANA ISABEL', 029537622, '2001-08-02', 1, 'no', '2020-02-04 11:22:06', ''),
(29, 'CORTES CANOVAS', 'VICTORIA', 048705839, '2001-07-09', 1, 'no', '2020-02-04 11:22:06', ''),
(30, 'LOPEZ ROJO', 'AINHOA', 048706931, '2002-01-04', 1, 'no', '2020-02-04 11:22:06', ''),
(31, 'JIMENEZ GARCIA', 'ANA ISABEL', 049197220, '1996-03-23', 1, 'si', '2020-02-04 11:22:06', ''),
(32, 'BERNABÉ GONZÁLEZ', 'PAULA', 049177426, '2000-10-30', 1, 'si', '2020-02-04 11:22:06', ''),
(41, 'VIDAL LOPEZ', 'ANGELA', 049183879, '1995-12-07', 1, 'si', '2020-02-04 11:22:06', ''),
(46, 'PADILLA MARTÍNEZ', 'MARIA', 023838542, '2003-05-16', 19, 'no', '2020-02-04 11:22:06', ''),
(47, 'GILBERTE SALAS', 'MARIA', 023309527, '2003-01-30', 2, 'no', '2020-02-04 11:22:06', ''),
(48, 'MUNUERA MANZANARES', 'ROCIO', 024460998, '2003-06-15', 19, 'no', '2020-02-04 11:22:06', ''),
(49, 'PASTOR GIMÉNEZ', 'Mª ELENA', 023330244, '2003-04-03', 2, 'no', '2020-02-04 11:22:06', ''),
(50, 'LÓPEZ NAVARRO', 'IRENE', 023838591, '2005-05-30', 19, 'no', '2020-02-04 11:22:06', ''),
(51, 'NAVARRO CARRILLO', 'MARTA', 023334764, '2003-10-01', 2, 'no', '2020-02-04 11:22:06', ''),
(52, 'PUCHE BASTIDA', 'ALBA', 023834535, '2004-07-14', 2, 'no', '2020-02-04 11:22:06', ''),
(53, 'ROMERA GONZÁLEZ', 'MARIA', 024461485, '2006-01-03', 19, 'no', '2020-02-04 11:22:06', ''),
(54, 'NAVARRO TEJEDOR', 'MARIA', 023331809, '1999-11-21', 2, 'no', '2020-02-04 11:22:06', ''),
(55, 'ROCAMORA LÓPEZ', 'ANA', 023299709, '2000-03-15', 2, 'no', '2020-02-04 11:22:06', ''),
(56, 'VARÓN GUEVARA', 'NURIA', 023331091, '1999-02-19', 2, 'no', '2020-02-04 11:22:06', ''),
(57, 'RUBIO GARCÍA', 'NATALIA', 023309033, '2001-04-29', 13, 'no', '2020-02-04 11:22:06', ''),
(58, 'CARRILLO MATEO', 'MARINA', 023833021, '2001-07-31', 19, 'no', '2020-02-04 11:22:06', ''),
(59, 'ABRIL MARTÍNEZ', 'EVA', 023306849, '2001-09-13', 13, 'no', '2020-02-04 11:22:06', ''),
(60, 'MORALES PÉREZ', 'CELIA', 023308367, '2001-03-26', 19, 'no', '2020-02-04 11:22:06', ''),
(61, 'NONNAST FORNIELES', 'CARINE', 023812674, '2000-12-10', 2, 'no', '2020-02-04 11:22:06', ''),
(62, 'LORENTE CERDÁ', 'ESPERANZA', 023832068, '2001-07-31', 2, 'no', '2020-02-04 11:22:06', ''),
(63, 'RODRIGUEZ RUIZ', 'EVA', 023332631, '1999-08-11', 2, 'no', '2020-02-04 11:22:06', ''),
(64, 'GINER PÉREZ', ' SILVIA',  023308823, '1998-11-27', 2, 'no', '2020-02-04 11:22:06', '2020-03-09 06:17:55'),
(65, 'NONNAST FORNIELES', 'ELENA', 023332579, '1998-08-11', 2, 'no', '2020-02-04 11:22:06', ''),
(70, 'CHACON SERNA', 'LUNA', 049312938, '2005-01-26', 3, 'no', '2020-02-04 11:22:06', ''),
(71, 'JUSTE ORTEGA', 'ROCIO', 049213774, '2004-06-26', 3, 'no', '2020-02-04 11:22:06', ''),
(72, 'LOPEZ ALCAHUD-MARTINEZ', 'LUCIA', 049425655, '2004-07-18', 3, 'no', '2020-02-04 11:22:06', ''),
(73, 'ROMERO DONATE', 'LUCIA', 049434959, '2003-01-02', 3, 'no', '2020-02-04 11:22:06', ''),
(74, 'AMOROS LOPEZ', 'LAURA', 044397761, '2003-01-02', 3, 'no', '2020-02-04 11:22:06', ''),
(75, 'ZAPATA PASTOR', 'MARIA', 049310576, '2003-01-02', 3, 'no', '2020-02-04 11:22:06', ''),
(76, 'ALFARO OJEDA', 'EMMA', 049311804, '2003-01-02', 3, 'no', '2020-02-04 11:22:06', ''),
(77, 'NAVARRO COLL', 'WAYRA', 020784919, '2005-01-02', 3, 'no', '2020-02-04 11:22:06', ''),
(78, 'GOMEZ LOPEZ', 'ANA', 049431419, '2004-01-02', 3, 'no', '2020-02-04 11:22:06', ''),
(79, 'RUEDA LEAL', 'SARA', 078821057, '2003-01-03', 3, 'no', '2020-02-04 11:22:06', ''),
(80, 'SIMON DE LA ROSA', 'MARIA', 048155108, '2003-01-09', 3, 'no', '2020-02-04 11:22:06', ''),
(81, 'MARIN GONZALEZ', 'LAURA', 049215207, '2004-01-02', 3, 'no', '2020-02-04 11:22:06', ''),
(82, 'CHACON SERNA', 'ALBA', 047099271, '2002-01-02', 3, 'no', '2020-02-04 11:22:06', ''),
(83, 'RUIZ MARTINEZ', 'ESTHER', 075456623, '2002-01-02', 3, 'no', '2020-02-04 11:22:06', ''),
(84, 'GONZALEZ MARTINEZ', 'MARTA', 049431233, '2002-01-02', 3, 'no', '2020-02-04 11:22:06', ''),
(85, 'GARCIA PICAZO', 'PAULA', 049425873, '1999-12-04', 3, 'no', '2020-02-04 11:22:06', ''),
(86, 'ALFARO GONZALEZ', 'ANA', 049314506, '2000-10-10', 3, 'no', '2020-02-04 11:22:06', ''),
(87, 'GOMEZ LOPEZ', 'LUZ', 049425855, '2001-04-14', 3, 'no', '2020-02-04 11:22:06', ''),
(88, 'NIETO GARCIA-VALLET', 'GEMA', 049314545, '2001-08-07', 3, 'no', '2020-02-04 11:22:06', ''),
(89, 'SEGURA ROMERA', 'ISABEL', 049210283, '2001-03-10', 3, 'no', '2020-02-04 11:22:06', ''),
(90, 'GONZALEZ PEREZ', 'ANDREA', 049210582, '2000-02-20', 3, 'no', '2020-02-04 11:22:06', ''),
(91, 'RUEDA LEAL', 'BEATRIZ', 078821056, '2001-09-07', 3, 'no', '2020-02-04 11:22:06', ''),
(92, 'CANO MARTINEZ', 'Mª DEL MAR', 049217414, '1999-12-17', 3, 'no', '2020-02-04 11:22:06', ''),
(93, 'GOMEZ LOPEZ', 'JULIA', 049425852, '1999-09-26', 3, 'no', '2020-02-04 11:22:06', ''),
(94, 'LOPEZ MONDEJAR', 'SARA', 048156674, '1998-08-26', 3, 'no', '2020-02-04 11:22:06', ''),
(95, 'BERNABE FERNANDEZ', 'EMMA', 048154577, '1996-10-10', 3, 'no', '2020-02-04 11:22:06', ''),
(96, 'GALLARDO QUEREDA', 'MARIA', 049311270, '1998-01-01', 3, 'no', '2020-02-04 11:22:06', ''),
(97, 'BARNUEVO COLLADO', 'MARIA', 048151508, '2000-02-14', 3, 'no', '2020-02-04 11:22:06', ''),
(98, 'SAEZ GOMEZ', 'MARIA', 04709973, '1996-01-10', 3, 'no', '2020-02-04 11:22:06', ''),
(99, 'TORRES VIZCAYA', 'BELEN', 049426125, '1998-11-20', 3, 'no', '2020-02-04 11:22:06', ''),
(100, 'MARTINEZ SANCHA', 'ANDREA', 049213613, '1997-06-23', 3, 'no', '2020-02-04 11:22:06', ''),
(101, 'CARRIÓN', 'ALBA MARIA', 047446073, '2001-08-28', 3, 'no', '2020-02-04 11:22:06', ''),
(103, 'GARCÍA JORDA', 'Mª CARMEN', 048156629, '2001-12-10', 3, 'no', '2020-02-04 11:22:06', ''),
(105, 'LILLO SAN NICOLAS', 'PAULA', 025441493, '2001-07-25', 3, 'no', '2020-02-04 11:22:06', ''),
(107, 'TORRENTE', 'CARLA', 047096670, '2001-05-04', 3, 'no', '2020-02-04 11:22:06', ''),
(108, 'FELIPE', 'NORA', 048151766, '2000-01-31', 3, 'no', '2020-02-04 11:22:06', ''),
(109, 'NOGUERA', 'RAQUEL MARINA', 049314216, '2000-12-13', 3, 'no', '2020-02-04 11:22:06', ''),
(110, 'DIANA SÁNCHEZ', 'MARIAM', 049213602, '2000-06-13', 3, 'no', '2020-02-04 11:22:06', ''),
(111, 'RODRIGUEZ GILI', 'ESTHER', 041583799, '1998-05-13', 4, 'no', '2020-02-04 11:22:06', ''),
(112, 'BARBA VILLAN', 'ELENA', 041584027, '1997-09-18', 4, 'no', '2020-02-04 11:22:06', ''),
(113, 'FUSTER TARRAGO', 'BLANCA', 041585086, '2005-01-10', 4, 'no', '2020-02-04 11:22:06', ''),
(115, 'RUBIO SEGUI', 'Mª FCA.', 041585676, '2004-10-25', 4, 'no', '2020-02-04 11:22:06', ''),
(116, 'ROSSELLO GENOVARD', 'NURIA', 041587535, '2002-02-27', 4, 'no', '2020-02-04 11:22:06', ''),
(117, 'SOLER BLOOM', 'JULIA', 041615348, '1997-10-05', 4, 'no', '2020-02-04 11:22:06', ''),
(118, 'VIVES MARTINEZ', 'LAURA', 041617014, '2000-09-05', 4, 'no', '2020-02-04 11:22:06', ''),
(119, 'NICOLAU JAUME', 'MARIA', 041617477, '2004-04-28', 4, 'no', '2020-02-04 11:22:06', ''),
(120, 'MASSOT JAUME', 'MARGALIDA', 041618231, '2003-09-20', 4, 'no', '2020-02-04 11:22:06', ''),
(121, 'MASSOT ROSSELLO', 'Mª BEL', 041618397, '2000-05-15', 4, 'no', '2020-02-04 11:22:06', ''),
(123, 'ROSA SEIJAS', 'CARMEN', 041620306, '2003-06-18', 4, 'no', '2020-02-04 11:22:06', ''),
(125, 'CERDA LLULL', 'AINA', 041620610, '2003-09-30', 4, 'no', '2020-02-04 11:22:06', ''),
(126, 'GARCIA GUAL', 'CLARA', 041623026, '2003-06-22', 4, 'no', '2020-02-04 11:22:06', ''),
(127, 'GARRIGA PASCUAL', 'CATERINA', 041658479, '2004-01-16', 4, 'no', '2020-02-04 11:22:06', ''),
(129, 'CAÑELLAS LOPEZ', 'Mª VICTORIA', 043209239, '2001-10-02', 4, 'no', '2020-02-04 11:22:06', ''),
(130, 'NOGUERA CUART', 'CRISTINA', 043211605, '1993-11-29', 14, 'no', '2020-02-04 11:22:06', ''),
(131, 'POL FIOL', 'Mª ANTONIA', 043229423, '2002-06-13', 4, 'no', '2020-02-04 11:22:06', ''),
(132, 'MOROTE LLABRES', 'Mª DEL MAR', 043461529, '2002-08-21', 4, 'no', '2020-02-04 11:22:06', ''),
(133, 'DONAIRE RAMIS', 'MARTINA', 043472312, '2003-02-24', 4, 'no', '2020-02-04 11:22:06', ''),
(134, 'CRESPI SALAS', 'JOANA Mª', 045614094, '2005-06-04', 4, 'no', '2020-02-04 11:22:06', ''),
(135, 'MULET CLADERA', 'Mª FRCA.', 078220750, '1998-10-26', 4, 'no', '2020-02-04 11:22:06', ''),
(136, 'CRESPI SALAS', 'ANTONIA', 078221118, '2002-04-02', 4, 'no', '2020-02-04 11:22:06', ''),
(138, 'MOYA MEGIAS', 'NEUS', 078222561, '2002-10-21', 4, 'no', '2020-02-04 11:22:06', ''),
(139, 'ALTURA HUTCHINSON', 'SOFIA', 049374698, '2003-02-02', 8, 'no', '2020-02-04 11:22:06', ''),
(140, 'CRESPO GADEA', 'AITANA', 099165893, '2005-02-08', 5, 'no', '2020-02-04 11:22:06', ''),
(141, 'JIMENEZ SANCHEZ', 'NORA', 048781088, '2004-08-31', 5, 'no', '2020-02-04 11:22:06', ''),
(142, 'TOMAS ALVADO', 'RAQUEL', 074017493, '2002-11-09', 8, 'no', '2020-02-04 11:22:06', ''),
(143, 'MOLINES BALLESTER', 'ELSA', 054375051, '2002-05-31', 8, 'no', '2020-02-04 11:22:06', ''),
(144, '', 'Secretariol', '', '0000-00-00', 0, 'no', '2020-02-04 11:22:06', '2020-03-11 10:57:54'),
(145, 'DIAZ CUESTA', 'MARGARITA', 048726075, '2004-01-12', 9, 'no', '2020-02-04 11:22:06', ''),
(146, 'RICO SANCHEZ', 'INES', 048801896, '2003-02-06', 9, 'no', '2020-02-04 11:22:06', ''),
(147, 'YOUNES REGIDOR', 'NAYUA', 048671611, '2003-08-20', 9, 'no', '2020-02-04 11:22:06', ''),
(148, 'GALAN SOLANA', 'ALBA', 050508276, '2005-02-26', 5, 'no', '2020-02-04 11:22:06', ''),
(150, 'FERRANDEZ MONTESINOS', 'MARTA', 074440276, '2003-02-09', 5, 'no', '2020-02-04 11:22:06', ''),
(151, 'MARTINEZ ESTEVEZ', 'MARIA', 050381897, '2005-02-01', 5, 'no', '2020-02-04 11:22:06', ''),
(152, 'ARAGONES PEREZ', 'CELIA', 048773233, '1999-01-15', 5, 'no', '2020-02-04 11:22:06', ''),
(154, 'LAJARA AGULLO', 'SUSANA', 048799623, '2001-03-04', 5, 'no', '2020-02-04 11:22:06', ''),
(155, 'MIÑANO FERRAIRO', 'GRACIA', 048767237, '2001-03-05', 5, 'no', '2020-02-04 11:22:06', ''),
(156, 'JIMENO AMARILLO', 'ROCIO', 048722973, '2001-05-10', 5, 'no', '2020-02-04 11:22:06', ''),
(157, 'MOLINA SANCHEZ', 'IRENE', 053248827, '2000-08-28', 5, 'no', '2020-02-04 11:22:06', ''),
(158, 'COMPANY BERNAL', 'ANGELA', 048787815, '1998-02-02', 5, 'no', '2020-02-04 11:22:06', ''),
(159, 'AUSINA SANCHEZ', 'SARA', 048767397, '1999-07-08', 8, 'no', '2020-02-04 11:22:06', ''),
(160, 'MENDOZA LOZANO', 'ALICIA', 048761882, '1997-04-23', 5, 'no', '2020-02-04 11:22:06', ''),
(169, 'FERNANDEZ DE BOBADILLA RAMOS', 'ZOE', 047260661, '2004-10-25', 6, 'no', '2020-02-04 11:22:06', ''),
(173, 'VERDERA MÜLBAIER', 'CELINE', 047260557, '2002-01-28', 6, 'no', '2020-02-04 11:22:06', ''),
(174, 'FERNÁNDEZ DE BOBADILLA RAMOS', 'NAIARA', 047260703, '2003-02-16', 6, 'no', '2020-02-04 11:22:06', ''),
(175, 'PEREZ LABRADOR', 'CARLA', 047594358, '2003-12-30', 6, 'no', '2020-02-04 11:22:06', ''),
(176, 'HURTADO COSTA', 'CRISTINA', 047260881, '2003-04-28', 6, 'no', '2020-02-04 11:22:06', ''),
(177, 'MORENO RAMÓN', 'MARTA', 047260869, '2003-01-03', 6, 'no', '2020-02-04 11:22:06', ''),
(178, 'CARA SERRANO', 'LORENA', 047260706, '2003-06-08', 6, 'no', '2020-02-04 11:22:06', ''),
(179, 'MUNTEANU', 'Mª DIANA', 09507975, '2003-09-07', 6, 'no', '2020-02-04 11:22:06', ''),
(180, 'LAPEÑA SANCLEMENTE', 'NEREA', 047409051, '2003-06-13', 6, 'no', '2020-02-04 11:22:06', ''),
(181, 'SIPAVICIUTE', 'ENRIKA', 007366276, '2003-10-14', 6, 'no', '2020-02-04 11:22:06', ''),
(182, 'BORDOY RIERA', 'ESTER', 047260581, '2003-10-21', 6, 'no', '2020-02-04 11:22:06', ''),
(183, 'MENDEZ MOYA', 'PRAXEDES', 047260466, '2001-03-08', 6, 'no', '2020-02-04 11:22:06', ''),
(184, 'DIAZ ZAFRA', 'CARLA', 047260688, '2001-03-24', 6, 'no', '2020-02-04 11:22:06', ''),
(185, 'FACCIOLI', 'ZOE', 007264987, '2001-12-26', 6, 'no', '2020-02-04 11:22:06', ''),
(186, 'TORRES GIRBES', 'CELIA', 007262051, '2001-09-15', 6, 'no', '2020-02-04 11:22:06', ''),
(187, 'MUÑOZ LUZON', 'ALBA', 047260665, '2001-12-28', 6, 'no', '2020-02-04 11:22:06', ''),
(188, 'FERRER QUINTANA', 'LAURA', 047260348, '1999-09-23', 6, 'no', '2020-02-04 11:22:06', ''),
(189, 'TUR MARTIN', 'CLAUDIA', 047260716, '2000-11-04', 6, 'no', '2020-02-04 11:22:06', ''),
(190, 'CUADRADO LLAHI', 'SAYEN', 047260438, '2000-01-13', 6, 'no', '2020-02-04 11:22:06', ''),
(191, 'ORENES BUENO', 'LUCIA', 049821309, '2005-08-28', 7, 'no', '2020-02-04 11:22:06', ''),
(192, 'VIDAL GAMBÍN', 'ÁNGELA', 048747283, '2005-05-17', 7, 'no', '2020-02-04 11:22:06', ''),
(193, 'VIDAL GAMBÍN', 'ANDREA', 048747282, '2005-05-17', 7, 'no', '2020-02-04 11:22:06', ''),
(194, 'VALENZUELA SÁNCHEZ', 'AIMIE', 049476020, '2006-08-31', 7, 'no', '2020-02-04 11:22:06', ''),
(195, 'CORBALÁN TORRES', 'MARTA', 049823330, '2004-07-15', 7, 'no', '2020-02-04 11:22:06', ''),
(196, 'MESEGUER WALKER', 'EVA', 048748135, '2005-05-15', 7, 'no', '2020-02-04 11:22:06', ''),
(198, 'GARCÍA HUERTAS', 'VIRGINIA', 058451159, '2006-09-23', 7, 'no', '2020-02-04 11:22:06', ''),
(199, 'GARCÍA PÉREZ', 'ALBA', 049858013, '2006-10-24', 7, 'no', '2020-02-04 11:22:06', ''),
(200, 'MOROTE PÉREZ', 'HELENA', 024458189, '2004-09-29', 2, 'no', '2020-02-04 11:22:06', ''),
(201, 'QUIÑONERO GRACIA', 'ANA EMILIA', 023333666, '2001-07-12', 2, 'no', '2020-02-04 11:22:06', ''),
(202, 'CERÓN DÍAZ', 'ELENA', 048637242, '2000-07-07', 1, 'no', '2020-02-04 11:22:06', ''),
(203, 'CAJA DÍAZ', 'ANA', 049169611, '1999-04-06', 1, 'no', '2020-02-04 11:22:06', ''),
(204, 'SÁNCHEZ PROVENCIO', 'AITANA', 049696075, '2005-12-13', 1, 'no', '2020-02-04 11:22:06', ''),
(205, 'VALVERDE SÁNCHEZ', 'MARIA', 058453001, '2006-03-24', 1, 'no', '2020-02-04 11:22:06', ''),
(206, 'GONZÁLEZ MARTÍNEZ', 'ANA', 048854579, '2006-06-04', 1, 'no', '2020-02-04 11:22:06', ''),
(207, 'DÍAZ SÁNCHEZ', 'ALBA', 058452930, '2006-06-18', 1, 'no', '2020-02-04 11:22:06', ''),
(209, 'ALCARÁZ IONITA', 'DELIA', 021011961, '2003-01-03', 2, 'no', '2020-02-04 11:22:06', ''),
(210, 'MAS GARCIA', 'LORENA', 043464395, '2003-05-20', 4, 'no', '2020-02-04 11:22:06', ''),
(211, 'MAS GELABERT', 'CARLOTA S.', 045371148, '2003-02-14', 4, 'no', '2020-02-04 11:22:06', ''),
(212, 'GARCÍA GUAL', 'NURIA', 041623025, '2000-08-18', 4, 'no', '2020-02-04 11:22:06', ''),
(213, 'CERDÁ LLUL', 'Mª DEL MAR', 041620609, '1999-12-14', 4, 'no', '2020-02-04 11:22:06', ''),
(214, 'CUART VERDERA', 'ENCARNACIÓN', 043047857, '1968-03-15', 14, 'no', '2020-02-04 11:22:06', ''),
(215, 'DEVESA SUCH', 'MARIA', 049230520, '2003-05-16', 8, 'no', '2020-02-04 11:22:06', ''),
(216, 'RAGNO GONZÁLEZ', 'ANNA', 049370299, '1999-04-29', 8, 'no', '2020-02-04 11:22:06', ''),
(217, 'IGLESIAS MIRALLES', 'LAURA', 048783335, '2004-10-22', 9, 'no', '2020-02-04 11:22:06', ''),
(218, 'CHORDA CEBRIAN', 'MAR', 048789639, '2004-05-13', 9, 'no', '2020-02-04 11:22:06', ''),
(219, 'MIRA AUDINIS', 'ESTHER', 050508805, '2006-01-11', 9, 'no', '2020-02-04 11:22:06', ''),
(220, 'ALCARAZ OLAYA', 'ANA', 048765694, '2004-01-10', 9, 'no', '2020-02-04 11:22:06', ''),
(221, 'GIMÉNEZ NARANJO', 'NATALIA', 021696211, '2003-06-22', 9, 'no', '2020-02-04 11:22:06', ''),
(222, 'PEÑAS BERNABEU', 'MARIA', 048674721, '2003-02-01', 9, 'no', '2020-02-04 11:22:06', ''),
(223, 'SALVADOR CARRATALA', 'LOURDES', 099166363, '2003-04-22', 9, 'no', '2020-02-04 11:22:06', ''),
(224, 'DICKAS GAMBÍN', 'SARA', 048674721, '2004-03-17', 9, 'no', '2020-02-04 11:22:06', ''),
(225, 'YOUNES REGIDOR', 'NOA', 048623847, '2001-08-20', 9, 'no', '2020-02-04 11:22:06', ''),
(226, 'NAVARRO ORTS', 'MONICA', 050383270, '2002-05-04', 9, 'no', '2020-02-04 11:22:06', ''),
(227, 'GONZÁLEZ MARTÍNEZ', 'VERÓNICA', 048765426, '2000-06-13', 9, 'no', '2020-02-04 11:22:06', ''),
(228, 'MOLLA BAÑON', 'RAQUEL', 050590759, '2002-04-30', 9, 'no', '2020-02-04 11:22:06', ''),
(229, 'SANCHÍS GIL', 'MELISSA', 050509429, '2000-07-24', 9, 'no', '2020-02-04 11:22:06', ''),
(230, 'CUENCA MÉNDEZ', 'LORENA', 053979202, '2000-10-11', 9, 'no', '2020-02-04 11:22:06', ''),
(231, 'HUGUET GONZÁLEZ', 'NICOLE', 048789689, '2002-02-19', 9, 'no', '2020-02-04 11:22:06', ''),
(232, 'CORCOLES IBORRA', 'ALBA', 048764354, '2002-05-09', 9, 'no', '2020-02-04 11:22:06', ''),
(233, 'PEREZ RUIZ', 'VICTORIA', 048770313, '2002-05-22', 9, 'no', '2020-02-04 11:22:06', ''),
(234, 'ARNEDO LÓPEZ', 'MARIA', 053239433, '1999-04-29', 9, 'no', '2020-02-04 11:22:06', ''),
(235, 'BRAVO GARCÍA', 'ALICIA', 003956504, '2007-01-01', 10, 'no', '2020-02-04 11:22:06', ''),
(236, 'GALVEZ LÓPEZ', 'MARTA', 003963890, '2004-01-01', 10, 'no', '2020-02-04 11:22:06', ''),
(237, 'GUTIERREZ AGUDO', 'PAULA', 004237877, '2004-01-01', 10, 'no', '2020-02-04 11:22:06', ''),
(238, 'DÍAZ GARRIDO', 'MARIA', 051186674, '2004-01-01', 10, 'no', '2020-02-04 11:22:06', ''),
(239, 'BALMASEDA GARCÍA', 'ANDREA', 004231469, '2002-07-13', 10, 'no', '2020-02-04 11:22:06', ''),
(240, 'SANCHEZ CAÑADAS', 'ANTONIA', 003942801, '2002-08-06', 10, 'no', '2020-02-04 11:22:06', ''),
(241, 'RODRIGUEZ SOLER', 'CARLA', 003940831, '2000-10-18', 10, 'no', '2020-02-04 11:22:06', ''),
(242, 'BRAVO GARCÍA', 'PILAR', 003956505, '1999-05-19', 10, 'no', '2020-02-04 11:22:06', ''),
(243, 'GARRIJO BAIDEZ', 'CARLA', 049908016, '2006-01-01', 3, 'no', '2020-02-04 11:22:06', ''),
(244, 'NOGUERA OISHI', 'NAOMI', 044448520, '2005-01-01', 3, 'no', '2020-02-04 11:22:06', ''),
(245, 'NOGUERA OISHI', 'SAYAKA', 049430721, '2007-01-01', 3, 'no', '2020-02-04 11:22:06', ''),
(246, 'FERNÁNDEZ DÍAZ', 'Mª DOLORES', 049217311, '2004-01-01', 3, 'no', '2020-02-04 11:22:06', ''),
(247, 'GOMÉZ ORTÍZ', 'CAMILA', 049212944, '2008-01-01', 3, 'no', '2020-02-04 11:22:06', ''),
(248, 'VALS SOTO', 'LAURA', 075528455, '2007-05-07', 3, 'no', '2020-02-04 11:22:06', ''),
(249, 'SÁNCHEZ BAUTISTA', 'LUCÍA', 049218065, '2003-01-01', 3, 'no', '2020-02-04 11:22:06', ''),
(250, 'NOGUERA OISHI', 'RAQUEL', 049314216, '2000-01-01', 3, 'no', '2020-02-04 11:22:06', ''),
(252, 'MARTÍNEZ LÓPEZ', 'SALMA', 047400186, '2002-01-01', 3, 'no', '2020-02-04 11:22:06', ''),
(253, 'TOLEDO REQUENA', 'NATALIA', 047446993, '2001-01-01', 3, 'no', '2020-02-04 11:22:06', ''),
(254, 'PEINA ESPARRIA', 'PAULA', 047447105, '2001-01-01', 3, 'no', '2020-02-04 11:22:06', ''),
(255, 'ROMERO PORRA ESTENA', 'PILAR', 021036155, '2004-01-01', 3, 'no', '2020-02-04 11:22:06', ''),
(256, 'MARTÍNEZ JIMÉNEZ', 'PAULA', 049908005, '2003-01-01', 3, 'no', '2020-02-04 11:22:06', ''),
(257, 'BURSJEIS', 'JULIE', 042251303, '2003-01-01', 3, 'no', '2020-02-04 11:22:06', ''),
(258, 'BURSJEIS', 'LOLA', 042251290, '2001-01-01', 3, 'no', '2020-02-04 11:22:06', ''),
(259, 'JIMÉNEZ APARICIO', 'MARIA', 049433421, '2001-01-01', 3, 'no', '2020-02-04 11:22:06', ''),
(260, 'CUESTA GUITIERREZ', 'JUDITH', 049426279, '2004-01-01', 3, 'no', '2020-02-04 11:22:06', ''),
(261, 'PARRA MARTÍNEZ', 'GISELA', 023282494, '1995-06-18', 1, 'no', '2020-02-04 11:22:06', ''),
(262, 'PADILLA HERNÁNDEZ', 'MARISA', 026547075, '2009-01-01', 19, 'no', '2020-02-04 11:22:06', ''),
(263, 'ROMERO RUÍZ', 'ALMA', 021065729, '2007-01-01', 19, 'no', '2020-02-04 11:22:06', ''),
(264, 'GALLEGO PIERNAS', 'SANDRA', '', '2005-01-01', 2, 'no', '2020-02-04 11:22:06', ''),
(265, 'MORILLAS BAYONAS', 'EVA ADELINA', '', '2004-01-01', 2, 'no', '2020-02-04 11:22:06', ''),
(266, 'CÁNOVAS REVERTE', 'PATRICIA', 023332304, '2002-01-01', 2, 'no', '2020-02-04 11:22:06', ''),
(267, 'CHUQUIN TUQUEREZ', 'JAZMIN', '', '2007-07-07', 11, 'no', '2020-02-04 11:22:06', ''),
(268, 'SÁNCHEZ GUILLÉN', 'MARIA', '', '2007-01-01', 11, 'no', '2020-02-04 11:22:06', ''),
(269, 'DELGADO CARNICER', 'PATRICIA', '', '2007-05-27', 11, 'no', '2020-02-04 11:22:06', ''),
(270, 'MARTÍNEZ PASCUAL', 'VICTORIA', '', '2007-05-28', 11, 'no', '2020-02-04 11:22:06', ''),
(271, 'PUENTE MARTÍNEZ', 'NATALIA', '', '2006-05-16', 11, 'no', '2020-02-04 11:22:06', ''),
(272, 'NAVARRO SILVESTRE', 'MAICA', '', '2006-01-01', 11, 'no', '2020-02-04 11:22:06', ''),
(273, 'SÁNCHEZ VALDIVIESO', 'LYDIA', '', '2007-02-16', 11, 'no', '2020-02-04 11:22:06', ''),
(274, 'MARTÍNEZ RUBIO', 'ADELA', '', '2005-01-01', 11, 'no', '2020-02-04 11:22:06', ''),
(275, 'SÁNCHEZ MARTÍNEZ', 'MARIA', '', '2005-11-10', 11, 'no', '2020-02-04 11:22:06', ''),
(276, 'NAVARRO BENITEZ', 'NEREA', '', '2004-01-01', 11, 'no', '2020-02-04 11:22:06', ''),
(277, 'ALARCÓN PÉREZ', 'ANABEL', '', '2004-01-01', 11, 'no', '2020-02-04 11:22:06', ''),
(278, 'MATURANA LORENTE', 'YESSICA', '', '2004-01-01', 11, 'no', '2020-02-04 11:22:06', ''),
(279, 'ANINO ASENSIO', 'NURIA', '', '2004-01-01', 11, 'no', '2020-02-04 11:22:06', ''),
(280, 'DIEPSTRATEN GARCÍA', 'YURENA', '', '2004-01-01', 11, 'no', '2020-02-04 11:22:06', ''),
(281, 'LOCUBICHE LÓPEZ', 'MARIA', '', '2003-01-01', 11, 'no', '2020-02-04 11:22:06', ''),
(282, 'LOCUBICHE LÓPEZ', 'BLANCA', '', '2003-01-01', 11, 'no', '2020-02-04 11:22:06', ''),
(283, 'ANINO ASENSIO', 'RAQUEL', '', '2002-08-21', 11, 'no', '2020-02-04 11:22:06', ''),
(284, 'HERNÁNDEZ GONZALEZ', 'CELINE', '', '2002-01-01', 11, 'no', '2020-02-04 11:22:06', ''),
(285, 'GALERA MOLINA', 'LIDIA', '', '2002-10-23', 11, 'no', '2020-02-04 11:22:06', ''),
(286, 'ROMERO MORA', 'PAULA', '23333038K', '2006-09-20', 12, 'no', '2020-02-04 11:22:06', ''),
(287, 'MARTÍNEZ NAVARRO', 'LAURA', '', '2006-01-28', 12, 'no', '2020-02-04 11:22:06', ''),
(288, 'NAVARRO CORTIJOS', 'SILVIA', '23837351J', '2006-07-12', 12, 'no', '2020-02-04 11:22:06', ''),
(289, 'DEL VALS CASANOVA', 'CARMEN MARÍA', '', '2004-12-15', 12, 'no', '2020-02-04 11:22:06', ''),
(290, 'COSTA RODRÍGUEZ', 'EVA', '', '2004-03-17', 12, 'no', '2020-02-04 11:22:06', ''),
(291, 'ANDREO SÁNCHEZ', 'JULIETA', '', '2004-06-24', 12, 'no', '2020-02-04 11:22:06', ''),
(292, 'RUSHER DÍAZ', 'ANA', '', '2003-01-12', 12, 'no', '2020-02-04 11:22:06', ''),
(293, 'DA SILVA', 'BEATRIZ', 'X7209836A', '2003-04-09', 12, 'no', '2020-02-04 11:22:06', ''),
(294, 'PEDRERO SÁNCHEZ', 'AINHOA', '49697292L', '2000-09-05', 12, 'no', '2020-02-04 11:22:06', ''),
(295, 'GARCÍA MARTÍNEZ', 'MARINA', '77837718Z', '2002-10-08', 12, 'no', '2020-02-04 11:22:06', ''),
(296, 'CARRASCO GARRE', 'OLGA', '23332115H', '2002-05-15', 12, 'no', '2020-02-04 11:22:06', ''),
(297, 'CASTILLEJO SÁNCHEZ', 'SANDRA', '23840489T', '2002-12-31', 12, 'no', '2020-02-04 11:22:06', ''),
(298, 'MARTÍNEZ IBÁÑEZ', 'ALBA', '23811308Y', '2000-07-27', 12, 'no', '2020-02-04 11:22:06', ''),
(299, 'LÓPEZ CÁNOVAS', 'MARÍA', '48835500Z', '2001-07-17', 12, 'no', '2020-02-04 11:22:06', ''),
(300, 'CERDÁ GARCÍA', 'LAURA', '77855095A', '2001-10-13', 12, 'no', '2020-02-04 11:22:06', ''),
(301, 'MARTÍNEZ NOGUERA', 'MARÍA JESÚS', '23836171Y', '2002-12-28', 12, 'no', '2020-02-04 11:22:06', ''),
(302, 'BEATTIA', 'ISABEL', 'X6335829L', '2001-11-15', 12, 'no', '2020-02-04 11:22:06', ''),
(303, 'LÓPEZ MURCIA', 'VICTORIA', '23335202A', '2000-04-20', 12, 'no', '2020-02-04 11:22:06', ''),
(304, 'GARCÍA ROSA', 'BELÉN', '23298067X', '1998-02-27', 12, 'no', '2020-02-04 11:22:06', ''),
(305, 'CARREÑO ESPADAS', 'LUCIA', 048745119, '2006-04-24', 1, 'no', '2020-02-04 11:22:06', ''),
(306, 'GONZÁLEZ MARTÍNEZ', 'ANDREA', 052828914, '2006-07-11', 1, 'no', '2020-02-04 11:22:06', ''),
(307, 'GÓMEZ GARCÍA', 'JULIA', 023836023, '2006-07-19', 1, 'no', '2020-02-04 11:22:06', ''),
(308, 'VALERO ALEJANDRO', 'NATALIA', 023329033, '2004-09-24', 1, 'no', '2020-02-04 11:22:06', ''),
(309, 'LÓPEZ HERNÁNDEZ', 'AINARA', 049172677, '2006-04-18', 1, 'no', '2020-02-04 11:22:06', ''),
(310, 'DÍAZ ANDREO', 'NATALIA', 058465643, '2006-04-27', 1, 'no', '2020-02-04 11:22:06', ''),
(311, 'SÁNCHEZ VELÁZQUEZ', 'CELIA', 021065022, '2007-09-05', 1, 'no', '2020-02-04 11:22:06', ''),
(312, 'SOTO MARTÍNEZ', 'MARTA', 049174106, '2006-09-02', 1, 'no', '2020-02-04 11:22:06', ''),
(313, 'PUCHE MUNUERA', 'CINTHYA', 058468494, '2007-09-13', 1, 'no', '2020-02-04 11:22:06', ''),
(314, 'GAMBÍN MORÁN', 'JULIA', 049173212, '2008-10-08', 1, 'no', '2020-02-04 11:22:06', ''),
(315, 'PALAZÓN CÁNOVAS', 'CLAUDIA', 049177308, '2008-04-30', 1, 'no', '2020-02-04 11:22:06', ''),
(316, 'MARTÍNEZ ALCARAZ', 'PAOLA', 017470469, '2005-12-01', 1, 'no', '2020-02-04 11:22:06', ''),
(317, 'BALSALOBRE SÁNCHEZ', 'ANGELA', 048852666, '2006-05-20', 1, 'no', '2020-02-04 11:22:06', ''),
(318, 'QUIÑONERO GARCÍA', 'HELENA', 058468468, '2007-09-21', 1, 'no', '2020-02-04 11:22:06', ''),
(319, 'OROZCO BENÍTEZ', 'DARLING', 023309313, '2006-06-10', 1, 'no', '2020-02-04 11:22:06', ''),
(320, 'NISTOR', 'IULIA ANDREA', '0Y1071910', '2005-06-19', 1, 'no', '2020-02-04 11:22:06', ''),
(321, 'RAMOS CABALLERO', 'IRENE', 077854843, '2006-02-18', 1, 'no', '2020-02-04 11:22:06', ''),
(322, 'DIEPSTRATEN GARCÍA	', 'LAURA', '', '2006-01-01', 11, 'no', '2020-02-04 11:22:06', ''),
(323, 'SÁNCHEZ MARTÍNEZ', 'MIREIA', 023335955, '2006-01-01', 13, 'no', '2020-02-04 11:22:06', ''),
(324, 'MÉNDEZ MOYA', 'LAURA', 047260829, '2006-10-15', 6, 'no', '2020-02-04 11:22:06', ''),
(325, 'COLOMAR CASTELLÓ', 'GISELA', 047260886, '2006-11-16', 6, 'no', '2020-02-04 11:22:06', ''),
(326, 'GIRÓN MARTÍNEZ', 'ÁNGELA', 047260923, '2004-12-01', 6, 'no', '2020-02-04 11:22:06', ''),
(327, 'ESCRIVÁ LOZANO', 'NAYARA', 020545037, '2004-10-18', 6, 'no', '2020-02-04 11:22:06', ''),
(328, 'KARPOVA', 'MARÍA', '0Y2338355', '2005-05-03', 8, 'no', '2020-02-04 11:22:06', ''),
(329, 'FERRER VIDAL', 'JULIA', 049233855, '2000-01-11', 8, 'no', '2020-02-04 11:22:06', ''),
(330, 'MAESTRE SANZ', 'INÉS', 004257923, '2007-03-02', 10, 'no', '2020-02-04 11:22:06', ''),
(331, 'CEREZO GARCÍA', 'LAURA', 048782632, '2006-01-04', 9, 'no', '2020-02-04 11:22:06', ''),
(332, 'MANCEBO GARCÍA', 'ELSA', 020545079, '2007-05-18', 6, 'no', '2020-02-04 11:22:06', ''),
(333, 'FERNÁNDEZ SCHULZ', 'ARIANNA', 047260790, '2007-03-22', 6, 'no', '2020-02-04 11:22:06', ''),
(334, 'ROIG MONTERO', 'PAULA', 047260888, '2007-01-31', 6, 'no', '2020-02-04 11:22:06', ''),
(335, 'BERAZATEGI HAMMER', 'KIARA', 0X9700604, '2007-07-08', 6, 'no', '2020-02-04 11:22:06', ''),
(336, 'PÉREZ VANOVERBEKE', 'NOA', 047260804, '2005-04-08', 6, 'no', '2020-02-04 11:22:06', ''),
(337, 'CASTILLO GÓMEZ', 'ADRIANA', 020545638, '2005-04-24', 6, 'no', '2020-02-04 11:22:06', ''),
(338, 'SÁNCHEZ TRILLO', 'BELEN', 047260835, '2002-04-04', 6, 'no', '2020-02-04 11:22:06', ''),
(339, 'ALVAREZ ALVES', 'BRIANNA', 048759493, '2007-05-01', 8, 'no', '2020-02-04 11:22:06', ''),
(340, 'BAKER', 'NATALIE', '0Y2405638', '2006-12-29', 8, 'no', '2020-02-04 11:22:06', ''),
(341, 'VELASCO GARCÍA', 'RUTH', 048761877, '2005-03-01', 8, 'no', '2020-02-04 11:22:06', ''),
(342, 'APARICIO TRONCHONI', 'JULIA', 074015579, '2005-09-21', 8, 'no', '2020-02-04 11:22:06', ''),
(343, 'MARTÍNEZ GALAVI', 'BELÉN', 049231871, '2003-08-12', 8, 'no', '2020-02-04 11:22:06', ''),
(344, 'BAKER', 'REBEKA', '0Y2405687', '2003-03-16', 8, 'no', '2020-02-04 11:22:06', ''),
(345, 'OROZCO IZQUIERDO', 'CRISTINA', 074017917, '2000-06-19', 8, 'no', '2020-02-04 11:22:06', ''),
(346, 'DE LA CUADRA LÓPEZ', 'CAROLINA', 003953128, '2006-09-07', 10, 'no', '2020-02-04 11:22:06', ''),
(347, 'MARTIN MENDEZ', 'REBECA', 003951204, '2007-12-05', 10, 'no', '2020-02-04 11:22:06', ''),
(348, 'TORRECILLAS MORA', 'AITANA ANTING', 003938242, '2005-05-23', 10, 'no', '2020-02-04 11:22:06', ''),
(349, 'JIMÉNEZ GIL', 'CLARA', 048793056, '2007-09-04', 9, 'no', '2020-02-04 11:22:06', ''),
(350, 'CODESO BONET', 'ADRIANA', 051266715, '2007-02-16', 9, 'no', '2020-02-04 11:22:06', ''),
(351, 'OLMO GONZALEZ', 'IRENE', 048768083, '2005-09-13', 9, 'no', '2020-02-04 11:22:06', ''),
(352, 'JIMENEZ GIL', 'LUCIA', 048793053, '2006-02-10', 9, 'no', '2020-02-04 11:22:06', ''),
(353, 'COBOS DE UNAMUNO', 'MARIA', 048766676, '2004-11-22', 9, 'no', '2020-02-04 11:22:06', ''),
(354, 'ALONSO MARTÍNEZ', 'AURORA', 048775553, '2005-09-25', 9, 'no', '2020-02-04 11:22:06', ''),
(355, 'WILBRINCK BERNABEU', 'SARA', 099166362, '2000-12-31', 9, 'no', '2020-02-04 11:22:06', ''),
(356, 'GAMEZ ÓRTIZ', 'ZARA', 049212944, '2008-12-04', 3, 'no', '2020-02-04 11:22:06', ''),
(357, 'SORIANO GONZÁLEZ', 'ROCIO', 051793460, '2007-07-01', 3, 'no', '2020-02-04 11:22:06', ''),
(358, 'RICCI', 'ELISA', '0Y0361724', '2009-12-31', 3, 'no', '2020-02-04 11:22:06', ''),
(359, 'TALAYA REQUENA', 'MAR', 051790179, '2007-01-01', 3, 'no', '2020-02-04 11:22:06', ''),
(360, 'RICCI', 'SOFIA', '0Y3617272', '2008-05-03', 3, 'no', '2020-02-04 11:22:06', ''),
(361, 'ASENSIO GARCÍA', 'IRENE', 049216828, '2009-02-11', 3, 'no', '2020-02-04 11:22:06', ''),
(362, 'RUEDA LEAL', 'BEGOÑA', 078821055, '2006-07-16', 3, 'no', '2020-02-04 11:22:06', ''),
(363, 'LOMBARDIA PÉREZ', 'CANDELA', 048259808, '2004-05-15', 3, 'no', '2020-02-04 11:22:06', ''),
(364, 'MARÍN DE LA CALLE', 'ELICIA', 049311624, '2004-10-16', 3, 'no', '2020-02-04 11:22:06', ''),
(365, 'SÁNCHEZ TAYLOR', 'SARA DANIELA', 049802416, '2001-01-01', 3, 'no', '2020-02-04 11:22:06', ''),
(366, 'PÉREZ IZQUIERDO', 'MARTINA', 049214991, '2004-02-12', 3, 'no', '2020-02-04 11:22:06', ''),
(367, 'CABRERA MARTÍNEZ', 'SANDRA', 047447540, '2003-02-15', 3, 'no', '2020-02-04 11:22:06', ''),
(368, 'SORIANO ALVAREZ', 'ISABEL', 051794445, '2003-04-06', 3, 'no', '2020-02-04 11:22:06', ''),
(369, 'MARÍN DE LA CALLE', 'CELIA', 049311623, '2002-10-13', 3, 'no', '2020-02-04 11:22:06', ''),
(370, 'MARTÍNEZ SANTOS', 'IRIS ', 049905716, '2001-11-19', 3, 'no', '2020-02-04 11:22:06', ''),
(371, 'MARTÍNEZ GARCÍA', 'LOURDES', 049904241, '2004-01-07', 3, 'no', '2020-02-04 11:22:06', ''),
(372, 'FERRER GONZÁLEZ', 'EDURNE', 047260847, '2007-01-04', 6, 'no', '2020-02-04 11:22:06', ''),
(373, 'PAÑOS CAMPILLO', 'AITANA', 020545041, '2006-04-28', 6, 'no', '2020-02-04 11:22:06', ''),
(374, 'TEODORA VLASCEANU', 'IZABELLA', '0Y0843437', '2006-07-29', 6, 'no', '2020-02-04 11:22:06', ''),
(375, 'IZQUIERDO GARCIA', 'JENIFER', 047433702, '2006-09-30', 6, 'no', '2020-02-04 11:22:06', ''),
(376, 'PITALUGA HERRERA', 'UMA', '0Y2136287', '2006-02-19', 6, 'no', '2020-02-04 11:22:06', ''),
(377, 'IZQUIERDO GARCÍA', 'CLAUDIA', 047433701, '2003-09-23', 6, 'no', '2020-02-04 11:22:06', ''),
(378, 'AGUILAR DEL RIO', 'LUCIA', 003943041, '2003-12-13', 10, 'no', '2020-02-04 11:22:06', ''),
(379, 'FERNÁNDEZ VELA', 'CÁRMEN', 048799033, '2006-09-14', 9, 'no', '2020-02-04 11:22:06', ''),
(380, 'MORENO OLIVAS', 'CÁRMEN', 053978824, '2007-11-28', 9, 'no', '2020-02-04 11:22:06', ''),
(381, 'LARRAINZAR GIJÓN', 'EGONE MARÍA', 099169682, '2006-07-15', 9, 'no', '2020-02-04 11:22:06', ''),
(382, 'COBA BORREL', 'AINARA', 099169818, '2007-09-26', 9, 'no', '2020-02-04 11:22:06', ''),
(383, 'BURDUN', 'SOFYA', 099169843, '2007-05-01', 9, 'no', '2020-02-04 11:22:06', ''),
(384, 'RUSSO DINNOZENCO', 'MARTINA', 099169844, '2008-01-02', 9, 'no', '2020-02-04 11:22:06', ''),
(385, 'TOKARENKO', 'KATYA', 099169842, '2009-01-05', 9, 'no', '2020-02-04 11:22:06', ''),
(386, 'LOSILLA PUERTA', 'EMMA', 048785044, '2008-11-03', 9, 'no', '2020-02-04 11:22:06', ''),
(387, 'CANOVAS MARTÍN', 'LUCIA', 099169684, '2006-04-21', 9, 'no', '2020-02-04 11:22:06', ''),
(388, 'MARTÍN LEAL', 'MAR', 099169683, '2004-10-14', 9, 'no', '2020-02-04 11:22:06', ''),
(389, 'COELLO AGUDO', 'ALICIA', 048675916, '1999-05-08', 9, 'no', '2020-02-04 11:22:06', ''),
(390, 'MOLLA BAÑON', 'LOLA', 050590755, '2000-10-09', 9, 'no', '2020-02-04 11:22:06', ''),
(391, 'SANFELIU GARCES', 'NEREA', 026759964, '2001-05-24', 9, 'no', '2020-02-04 11:22:06', ''),
(392, 'BAÑO MARTÍNEZ', 'CLARA', 058477021, '2009-05-14', 1, 'no', '2020-02-04 11:22:06', ''),
(393, 'BELCHÍ MAYOR', 'LUCÍA', 024468886, '2009-01-24', 1, 'no', '2020-02-04 11:22:06', ''),
(394, 'ANDANUCHE NAVARRO', 'MARTA', 021068572, '2009-02-05', 1, 'no', '2020-02-04 11:22:06', ''),
(395, 'SÁNCHEZ MELGAR', 'ALBA', 021069784, '2009-12-18', 1, 'no', '2020-02-04 11:22:06', ''),
(396, 'TOLEDO ANDREO', 'MARÍA', 048128878, '2007-04-11', 1, 'no', '2020-02-04 11:22:06', ''),
(397, 'SERRANO MORA', 'MARTA', 058476030, '2008-05-01', 1, 'no', '2020-02-04 11:22:06', ''),
(398, 'FERNÁNDEZ TOVAR', 'GEMA MARIA', 058477021, '2005-10-24', 1, 'no', '2020-02-04 11:22:06', ''),
(399, 'CEREZO GARCÍA', 'ANA', 020082986, '2006-06-14', 1, 'no', '2020-02-04 11:22:06', ''),
(400, 'GONZÁLEZ MARÍA', 'ALEJANDRA', 048854519, '2006-12-12', 1, 'no', '2020-02-04 11:22:06', ''),
(401, 'MARIE ROSS', 'LUCY', '0Y3061345', '2006-07-21', 1, 'no', '2020-02-04 11:22:06', ''),
(402, 'PUZHI', 'LUZ DEL ROCIO', '0Y1735332', '2009-01-01', 12, 'no', '2020-02-04 11:22:06', ''),
(403, 'LÓPEZ CARMONA', 'CELIA', 023835893, '2008-01-01', 12, 'no', '2020-02-04 11:22:06', ''),
(404, 'ANDREO ORTÍZ', 'TERESA', '', '2008-01-01', 12, 'no', '2020-02-04 11:22:06', ''),
(405, 'RUÍZ ROMERO', 'LAURA', 024459909, '2008-01-01', 12, 'no', '2020-02-04 11:22:06', ''),
(406, 'SEGOVIA MORA', 'MARINA', 023829551, '2007-01-01', 12, 'no', '2020-02-04 11:22:06', ''),
(407, 'ALEDO GIRONA', 'SARA', 023832186, '2006-01-01', 12, 'no', '2020-02-04 11:22:06', ''),
(408, 'MARTÍNEZ CANOVAS', 'LAURA', 026545043, '2005-01-01', 12, 'no', '2020-02-04 11:22:06', ''),
(409, 'RACERO TUDELA', 'LORENA', 053846826, '2004-01-01', 12, 'no', '2020-02-04 11:22:06', ''),
(410, 'WILKES', 'EMILY', '', '2004-01-01', 12, 'no', '2020-02-04 11:22:06', ''),
(411, 'MARTÍNEZ BARCELÓ', 'MARTA', 023329208, '2004-01-01', 12, 'no', '2020-02-04 11:22:06', ''),
(412, 'CANO ESPARZA', 'NORAH', '', '2004-01-01', 12, 'no', '2020-02-04 11:22:06', ''),
(413, 'CAMPOY SEGURA', 'ELENA', 023813074, '2009-01-01', 19, 'no', '2020-02-04 11:22:06', ''),
(414, 'BLAYA PEREZ', 'TANIA', 023810923, '2007-01-01', 19, 'no', '2020-02-04 11:22:06', ''),
(415, 'MARÍN LÓPEZ', 'GLORIA', 026859761, '2008-01-01', 19, 'no', '2020-02-04 11:22:06', ''),
(416, 'GARCÍA GIL', 'SOFIA', 021065729, '2008-01-01', 19, 'no', '2020-02-04 11:22:06', ''),
(417, 'SÁNCHEZ GUEVARA', 'Mª PILAR', 023334328, '2005-11-22', 19, 'no', '2020-02-04 11:22:06', ''),
(418, 'ALVAREZ ALVAREZ', 'CARLA', 054198391, '2006-01-01', 2, 'no', '2020-02-04 11:22:06', ''),
(419, 'MULA RODRIGUEZ', 'MARÍA', '', '2006-01-01', 2, 'no', '2020-02-04 11:22:06', ''),
(420, 'ALVAREZ ALVAREZ', 'MALENA', 054198393, '2004-01-01', 2, 'no', '2020-02-04 11:22:06', ''),
(422, 'CANTOS GARCÍA', 'IRENE', 024462170, '2003-01-01', 2, 'no', '2020-02-04 11:22:06', ''),
(423, 'DÍAZ CAMPOY', 'LORENA', '', '2008-01-01', 11, 'no', '2020-02-04 11:22:06', ''),
(424, 'LÓPEZ GARCÍA', 'AROA', '', '2004-01-01', 11, 'no', '2020-02-04 11:22:06', ''),
(425, 'GIMÉNEZ CAMUS', 'SANDRA', '', '2004-01-01', 11, 'no', '2020-02-04 11:22:06', ''),
(426, 'MARÍN GIMÉNEZ', 'ALBA', '', '2004-01-01', 11, 'no', '2020-02-04 11:22:06', ''),
(427, 'MARTOS QUESADA', 'LUCIA', '', '2007-01-01', 11, 'no', '2020-02-04 11:22:06', ''),
(428, 'PÉREZ JIMENEZ', 'SANDRA', '', '2007-12-21', 11, 'no', '2020-02-04 11:22:06', ''),
(429, 'RODRIGUEZ HAROS', 'MARIA', '', '2007-01-01', 11, 'no', '2020-02-04 11:22:06', ''),
(430, 'SILVENTE NEVES', 'VITORIA', '', '2005-02-13', 11, 'no', '2020-02-04 11:22:06', ''),
(431, 'RODRIGUEZ GONZALEZ', 'MARA', '', '2007-01-01', 11, 'no', '2020-02-04 11:22:06', ''),
(432, 'TUDELA MULA', 'ALMUDENA', '', '2006-01-01', 12, 'no', '2020-02-04 11:22:06', ''),
(433, 'RUIZ RODRIGUEZ', 'MARIA MAGDALENA', '', '2006-08-28', 12, 'no', '2020-02-04 11:22:06', ''),
(434, 'GARCÍA LÓPEZ', 'PAULA', 051792671, '2008-10-31', 3, 'no', '2020-02-04 11:22:06', ''),
(435, 'TELLEZ MANZANO', 'DANA SOFÍA', 049310967, '2009-12-22', 3, 'no', '2020-02-04 11:22:06', ''),
(436, 'MONTEAGUDO MARTÍNEZ', 'LEYRE', 099086780, '2010-07-23', 3, 'no', '2020-02-04 11:22:06', ''),
(437, 'SELENA MORALES', 'MARIA', 049801301, '2010-07-23', 3, 'no', '2020-02-04 11:22:06', ''),
(438, 'ROMERO SÁNCHEZ', 'LUCIA', 054745965, '2006-07-17', 3, 'no', '2020-02-04 11:22:06', ''),
(439, 'GARCÍA GARCÍA', 'SARA', 049904134, '2007-10-03', 3, 'no', '2020-02-04 11:22:06', ''),
(440, 'PARDO MARTÍNEZ', 'MARTA', 048151505, '2005-12-01', 3, 'no', '2020-02-04 11:22:06', ''),
(441, 'TOLEDO RUÍZ', 'LAURA', 049313698, '2006-02-14', 3, 'no', '2020-02-04 11:22:06', ''),
(442, 'CORTÉS BARRA', 'LIDIA', 099086779, '2009-03-12', 3, 'no', '2020-02-04 11:22:06', ''),
(443, 'GARCÍA GARCÍA', 'MARIA', 049427279, '2009-01-25', 3, 'no', '2020-02-04 11:22:06', ''),
(444, 'CORTÉS AUÑON', 'INÉS', 049310458, '2008-03-31', 3, 'no', '2020-02-04 11:22:06', ''),
(445, 'NAVARRO MENCHERO', 'NATALIA', 049908457, '2006-02-01', 3, 'no', '2020-02-04 11:22:06', ''),
(446, 'LÓPEZ GONZÁLEZ', 'LAURA', 049311842, '2006-01-28', 3, 'no', '2020-02-04 11:22:06', ''),
(447, 'MEDRANO ALCOCER', 'MARÍA JOSÉ', 049906543, '2008-05-15', 3, 'no', '2020-02-04 11:22:06', ''),
(448, 'CUESTA GUTIERREZ', 'IDAIRA', 049426280, '2006-10-22', 3, 'no', '2020-02-04 11:22:06', ''),
(449, 'BAUTISTA DÍAZ', 'LUCÍA', 049310610, '2007-05-28', 3, 'no', '2020-02-04 11:22:06', ''),
(450, 'LÓPEZ HARO', 'YOLANDA', 049907474, '2004-04-01', 3, 'no', '2020-02-04 11:22:06', ''),
(451, 'PEÑALVER SIMM', 'NOELIA', 'PAA426029', '2007-12-13', 8, 'no', '2020-02-04 11:22:06', ''),
(452, 'CABEZAS', 'SOFÍA', 'Y-1405712', '2007-09-22', 8, 'no', '2020-02-04 11:22:06', ''),
(453, 'TORTOSA BENIATE', 'SOFÍA', 048759837, '2008-02-20', 8, 'no', '2020-02-04 11:22:06', ''),
(454, 'PIÑERA QUINTERO', 'ADRIANA', 048761712, '2008-01-19', 8, 'no', '2020-02-04 11:22:06', ''),
(455, 'UMMELS', 'CHANTAL', 'NN3H6RF02', '2007-06-11', 8, 'no', '2020-02-04 11:22:06', ''),
(456, 'SÁNCHEZ SANTAMARIA', 'MARIETA', 048759968, '2007-06-05', 8, 'no', '2020-02-04 11:22:06', ''),
(457, 'CABALLERO SÁNCHEZ', 'CLAUDIA', 049371853, '2008-04-28', 8, 'no', '2020-02-04 11:22:06', ''),
(458, 'JIMÉNEZ ESCARCENA', 'LAURA', 099169972, '2008-07-25', 8, 'no', '2020-02-04 11:22:06', ''),
(459, 'MAYOR SÁNCHEZ', 'ANNA', 048761366, '2008-07-29', 8, 'no', '2020-02-04 11:22:06', ''),
(460, 'CAMPOS ESTRAFA', 'GRACI', 049232029, '2008-09-24', 8, 'no', '2020-02-04 11:22:06', ''),
(461, 'COZAC ENSONE', 'ABRIL', 049748268, '2005-06-24', 8, 'no', '2020-02-04 11:22:06', ''),
(462, 'JURADO ARANGURI', 'CLARET', 048683922, '2005-03-15', 8, 'no', '2020-02-04 11:22:06', ''),
(463, 'ROGGEN MIRA', 'CLAUDIA', 049233407, '2005-11-11', 8, 'no', '2020-02-04 11:22:06', ''),
(464, 'POZO GUTIERREZ', 'MIREIA', 049373645, '2006-09-21', 8, 'no', '2020-02-04 11:22:06', ''),
(465, 'VERDUZCO ESPINOSA', 'AITANA', 048784744, '2006-09-27', 8, 'no', '2020-02-04 11:22:06', ''),
(466, 'SERER BOYS', 'NATASHA', 048759424, '2007-10-17', 8, 'no', '2020-02-04 11:22:06', ''),
(467, 'COLOMBO GONZÁLEZ', 'ARIANNA', 048682570, '2005-06-05', 8, 'no', '2020-02-04 11:22:06', ''),
(468, 'ESTEBAN BOU', 'PATRICIA', 049761397, '2002-04-01', 8, 'no', '2020-02-04 11:22:06', ''),
(469, 'RAMALLO', 'MICAELA', '0Y1670533', '2002-07-21', 8, 'no', '2020-02-04 11:22:06', ''),
(470, 'TIRADO MORENO', 'MARIOLA', 049411361, '2010-04-20', 15, 'no', '2020-02-04 11:22:06', ''),
(471, 'CARDONA BELLO', 'LUCIA', 048230678, '2008-12-16', 15, 'no', '2020-02-04 11:22:06', ''),
(472, 'BONET CABEZAS', 'ALBA LOUISE', 048230787, '2008-10-02', 15, 'no', '2020-02-04 11:22:06', ''),
(473, 'ANTICH AMOR', 'BERTA', 048199145, '2008-07-04', 15, 'no', '2020-02-04 11:22:06', ''),
(474, 'BONET PEREZ DE OLAGUER', 'MALEN', 048232981, '2008-05-19', 15, 'no', '2020-02-04 11:22:06', ''),
(475, 'VALVERDE ROMAN', 'PAULA', 048198113, '2008-02-09', 15, 'no', '2020-02-04 11:22:06', ''),
(476, 'KELLNER', 'CHANTAL', 000200696, '2007-12-16', 15, 'no', '2020-02-04 11:22:06', ''),
(477, 'MARTINEZ CAMPOY', 'BLANCA', 046077097, '2007-08-06', 15, 'no', '2020-02-04 11:22:06', ''),
(478, 'BARRIONUEVO TUR', 'ANDREA', 048198608, '2007-10-01', 15, 'no', '2020-02-04 11:22:06', ''),
(479, 'MEGIDO LUX', 'GRETA', 048198320, '2006-11-11', 15, 'no', '2020-02-04 11:22:06', ''),
(480, 'BELDA BELDA', 'MARIOLA', 047433605, '2006-11-04', 15, 'no', '2020-02-04 11:22:06', ''),
(481, 'MARI MARQUINA', 'MARTA', 047430015, '2006-07-04', 15, 'no', '2020-02-04 11:22:06', ''),
(482, 'ROIG PLANELLS', 'LAIA', 047406773, '2004-10-30', 15, 'no', '2020-02-04 11:22:06', ''),
(483, 'TUR PLANELLS', 'LUCIA', 049410084, '2004-10-16', 15, 'no', '2020-02-04 11:22:06', ''),
(484, 'CAPITAN', 'BEATRIZ', 007864166, '2004-09-11', 15, 'no', '2020-02-04 11:22:06', ''),
(485, 'CAIÑA GIMENO', 'RUTH', 047432018, '2004-06-30', 15, 'no', '2020-02-04 11:22:06', ''),
(486, 'SART MARCOS', 'MIRIAM', 047406345, '2004-03-18', 15, 'no', '2020-02-04 11:22:06', ''),
(487, 'SUAREZ YERN', 'JOELE ÁFRICA', 047405317, '2004-02-13', 15, 'no', '2020-02-04 11:22:06', ''),
(488, 'TEJADA LARA', 'LUCIA', 047409408, '2003-06-02', 15, 'no', '2020-02-04 11:22:06', ''),
(489, 'MUÑOZ RODRIGUEZ', 'ANA', 048230968, '2002-12-10', 15, 'no', '2020-02-04 11:22:06', ''),
(490, 'RIVERA HENAO', 'VALENTINA', 049108942, '2002-08-24', 15, 'no', '2020-02-04 11:22:06', ''),
(491, 'PIEDRA RIERA', 'LOLA', 048198464, '2001-06-03', 15, 'no', '2020-02-04 11:22:06', ''),
(492, 'SART MARCOS', 'ANDREA', 047406344, '2001-04-28', 15, 'no', '2020-02-04 11:22:06', ''),
(493, 'PLATA ESCOLAN', 'HENAR', 047408673, '2000-04-12', 15, 'no', '2020-02-04 11:22:06', ''),
(494, 'ORMAHECHEA GREGORI', 'NEREA', 047433414, '2000-03-23', 15, 'no', '2020-02-04 11:22:06', ''),
(495, 'ORTIZ RAMOS', 'Mª EVELYN', 047252111, '1992-12-15', 15, 'no', '2020-02-04 11:22:06', ''),
(496, 'CIVICO CUENCA', 'CLAUDIA', 046995899, '1993-11-11', 15, 'no', '2020-02-04 11:22:06', ''),
(497, 'PASTOR MARTÍNEZ', 'CANDELA', 054204056, '2008-02-26', 9, 'no', '2020-02-04 11:22:06', ''),
(498, 'LEIVA UCLES', 'JULIA', 099169996, '2008-10-01', 9, 'no', '2020-02-04 11:22:06', ''),
(499, 'ORTS LLORET', 'MARIA DOLORES', 048770938, '2007-11-08', 9, 'no', '2020-02-04 11:22:06', ''),
(500, 'OLIVER GASNIER', 'LAIA', 099169989, '2008-12-27', 9, 'no', '2020-02-04 11:22:06', ''),
(501, 'ESTEBAN CABEZUELO', 'ADA', 099169991, '2010-01-01', 9, 'no', '2020-02-04 11:22:06', ''),
(502, 'BOBKOVA', 'DARIA', 099169993, '2010-02-18', 9, 'no', '2020-02-04 11:22:06', ''),
(503, 'SÁNCHEZ FERNÁNDEZ', 'LEYRE', 099169896, '2005-01-01', 9, 'no', '2020-02-04 11:22:06', ''),
(504, 'SOLA PIÑERO', 'INES', 074533575, '2005-01-01', 9, 'no', '2020-02-04 11:22:06', ''),
(505, 'PASTOR CERDÁ', 'BELEN', 054205059, '2005-06-27', 9, 'no', '2020-02-04 11:22:06', ''),
(506, 'POVEDA MARTÍNEZ', 'AITANA', 099169687, '2006-01-01', 9, 'no', '2020-02-04 11:22:06', ''),
(507, 'CORRALES RAMIREZ', 'ARIADNA', 048667475, '2005-06-24', 9, 'no', '2020-02-04 11:22:06', ''),
(508, 'URIBE CARDOZO', 'OLIVIA', 099169818, '', 9, 'no', '2020-02-04 11:22:06', ''),
(509, 'EGEA ORTÍZ', 'ROCIO', '', '2007-01-01', 7, 'no', '2020-02-04 11:22:06', ''),
(510, 'GUTIERREZ GARCÍA', 'LAURA', '', '2007-01-01', 7, 'no', '2020-02-04 11:22:06', ''),
(511, 'GARCÍA MARTÍNEZ', 'ANGELA', '', '2008-01-01', 7, 'no', '2020-02-04 11:22:06', ''),
(512, 'ALCANIZ HERNÁNDEZ', 'CAROLINA', '', '2008-01-01', 7, 'no', '2020-02-04 11:22:06', ''),
(513, 'SÁNCHEZ LÓPEZ', 'SOFÍA', '', '2007-01-01', 7, 'no', '2020-02-04 11:22:06', ''),
(514, 'VILLA NAVARRO', 'MARIA', '', '2007-01-01', 7, 'no', '2020-02-04 11:22:06', ''),
(515, 'GALLARDO SERRANO', 'LORENA', '', '2009-01-01', 13, 'no', '2020-02-04 11:22:06', ''),
(516, 'GALLARDO SERRANO', 'ELENA', 023838286, '2006-01-01', 13, 'no', '2020-02-04 11:22:06', ''),
(517, 'MULA RODRIGUEZ', 'MARIA', 024458733, '2006-01-01', 13, 'no', '2020-02-04 11:22:06', ''),
(518, 'BAYONAS RODRIGUEZ', 'MARI CARMEN', 024458732, '2004-01-01', 13, 'no', '2020-02-04 11:22:06', ''),
(519, 'MARTÍNEZ PASCUAL', 'ANGELA', '', '2010-09-13', 11, 'no', '2020-02-04 11:22:06', ''),
(520, 'DÍAZ MARTÍNEZ', 'LAURA', '', '2006-07-19', 12, 'no', '2020-02-04 11:22:06', ''),
(521, 'DÍAZ MARTÍNEZ', 'VIRGINIA', '', '2006-07-18', 12, 'no', '2020-02-04 11:22:06', ''),
(522, 'CRESPO CANOVAS', 'MARIA', '', '2006-02-14', 12, 'no', '2020-02-04 11:22:06', ''),
(523, 'MARTÍNEZ ANDREU', 'ALICIA', '', '2005-08-28', 12, 'no', '2020-02-04 11:22:06', ''),
(524, 'RODRIGUEZ LIDÓN', 'IRENE', '', '2006-03-18', 12, 'no', '2020-02-04 11:22:06', ''),
(525, 'GARCÍA VALENZUELA', 'ESTRELLA', '', '2005-04-26', 12, 'no', '2020-02-04 11:22:06', ''),
(526, 'CANOVAS ASENSIO', 'ADRIANA', '', '2005-05-15', 12, 'no', '2020-02-04 11:22:06', ''),
(527, 'DUNKAN ROSA', 'ANISIA', '', '2004-08-22', 12, 'no', '2020-02-04 11:22:06', ''),
(528, 'MARTÍNEZ MARTÍNEZ', 'PILAR MARIA', '', '2004-12-29', 12, 'no', '2020-02-04 11:22:06', ''),
(529, 'DÍAZ SCHIMANSKY', 'NINA', 023837231, '2009-01-01', 13, 'no', '2020-02-04 11:22:06', ''),
(530, 'GÓNZALEZ SÁNCHEZ', 'IRENE', 026862157, '2010-01-01', 19, 'no', '2020-02-04 11:22:06', ''),
(531, 'GARRIDO PÉREZ', 'AINARA', '', '2008-01-01', 7, 'no', '2020-02-04 11:22:06', ''),
(532, 'MAZUECOS-RECHE GONZÁLEZ', 'BLANCA', '', '2006-01-01', 2, 'no', '2020-02-04 11:22:06', ''),
(533, 'DE GONELL', 'MARIANELLA', '', '2007-01-01', 7, 'no', '2020-02-04 11:22:06', ''),
(534, 'BERNAL FERAO', 'PATRICIA', '', '2005-01-01', 7, 'no', '2020-02-04 11:22:06', ''),
(535, 'PÉREZ GREGORIO', 'CLARA', '', '2005-01-01', 7, 'no', '2020-02-04 11:22:06', ''),
(536, 'MARTÍNEZ MARTÍN', 'NEREA', '', '2010-01-01', 7, 'no', '2020-02-04 11:22:06', ''),
(537, 'ARAGONESES SANTACRUZ', 'BLANCA', '', '2010-01-01', 7, 'no', '2020-02-04 11:22:06', ''),
(538, 'PÉREZ GARCÍA', 'MARIA PILAR', '', '2008-01-01', 7, 'no', '2020-02-04 11:22:06', ''),
(539, 'BERTHELOT CUELLO', 'MARINA', '', '2008-01-01', 7, 'no', '2020-02-04 11:22:06', ''),
(540, 'CAZORLA BENEDICTO', 'MARIELA', 058485702, '2010-07-13', 1, 'no', '2020-02-04 11:22:06', ''),
(541, 'FERNANDEZ MIRAS', 'PAULA', 049176441, '2009-01-01', 1, 'no', '2020-02-04 11:22:06', ''),
(542, 'LÓPEZ GARCÍA', 'CARLA', '', '2010-01-01', 1, 'no', '2020-02-04 11:22:06', ''),
(543, 'VERA SEMPERE', 'MIRIAM', 058479291, '2010-02-04', 1, 'no', '2020-02-04 11:22:06', ''),
(544, 'LUCAS LÓPEZ', 'AINHOA', 049176196, '2010-01-01', 1, 'no', '2020-02-04 11:22:06', ''),
(545, 'GÓMEZ MALDONADO', 'MAR', '', '2009-01-01', 1, 'no', '2020-02-04 11:22:06', ''),
(546, 'REDONDO JAVALOY', 'ARANTXA', 052042702, '2009-09-28', 1, 'no', '2020-02-04 11:22:06', ''),
(547, 'MARTÍNEZ RODRIGUEZ', 'ANDREA', 049440037, '2010-01-01', 1, 'no', '2020-02-04 11:22:06', ''),
(548, 'JIMÉNEZ MORALES', 'PILAR', 024468979, '2010-01-01', 1, 'no', '2020-02-04 11:22:06', ''),
(549, 'SÁNCHEZ GONZÁLEZ', 'MARIA ISABEL', 023832597, '2007-01-01', 19, 'no', '2020-02-04 11:22:06', ''),
(550, 'MOLINA GONZÁLEZ', 'MARINA', 023840364, '2009-01-01', 19, 'no', '2020-02-04 11:22:06', ''),
(551, 'AGUILAR SÁNCHEZ', 'MARIA DEL PILAR', '', '2007-01-01', 12, 'no', '2020-02-04 11:22:06', ''),
(552, 'FERNÁNDEZ GONZÁLEZ', 'LUCÍA', 041664049, '2009-01-01', 16, '', '2020-02-04 11:22:06', ''),
(553, 'MORA ADÁN', 'MARINA', 045691752, '2006-01-01', 16, '', '2020-02-04 11:22:06', ''),
(554, 'NADAL ESCANDELL', 'BELISA', 049605247, '2007-01-01', 16, '', '2020-02-04 11:22:06', ''),
(555, 'POLO', 'ANGELA', 043469429, '2006-01-01', 16, '', '2020-02-04 11:22:06', ''),
(556, 'QUINTANA RUIZ', 'ALBA', 043187692, '2000-01-01', 16, '', '2020-02-04 11:22:06', ''),
(557, 'FONTCUBERTA RIGO', 'MARGALIDA', 041658647, '2001-01-01', 16, '', '2020-02-04 11:22:06', ''),
(558, 'MARTIN FARIÑAS', 'PATRICIA', 043483386, '2002-01-01', 16, '', '2020-02-04 11:22:06', ''),
(559, 'IBARS CULLA', 'LIDIA', 043224994, '2002-01-01', 16, '', '2020-02-04 11:22:06', ''),
(560, 'RUIZ ALCINA', 'CELIA', 045190737, '2002-01-01', 16, '', '2020-02-04 11:22:06', ''),
(561, 'BONNIN PLANELLS', 'PAULA', 043214064, '2001-01-01', 16, '', '2020-02-04 11:22:06', ''),
(562, 'MOREY SÁNCHEZ', 'SARA', 046397419, '2003-01-01', 16, '', '2020-02-04 11:22:06', ''),
(563, 'MOREY SÁNCHEZ', 'NEUS', 046397421, '2005-01-01', 16, '', '2020-02-04 11:22:06', ''),
(564, 'ESTEVA MUÑOZ', 'ALBA', 043231133, '2003-01-01', 16, '', '2020-02-04 11:22:06', ''),
(565, 'TORO SERRANO', 'PAULA', 043231861, '2003-01-01', 16, '', '2020-02-04 11:22:06', ''),
(566, 'PORTAL SÁNCHEZ', 'OLGA', 049483819, '2005-01-01', 16, '', '2020-02-04 11:22:06', ''),
(567, 'RUIZ EZELIUS', 'ELSA', 041658265, '2006-01-01', 4, '', '2020-02-04 11:22:06', ''),
(568, 'ACUNA', 'SOPHIA ELENA', 009670664, '2006-01-01', 4, '', '2020-02-04 11:22:06', ''),
(569, 'AGUILO RUIZ', 'NURIA', 045609637, '2007-01-01', 4, '', '2020-02-04 11:22:06', ''),
(570, 'REUS SANS', 'ARIADNA', 041692085, '2007-01-01', 4, '', '2020-02-04 11:22:06', ''),
(571, 'AMER GELABERT', 'LAIA', 045193314, '2009-01-01', 4, '', '2020-02-04 11:22:06', ''),
(572, 'LOPEZ-PINTO GINARD', 'TERESA', 045694067, '2009-01-01', 4, '', '2020-02-04 11:22:06', ''),
(573, 'CODESO ESTEVEZ', 'AINHOA', 045691976, '2009-01-01', 4, '', '2020-02-04 11:22:06', ''),
(574, 'SERRA PUJOL', 'MARIONA', 020550002, '2008-01-01', 4, '', '2020-02-04 11:22:06', ''),
(575, 'GRANADO FERNANDEZ', 'LUCIA', 045695055, '2005-01-01', 4, '', '2020-02-04 11:22:06', ''),
(576, 'BATLE QUETGLAS', 'MARTA', 045969453, '2005-01-01', 4, '', '2020-02-04 11:22:06', ''),
(577, 'ALONSO GARCÍA', 'MARIA', 049772529, '2005-01-01', 4, '', '2020-02-04 11:22:06', ''),
(578, 'PUJOL ONIEVA', 'CLARA', 078222192, '2005-01-01', 4, '', '2020-02-04 11:22:06', ''),
(579, 'DOMENECH FIOL', 'AINA', 043478926, '2005-01-01', 4, '', '2020-02-04 11:22:06', ''),
(580, 'GONZALEZ MIR', 'NORA', 045189906, '2004-01-01', 4, '', '2020-02-04 11:22:06', ''),
(581, 'VALLE MARTORELL', 'LAURA', 041657900, '2004-01-01', 4, '', '2020-02-04 11:22:06', ''),
(582, 'GARCIAS GINART', 'MARIA', 041583292, '1994-01-01', 4, '', '2020-02-04 11:22:06', ''),
(583, 'GRANADO GERNANDEZ', 'NEUS', 045695057, '2002-01-01', 4, '', '2020-02-04 11:22:06', ''),
(584, 'JAVEGA MAESO', 'LEIRE', 049213866, '2008-01-01', 3, '', '2020-02-04 11:22:06', ''),
(585, 'MARTÍNEZ GONZÁLEZ', 'MARI LUZ', 099086901, '2011-01-01', 3, '', '2020-02-04 11:22:06', ''),
(586, 'FERNÁNDEZ MUÑOZ', 'BLANCA', 055051116, '2011-01-01', 3, '', '2020-02-04 11:22:06', ''),
(587, 'LIÑAN MONTEALEGRE', 'VALERIA', 004742447, '2008-01-01', 3, '', '2020-02-04 11:22:06', ''),
(588, 'ROMERO FRANCO', 'ESTHER', 004921558, '2009-01-01', 3, '', '2020-02-04 11:22:06', ''),
(589, 'ROMERO ALVAREZ', 'IRENE', 049212739, '2008-01-01', 3, '', '2020-02-04 11:22:06', ''),
(590, 'CONTRERAS FERNÁNDEZ', 'LUZ', 051790846, '2012-01-01', 3, '', '2020-02-04 11:22:06', ''),
(591, 'MARTÍNEZ CARO', 'SOFIA', 049428570, '2010-01-01', 3, '', '2020-02-04 11:22:06', ''),
(592, 'GÓMEZ COUQUE', 'CANDELA', 049314804, '2006-01-01', 3, '', '2020-02-04 11:22:06', ''),
(593, 'SÁNCHEZ SÁNCHEZ', 'MARIA', 099086902, '2005-01-01', 3, '', '2020-02-04 11:22:06', ''),
(594, 'VERDERA MARQUEZ', 'EVELIN', 020545044, '2009-01-01', 6, '', '2020-02-04 11:22:06', ''),
(595, 'BERAZATEGUI', 'MIA', 002239169, '2010-01-01', 6, '', '2020-02-04 11:22:06', ''),
(596, 'SERRA CARDONA', 'EVA', 020545333, '2010-01-01', 6, '', '2020-02-04 11:22:06', ''),
(597, 'MANCEBO GARCIA', 'ENMA', 020545081, '2007-01-01', 6, '', '2020-02-04 11:22:06', ''),
(598, 'TABINI', 'LOLA', 000620786, '2007-01-01', 6, '', '2020-02-04 11:22:06', ''),
(599, 'CAMPILLO RIVAS', 'LEONOR', 077542832, '2009-01-01', 6, '', '2020-02-04 11:22:06', ''),
(600, 'ESCANDELL FERRER', 'JULIA', 048231295, '2008-01-01', 6, '', '2020-02-04 11:22:06', ''),
(601, 'MANCEBO BAÑOS', 'ANA MARIA', 047260950, '2008-01-01', 6, '', '2020-02-04 11:22:06', ''),
(602, 'ALEGRE VALLADOLIZ', 'AROA', 020545042, '2008-01-01', 6, '', '2020-02-04 11:22:06', ''),
(603, 'CABRERA MELIAN', 'ROMINA', 078643505, '1992-01-01', 6, '', '2020-02-04 11:22:06', ''),
(604, 'LÓPEZ ZARAGOZA', 'MARIA YUE', 009136858, '2004-01-01', 17, '', '2020-02-04 11:22:06', ''),
(605, 'MEDRANO ROMERA', 'ANDREA', 051246683, '2004-01-01', 17, '', '2020-02-04 11:22:06', ''),
(606, 'MARTIN CAMPOS', 'CELIA', 049151514, '2004-01-01', 17, '', '2020-02-04 11:22:06', ''),
(607, 'GARCÍA OLANO', 'SARA', 006024731, '2005-01-01', 17, '', '2020-02-04 11:22:06', ''),
(608, 'MATEOS PEDRERO', 'NOA', 049138012, '2005-01-01', 17, '', '2020-02-04 11:22:06', ''),
(609, 'SÁNCHEZ GARCÍA', 'CLAUDIA', 006637992, '2003-01-01', 17, '', '2020-02-04 11:22:06', ''),
(610, 'GIL DE ZUÑIGA CACERES', 'CLAUIDA', 050252798, '2003-01-01', 17, '', '2020-02-04 11:22:06', ''),
(611, 'SANTOS MIGUEL', 'LUCIA', 006598746, '2003-01-01', 17, '', '2020-02-04 11:22:06', ''),
(612, 'RIVAS LOPESINOS', 'ARANZAZÚ', 054243074, '2003-01-01', 17, '', '2020-02-04 11:22:06', ''),
(613, 'LARRABEITI MARTÍNEZ', 'MARIA', 054032856, '2003-01-01', 17, '', '2020-02-04 11:22:06', ''),
(614, 'GORGOJO DE FRUTOS', 'CARLA', 011897512, '2002-01-01', 17, '', '2020-02-04 11:22:06', ''),
(615, 'DE VICENCE JOHNSON', 'CLAUDIA', 006032425, '2001-01-01', 17, '', '2020-02-04 11:22:06', ''),
(616, 'ALVAREDO DEL PINO', 'LAURA', 001939305, '2001-01-01', 17, '', '2020-02-04 11:22:06', ''),
(617, 'GIMÉNEZ MIRA', 'ESTHER', 050508805, '2006-01-01', 9, '', '2020-02-04 11:22:06', ''),
(618, 'MESEGUER BLASCO', 'PAULA', 054208201, '2006-01-01', 9, '', '2020-02-04 11:22:06', ''),
(619, 'LÓPEZ SEMPERE', 'MARINA', 050509397, '2008-01-01', 9, '', '2020-02-04 11:22:06', ''),
(620, 'MORENO FERNÁNDEZ', 'EVA', 005468605, '2007-01-01', 9, '', '2020-02-04 11:22:06', ''),
(621, 'CARRASCO YEPES', 'SARA', 074385768, '2001-01-01', 9, '', '2020-02-04 11:22:06', ''),
(622, 'VALERO ALEJANDRO', 'AMANDA', '', '2011-07-21', 1, '', '2020-02-04 11:22:06', ''),
(623, 'MELLADO COSTA', 'AMAYA', 021068734, '2011-09-26', 1, '', '2020-02-04 11:22:06', ''),
(624, 'ARROYO MORA', 'REBECA', '', '2011-03-02', 1, '', '2020-02-04 11:22:06', ''),
(625, 'GARCÍA GRAÑÉ', 'LIDIA', '', '2011-02-11', 1, '', '2020-02-04 11:22:06', ''),
(626, 'ORTUÑO AZNAR', 'MARÍA', '', '2011-01-08', 1, '', '2020-02-04 11:22:06', ''),
(627, 'HOUDUSSE VIALAS', 'CAROLINE', '0Y1773150', '2010-03-21', 1, '', '2020-02-04 11:22:06', ''),
(628, 'BALSALOBRE SÁNCHEZ', 'JULIA', '', '2010-11-08', 1, '', '2020-02-04 11:22:06', ''),
(629, 'GONZÁLEZ ESPADAS', 'MARÍA', '21068573K', '2010-08-17', 1, '', '2020-02-04 11:22:06', ''),
(630, 'GÓNZALEZ MARTÍNEZ', 'LAURA', '49176853R', '2010-06-11', 1, '', '2020-02-04 11:22:06', ''),
(631, 'PATRU', 'ANDREA LORENA', '01657394M', '2010-10-23', 1, '', '2020-02-04 11:22:06', ''),
(632, 'SÁEZ VEINTIMILLA', 'MARÍA ISABEL', '23836168A', '2010-06-30', 1, '', '2020-02-04 11:22:06', ''),
(633, 'STAN', 'MADALINA GABRIELA', 'Y-1676395-', '2010-11-06', 1, '', '2020-02-04 11:22:06', ''),
(634, 'PATRU', 'DENISA NICOLETA', '00877079B', '2009-07-02', 1, '', '2020-02-04 11:22:06', ''),
(635, 'BONILLO MARTÍNEZ', 'ISABEL', '', '2010-01-01', 18, '', '2020-02-04 11:22:06', ''),
(636, 'FERNANDEZ FERNANDEZ', 'ALMUDENA', '', '2008-01-01', 18, '', '2020-02-04 11:22:06', ''),
(637, 'GARCIA IONOVA', 'NATALIA', '', '2008-01-01', 18, '', '2020-02-04 11:22:06', ''),
(638, 'MARTINEZ PEÑA', 'NAZARET', '', '2008-01-01', 18, '', '2020-02-04 11:22:06', ''),
(639, 'MENA CUELI', 'CRISTINA', '', '2008-01-01', 18, '', '2020-02-04 11:22:06', ''),
(640, 'GOMEZ EGEA', 'MARIA EUGENIA', '', '2007-01-01', 18, '', '2020-02-04 11:22:06', ''),
(641, 'SANCHEZ TERUEL', 'ALBA', '', '2006-01-01', 18, '', '2020-02-04 11:22:06', ''),
(642, 'PEREZ SANCHEZ', 'ISABEL', '', '2006-01-01', 18, '', '2020-02-04 11:22:06', ''),
(643, 'PARRA PARRA', 'MARIA DEL MAR', '', '2006-01-01', 18, '', '2020-02-04 11:22:06', ''),
(644, 'MORATA MARTINEZ', 'AITANA', '', '2008-01-01', 18, '', '2020-02-04 11:22:06', ''),
(645, 'DIAZ PARRA', 'LAURA', '', '2010-01-01', 11, '', '2020-02-04 11:22:06', ''),
(646, 'HARO JEREZ', 'FATIMA', '', '2010-01-01', 11, '', '2020-02-04 11:22:06', ''),
(647, 'RODAS PEREZ', 'DANIELA', '', '2010-01-01', 11, '', '2020-02-04 11:22:06', ''),
(648, 'PEREZ MARTINEZ', 'CLAUDIA', '', '2010-01-01', 11, '', '2020-02-04 11:22:06', ''),
(649, 'PEREZ SANCHEZ', 'GEMA', '', '2007-01-01', 11, '', '2020-02-04 11:22:06', ''),
(650, 'LOPEZ GUIRAO', 'IRENE', '', '2005-01-01', 11, '', '2020-02-04 11:22:06', ''),
(651, 'RUBIO SOSA', 'NADIRA', 030295598, '2010-01-01', 13, '', '2020-02-04 11:22:06', ''),
(652, 'PEREZ JIMENEZ', 'ESTELA', '', '2010-01-01', 13, '', '2020-02-04 11:22:06', ''),
(653, 'LOPEZ CARRASCO', 'MARTA', 023831736, '2010-01-01', 13, '', '2020-02-04 11:22:06', ''),
(654, 'VICO DIAZ', 'CARMEN', '', '2008-01-01', 13, '', '2020-02-04 11:22:06', ''),
(655, 'VIUDEZ MORALES', 'PAULA', 023829636, '2007-01-01', 13, '', '2020-02-04 11:22:06', ''),
(656, 'SEGURA GUIRAO', 'DANIELA', '', '2007-01-01', 13, '', '2020-02-04 11:22:06', ''),
(657, 'SIMON ROMERA', 'ANA', 023330171, '2007-01-01', 13, '', '2020-02-04 11:22:06', ''),
(658, 'GAZQUEZ PERNIAS', 'CLAUDIA', 023334284, '2006-01-01', 13, '', '2020-02-04 11:22:06', ''),
(659, 'SIMON PARRA', 'ANA', 024459818, '2006-01-01', 13, '', '2020-02-04 11:22:06', ''),
(660, 'GARCIA DE LAS BAYONAS ROSIQUE', 'LUCIA', 023834846, '2005-01-01', 13, '', '2020-02-04 11:22:06', ''),
(661, 'ROMERO RUIZ', 'NOA', '', '2011-01-01', 19, '', '2020-02-04 11:22:06', ''),
(662, 'BLAYA PÉREZ', 'ADRIANA', '24458207F', '2011-01-01', 19, '', '2020-02-04 11:22:06', ''),
(663, 'MORALES MONTIEL', 'NEREA', '23837205M', '2010-01-01', 19, '', '2020-02-04 11:22:06', ''),
(664, 'PERALTA MUÑOZ', 'CELIA', '23833953L', '2007-01-01', 2, '', '2020-02-04 11:22:06', ''),
(665, 'PACO BONAQUE', 'MARIA', '23810464J', '2003-01-01', 2, '', '2020-02-04 11:22:06', ''),
(666, '', 'PAULA', '', '2010-01-01', 18, '', '2020-02-04 11:22:06', ''),
(667, '', 'MARIA', '', '2010-01-01', 18, '', '2020-02-04 11:22:06', ''),
(668, 'MIRAS MORALES', 'PAULA', '', '2010-01-01', 18, '', '2020-02-04 11:22:06', ''),
(669, 'PÉREZ TORREGROSA', 'MARIA', '', '2010-01-01', 18, '', '2020-02-04 11:22:06', ''),
(670, 'GÓMEZ PARRA', 'ANDREA', '', '2007-01-01', 18, '', '2020-02-04 11:22:06', ''),
(671, 'PARRA GARCÍA', 'ELENA', '', '2006-01-01', 18, '', '2020-02-04 11:22:06', ''),
(672, 'MULERO LÓPEZ', 'MARIBEL', '', '2008-01-01', 12, '', '2020-02-04 11:22:06', ''),
(673, 'MARTÍNEZ ANDREU', 'SARA', '', '2007-01-01', 12, '', '2020-02-04 11:22:06', ''),
(674, 'MARGOT', 'CLARA', '', '2008-01-01', 12, '', '2020-02-04 11:22:06', ''),
(675, 'PERIAGO MARTÍNEZ', 'MARIA ISABEL', '', '2006-01-01', 12, '', '2020-02-04 11:22:06', ''),
(676, 'VELEZ CANOVAS', 'ANDREA', '', '2005-01-01', 12, '', '2020-02-04 11:22:06', ''),
(677, 'MORA VIVANCOS', 'MARINA', '', '2005-01-01', 12, '', '2020-02-04 11:22:06', ''),
(678, 'RODRIGUEZ MIRAS', 'ROCIO', '', '2005-01-01', 12, '', '2020-02-04 11:22:06', ''),
(679, 'CANOVAS MARQUEZ', 'MARIA', '', '2005-01-01', 12, '', '2020-02-04 11:22:06', ''),
(680, 'MARTINEZ GARCIA', 'MARINA', '', '2002-01-01', 12, '', '2020-02-04 11:22:06', ''),
(681, 'VALDIBIESO SÁNCHEZ', 'LYDIA', '', '2007-01-01', 11, '', '2020-02-04 11:22:06', ''),
(683, 'PEÑA JIMENEZ', 'ESTELA', 024461878, '2010-01-01', 13, '', '2020-02-04 11:22:06', ''),
(684, 'ESPINOSA CHARARRIÁ', 'ISABELLA', 'XDC190703', '2008-01-01', 20, '', '2020-02-04 11:22:06', ''),
(685, 'SOBRINO CANO', 'ADRIANA', 099160238, '2009-01-01', 20, '', '2020-02-04 11:22:06', ''),
(686, 'RODRIGO OBADIA', 'CHLOÉ', 099160373, '2009-01-01', 20, '', '2020-02-04 11:22:06', ''),
(687, 'BALBOA OLTRA', 'LAURA', 048766334, '2002-01-01', 20, '', '2020-02-04 11:22:06', ''),
(688, 'MORAL INIESTA', 'AROA', 049433534, '2006-01-01', 20, '', '2020-02-04 11:22:06', ''),
(689, 'LÓPEZ BEVIÁ', 'LUCÍA', 048802551, '2005-01-01', 20, '', '2020-02-04 11:22:06', ''),
(690, 'GARCÍA GINER', 'CAROLINA', 048726104, '2004-01-01', 20, '', '2020-02-04 11:22:06', ''),
(691, 'MOYANO RUBIO', 'ALBA', 048768308, '2005-01-01', 20, '', '2020-02-04 11:22:06', ''),
(692, 'NAVARRO OLIVARES', 'LUCÍA', 053249544, '2004-01-01', 20, '', '2020-02-04 11:22:06', ''),
(693, 'ORDÓÑEZ DOMENCH', 'PAULA', 050504584, '2004-01-01', 20, '', '2020-02-04 11:22:06', ''),
(694, 'OÑATE GARCÍA DE GAMARRA', 'CRISTINA', 049907883, '2003-01-01', 20, '', '2020-02-04 11:22:06', ''),
(695, 'MARCHANTE MARTÍNEZ', 'PAULA', 049907883, '2011-04-16', 3, '', '2020-02-04 11:22:06', ''),
(696, 'MINGUEZ RODRIGUEZ', 'VEGA', 049906513, '2012-08-21', 3, '', '2020-02-04 11:22:06', ''),
(697, 'MEDRANO ALCOCER', 'MARIA VICTORIA', 055051964, '2012-09-15', 3, '', '2020-02-04 11:22:06', ''),
(698, 'GONZALEZ MORENO', 'MARIA PATRICIA', 049427854, '2011-02-06', 3, '', '2020-02-04 11:22:06', ''),
(699, 'REDONDO GARCIA', 'LAURA', 049431786, '2010-02-08', 3, '', '2020-02-04 11:22:06', ''),
(700, 'EXPOSITO ALFARO', 'DANIELA', 055320616, '2009-10-14', 3, '', '2020-02-04 11:22:06', ''),
(701, 'CONTRERAS SANCHEZ-AGUILERA', 'SILVIA', 049218789, '2009-12-28', 3, '', '2020-02-04 11:22:06', ''),
(702, 'GOMEZ RODRIGUEZ', 'NOELIA', 049904339, '2009-11-23', 3, '', '2020-02-04 11:22:06', ''),
(703, 'MELERO VALERA', 'SOFIA', 046412826, '2009-11-12', 3, '', '2020-02-04 11:22:06', ''),
(704, 'ARENAS JIMENEZ', 'MARIA', 049426616, '2011-03-18', 3, '', '2020-02-04 11:22:06', ''),
(705, 'DONATE RODRIGUEZ', 'INES', 049427576, '2010-06-30', 3, '', '2020-02-04 11:22:06', ''),
(706, 'TALAYA REQUENA', 'MARIA', 051793337, '2007-01-01', 3, '', '2020-02-04 11:22:06', ''),
(707, 'RODRIGUEZ-CARREÑO PRIETO', 'OLIVIA', 049410718, '2011-01-01', 15, '', '2020-02-04 11:22:06', ''),
(708, 'PARVU', 'GEORGE STEFAN', 001751529, '2010-01-01', 15, '', '2020-02-04 11:22:06', ''),
(709, 'CARDONA BELLO', 'LAIA', 049413226, '2012-01-01', 15, '', '2020-02-04 11:22:06', ''),
(710, 'COSTA CARDONA', 'TANIT', 048233367, '2010-01-01', 15, '', '2020-02-04 11:22:06', ''),
(711, 'SANSOLDO', 'MARIA JULIA', 004295462, '2010-01-01', 15, '', '2020-02-04 11:22:06', ''),
(712, 'CARJARU', 'SARA NICOLE', 002532867, '2011-01-01', 15, '', '2020-02-04 11:22:06', ''),
(713, 'YUSTE COSTA', 'LUCIA', 049416292, '2012-01-01', 15, '', '2020-02-04 11:22:06', ''),
(714, 'GIRADO SANCHEZ', 'KAILA', 049412121, '2012-01-01', 15, '', '2020-02-04 11:22:06', ''),
(715, 'DRICOT ESCANDELL', 'JASMINE', 047433104, '2007-01-01', 15, '', '2020-02-04 11:22:06', ''),
(716, 'GABARRON DE HOYOS', 'CLAUDIA', 012431416, '2008-01-01', 15, '', '2020-02-04 11:22:06', ''),
(717, 'TORRES MORENO', 'ISABEL', 048199941, '2008-01-01', 15, '', '2020-02-04 11:22:06', ''),
(718, 'MARI BREUNING', 'PAULA', 047258931, '2002-01-01', 15, '', '2020-02-04 11:22:06', ''),
(719, 'RIERA NOGUERA', 'JUAN VICENTE', 047253872, '1989-01-01', 15, '', '2020-02-04 11:22:06', ''),
(720, 'MARI TORRES', 'MARTINA', 048230700, '2009-01-01', 15, '', '2020-02-04 11:22:06', ''),
(721, 'MOLERO GIMENEZ', 'ADRIANA', 048231748, '2009-01-01', 15, '', '2020-02-04 11:22:06', ''),
(722, 'AMOR SERBAN', 'ALEXIA ALEGRA', 048232269, '2008-01-01', 15, '', '2020-02-04 11:22:06', ''),
(723, 'DORADO JUZMANOVA', 'GABRIELA', 000204813, '2007-01-01', 15, '', '2020-02-04 11:22:06', ''),
(724, 'MAS ESCANDELL', 'ADRIA', 048230870, '2008-01-01', 15, '', '2020-02-04 11:22:06', ''),
(725, 'COSTA SANSANO', 'ANGEL', 049411666, '2008-01-01', 15, '', '2020-02-04 11:22:06', ''),
(726, 'DENGRA RUIZ', 'MARIA', 048231357, '2009-01-01', 15, '', '2020-02-04 11:22:06', ''),
(727, 'SOLANO GARCIA', 'ANAIS', 048231960, '2008-01-01', 15, '', '2020-02-04 11:22:06', ''),
(728, 'MAS ESCANDELL', 'LLUNA', 049412618, '2011-01-01', 15, '', '2020-02-04 11:22:06', ''),
(729, 'COLL BLÁZQUEZ', 'CANDELA', 099160186, '2008-01-01', 9, '', '2020-02-04 11:22:06', ''),
(730, 'OUTES COVA', 'AINHOA', 054798057, '2009-01-01', 9, '', '2020-02-04 11:22:06', ''),
(731, 'PACHECO ROBLES', 'CARLA', 099169998, '2005-01-01', 9, '', '2020-02-04 11:22:06', ''),
(732, 'PÉREZ GARCIA', 'AITANA', 099160189, '2006-01-01', 9, '', '2020-02-04 11:22:06', ''),
(733, 'TARIFA SÁNCHEZ', 'ALBA', 050593510, '2006-01-01', 9, '', '2020-02-04 11:22:06', ''),
(734, 'GÓNZALEZ CULUBET', 'NINA', 006335001, '2008-01-01', 21, '', '2020-02-04 11:22:06', ''),
(735, 'MORENTE CUADRADO', 'BEATRIZ', 024458436, '2012-01-01', 19, '', '2020-02-04 11:22:06', ''),
(736, 'MORENTE CUADRADO', 'TRINIDAD', 024458435, '2012-01-01', 19, '', '2020-02-04 11:22:06', ''),
(737, 'GRANADOS GARCÍA', 'CLARA', 030294447, '2012-01-01', 19, '', '2020-02-04 11:22:06', ''),
(738, 'MARTÍNEZ GARCÍA', 'MARÍA', 024458085, '2012-01-01', 19, '', '2020-02-04 11:22:06', ''),
(739, 'CAMPOY SEGURA', 'PATRICIA', 026547715, '2013-01-01', 19, '', '2020-02-04 11:22:06', ''),
(740, 'GONZALEZ SANCHEZ', 'LEIRE', '', '2014-01-01', 19, '', '2020-02-04 11:22:06', ''),
(741, 'MARÍN LÓPEZ', 'ALBA', '', '2013-01-01', 19, '', '2020-02-04 11:22:06', ''),
(742, 'REVERTE PÉREZ', 'ALICIA', '', '2012-01-01', 19, '', '2020-02-04 11:22:06', ''),
(743, 'MÉNDEZ PÉREZ', 'MERCEDES', '', '2013-01-01', 19, '', '2020-02-04 11:22:06', ''),
(744, 'PADILLA PAREDES', 'MARINA', '', '2013-01-01', 19, '', '2020-02-04 11:22:06', ''),
(745, 'GARCÍA PÉREZ', 'LOLA', '026861387D', '2011-01-01', 19, '', '2020-02-04 11:22:06', ''),
(746, 'GUIRAO MUÑOZ', 'ESTRELLA', 023837472, '2009-01-01', 19, '', '2020-02-04 11:22:06', ''),
(747, 'SEGURA CARO', 'CLAUDIA', 023840461, '2009-01-01', 19, '', '2020-02-04 11:22:06', ''),
(748, 'MACHACA JIMÉNEZ', 'MADELEIN', 026863951, '2009-01-01', 19, '', '2020-02-04 11:22:06', ''),
(749, 'MARTÍNEZ ROMERA', 'SERGIO', '', '2008-01-01', 19, '', '2020-02-04 11:22:06', ''),
(750, 'VÁZQUEZ ÚBEDA', 'SANDRA', 023839793, '2008-01-01', 19, '', '2020-02-04 11:22:06', ''),
(751, 'MARTÍNEZ HERNÁNDEZ', 'AITANA', '', '2008-01-01', 19, '', '2020-02-04 11:22:06', ''),
(752, 'VIELSA GARCÍA', 'ANA MARÍA', 023335367, '2007-01-01', 19, '', '2020-02-04 11:22:06', ''),
(753, 'MARTÍNEZ ROMERA', 'LUCÍA', '', '2007-01-01', 19, '', '2020-02-04 11:22:06', ''),
(754, 'LÓPEZ RUIZ', 'MARA', '', '2014-01-01', 18, '', '2020-02-04 11:22:06', ''),
(755, 'LÓPEZ RUIZ', 'NOA', '', '2011-01-01', 18, '', '2020-02-04 11:22:06', ''),
(756, 'SÁNCHEZ MOLINA', 'AROA', '', '2009-01-01', 18, '', '2020-02-04 11:22:06', ''),
(757, 'VILAR ARTERO', 'VICTORIA', '', '2009-01-01', 18, '', '2020-02-04 11:22:06', ''),
(758, 'GARCÍA IONOVA', 'SOFIA', '', '2004-01-01', 18, '', '2020-02-04 11:22:06', ''),
(759, 'RODRIGUEZ MARTÍNEZ', 'IRIA', '', '2004-01-01', 18, '', '2020-02-04 11:22:06', ''),
(760, 'PUIG-YERRERO VALDIVIESO', 'ANGELA', '', '2006-01-01', 18, '', '2020-02-04 11:22:06', ''),
(761, 'SIMA', 'LAVINIA MARÍA', '', '2009-01-01', 13, '', '2020-02-04 11:22:06', ''),
(762, 'SALINAS GARCÍA', 'JOSE', '', '2009-01-01', 13, '', '2020-02-04 11:22:06', ''),
(763, 'REVERTE MELENCHÓN', 'ANGELA', '', '2006-01-01', 13, '', '2020-02-04 11:22:06', ''),
(764, 'JIMENEZ SÁNCHEZ', 'DANIELA', '', '2010-01-01', 11, '', '2020-02-04 11:22:06', ''),
(766, 'MARTINEZ PÉREZ', 'CLAUDIA', '', '2010-01-01', 11, '', '2020-02-04 11:22:06', ''),
(767, 'DÍAZ MARTÍNEZ', 'VERONICA', '', '2007-01-01', 11, '', '2020-02-04 11:22:06', ''),
(768, 'VERA SEMPERE', 'NOELIA', 058479290, '2012-01-01', 1, '', '2020-02-04 11:22:06', ''),
(769, 'MELLADO COSTA', 'NATALIA', 024469733, '2013-01-01', 1, '', '2020-02-04 11:22:06', ''),
(770, 'CORBALAN MERINO', 'ROCIO', 049856519, '2010-01-01', 1, '', '2020-02-04 11:22:06', ''),
(771, 'ARCAS PUJANTE', 'EDURNE', 058489064, '2008-01-01', 1, '', '2020-02-04 11:22:06', ''),
(772, 'PEÑA GUERRERO', 'PALOMA', 0X9520356, '2006-01-01', 1, '', '2020-02-04 11:22:06', ''),
(773, 'JIMÉNEZ SÁNCHEZ', 'BLANCA', '', '2012-01-01', 11, '', '2020-02-04 11:22:06', ''),
(778, 'GARCÍA ROJAS', 'SAYOA', '', '2008-01-01', 19, '', '2020-02-04 11:22:06', ''),
(784, 'GARCÍA CAMPOS', 'PAULA', '', '0000-00-00', 18, '', '2020-02-04 11:22:06', '');



-- ---------------------------------------------------------
--
-- Table structure for table : `paneles`
--
-- ---------------------------------------------------------

CREATE TABLE `paneles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(20) DEFAULT NULL,
  `numero_jueces` int(11) DEFAULT NULL,
  `peso` int(11) DEFAULT NULL,
  `descripcion` varchar(700) DEFAULT NULL,
  `puntua` varchar(2) DEFAULT 'si',
  `id_competicion` int(11) DEFAULT NULL,
  `color` varchar(11) DEFAULT NULL,
  `creado` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `modificado` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `paneles`
--

INSERT INTO `paneles` (`id`, `nombre`, `numero_jueces`, `peso`, `descripcion`, `puntua`, `id_competicion`, `color`, `creado`, `modificado`) VALUES
(61, 'Panel único', 5, 100, 'Panel único de 5 jueces', 'si', 46, '', '2020-03-11 11:29:10', '2020-03-11 11:29:24');



-- ---------------------------------------------------------
--
-- Table structure for table : `puesto_juez`
--
-- ---------------------------------------------------------

CREATE TABLE `puesto_juez` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(25) DEFAULT NULL,
  `id_juez` int(11) DEFAULT NULL,
  `id_competicion` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=148 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `puesto_juez`
--

INSERT INTO `puesto_juez` (`id`, `nombre`, `id_juez`, `id_competicion`) VALUES
(146, 'Secretario', 82, 46),
(147, 'Juez arbitro', 82, 46);



-- ---------------------------------------------------------
--
-- Table structure for table : `usertype`
--
-- ---------------------------------------------------------

CREATE TABLE `usertype` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `usertype_nombre` varchar(30) COLLATE utf8_spanish2_ci DEFAULT NULL,
  `level` int(3) DEFAULT NULL,
  `creado` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `modificado` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Dumping data for table `usertype`
--

INSERT INTO `usertype` (`id`, `usertype_nombre`, `level`, `creado`, `modificado`) VALUES
(1, 'Administrador', 999, '2021-03-02 18:12:40', '2021-03-03 10:02:31'),
(2, 'Secretario', 600, '2021-03-02 21:03:33', '2021-03-03 10:02:48'),
(3, 'Delegado jueces', 500, '2021-03-02 21:10:20', '2021-03-03 10:03:17'),
(4, 'Juez', 400, '2021-03-02 21:10:37', '2021-03-03 10:02:53'),
(5, 'Club', 300, '2021-03-03 07:56:42', '2021-03-03 10:02:55'),
(6, 'Invitado', 100, '2021-03-03 08:32:15', '2021-03-03 10:03:26');



-- ---------------------------------------------------------
--
-- Table structure for table : `usuarios`
--
-- ---------------------------------------------------------

CREATE TABLE `usuarios` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(30) COLLATE utf8_spanish2_ci DEFAULT NULL,
  `email` varchar(30) COLLATE utf8_spanish2_ci DEFAULT NULL,
  `telefono` int(11) DEFAULT NULL,
  `password` varchar(10) COLLATE utf8_spanish2_ci DEFAULT NULL,
  `usertype` varchar(15) COLLATE utf8_spanish2_ci DEFAULT '1',
  `comentario` varchar(100) COLLATE utf8_spanish2_ci DEFAULT NULL,
  `creado` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `modificado` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `username`, `email`, `telefono`, `password`, `usertype`, `comentario`, `creado`, `modificado`) VALUES
(1, 'admin', 'xascorro@gmail.com', 646677447, 'xas', 1, 'Usuario administrador', '', '2021-03-03 10:03:32'),
(13, 'secretario', 'xasc@g.com', 0, 'xas', 2, 'Secretario de competición activa', '2021-03-02 20:43:41', '2021-03-03 10:04:10'),
(15, 'delegado', 'xascorro@gmail.com', 646677447, 'xas', 3, 'Mi usuario', '2021-03-02 21:17:55', '2021-03-03 10:04:17'),
(16, 'juez', 'pepe@g.com', '', 'xas', 4, '', '2021-03-02 21:18:58', '2021-03-03 10:04:19'),
(17, 'club', 'pepe@g.com', '', 'xas', 5, '', '2021-03-02 21:18:58', '2021-03-03 10:04:34'),
(18, 'invitado', 'pepe@g.com', '', 'xas', 6, '', '2021-03-02 21:18:58', '2021-03-03 10:04:32');


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;