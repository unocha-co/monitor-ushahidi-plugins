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

class Av_Controller extends Admin_Controller
{
	function __construct()
	{
		parent::__construct();
	
		$this->template->this_page = 'reports';
    }

    /*
	* Sync incidents from Sidih database
    */
	function sync_sidih($continue = null)
	{
		$this->template->content = new View('av/admin/sync_sidih');
        $this->template->content->title = Kohana::lang('av.sync_reports_sidih');
        
        $f_ini = '2008-1-1';
        if (!isset($_SESSION['json_sync_sidih']) || is_null($continue)){
            
            // Get last import date
            //$max_date =  $this->db->query('SELECT MAX(import_date) AS max FROM incident_sidih')->current()->max;
            $sync_eve_id =  $this->db->query('SELECT MAX(sidih_id) AS max FROM incident_sidih')->current()->max;
            
            // x => security parameter
            $json = @file_get_contents("https://sidi.umaic.org/sissh/cron_jobs/ecompleja_webservice.php?x=w3x&id=$sync_eve_id");
            //$json = @file_get_contents("/home/ochacol/www/emergenciacompleja/media/uploads/2012_2013.json");
            
            if ($json === false){
                $status_server = Kohana::lang('av.server_down');
                $num_total = 0;
            }
            else{
                $reports = json_decode($json,TRUE);
                //var_dump($reports);
                $status_server = Kohana::lang('av.server_ok');
                $num_total = count($reports);
                $server_ok = 1;
                //$periodo = "<b>$f_ini</b> hasta <b>".date('Y-m-d', time()).'</b> (hoy)';
                $periodo = "Se importa desde el Id=$sync_eve_id";

            }
        }
        else
            $json = $_SESSION['json_sync_sidih'];


        $this->template->content->continue = $continue;
        $this->template->content->server_ok = $server_ok;

        if ($continue == 1){
            $success = array();
            $confirm_import = array();
            $num_imports = 0; 
            $num_log = 0;
            $num_dup = 0;
            $count = 0;

            foreach ($reports as $report){

                $inc = $report['incident'];
                $sidih_id = $inc['sidih_id'];

                // Ckeck import log table
                $count = ORM::factory('incident_sidih')->where(array('sidih_id' => $sidih_id))->count_all();
                //if (!isset($_POST)) $count = $this->db->count_records('incident_sidih', array('sidih_id' => $sidih_id));
                if (!isset($_POST)) $count = 0; 
                if ($count == 0){

                    $category = $report['category'];
                    $victims = $report['victim'];
                    $actor = $report['actor'];
                    $news = $report['news'];

                    $city = ORM::factory('city')->where('divipola',$inc['mun_id'])->find();
                    $location = new Location_Model();
                    $location->country_id = $city->country_id;
                    $location->location_name = $city->city;
                    $location->latitude = $city->city_lat;
                    $location->longitude = $city->city_lon;
                    $location->state_id = $city->state_id;
                    $location->city_id = $city->id;
                    $location->location_visible = 1;
                    
                    if (empty($_POST)){
                        // Find possible duplication
                        /*
                        $dups = Av_Model::_find_duplicity(array('date' => $inc['incident_date'],
                                                                'category' => $category,
                                                                'lat' => $location->latitude,
                                                                'lon' => $location->longitude
                                                          )
                                            );
                        */

                        $dups = array();
                        $ok = 'Ok!';
                        $duplicities = '';
                        $class = '';
                        if (count($dups) > 0){

                            $ok = '<br /><br />'.Kohana::lang('av.server_duplicity');
                            $class = 'red';
                            $duplicities = '<ul>';    
                            foreach ($dups as $dup){
                                $duplicities .= '<li><a href="'.url::site().'admin/reports/edit/'.$dup->id.'" target="_blank">'.$dup->incident_title.'</a></li>';
                            }
                            $duplicities .= '</ul>'; 

                            $confirm_import[$sidih_id] = 1;
                            $num_dup++;
                        }

                        //$success[$sidih_id] = '<span class="'.$class.'">'.$inc['sidih_id'].''.$inc['incident_title'].'***'.$city->city_lat.'###'.$city->city_lon.' --- '.$ok.'----'.$inc['incident_date'].'</span><br /><br />'.$duplicities;
                        $success[$sidih_id] = '<span class="'.$class.'">'.$inc['incident_title'].' --- '.$ok.'</span><br /><br />'.$duplicities;
                    }
                    else{
                        // Inserta no duplicidades y duplicidades marcadas como SI
                        if (!isset($_POST["import_$sidih_id"]) || (isset($_POST["import_$sidih_id"]) && $_POST["import_$sidih_id"] == 1)){
                            
                            // Check q no este importando el evento, esto pasa 
                            // cuando se hacen dos importaciones el mismo dia

                            $count = ORM::factory('incident_sidih')->where(array('sidih_id' => $sidih_id))->count_all();

                            if ($count == 0) {
                                // STEP 1: SAVE LOCATION
                                $location->location_date = date("Y-m-d H:i:s",time());
                                $location->save();

                                // STEP 2: SAVE INCIDENT
                                $incident = new Incident_Model();
                                $incident->location_id = $location->id;
                                $incident->form_id = $inc['form_id'];
                                $incident->user_id = $_SESSION['auth_user']->id;
                                $incident->incident_active = 1;
                                $incident->incident_verified = 1;
                                $incident->incident_mode = 5;  // Sidih
                                $incident->incident_title = $inc['incident_title'];
                                $incident->incident_description = utf8_decode(stripslashes($inc['incident_description']));

                                $incident_date = explode("/",$inc['incident_date']);

                                $incident_date = $inc['incident_date'];
                                $incident_time = '00:00:00';
                                $incident->incident_date = date( "Y-m-d H:i:s", strtotime($incident_date . " " . $incident_time) );				
                                $incident->incident_dateadd = date("Y-m-d H:i:s",time());
                                $incident->save();

                                $incident_sidih = new Incident_Sidih_Model();
                                $incident_sidih->incident_id = $incident->id;
                                $incident_sidih->sidih_id = $sidih_id;
                                $incident_sidih->import_date = date("Y-m-d H:i:s",time());
                                $incident_sidih->save();

                                // STEP 3: SAVE CATEGORIES
                                foreach($category as $item)
                                {
                                    $incident_category = new Incident_Category_Model();
                                    $incident_category->incident_id = $incident->id;
                                    $incident_category->category_id = $item;
                                    $incident_category->save();

                                }
            
                                // News
                                foreach ($news as $new)
                                {
                                    if (!empty($new['id_fuente']))
                                    {
                                        // Ushahidi core
                                        $nm = new Media_Model();
                                        $nm->location_id = $incident->location_id;
                                        $nm->incident_id = $incident->id;
                                        $nm->media_type = 4;		// News
                                        $nm->media_link = $new['medio'];
                                        $nm->media_date = date("Y-m-d H:i:s",strtotime($new['fecha']));
                                        $nm->save();
                                        
                                        // Source detail plugin
                                        $sd = new Sourcedetail_Model();
                                        $sd->incident_id = $incident->id;
                                        $sd->location_id = $incident->location_id;
                                        $sd->source_type_id = $new['id_fuente'];
                                        $sd->source_id = $new['id_subfuente'];
                                        $sd->source_date = date('Y-m-d', strtotime($new['fecha']));
                                        $sd->source_desc = $new['desc'];
                                        $sd->source_reference = $new['medio'];

                                        $sd->save();
                                    }
                                }

                                // SAVE ACTOR
                                foreach ($actor as $_ida) {
                                    $aic = new Actor_Incident_Category_Model();
                                    $aic->incident_category_id = $incident_category->id;
                                    $aic->actor_id = $_ida;

                                    $aic->save();
                                }

                                // SAVE VICTIMS
                                foreach ($victims as $victim){
                                    if (isset($victim['victim_cant'])){
                                        $vict = new Victim_Model();
                                        
                                        $vict->victim_cant = $victim['victim_cant'];
                                        $vict->incident_category_id = $incident_category->id;
                                        $vict->incident_id = $incident->id;
                                        
                                        if (isset($victim['victim_gender_id'])) $vict->victim_gender_id = $victim['victim_gender_id'];
                                        if (isset($victim['victim_sub_ethnic_group_id'])) $vict->victim_sub_ethnic_group_id = $victim['victim_sub_ethnic_group_id'];
                                        if (isset($victim['victim_condition_id'])) $vict->victim_condition_id = $victim['victim_condition_id'];
                                        if (isset($victim['victim_sub_condition_id'])) $vict->victim_sub_condition_id = $victim['victim_sub_condition_id'];
                                        if (isset($victim['victim_occupation_id'])) $vict->victim_occupation_id = $victim['victim_occupation_id'];
                                        if (isset($victim['victim_age_group_id'])) $vict->victim_age_group_id = $victim['victim_age_group_id'];
                                        if (isset($victim['victim_age_id'])) $vict->victim_age_id = $victim['victim_age_id'];
                                        if (isset($victim['victim_status_id'])) $vict->victim_status_id = $victim['victim_status_id'];

                                        $vict->save();
                                    }
                                }

                                // Campos de acceso
                                $acceso = array(
                                3 => array(3,29),
                                4 => array(2,4,5,6,7,8,9),
                                5 => array(30,31,32),
                                7 => array(37,38),
                                9 => array(2,4,5,6,7,8,9,3,13,34,37,38));
                                
                                foreach($category as $_idc) {
                                    foreach($acceso as $_v => $_ids) {
                                        if (in_array($_idc, $_ids)) {
                                            $form_response = new Form_Response_Model();
                                            $form_response->form_field_id = 1;
                                            $form_response->incident_id = $incident->id;
                                            $form_response->form_response = 'cat_'.$_v;

                                            $form_response->save();
                                        }
                                    }
                                }
                            }
                        }
                        else    $num_dup++;

                    }
                    $num_imports++;
                }
                else{
                    $success[$sidih_id] = '<span class="no_import">'.$inc['incident_title'].' --- Existe en importación anterior</span>';
                    $num_log++;
                }
            }

            if ($_POST){
                $this->template->content->import_summary = "Eventos importados: <b>".($num_imports - $num_dup)."</b><br />Eventos no importados por duplicidad: $num_dup <br />Eventos existentes en importaciones anteriores: $num_log";
            }

            $this->template->content->success = $success;
            $this->template->content->success_summary = "Total eventos: $num_total | Eventos a importar: ".($num_imports - $num_dup)." | Eventos con posible duplicidad: $num_dup | Eventos existentes en importaciones anteriores: $num_log";
            $this->template->content->confirm_import = $confirm_import;
        }
        else{
            $this->template->content->status_server = $status_server;
            $this->template->content->periodo = $periodo;
            $this->template->content->num_total = $num_total;
        }
    }
    
