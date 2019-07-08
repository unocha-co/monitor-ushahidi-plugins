<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Av Controller.
 * This controller has all actions to monitor map, reports 
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

class Monitor_Controller extends Template_Controller
{

    private $meses = array('', 'Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic');
	
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
	* Get one report detail to feature select event in map
    */
	function single_report_map($id)
	{
	    $incidents = Incident_Model::get_incidents(array("i.id = $id"));
        
        $this->incidentsToJson($incidents);
    }

    /*
	* Get reports list to feature select event in map
    */
	function reports_list_map()
	{
		// Reports
		//$incidents = Incident_Model::get_incidents(reports::$params);
        $max_e = 20;
		$incidents = reports::fetch_incidents(true, $max_e);
        $this->incidentsToJson($incidents);
    }
    
    /*
	* Incidents to json
    */
    function incidentsToJson($incidents) {

		$jis = array();
        $cats_parent = array();
        $cats_parent_id = array();
        foreach ($incidents as $incident)
		{
            $cats_tree = array();
			// Get all info of an incident
            $incident = ORM::factory('incident', $incident->incident_id);
            // Date
            $_date = explode('-', date('Y-m-j', strtotime($incident->incident_date)));
            // Cats
            foreach($incident->incident_category as $category) {

                // don't show hidden categoies
                if($category->category->category_visible == 0)
                {
                    continue;
                }

                $parent_id = $category->category->parent_id;

                if ($parent_id == 0) {
                    $cats_tree[$category->category->category_title] = array();
                }
                else {
                    if (!in_array($parent_id, $cats_parent_id)) {
                        $_pa = ORM::factory('category', $parent_id);
                        $cat_parent[$parent_id] = $_pa->category_title;
                    } 

                    $cats_tree[$cat_parent[$parent_id]][] = $category->category->category_title;
                }
            }

            // Sources detail
            $srcs = array();
            $sds = ORM::factory('sourcedetail')->where('incident_id = '.$incident->id)->find_all();
            //$sds = ORM::factory('sourcedetail')->find_all();
            foreach($sds as $sd) {   

                // Ocultamos por ahora referencia que tenga bitacora dentro de la referencia
                if (strpos($sd->source_reference, 'Bitácora') === false) {
                    // Elimina primer caracter extraño de reference, porque 
                    // antes del http, ese caracter hace que en monitor la 
                    // darle click no lo abra bien, caracter no-ascii, caracter 
                    // unicode
                    $ref = preg_replace('/[^(\x20-\x7F)]*/','', $sd->source_reference);
                    $srcs[] = array($sd->source_type->source_type,$sd->source->source,$ref,$sd->source_desc);
                }
            }

            // Ocultamos actores en titulo
            $_ti = explode('.', $incident->incident_title);
            if (count($_ti) == 4) {
                $_titulo = $_ti[0].'.'.$_ti[2].'.'.$_ti[3];
            }
            else {
                $_titulo = $incident->incident_title;
            }

            // Array to json
            $_st = ORM::factory('state', $incident->location->state_id);
            $jis[] = array( 'q' => 'violencia',
                            'id' => $incident->id,
                            't' => $_titulo,
                            'd' => $_date[2].' de '.$this->meses[$_date[1]*1].' de '. $_date[0],
                            'c' => $cats_tree,
                            'ln' => $incident->location->location_name,
                            'ld' => $_st->divipola,
                            'ldn' => $_st->state,
                            'desc' => $incident->incident_description,
                            'f' => $srcs,
                            'v' => Victim_Model::getVictims($incident->id)
                        );
        }

		header('Content-type: application/json; charset=utf-8');
		$this->template->json = json_encode($jis);
    }
    
    /*
	* 
    */
	function update_reports_acceso()
	{

        $this->auto_render = false;
        
        $acceso = array(
        3 => array(3,29),
        4 => array(2,4,5,6,7,8,9),
        5 => array(30,31,32),
        7 => array(37,38),
        9 => array(2,4,5,6,7,8,9,3,13,34,37,38));
        
        //$_incs = ORM::factory('incident_category')->find_all();
        $this->db = new Database();
        $_incs = $this->db->query('SELECT * FROM incident_category');
        
        foreach($_incs as $_inc) {
            foreach($acceso as $_v => $_ids) {
                if (in_array($_inc->category_id, $_ids)) {
                    $form_response = new Form_Response_Model();
                    $form_response->form_field_id = 1;
                    $form_response->incident_id = $_inc->incident_id;
                    $form_response->form_response = 'cat_'.$_v;

                    echo "Incident:".$_inc->incident_id.", Categoria:".$_inc->category_id.", acceso = cat_$_v <br />";
                    $form_response->save();
                }
            }
        }
    }
    
