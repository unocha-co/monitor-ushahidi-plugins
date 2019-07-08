
CREATE  TABLE IF NOT EXISTS `victim` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `victim_cant` INT(11) NOT NULL COMMENT '\'Almacena la cantidad de víctimas, dependiendo el tipo de evento' ,
  `victim_gender_id` INT(11) NULL ,
  `victim_sub_ethnic_group_id` INT(11) NULL ,
  `incident_category_id` INT(11) NULL ,
  `victim_sub_condition_id` INT(11) NULL ,
  `victim_occupation_id` INT(11) NULL ,
  `victim_age_group_id` INT(11) NULL ,
  `victim_condition_id` INT(11) NULL ,
  `victim_age_id` INT(11) NULL ,
  `victim_status_id` INT(11) NULL ,
  `victim_ethnic_group_id` INT(11) NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_victim_victim_gender1` (`victim_gender_id` ASC) ,
  INDEX `fk_victim_victim_sub_ethnic_group1` (`victim_sub_ethnic_group_id` ASC) ,
  INDEX `fk_victim_incident_category1` (`incident_category_id` ASC) ,
  INDEX `fk_victim_victim_sub_condition1` (`victim_sub_condition_id` ASC) ,
  INDEX `fk_victim_victim_occupation1` (`victim_occupation_id` ASC) ,
  INDEX `fk_victim_victim_age_group1` (`victim_age_group_id` ASC) ,
  INDEX `fk_victim_victim_condition1` (`victim_condition_id` ASC) ,
  INDEX `fk_victim_victim_age1` (`victim_age_id` ASC) ,
  INDEX `fk_victim_victim_status1` (`victim_status_id` ASC) ,
  INDEX `fk_victim_victim_ethnic_group1` (`victim_ethnic_group_id` ASC) ,
  CONSTRAINT `fk_victim_victim_gender1`
    FOREIGN KEY (`victim_gender_id` )
    REFERENCES `victim_gender` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_victim_victim_sub_ethnic_group1`
    FOREIGN KEY (`victim_sub_ethnic_group_id` )
    REFERENCES `victim_sub_ethnic_group` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_victim_incident_category1`
    FOREIGN KEY (`incident_category_id` )
    REFERENCES `incident_category` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_victim_victim_sub_condition1`
    FOREIGN KEY (`victim_sub_condition_id` )
    REFERENCES `victim_sub_condition` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_victim_victim_occupation1`
    FOREIGN KEY (`victim_occupation_id` )
    REFERENCES `victim_occupation` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_victim_victim_age_group1`
    FOREIGN KEY (`victim_age_group_id` )
    REFERENCES `victim_age_group` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_victim_victim_condition1`
    FOREIGN KEY (`victim_condition_id` )
    REFERENCES `victim_condition` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_victim_victim_age1`
    FOREIGN KEY (`victim_age_id` )
    REFERENCES `victim_age` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_victim_victim_status1`
    FOREIGN KEY (`victim_status_id` )
    REFERENCES `victim_status` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_victim_victim_ethnic_group1`
    FOREIGN KEY (`victim_ethnic_group_id` )
    REFERENCES `victim_ethnic_group` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = MyISAM
AUTO_INCREMENT = 469
COMMENT = 'Detail of Incident victims';
CREATE  TABLE IF NOT EXISTS `victim_occupation` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT '\'Almacena el identificador de la Ocupació' ,
  `occupation` VARCHAR(100) NOT NULL COMMENT '\'Almacena el nombre de la Ocupació' ,
  PRIMARY KEY (`id`) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
COMMENT = '\'Almacena la ocupación de la víctima afecta por algún evento';

CREATE  TABLE IF NOT EXISTS `victim_status` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `status` VARCHAR(100) NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
COMMENT = 'Status of incident involved';

CREATE  TABLE IF NOT EXISTS `victim_condition` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `condition` VARCHAR(30) NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
COMMENT = 'Condition of incident victims';

CREATE  TABLE IF NOT EXISTS `victim_sub_condition` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT '\'Almacena el identificado' ,
  `victim_condition_id` INT(11) NOT NULL ,
  `sub_condition` VARCHAR(100) NOT NULL COMMENT '\'Almacena el nombre de la sub condició' ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_victim_sub_condition_victim_condition1` (`victim_condition_id` ASC) ,
  CONSTRAINT `fk_victim_sub_condition_victim_condition1`
    FOREIGN KEY (`victim_condition_id` )
    REFERENCES `victim_condition` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
COMMENT = 'Sub Conditions of incident victims';

CREATE  TABLE IF NOT EXISTS `victim_age_group` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `victim_age_id` INT(11) NOT NULL ,
  `age_group` VARCHAR(100) NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_victim_age_group_victim_age1` (`victim_age_id` ASC) ,
  CONSTRAINT `fk_victim_age_group_victim_age1`
    FOREIGN KEY (`victim_age_id` )
    REFERENCES `victim_age` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
COMMENT = 'Age groups of incident victims';

CREATE  TABLE IF NOT EXISTS `victim_age` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `age` VARCHAR(50) NOT NULL DEFAULT '' ,
  PRIMARY KEY (`id`) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
COMMENT = 'Age of incident victims';

CREATE  TABLE IF NOT EXISTS `victim_sub_ethnic_group` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `victim_ethnic_group_id` INT(11) NOT NULL ,
  `sub_ethnic_group` VARCHAR(100) NULL DEFAULT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_victim_sub_ethnic_group_victim_ethnic_group1` (`victim_ethnic_group_id` ASC) ,
  CONSTRAINT `fk_victim_sub_ethnic_group_victim_ethnic_group1`
    FOREIGN KEY (`victim_ethnic_group_id` )
    REFERENCES `victim_ethnic_group` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
COMMENT = 'Sub Ethnic groups of incident victims';

CREATE  TABLE IF NOT EXISTS `victim_ethnic_group` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT '\'Almacena el identificador de la etnia' ,
  `ethnic_group` VARCHAR(50) NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
COMMENT = 'Ethnic groups of incident victims';

CREATE  TABLE IF NOT EXISTS `victim_gender` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `gender` VARCHAR(30) NOT NULL DEFAULT '' ,
  PRIMARY KEY (`id`) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
COMMENT = 'Gender of incident victims';

CREATE  TABLE IF NOT EXISTS `state` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `state` VARCHAR(100) NULL DEFAULT NULL ,
  `divipola` VARCHAR(5) NULL DEFAULT NULL ,
  `country_id` INT(11) NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_state_country1` (`country_id` ASC) ,
  CONSTRAINT `fk_state_country1`
    FOREIGN KEY (`country_id` )
    REFERENCES `sidce`.`country` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = MyISAM;

CREATE  TABLE IF NOT EXISTS `incident_sidih` (
  `sidih_id` INT NOT NULL ,
  `incident_id` BIGINT(20) UNSIGNED NOT NULL ,
  `import_date` DATE NOT NULL ,
  PRIMARY KEY (`sidih_id`, `incident_id`) ,
  INDEX `fk_sidih_id_incident_id_incident1` (`incident_id` ASC) ,
  CONSTRAINT `fk_sidih_id_incident_id_incident1`
    FOREIGN KEY (`incident_id` )
    REFERENCES `incident` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = MyISAM
COMMENT = 'Event ID in SIDIH => Incident ID in ECompleja';