    /*
	* Sync incidents from Sidih database without check
    */
	function sync_sidih_direct($continue = null)
	{
		$this->template->content = new View('av/admin/sync_sidih');
        $this->template->content->title = Kohana::lang('av.sync_reports_sidih');
        
            
        // Get last import date
        //$sync_eve_id =  $this->db->query('SELECT MAX(sidih_id) AS max FROM incident_sidih')->current()->max;
        
        // Borra desde febrero 2013, sidih_id = 46990, incident_id=41253
        $id_borrar_desde = 46990;

        $sync_eve_id = $id_borrar_desde;

        // Borrar desde enero 2012
        //$id_borrar_desde = 38610;
        
        
        // x => security parameter
        //$json = @file_get_contents("http://localhost/sissh/cron_jobs/ecompleja_webservice.php?x=w3x&id=$sync_eve_id");
        $json = @file_get_contents("https://sidi.umaic.org/sissh/cron_jobs/ecompleja_webservice.php?x=w3x&id=$sync_eve_id");
        //$json = @file_get_contents("/home/ochacol/www/emergenciacompleja/media/uploads/sidih_ecompleja.json");
        
        if ($json === false){
            $status_server = 'Down';
        }
        else{
            $reports = json_decode($json,TRUE);
            //var_dump($reports);
            $status_server = Kohana::lang('av.server_ok');
            $num_total = count($reports);
            $server_ok = 1;
            //$periodo = "<b>$f_ini</b> hasta <b>".date('Y-m-d', time()).'</b> (hoy)';
            $periodo = "Se importa desde el Id=$sync_eve_id";

            $num_imports = 0; 
            $num_log = 0;
            $num_dup = 0;
            $count = 0;

            // Borra solo los sincronizados con sidih, por si ya estan creando por otro lado
            $con = "IN (SELECT incident_id FROM incident_sidih WHERE sidih_id > $id_borrar_desde)";

            $lcts = $this->db->query("SELECT id FROM location WHERE id IN (SELECT location_id FROM incident WHERE id $con)");

            $ids_loc = array();
            foreach($lcts as $lct) {
                $ids_loc[] = $lct->id;
            }

            $ids_loc = implode(',',$ids_loc);

            $incs = $this->db->query("SELECT incident_id FROM incident_sidih WHERE sidih_id > $id_borrar_desde");
            $ids_inc = array();
            foreach($incs as $inc) {
                $ids_inc[] = $inc->incident_id;
            }
            
            $ids_inc = implode(',',$ids_inc);

            $cond_loc = "IN ($ids_loc)";
            $cond_inc = "incident_id IN ($ids_inc)";
            $cond = "id IN ($ids_inc)";

            $this->db->query("DELETE FROM location WHERE id $cond_loc");
            $this->db->query("DELETE FROM actor_incident_category WHERE incident_category_id IN (SELECT id FROM incident_category WHERE $cond_inc)");
             
            $this->db->query("DELETE FROM incident_category WHERE $cond_inc"); 
            $this->db->query("DELETE FROM form_response WHERE $cond_inc");
            
            $this->db->query("DELETE FROM comment WHERE $cond_inc");
            $this->db->query("DELETE FROM media WHERE $cond_inc");
            $this->db->query("DELETE FROM message WHERE $cond_inc");
            $this->db->query("DELETE FROM reporter WHERE location_id $cond_loc");
            $this->db->query("DELETE FROM verified WHERE $cond_inc");
            $this->db->query("DELETE FROM rating WHERE $cond_inc");
            $this->db->query("DELETE FROM victim WHERE $cond_inc");
            $this->db->query("DELETE FROM source_detail WHERE $cond_inc");
            $this->db->query("DELETE FROM incident WHERE $cond");
            $this->db->query("DELETE FROM incident_sidih WHERE sidih_id > $id_borrar_desde");
             
            
            foreach ($reports as $report){

                $inc = $report['incident'];
                $sidih_id = $inc['sidih_id'];

                // Ckeck import log table
                //$count = ORM::factory('incident_sidih')->where(array('sidih_id' => $sidih_id))->count_all();
                //if ($count == 0){

                    $category = $report['category'];
                    $victims = $report['victim'];
                    $actor = $report['actor'];
                    $news = $report['news'];

                    $city = ORM::factory('city')->where('divipola',$inc['mun_id'])->find();
                    $location = new Location_Model();
                    $location->country_id = $city->country_id;
                    $location->location_name = $city->city;
                    $location->latitude = $city->city_lat;
                    $location->longitude = $city->city_lon;
                    $location->state_id = $city->state_id;
                    $location->city_id = $city->id;
                    $location->location_visible = 1;
                    
                            
                    // STEP 1: SAVE LOCATION
                    $location->location_date = date("Y-m-d H:i:s",time());
                    $location->save();

                    // STEP 2: SAVE INCIDENT
                    $incident = new Incident_Model();
                    $incident->location_id = $location->id;
                    $incident->form_id = $inc['form_id'];
                    $incident->user_id = $_SESSION['auth_user']->id;
                    $incident->incident_active = 1;
                    $incident->incident_verified = 1;
                    $incident->incident_mode = 5;  // Sidih
                    $incident->incident_title = $inc['incident_title'];
                    $incident->incident_description = utf8_decode(stripslashes($inc['incident_description']));

                    $incident_date = explode("/",$inc['incident_date']);

                    $incident_date = $inc['incident_date'];
                    $incident_time = '00:00:00';
                    $incident->incident_date = date( "Y-m-d H:i:s", strtotime($incident_date . " " . $incident_time) );				
                    $incident->incident_dateadd = date("Y-m-d H:i:s",time());
                    $incident->save();

                    $incident_sidih = new Incident_Sidih_Model();
                    $incident_sidih->incident_id = $incident->id;
                    $incident_sidih->sidih_id = $sidih_id;
                    $incident_sidih->import_date = date("Y-m-d H:i:s",time());
                    $incident_sidih->save();

                    // STEP 3: SAVE CATEGORIES
                    foreach($category as $item)
                    {
                        $incident_category = new Incident_Category_Model();
                        $incident_category->incident_id = $incident->id;
                        $incident_category->category_id = $item;
                        $incident_category->save();

                    }

                    // News
                    foreach ($news as $new)
                    {
                        if (!empty($new['id_fuente']))
                        {
                            // Ushahidi core
                            $nm = new Media_Model();
                            $nm->location_id = $incident->location_id;
                            $nm->incident_id = $incident->id;
                            $nm->media_type = 4;		// News
                            $nm->media_link = $new['medio'];
                            $nm->media_date = date("Y-m-d H:i:s",strtotime($new['fecha']));
                            $nm->save();
                            
                            // Source detail plugin
                            $sd = new Sourcedetail_Model();
                            $sd->incident_id = $incident->id;
                            $sd->location_id = $incident->location_id;
                            $sd->source_type_id = $new['id_fuente'];
                            $sd->source_id = $new['id_subfuente'];
                            $sd->source_date = date('Y-m-d', strtotime($new['fecha']));
                            $sd->source_desc = $new['desc'];
                            $sd->source_reference = $new['medio'];

                            $sd->save();
                        }
                    }

                    // SAVE ACTOR
                    foreach ($actor as $_ida) {
                        $aic = new Actor_Incident_Category_Model();
                        $aic->incident_category_id = $incident_category->id;
                        $aic->actor_id = $_ida;

                        $aic->save();
                    }

                    // SAVE VICTIMS
                    foreach ($victims as $victim){
                        if (isset($victim['victim_cant'])){
                            $vict = new Victim_Model();
                            
                            $vict->victim_cant = $victim['victim_cant'];
                            $vict->incident_category_id = $incident_category->id;
                            $vict->incident_id = $incident->id;
                            
                            if (isset($victim['victim_gender_id'])) $vict->victim_gender_id = $victim['victim_gender_id'];
                            if (isset($victim['victim_sub_ethnic_group_id'])) $vict->victim_sub_ethnic_group_id = $victim['victim_sub_ethnic_group_id'];
                            if (isset($victim['victim_condition_id'])) $vict->victim_condition_id = $victim['victim_condition_id'];
                            if (isset($victim['victim_sub_condition_id'])) $vict->victim_sub_condition_id = $victim['victim_sub_condition_id'];
                            if (isset($victim['victim_occupation_id'])) $vict->victim_occupation_id = $victim['victim_occupation_id'];
                            if (isset($victim['victim_age_group_id'])) $vict->victim_age_group_id = $victim['victim_age_group_id'];
                            if (isset($victim['victim_age_id'])) $vict->victim_age_id = $victim['victim_age_id'];
                            if (isset($victim['victim_status_id'])) $vict->victim_status_id = $victim['victim_status_id'];

                            $vict->save();
                        }
                    }

                    // Campos de acceso
                    $acceso = array(
                    3 => array(3,29),
                    4 => array(2,4,5,6,7,8,9),
                    5 => array(30,31,32),
                    7 => array(37,38),
                    9 => array(2,4,5,6,7,8,9,3,13,34,37,38));
                    
                    foreach($category as $_idc) {
                        foreach($acceso as $_v => $_ids) {
                            if (in_array($_idc, $_ids)) {
                                $form_response = new Form_Response_Model();
                                $form_response->form_field_id = 1;
                                $form_response->incident_id = $incident->id;
                                $form_response->form_response = 'cat_'.$_v;

                                $form_response->save();
                            }
                        }
                    }

                    $num_imports++;
               // }
            }
            
            $status_server = 'ok!';
            $this->template->content->import_summary = "Eventos importados: <b>".($num_imports - $num_dup)."</b><br />Eventos no importados por duplicidad: $num_dup <br />Eventos existentes en importaciones anteriores: $num_log";
        }
        
        $this->template->content->status_server = $status_server;
        $this->template->content->periodo = $periodo;
        $this->template->content->num_total = $num_total;
    }
}
