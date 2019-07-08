<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Model for Victims Sub Condition
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

class Victim_Sub_Condition_Model extends ORM
{
	protected $belongs_to = array('victim_condition');
	protected $has_many = array('victim');
	
	// Database table name
	protected $table_name = 'victim_sub_condition';

	/**
	 * Retrieve array with key=>value options to dropdowns input
	 * @param bool $blank Set firt options to blank
	 * @return array $options
	 */
    static function get_dropdown_options()
    {
        $eths = ORM::factory('victim_condition')->orderby('condition')->find_all();
        
        foreach ($eths as $eth){
            $options[$eth->id.'|'.$eth->condition] = ORM::factory('victim_sub_condition')->where('victim_condition_id',$eth->id)->orderby('sub_condition')->select_list('id','sub_condition');
        }

        return $options;
    }
}
