
INSERT INTO `victim_age` (`id`, `age`) VALUES
(1, 'Desconocido'),
(2, 'Mayor de 18 años'),
(3, 'Menor de 18 años');

--
-- Volcado de datos para la tabla `victim_age_group`
--

INSERT INTO `victim_age_group` (`id`, `victim_age_id`, `age_group`) VALUES
(1, 1, 'Desconocido'),
(2, 2, '18 a 25 años'),
(3, 2, '26 a 40 años'),
(4, 2, '41 a 60 años'),
(5, 2, 'Más de 61 años'),
(6, 3, '0 a 5 años'),
(7, 3, '6 a 10 años'),
(8, 3, '11 a 14 años'),
(9, 3, '15 a 17 años'),
(10, 3, 'Sin información'),
(11, 2, 'Mayor de edad sin información');

--
-- Volcado de datos para la tabla `victim_condition`
--

INSERT INTO `victim_condition` (`id`, `condition`) VALUES
(1, 'Actor Armado no Estatal'),
(2, 'Civil'),
(3, 'Desconocido'),
(4, 'Militar'),
(18, 'Otras Fuerzas del Estado'),
(19, 'Sin información'),
(20, 'Desmovilizado');

--
-- Volcado de datos para la tabla `victim_ethnic_group`
--

INSERT INTO `victim_ethnic_group` (`id`, `ethnic_group`) VALUES
(1, 'Indígena'),
(2, 'AfroColombiano'),
(3, 'Gitano(a) / Rom'),
(4, 'Extranjero'),
(5, 'Sin información'),
(6, 'Otro'),
(7, 'No Aplica');

--
-- Volcado de datos para la tabla `victim_gender`
--

INSERT INTO `victim_gender` (`id`, `gender`) VALUES
(1, 'Femenino'),
(2, 'Masculino'),
(4, 'Desconocido');

--
-- Volcado de datos para la tabla `victim_occupation`
--

INSERT INTO `victim_occupation` (`id`, `occupation`) VALUES
(1, 'Campesino(a) / Agricultor(a)'),
(2, 'Candidato(a) a cargos de elección popular / cargos públicos'),
(3, 'Comerciante'),
(4, 'Defensor (a) DD.HH'),
(5, 'Diplomático(a)'),
(6, 'Funcionario (a) Público(a)'),
(7, 'Lider(esa) Social/Comunitario'),
(8, 'Maestro(a)'),
(9, 'Miembro de misión humanitaria'),
(10, 'Miembro de misión médica'),
(11, 'Miembro de misión religiosa'),
(12, 'Miembro ONG'),
(13, 'Periodista(a)'),
(14, 'Sindicalista'),
(15, 'Transportador(a)'),
(16, 'Desconocida'),
(17, 'Otro'),
(18, 'Ex candidato/ex funcionario público'),
(19, 'No Aplica'),
(20, 'Estudiante'),
(21, 'Sin Información'),
(22, 'Combatiente'),
(23, 'Contratista'),
(24, 'Trabajador de la salud'),
(25, 'Ganadero (a)'),
(26, 'Trabajador de la rama judicial'),
(27, 'Alcalde');

--
-- Volcado de datos para la tabla `victim_status`
--

INSERT INTO `victim_status` (`id`, `status`) VALUES
(1, 'Herido'),
(2, 'Muerto'),
(3, 'Desconocido'),
(4, 'No aplica');

--
-- Volcado de datos para la tabla `victim_sub_condition`
--

INSERT INTO `victim_sub_condition` (`id`, `victim_condition_id`, `sub_condition`) VALUES
(1, 4, 'Ejército Nacional de Colombia'),
(2, 4, 'Armada Nacional de Colombia'),
(3, 4, 'Fuerza Aérea Colombiana'),
(4, 4, 'Policía Nacional de Colombia '),
(30, 18, 'Sin información'),
(8, 4, 'Otro'),
(9, 1, 'Fuerzas Armadas Revolucionarias de Colombia - FARC'),
(10, 1, 'Ejército de Liberación Nacional - ELN'),
(11, 1, 'Otras Guerrillas'),
(12, 1, 'Grupo de Autodefensa'),
(13, 1, 'Nuevo Grupo Armado'),
(14, 1, 'Banda Emergente'),
(15, 1, 'Águilas Negras'),
(16, 1, 'Los Machos'),
(18, 1, 'Otro'),
(19, 1, 'Autodefensas Unidas de Colombia - AUC'),
(20, 1, 'Banda Criminal'),
(21, 1, 'Grupo delincuencial'),
(23, 1, 'Los Rastrojos'),
(24, 1, 'Mano Negra '),
(25, 1, 'Nueva Generación'),
(28, 18, 'DAS'),
(29, 18, 'CTI - Fiscalía'),
(31, 18, 'SIJIN');