    /*
	* 
    */
	function depuracion_2012()
	{
        
        $this->auto_render = false;
        
        $fp = fopen('media/uploads/all_final.csv','r');
        $this->db = new Database();
        $sql = "";
		while (!feof($fp)){

			$fila = explode(',', fgets($fp));

            if (!empty($fila[0])){
                $sidih_id = $fila[0];
                
                $_is = $this->db->query('SELECT incident_id FROM incident_sidih WHERE sidih_id='.$sidih_id);
                if (!empty($_is[0]->incident_id)) {
                    $id = $_is[0]->incident_id;

                    $_incs = $this->db->query('SELECT id FROM incident_category WHERE incident_id='.$id);
                    foreach($_incs as $i => $_inc) {
                        $id_c = $_inc->id;
                        $sql .= "DELETE FROM actor_incident_category WHERE incident_category_id = $id_c;<br />";
                        for($c=1;$c<4;$c++) {
                            $id_a = trim($fila[$c]);
                            if (!empty($id_a)) {
                                $sql .= "INSERT INTO actor_incident_category (incident_category_id,actor_id) VALUES ($id_c,$id_a);<br />";
                            }
                        }
                    }
                }
            }
        }

        echo $sql;
    }
    
    /*
	* 
    */
	function depuracion_2012_final()
	{
        
        $this->auto_render = false;
        
        $fp = fopen('media/uploads/all_final_dic.csv','r');
        $this->db = new Database();
        $sql = "";
        $cod_internto_to_id = array(10051=>1112, 10052=>1113, 10053=>1114,10050=>1115,10054=>1116,10055=>1117,10056=>1118);
		while (!feof($fp)){

			$fila = explode(',', fgets($fp));

            if (!empty($fila[0])){
                $sidih_id = $fila[0];
                
                $nd = 0;
                
                $_is = $this->db->query('SELECT incident_id FROM incident_sidih WHERE sidih_id='.$sidih_id);
                // Numero descripciones del evento
                if (!empty($_is[0]->incident_id)) {
                    $id = $_is[0]->incident_id;

                    $_incs = $this->db->query('SELECT id FROM incident_category WHERE incident_id='.$id);
                    $num_desc = count($_is);
                    foreach($_incs as $i => $_inc) {
                        $id_c = $_inc->id;
                        $van = array();
                        
                        // Borra si tiene mas info que bisabuelo
                        if (!empty($fila[2])) {
                            $sql .= "DELETE FROM actor_incident_category WHERE incident_category_id = $id_c;<br />";
                        }

                        for($c=1;$c<=4;$c++) {
                            $ids = explode('-', $fila[$c]);
                            if ($num_desc == 1) {
                                foreach($ids as $_id) {
                                    $id_a = trim($_id);
                                    
                                    if ($id_a > 10049) {
                                        $id_a = $cod_internto_to_id[$id_a];
                                    }
                                    
                                    $repetido = in_array($id_a, $van);
                                    
                                    if (!empty($id_a) && !$repetido) {
                                        $sql .= "INSERT INTO actor_incident_category (incident_category_id,actor_id) VALUES ($id_c,$id_a);<br />";
                                        $van[] = $id_a;
                                    }
                                }
                            }
                            else {
                                $id_a = trim($_id);
                                
                                if ($id_a > 10049) {
                                    $id_a = $cod_internto_to_id[$id_a];
                                }
                                
                                $repetido = in_array($id_a, $van);
                                
                                if (!empty($id_a) && !$repetido) {
                                    $sql .= "INSERT INTO actor_incident_category (incident_category_id,actor_id) VALUES ($id_c,$id_a);<br />";
                                    $van[] = $id_a;
                                }
                            
                            }
                        }
                        $nd++;
                    }
                }
            }
        }

        echo $sql;
    }
}
