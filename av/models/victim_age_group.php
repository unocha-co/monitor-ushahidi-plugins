<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Model for Victims Age Group
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module     Country Model  
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */

class Victim_Age_Group_Model extends ORM
{
	protected $belongs_to = array('victim_age');
	protected $has_many = array('victim');
	
	// Database table name
	protected $table_name = 'victim_age_group';

	/**
	 * Retrieve array with key=>value options to dropdowns input
	 * @param bool $blank Set firt options to blank
	 * @return array $options
	 */
    static function get_dropdown_options()
    {
        $eths = ORM::factory('victim_age')->orderby('age')->find_all();
        
        foreach ($eths as $eth){
            $options[$eth->id.'|'.$eth->age] = ORM::factory('victim_age_group')->where('victim_age_id',$eth->id)->select_list('id','age_group');
        }

        return $options;
    }
}
