<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Model for Victims of incidents
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

class Victim_Model extends ORM
{
	protected $belongs_to = array('incident_category','incident',
	                            'victim_age_group',
                                'victim_age',
                                'victim_condition',
                                'victim_sub_condition',
                                'victim_status',
                                'victim_occupation',
                                'victim_gender',
                                'victim_ethnic_group',
                                'victim_sub_ethnic_group');
	
	// Database table name
	protected $table_name = 'victim';

    
	/**
	 * Retrieve number of victim for an incident
	 * @param int $incident_id Id of incident
	 * @param string $filters url filters defined in timeline.js, line 703
	 * @return int Number of victims
	 */
	public static function get_num_victims($id,$filters = null)
    {
		$db = new Database();
        
        $cond = "incident_id = $id";

        // Map Victim Filters
        if (!empty($filters)){
            $fls = explode('~',$filters);
            foreach ($fls as $f){
                $val = explode(':',$f);
                if (!empty($val[1])){
                    switch ($val[0]){
                        case 'g':
                            $cond .= ' AND victim_gender_id = '.$val[1];
                        break;
                        case 's':
                            $cond .= ' AND victim_status_id = '.$val[1];
                        break;
                        case 'a':
                            $id_tmp = explode('|',$val[1]);

                            $cond .= ' AND victim_age_id = '.$id_tmp[0];
                            if (isset($id_tmp[1]))  $cond .= ' AND victim_age_group_id = '.$id_tmp[1];
                        break;
                    }
                }
            }
        }

        $count = $db->query("SELECT SUM(victim_cant) AS sum FROM victim WHERE $cond GROUP BY incident_id");
        
        if (isset($count[0]))
    		return $count[0]->sum;
        else    return 0;
    }
    
    /* 
     * Retorna la info para ver victimas de incidente
     *
     * @param int $id Id del incidente
     *
     * @return string json
     */
    public static function getVictims($id) {
        
        $db = new Database();

        $avs = ORM::factory('victim')
                ->where('incident_id', $id)
                ->find_all();

        $genders = ORM::factory('victim_gender')
                ->select_list('id','gender');

        $conditions = ORM::factory('victim_condition')
                ->select_list('id','condition');
        
        $sub_conditions = ORM::factory('victim_sub_condition')
                ->select_list('id','sub_condition');
        
        $ages = ORM::factory('victim_age')
                ->select_list('id','age');
        
        $age_groups = ORM::factory('victim_age_group')
                ->select_list('id','age_group');
        
        $ethnic_groups = ORM::factory('victim_ethnic_group')
            ->select_list('id','ethnic_group');

        $sub_ethnic_groups = ORM::factory('victim_sub_ethnic_group')
            ->select_list('id','sub_ethnic_group');

        $statues = ORM::factory('victim_status')
            ->select_list('id','status');

        $occupations = ORM::factory('victim_occupation')
                ->select_list('id','occupation');

        $victimas = array();
        foreach($avs as $av) {
            
            // Categoria
            $cat = $db->query("SELECT category_title 
                FROM incident_category AS ic 
                JOIN category AS c ON ic.category_id = c.id 
                WHERE ic.id = ".$av->incident_category_id);

            $category = $cat[0]->category_title;

            $cant = (empty($av->victim_cant)) ? 0 : $av->victim_cant;
            $gender_id = (empty($av->victim_gender_id)) ? 0 : $av->victim_gender_id;
            $gender = (empty($av->victim_gender_id)) ? '' : $genders[$av->victim_gender_id];
               
            $condition_id = (empty($av->victim_condition_id)) ? 0 : $av->victim_condition_id;
            $condition = (empty($av->victim_condition_id)) ? '' : $conditions[$av->victim_condition_id];

            $sub_condition_id = (empty($av->victim_condition_id)) ? 0 : $av->victim_condition_id;
            $sub_condition = (empty($av->victim_sub_condition_id)) ? '' : $sub_conditions[$av->victim_sub_condition_id];
            
            $age_id = (empty($av->victim_age_id)) ? 0 : $av->victim_age_id;
            $age = (empty($av->victim_age_id)) ? '' : $ages[$av->victim_age_id];
            
            $age_group_id = (empty($av->victim_age_group_id)) ? 0 : $av->victim_age_group_id;
            $age_group = (empty($av->victim_age_group_id)) ? '' : $age_groups[$av->victim_age_group_id];

            $ethnic_group_id = (empty($av->victim_ethnic_group_id)) ? 0 : $av->victim_ethnic_group_id;
            $ethnic_group = (empty($av->victim_ethnic_group_id)) ? '' : $ethnic_groups[$av->victim_ethnic_group_id];

            $sub_ethnic_group_id = (empty($av->victim_sub_ethnic_group_id)) ? 0 : $av->victim_sub_ethnic_group_id;
            $sub_ethnic_group = (empty($av->victim_sub_ethnic_group_id)) ? '' : $sub_ethnic_groups[$av->victim_sub_ethnic_group_id];
            
            $status_id = (empty($av->victim_status_id)) ? 0 : $av->victim_status_id;
            $status = (empty($av->victim_status_id)) ? '' : $statues[$av->victim_status_id];
            
            $occupation_id = (empty($av->victim_occupation_id)) ? 0 : $av->victim_occupation_id;
            $occupation = (empty($av->victim_occupation_id)) ? '' : $occupations[$av->victim_occupation_id];

            $victimas[] = compact('category',
                                    'cant',
                                    'gender_id',
                                    'gender',
                                    'sub_ethnic_group_id',
                                    'sub_ethnic_group',
                                    'sub_condition_id',
                                    'sub_condition',
                                    'occupation_id',
                                    'occupation',
                                    'age_group_id',
                                    'age_group',
                                    'condition_id',
                                    'condition',
                                    'age_id',
                                    'age',
                                    'status_id',
                                    'status',
                                    'ethnic_group_id',
                                    'ethnic_group'
                                    );
        }
        
        return $victimas;

    }

}
