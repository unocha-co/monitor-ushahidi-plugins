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

class Av_Controller extends Template_Controller
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
     * @param int $cat_id Id de la categoria seleccionada
     *
     * @return string json
     */
    function edit($id, $cat_id) {

        $ics = ORM::factory('incident_category')
                ->where('incident_id', $id)
                ->where('category_id', $cat_id)
                ->find();

        $id_ic = $ics->id;

        $avs = ORM::factory('actor_incident_category')
                ->where('incident_category_id', $id_ic)
                ->orderby('actor_id', 'desc')
                ->find_all();

        $actores = array();
        foreach($avs as $av) {
            $actores[] = $av->actor_id;
        }
        
        $avs = ORM::factory('victim')
                ->where('incident_category_id', $id_ic)
                ->find_all();

        $victimas = array();
        foreach($avs as $av) {
            
            $cant = (empty($av->victim_cant)) ? 0 : $av->victim_cant;
            $gender_id = (empty($av->victim_gender_id)) ? 0 : $av->victim_gender_id;
            
            $condition_id = (empty($av->victim_condition_id)) ? 0 : $av->victim_condition_id;

            $sub_condition_id = (empty($av->victim_condition_id)) ? 0 : $av->victim_condition_id;
            if (!empty($av->victim_sub_condition_id)) {
                $sub_condition_id .= '|'.$av->victim_sub_condition_id;
            }
            
            $age_id = (empty($av->victim_age_id)) ? 0 : $av->victim_age_id;
            $age_group_id = (empty($av->victim_age_id)) ? 0 : $av->victim_age_id;

            if (!empty($av->victim_age_group_id)) {
                $age_group_id .= '|'.$av->victim_age_group_id;
            }

            $ethnic_group_id = (empty($av->victim_ethnic_group_id)) ? 0 : $av->victim_ethnic_group_id;
            $sub_ethnic_group_id = (empty($av->victim_ethnic_group_id)) ? 0 : $av->victim_ethnic_group_id;
            
            if (!empty($av->victim_sub_ethnic_group_id)) {
                $sub_ethnic_group_id .= '|'.$av->victim_sub_ethnic_group_id;
            }
            
            $status_id = (empty($av->victim_status_id)) ? 0 : $av->victim_status_id;
            $occupation_id = (empty($av->victim_occupation_id)) ? 0 : $av->victim_occupation_id;

            $victimas[] = compact('cant',
                                    'gender_id',
                                    'sub_ethnic_group_id',
                                    'sub_condition_id',
                                    'occupation_id',
                                    'age_group_id',
                                    'condition_id',
                                    'age_id',
                                    'status_id',
                                    'ethnic_group_id'
                                    );
        }

        $json = compact('actores', 'victimas');
        
        header('Content-type: application/json; charset=utf-8');
		$this->template->json = json_encode($json);

    }
    
    function getActorASjson(){
        $parent_id = $_GET['key'];
        $actores = array();
        foreach(ORM::factory('actor')->where('parent_id',$parent_id)->select_list('id','actor') as $_id => $_n) {
            
            $count = ORM::factory('actor')->where('parent_id',$_id)->count_all();
            $lazy = ($count == 0) ? false : true;
            $actores[] = array('title' => $_n, 'ida' => $_id, 'isLazy' => $lazy);
        }

		header('Content-type: application/json; charset=utf-8');
		$this->template->json = json_encode($actores);

    }
    
    /**
     * Lista cuarto nivel de actores
     *
     * @param int $id
     * @param int $cat_id
     * @param int $incident_id
     *
     */
    function getActores($id, $cat_id, $incident_id){

        $edit = false;

        // En editar evento
        if (!empty($incident_id)) {
            
            $ics = ORM::factory('incident_category')
                    ->where('incident_id', $incident_id)
                    ->where('category_id', $cat_id)
                    ->find();

            $id_ic = $ics->id;
            
            $avs = ORM::factory('actor_incident_category')
                    ->where('incident_category_id', $id_ic)
                    ->orderby('actor_id', 'desc')
                    ->find_all();

            $seleccionados = array();
            foreach($avs as $av) {
                $seleccionados[] = $av->actor_id;
            }

            $edit = true;
        }

        $actores = array();
        foreach(ORM::factory('actor')->where('parent_id',$id)->orderby('actor')->select_list('id','actor') as $_id => $_n) {

            $arr = array('title' => $_n, 'ida' => $_id);

            if ($edit) {
                if (in_array($_id, $seleccionados)) {
                    $arr['select'] = true;
                }
            }

            $actores[] = $arr;
        }

		header('Content-type: application/json; charset=utf-8');
		$this->template->json = json_encode($actores);

    }

    /* 
     * API violencia
     *
     * @param string $caso lista_eventos
     * @param string $estado 'todos,creados,modificados,sidih
     * @param int $fecha desde|hasta
     *
     * @return string json
     */
    function api($caso,$estado,$fecha='') {
        
	    ini_set('memory_limit', '512M' );

        switch($caso) {
			case 'listar2':
			$incidentes = array(); 
                
                // Esta se toma del script de importacion de categorias cat_eventos_to_ushahidi.php, invertido
                $id_cat_ushahidi_to_sidih = array(1=>1, 10=>2, 27=>3, 35=>4,39=>5, 48=>6, 55=>7);
                
                $id_scat_ushahidi_to_sidih = array(2=>5, 3=>7, 4=>1, 5=>2, 6=>4, 7=>8, 8=>3, 9=>6, 11=>18, 12=>15, 13=>22, 14=>23, 15=>12, 16=>10, 17=>9, 
                    18=>11, 19=>21, 20=>48, 21=>13, 22 => 14, 23=>16, 24=>20, 25=>17, 26=>19, 28=>28, 29=>29, 
                    30=>27, 31=>25, 32=>24, 33=>26, 34=>30, 36=>47, 37=>31, 38=>32, 40=>42, 41=>40, 42=>38, 43=>36, 
                    44=>35, 45=>39, 46=>37, 47=>41, 49=>46, 50=>45, 51=>44, 52=>43, 53=>49, 54=>50,
                    56=>51, 57=>52,58=>53,59=>54,60=>55);
                
                // Se sincronizan $desde|$hasta

                // Filtro en intervalo
                $cond_fecha = '1=1';
                if (strpos($fecha,'|') !== false) {
                    list($fi, $ff) = explode('|', $id);
                    $cond_fecha = "incident_date BETWEEN '$fi' AND '$ff'";
                }

                // Para sidih todos los modificados y los creados del dia anterior
                $cond_sidih = '1=1';
                if ($estado == 'sidih') {
                    $cond_sidih = "incident_datemodify >= '$fecha' OR incident_dateadd >= '$fecha'";
                }

                $incs = ORM::factory('incident')->where("id in(85670,85861,85862,85959,85960,86010,86021,86089,86171,86920) AND incident_active = 1")->find_all();

                foreach($incs as $inc) {

                    $incident_id = $inc->id;
                    
                    $id_cat = array();
                    $id_subcat = array();
                    $id_muns = array();
                    $lugar = array();
                    $id_actor_0 = array();
                    $id_actor = array();
                    $id_subactor = array();
                    $id_subsubactor = array();
                    $num_victimas = array();
                    $id_sexo = array();
                    $id_edad = array();
                    $id_rango_edad = array();
                    $id_etnia = array();
                    $id_sub_etnia = array();
                    $id_condicion = array();
                    $id_sub_cond = array();
                    $id_estado = array();
                    $id_ocupacion = array();
                    $num_vict_desc = array();
                    $num_actores_0_desc = array();
                    $num_actores_desc = array();
                    $num_subactores_desc = array();
                    $num_subsubactores_desc = array();
                    
                    // sub categorias
                    foreach($inc->incident_category as $i => $ic) {


                        $id_cat[] = $id_cat_ushahidi_to_sidih[$ic->category->parent_id];
                        $id_subcat[] = $id_scat_ushahidi_to_sidih[$ic->category->id];
                        
                        $num_vict_desc[$i] = 0;
                        $num_actores_0_desc[$i] = 0;
                        $num_actores_desc[$i] = 0;
                        $num_subactores_desc[$i] = 0;
                        $num_subsubactores_desc[$i] = 0;

                        // Actores
                        $acts = ORM::factory('actor_incident_category')
                                ->select('actor_id')
                                ->where('incident_category_id = '.$ic->id)
                                ->orderby('actor_id','asc')
                                ->find_all();

                        foreach($acts as $act) {
                            
                            switch($act->actor->level) {
                                case 0:
                                    $id_actor_0[] = $act->actor_id;
                                    $num_actores_0_desc[$i] += 1;
                                break;
                                case 1:
                                    $id_actor[] = $act->actor_id;
                                    $num_actores_desc[$i] += 1;
                                break;
                                case 2:
                                    $id_subactor[] = $act->actor_id;
                                    $num_subactores_desc[$i] += 1;
                                break;
                                case 3:
                                    $id_subsubactor[] = $act->actor_id;
                                    $num_subsubactores_desc[$i] += 1;
                                break;
                            }
                        }
                        
                        // Victimas
                        $victs = ORM::factory('victim')
                                ->where('incident_category_id = '.$ic->id)
                                ->find_all();
                        $num_v = 0;
                        foreach($victs as $vict) {
                            $num_vict_desc[$i] += 1;
                            $num_victimas[] = empty($vict->victim_cant) ? 0 : $vict->victim_cant;
                            $id_sexo[] = empty($vict->victim_gender_id) ? 0 : $vict->victim_gender_id;
                            $id_edad[] = empty($vict->victim_age_id) ? 0 : $vict->victim_age_id;
                            $id_rango_edad[] = empty($vict->victim_age_group_id) ? 0 : $vict->victim_age_group_id;
                            $id_etnia[] = empty($vict->victim_ethnic_group_id) ? 0 : $vict->victim_ethnic_group_id;
                            $id_sub_etnia[] = empty($vict->victim_sub_ethnic_group_id) ? 0 : $vict->victim_sub_ethnic_group_id;
                            $id_condicion[] = empty($vict->victim_condition_id) ? 0 : $vict->victim_condition_id;
                            $id_sub_cond[] = empty($vict->victim_sub_condition_id) ? 0 : $vict->victim_sub_condition_id;
                            $id_estado[] = empty($vict->victim_status_id) ? 0 : $vict->victim_status_id;
                            $id_ocupacion[] = empty($vict->victim_occupation_id) ? 0 : $vict->victim_occupation_id;

                            $num_v++;
                        }

                        if ($num_v == 0) {
                            $num_victimas[] = 0;
                            $id_sexo[] = 0;
                            $id_edad[] = 0;
                            $id_rango_edad[] = 0;
                            $id_etnia[] = 0;
                            $id_sub_etnia[] = 0;
                            $id_condicion[] = 0;
                            $id_sub_cond[] = 0;
                            $id_estado[] = 0;
                            $id_ocupacion[] = 0;
                        }
                    }
                    
                    // Fuentes
                    $id_subfuente = array();
                    $fecha_fuente = array();
                    $desc_fuente = array();
                    $refer_fuente = array();
                    $sds = ORM::factory('sourcedetail')->where('incident_id = '.$inc->id)->find_all();
                    foreach($sds as $sd) {
                        $id_subfuente[] = $sd->source_id;
                        $fecha_fuente[] = $sd->source_date;
                        $desc_fuente[] = $sd->source_desc;
                        $refer_fuente[] = $sd->source_reference;
                    }

                    // Municipios
                    //echo $inc->location->id.'<br />';
                    if (empty($inc->location->city_id) || $inc->location->city_id < 0) {
                        $divipola = '00000';
                    }
                    else {
                        $city = ORM::factory('city')->where('id = '.$inc->location->city_id)->find_all();
                        $divipola = $city[0]->divipola;
                    }

                    $id_muns[] = $divipola;
                    $lugar[] = $inc->location->location_name;
                    
                    $sintesis = $inc->incident_description;
                    $fecha_evento = $inc->incident_date;
                    $fecha_update = $inc->incident_datemodify;

                    $incidentes[] = compact('incident_id','sintesis', 'fecha_evento','fecha_update', 'id_cat','id_subcat', 
                        'id_muns','lugar', 'id_subfuente','fecha_fuente','desc_fuente','refer_fuente',
                        'id_actor_0','id_actor','id_subactor','id_subsubactor',
                        'num_victimas','id_sexo','id_edad','id_rango_edad','id_etnia','id_sub_etnia','id_condicion',
                        'id_sub_cond','id_estado','id_ocupacion',
                        'num_vict_desc','num_actores_0_desc','num_actores_desc','num_subactores_desc','num_subsubactores_desc'
                    );
                }
                
                $j = json_encode($incidentes);
                    
                if ($estado == 'sidih') {
                    file_put_contents('/var/www/umaic_org/monitor/violenciaarmada/media/uploads/sidih_sync2.json',$j);
                }
	        
                header('Content-type: application/json; charset=utf-8');
	        $this->template->json = $j;

			
			
			
			
			
			break;
            case 'listar':
                $incidentes = array(); 
                
                // Esta se toma del script de importacion de categorias cat_eventos_to_ushahidi.php, invertido
                $id_cat_ushahidi_to_sidih = array(1=>1, 10=>2, 27=>3, 35=>4,39=>5, 48=>6, 55=>7);
                
                $id_scat_ushahidi_to_sidih = array(2=>5, 3=>7, 4=>1, 5=>2, 6=>4, 7=>8, 8=>3, 9=>6, 11=>18, 12=>15, 13=>22, 14=>23, 15=>12, 16=>10, 17=>9, 
                    18=>11, 19=>21, 20=>48, 21=>13, 22 => 14, 23=>16, 24=>20, 25=>17, 26=>19, 28=>28, 29=>29, 
                    30=>27, 31=>25, 32=>24, 33=>26, 34=>30, 36=>47, 37=>31, 38=>32, 40=>42, 41=>40, 42=>38, 43=>36, 
                    44=>35, 45=>39, 46=>37, 47=>41, 49=>46, 50=>45, 51=>44, 52=>43, 53=>49, 54=>50,
                    56=>51, 57=>52,58=>53,59=>54,60=>55);
                
                // Se sincronizan $desde|$hasta

                // Filtro en intervalo
                $cond_fecha = '1=1';
                if (strpos($fecha,'|') !== false) {
                    list($fi, $ff) = explode('|', $fecha); 
                    $cond_fecha = "incident_date BETWEEN '$fi' AND '$ff'";
                }

                // Para sidih todos los modificados y los creados del dia anterior
                $cond_sidih = '1=1';
                if ($estado == 'sidih') {
                    $cond_sidih = "incident_datemodify >= '$fecha' OR incident_dateadd >= '$fecha'";
                }

                $incs = ORM::factory('incident')->where("$cond_fecha AND $cond_sidih AND incident_active = 1")->find_all();

                foreach($incs as $inc) {

                    $incident_id = $inc->id;
                    
                    $id_cat = array();
                    $id_subcat = array();
                    $id_muns = array();
                    $lugar = array();
                    $id_actor_0 = array();
                    $id_actor = array();
                    $id_subactor = array();
                    $id_subsubactor = array();
                    $num_victimas = array();
                    $id_sexo = array();
                    $id_edad = array();
                    $id_rango_edad = array();
                    $id_etnia = array();
                    $id_sub_etnia = array();
                    $id_condicion = array();
                    $id_sub_cond = array();
                    $id_estado = array();
                    $id_ocupacion = array();
                    $num_vict_desc = array();
                    $num_actores_0_desc = array();
                    $num_actores_desc = array();
                    $num_subactores_desc = array();
                    $num_subsubactores_desc = array();
                    
                    // sub categorias
                    foreach($inc->incident_category as $i => $ic) {


                        $id_cat[] = $id_cat_ushahidi_to_sidih[$ic->category->parent_id];
                        $id_subcat[] = $id_scat_ushahidi_to_sidih[$ic->category->id];
                        
                        $num_vict_desc[$i] = 0;
                        $num_actores_0_desc[$i] = 0;
                        $num_actores_desc[$i] = 0;
                        $num_subactores_desc[$i] = 0;
                        $num_subsubactores_desc[$i] = 0;

                        // Actores
                        $acts = ORM::factory('actor_incident_category')
                                ->select('actor_id')
                                ->where('incident_category_id = '.$ic->id)
                                ->orderby('actor_id','asc')
                                ->find_all();

                        foreach($acts as $act) {
                            
                            switch($act->actor->level) {
                                case 0:
                                    $id_actor_0[] = $act->actor_id;
                                    $num_actores_0_desc[$i] += 1;
                                break;
                                case 1:
                                    $id_actor[] = $act->actor_id;
                                    $num_actores_desc[$i] += 1;
                                break;
                                case 2:
                                    $id_subactor[] = $act->actor_id;
                                    $num_subactores_desc[$i] += 1;
                                break;
                                case 3:
                                    $id_subsubactor[] = $act->actor_id;
                                    $num_subsubactores_desc[$i] += 1;
                                break;
                            }
                        }
                        
                        // Victimas
                        $victs = ORM::factory('victim')
                                ->where('incident_category_id = '.$ic->id)
                                ->find_all();
                        $num_v = 0;
                        foreach($victs as $vict) {
                            $num_vict_desc[$i] += 1;
                            $num_victimas[] = empty($vict->victim_cant) ? 0 : $vict->victim_cant;
                            $id_sexo[] = empty($vict->victim_gender_id) ? 0 : $vict->victim_gender_id;
                            $id_edad[] = empty($vict->victim_age_id) ? 0 : $vict->victim_age_id;
                            $id_rango_edad[] = empty($vict->victim_age_group_id) ? 0 : $vict->victim_age_group_id;
                            $id_etnia[] = empty($vict->victim_ethnic_group_id) ? 0 : $vict->victim_ethnic_group_id;
                            $id_sub_etnia[] = empty($vict->victim_sub_ethnic_group_id) ? 0 : $vict->victim_sub_ethnic_group_id;
                            $id_condicion[] = empty($vict->victim_condition_id) ? 0 : $vict->victim_condition_id;
                            $id_sub_cond[] = empty($vict->victim_sub_condition_id) ? 0 : $vict->victim_sub_condition_id;
                            $id_estado[] = empty($vict->victim_status_id) ? 0 : $vict->victim_status_id;
                            $id_ocupacion[] = empty($vict->victim_occupation_id) ? 0 : $vict->victim_occupation_id;

                            $num_v++;
                        }

                        if ($num_v == 0) {
                            $num_victimas[] = 0;
                            $id_sexo[] = 0;
                            $id_edad[] = 0;
                            $id_rango_edad[] = 0;
                            $id_etnia[] = 0;
                            $id_sub_etnia[] = 0;
                            $id_condicion[] = 0;
                            $id_sub_cond[] = 0;
                            $id_estado[] = 0;
                            $id_ocupacion[] = 0;
                        }
                    }
                    
                    // Fuentes
                    $id_subfuente = array();
                    $fecha_fuente = array();
                    $desc_fuente = array();
                    $refer_fuente = array();
                    $sds = ORM::factory('sourcedetail')->where('incident_id = '.$inc->id)->find_all();
                    foreach($sds as $sd) {
                        $id_subfuente[] = $sd->source_id;
                        $fecha_fuente[] = $sd->source_date;
                        $desc_fuente[] = $sd->source_desc;
                        $refer_fuente[] = $sd->source_reference;
                    }

                    // Municipios
                    //echo $inc->location->id.'<br />';
                    if (empty($inc->location->city_id) || $inc->location->city_id < 0) {
                        $divipola = '00000';
                    }
                    else {
                        $city = ORM::factory('city')->where('id = '.$inc->location->city_id)->find_all();
                        $divipola = $city[0]->divipola;
                    }

                    $id_muns[] = $divipola;
                    $lugar[] = $inc->location->location_name;
                    
                    $sintesis = $inc->incident_description;
                    $fecha_evento = $inc->incident_date;
                    $fecha_update = $inc->incident_datemodify;

                    $incidentes[] = compact('incident_id','sintesis', 'fecha_evento','fecha_update', 'id_cat','id_subcat', 
                        'id_muns','lugar', 'id_subfuente','fecha_fuente','desc_fuente','refer_fuente',
                        'id_actor_0','id_actor','id_subactor','id_subsubactor',
                        'num_victimas','id_sexo','id_edad','id_rango_edad','id_etnia','id_sub_etnia','id_condicion',
                        'id_sub_cond','id_estado','id_ocupacion',
                        'num_vict_desc','num_actores_0_desc','num_actores_desc','num_subactores_desc','num_subsubactores_desc'
                    );
                }
                
                $j = json_encode($incidentes);
                    
                if ($estado == 'sidih') {
                    file_put_contents('/var/www/umaic_org/monitor/violenciaarmada/media/uploads/sidih_sync.json',$j);
                }
	        
                header('Content-type: application/json; charset=utf-8');
	        $this->template->json = $j;

            break;
        }
    }

}
