-- -----------------------------------------------------
-- Table `source_detail`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `source_detail` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `source_type_id` INT NOT NULL ,
  `source_id` INT NOT NULL ,
  `incident_id` INT NOT NULL ,
  `location_id` INT NOT NULL ,
  `source_date` DATE NOT NULL ,
  `source_reference` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_source_detail_source_type1` (`source_type_id` ASC) ,
  INDEX `fk_source_detail_source1` (`source_id` ASC) )
ENGINE = MyISAM
COMMENT = 'Detail of sources to source_detail plugin';
