<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Access Controller.
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

class Access_Controller extends Template_Controller
{
    /**
	 * Name of the view template for this controller
	 * @var string
     */
	public $template = 'json';
    
    function __construct()
	{
		parent::__construct();
	}
	
    /* 
     * Retorna la info para editar incidente
     *
     * @param int $id Id del incidente
     *
     * @return string json
     */
    function edit($id) {

        $acs = ORM::factory('form_response')
                ->where('incident_id', $id)
                ->find_all();

        $acceso = array();
        foreach($acs as $ac) {
            foreach(explode(',',$ac->form_response) as $acs) {
                $acceso[] = $acs;
            }
        }

        $json = compact('acceso');
        
        header('Content-type: application/json; charset=utf-8');
		$this->template->json = json_encode($json);

    }
    
}
