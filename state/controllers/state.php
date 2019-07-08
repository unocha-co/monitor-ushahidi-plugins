<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Av Controller.
 * This controller will take care of adding and synchronize repors with sidih in the Admin section.
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     OCHA Colombia 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module     Admin Reports Controller  
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */

class State_Controller extends Controller
{

	function __construct()
	{
		parent::__construct();
	
	}
	
    /*
	* Get cities of state
    */
	function cities($state_id)
	{
        //echo json_encode(array('' => 'Ciudad') + ORM::factory('city')->where('state_id', $state_id)->orderby('city')->select_list('CONCAT(city_lon,",",city_lat)','city'));
        $cities = array();
        $_rs = ORM::factory('city')->select('city_lon AS lon, city_lat AS lat, city AS n, id')->where('state_id', $state_id)->orderby('city')->find_all();
        foreach($_rs as $_r) {
            $c['n'] = $_r->n;
            $c['id'] = $_r->id;
            $c['lonlat'] = $_r->lon.','.$_r->lat;

            $cities[] = $c;
        }
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($cities);
    }
    
    /* 
     * Retorna la info para editar incidente
     *
     * @param int $id Id del incidente
     *
     * @return string json
     */
    function edit($id) {
        
        $l = ORM::factory('incident')
                ->where('id', $id)
                ->find();

        $state_id = $l->location->state_id;
        $city_id = $l->location->city_id;

        header('Content-type: application/json; charset=utf-8');
		echo json_encode(compact('state_id', 'city_id'));
    }
}
