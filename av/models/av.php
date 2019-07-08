<?php defined('SYSPATH') or die('No direct script access.');
/**
* Model for Actionable
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module     Actionable Model  
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */

class Av_Model extends ORM
{
	protected $belongs_to = array('incident');
	
	// Database table name
    //protected $table_name = '';
    
    /*
	* Find possible incident duplication with active incidents
    * access private
	* @param array $fields Fields to compare, keys are table column names
    */
	public static function _find_duplicity($fields){
        
        $db = new Database();
        $cond = array('incident_active = 1');

        if (isset($fields['date'])){
            $date_comp = $fields['date'];
            $yyyy = date('Y',strtotime($date_comp));
            $mm = date('m',strtotime($date_comp))*1;
            $dd = date('d',strtotime($date_comp))*1;
            
            $cond[] = "YEAR(incident_date) = $yyyy AND MONTH(incident_date) = $mm AND DAY(incident_date) = $dd ";

        }

        if (isset($fields['category'])){
            $cond[] = 'category_id IN ('.implode(',',$fields['category']).')';
        }

        if (isset($fields['lat'])){
            // Field latitude in location table is double, 6 decimals
            $cond[] = ' latitude = '.number_format($fields['lat'],6);
        }
        
        if (isset($fields['lon'])){
            // Field latitude in location table is double, 6 decimals
            $cond[] = ' longitude = '.number_format($fields['lon'],6);
        }
        
        $sql = 'SELECT * FROM incident INNER JOIN incident_category ON incident.id = incident_category.incident_id INNER JOIN location ON incident.location_id = location.id WHERE '.implode(' AND ',$cond);
        $dups = $db->query($sql);

        return $dups;
    }

}
