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

class Actor_Model extends ORM
{
	protected $belongs_to = array('incident_category');
	protected $has_many = array('actor');
	
	// Database table name
	protected $table_name = 'actor';

	/**
	 * Retrieve array with key=>value options to dropdowns input
	 * @param bool $blank Set firt options to blank
	 * @return array $options
	 */
    static function get_dropdown_options()
    {
        $rows = ORM::factory('actor')->where('parent_id=0')->orderby('actor')->find_all();
        
        foreach ($rows as $row){
            $hijos = array();
            $_t = ORM::factory('actor')->where('parent_id',$row->id)->orderby('actor')->select_list('id','actor');

            foreach($_t as $_id => $_n) {

                $_hijos = ORM::factory('actor')->where('parent_id',$_id)->orderby('actor')->select_list('id','actor');
                $hijos[] = array('id' => $_id, 'n' => $_n, 'h' => $_hijos);
                
                /* 
                foreach($_hijos as $_idn => $_nn) {
                    $_h[] = array('id' => $_idn, 'n' => $_nn, 'h' => ORM::factory('actor')->where('parent_id',$_idn)->select_list('id','actor'));
                }
                
                $hijos[] = array('id' => $_id, 'n' => $_n, 'h' => $_h);
                 */
            }
            
            $opts[] =  array('id' => $row->id, 'n' => $row->actor, 'h' => $hijos);
        }
        return $opts;
    }
}
