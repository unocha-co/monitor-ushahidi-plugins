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

class Source_Model extends ORM
{
	protected $belongs_to = array('source_type');
	protected $has_many = array('source_detail');
	
	// Database table name
	protected $table_name = 'source';

	/**
	 * Retrieve array with key=>value options to generate lists
	 * @return array $list
	 */
    static function get_list()
    {
        $rows = ORM::factory('source_type')->orderby('source_type')->find_all();
        
        foreach ($rows as $row){
            $hijos = array();
            foreach(ORM::factory('source')->where('source_type_id',$row->id)->orderby('source')->select_list('id','source') as $_id => $_n) {
                $hijos[$_id] = $_n;
            }
            
            $opts[] =  array('id' => $row->id, 'n' => $row->source_type, 'h' => $hijos);
        }
        return $opts;
    }
}
