-- -----------------------------------------------------
-- Table `source_type`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `source_type` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `source_type` VARCHAR(100) NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = MyISAM
COMMENT = 'Source type to source_detail plugin';

INSERT INTO `source_type` (`id`, `source_type`) VALUES
(1, 'Prensa'),
(2, 'Radio'),
(3, 'TV'),
(4, 'OCHA'),
(5, 'Agencia UN'),
(6, 'Entidad Pública'),
(7, 'ONG'),
(8, 'Sociedad Civil'),
(9, 'Otros'),
(11, 'Organismo Multilateral');