--
-- Volcado de datos para la tabla `victim_sub_ethnic_group`
--

INSERT INTO `victim_sub_ethnic_group` (`id`, `victim_ethnic_group_id`, `sub_ethnic_group`) VALUES
(1, 1, 'Awa'),
(2, 1, 'Pasto'),
(3, 1, 'Yanacona'),
(4, 1, 'Coconuco'),
(5, 1, 'Guanaca'),
(6, 1, 'Totoroe'),
(7, 1, 'Paez'),
(8, 1, 'Guambiano'),
(9, 1, 'Eperara Siapidara'),
(10, 1, 'Waunaan'),
(11, 1, 'Emberá'),
(12, 1, 'Tule / Cuna'),
(13, 1, 'Goreguaje'),
(14, 1, 'Kamsá'),
(15, 1, 'Inga'),
(16, 1, 'Kofán'),
(17, 1, 'Siona'),
(18, 1, 'Huitoto'),
(19, 1, 'Miraña'),
(20, 1, 'Muinane'),
(21, 1, 'Nonuya'),
(22, 1, 'Andoque'),
(23, 1, 'Yauna'),
(24, 1, 'Carijona'),
(25, 1, 'Tanimuca'),
(26, 1, 'Yuri'),
(27, 1, 'Ocaira'),
(28, 1, 'Cocama'),
(29, 1, 'Yagua'),
(30, 1, 'Ticuna'),
(31, 1, 'Tucano'),
(32, 1, 'Cabiyarí'),
(33, 1, 'Matapí'),
(34, 1, 'Bora'),
(35, 1, 'Letuama'),
(36, 1, 'Yucuna'),
(37, 1, 'Macuna'),
(38, 1, 'Makú'),
(39, 1, 'Desano'),
(40, 1, 'Tariano'),
(41, 1, 'Barasana'),
(42, 1, 'Tuyuca'),
(88, 2, 'AfroColombiano'),
(44, 1, 'Carapana'),
(45, 1, 'Wanano'),
(46, 1, 'Cubeo'),
(47, 1, 'Pisamira'),
(48, 1, 'Taibano'),
(49, 1, 'Yurutí'),
(50, 1, 'Piratapuyo'),
(51, 1, 'Tatuyo'),
(52, 1, 'Siriano'),
(53, 1, 'Yanua'),
(54, 1, 'Curripaco'),
(55, 1, 'Puinave'),
(56, 1, 'Piaroa'),
(57, 1, 'Guayabero'),
(58, 1, 'Achagua'),
(59, 1, 'Piapoco'),
(60, 1, 'Sáliba'),
(61, 1, 'Masiguare'),
(62, 1, 'Cuiba'),
(63, 1, 'Amorúa'),
(64, 1, 'Sikuani'),
(65, 1, 'Betoye'),
(66, 1, 'Chiricoa'),
(67, 1, 'Makaguaje'),
(68, 1, 'Dujo'),
(69, 1, 'Tame'),
(70, 1, 'Pijao'),
(71, 1, 'Muisca'),
(72, 1, 'Guane'),
(73, 1, 'U''wa / Tunebo'),
(74, 1, 'Bari'),
(75, 1, 'Emberá Katío'),
(76, 1, 'Zenú'),
(77, 1, 'Pacabuy'),
(78, 1, 'Wiwa'),
(79, 1, 'Yuko'),
(80, 1, 'Chimila'),
(81, 1, 'Mokaná'),
(82, 1, 'Arhuaco / Ijka'),
(83, 1, 'Kankuamo'),
(84, 1, 'Kogi / Kaggaba'),
(85, 1, 'Sanha'),
(86, 1, 'Wayú'),
(87, 1, 'Desconocido'),
(89, 4, 'Extranjero'),
(90, 3, 'Gitano(a) / Rom');
