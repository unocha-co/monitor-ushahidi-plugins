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

class Sourcedetail_Controller extends Template_Controller
{
	function __construct()
	{
		parent::__construct();
	
	}
    
    /**
	 * Name of the view template for this controller
	 * @var string
	 */
	public $template = 'json';
	
    /* 
     * Retorna la info para editar incidente
     *
     * @param int $id Id del incidente
     *
     * @return string json
     */
    function edit($id) {

        $srcs = ORM::factory('sourcedetail')
                ->where('incident_id', $id)
                ->find_all();

        $sources = array();
        foreach($srcs as $src) {
            
            $source_id = $src->source_type_id.'|'.$src->source_id;
            $source_date = $src->source_date;
            $source_desc = $src->source_desc;
            $source_reference = $src->source_reference;

            $sources[] = compact('source_id','source_date','source_desc','source_reference');
        }
        
        $json = compact('sources');
        
        header('Content-type: application/json; charset=utf-8');
		$this->template->json = json_encode($json);

    }
}
