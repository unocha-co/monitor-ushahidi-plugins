<?php
/**
 * Performs install/uninstall methods for the actionable plugin
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author	   Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module	   Actionable Installer
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */

class Sourcedetail_Install {

	/**
	 * Constructor to load the shared database library
	 */
	public function __construct()
	{
		$this->db = Database::instance();
	}

	/**
	 * Creates the required database tables for the actionable plugin
	 */
	public function run_install()
	{

        $sql = "CREATE  TABLE IF NOT EXISTS `source_type` (
              `id` INT NOT NULL AUTO_INCREMENT ,
              `source_type` VARCHAR(100) NOT NULL ,
              PRIMARY KEY (`id`) )
            ENGINE = MyISAM
            COMMENT = 'Source type to source_detail plugin';
        ";    
        $this->db->query($sql);

        $sql = "CREATE  TABLE IF NOT EXISTS `source` (
            `id` INT NOT NULL AUTO_INCREMENT ,
            `source_type_id` INT NOT NULL ,
            `source` VARCHAR(200) NULL ,
            PRIMARY KEY (`id`) ,
            INDEX `fk_source_source_type` (`source_type_id` ASC) )
                ENGINE = MyISAM
                COMMENT = 'Sources to source_detail Plugin';
        ";
        $this->db->query($sql);

        $sql = "
        CREATE  TABLE IF NOT EXISTS `source_detail` (
              `id` INT NOT NULL AUTO_INCREMENT ,
              `source_type_id` INT NOT NULL ,
              `source_id` INT NOT NULL ,
              `incident_id` INT NOT NULL ,
              `location_id` INT NOT NULL ,
              `source_date` DATE NOT NULL ,
              `source_desc` TEXT NOT NULL ,
              `source_reference` TEXT NOT NULL ,
              PRIMARY KEY (`id`) ,
              INDEX `fk_source_detail_source_type1` (`source_type_id` ASC) ,
              INDEX `fk_source_detail_source1` (`source_id` ASC) )
            ENGINE = MyISAM
            COMMENT = 'Detail of sources to source_detail plugin';
        ";
        $this->db->query($sql);

	}

	/**
	 * Deletes the database tables for the actionable module
	 */
	public function uninstall()
	{
	}
}
