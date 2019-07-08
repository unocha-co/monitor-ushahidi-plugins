<?php
/**
 * Victim helper. Displays victims form on the front-end.
 *
 * @package    Victim
 * @author     Ushahidi Team
 * @copyright  (c) 2008 Ushahidi Team
 * @license    http://www.ushahidi.com/license.html
 */
class avh_Core {

    /**
     * Displays victim form in report submit
     */
	public static function vform()
	{


        $html = '';

        $genders = array('');
        $genders += ORM::factory('victim_gender')->orderby('gender')->select_list('id','gender');

        $status = array('');
        $status += ORM::factory('victim_status')->orderby('status')->select_list('id','status');

        $occupation = array('');
        $occupation += ORM::factory('victim_occupation')->orderby('occupation')->select_list('id','occupation');

        // Sub Ethnic Group
        $subs = Victim_Sub_Ethnic_Group_Model::get_dropdown_options();
        $dropdown_s_e = '<select name="victim_sub_ethnic_group_id[]"><option value="0"></option>';
        foreach ($subs as $t => $s_e){
            $tmp = explode('|',$t);
            $id_p = $tmp[0];
            $dropdown_s_e .= "<option value='$id_p' class='optgroup'>$tmp[1]</option>";
            foreach ($s_e as $idx => $op){
                $dropdown_s_e .= "<option value='$id_p|$idx' class='option'>&nbsp;&nbsp;&nbsp;$op</option>";
            }
        }
        $dropdown_s_e .= '</select>';

        // Sub Condition
        $subs = Victim_Sub_Condition_Model::get_dropdown_options();
        $dropdown_s_c = '<select name="victim_sub_condition_id[]"><option value="0"></option>';
        foreach ($subs as $t => $s_e){
            $tmp = explode('|',$t);
            $id_p = $tmp[0];
            $dropdown_s_c .= "<option value='$id_p' class='optgroup'>$tmp[1]</option>";
            foreach ($s_e as $idx => $op){
                $dropdown_s_c .= "<option value='$id_p|$idx' class='option'>&nbsp;&nbsp;&nbsp;$op</option>";
            }
        }
        $dropdown_s_c .= '</select>';

        // Age - Age Group
        $subs = Victim_Age_Group_Model::get_dropdown_options();
        $dropdown_a_g = '<select name="victim_age_group_id[]"><option value="0"></option>';
        foreach ($subs as $t => $s_e){
            $tmp = explode('|',$t);
            $id_p = $tmp[0];
            $dropdown_a_g .= "<option value='$id_p' class='optgroup'>$tmp[1]</option>";
            foreach ($s_e as $idx => $op){
                $dropdown_a_g .= "<option value='$id_p|$idx' class='option'>&nbsp;&nbsp;&nbsp;$op</option>";
            }
        }
        $dropdown_a_g .= '</select>';


        $html .= '<div id="victim_category_div_0" class="av_category_div victim_category_div">';
        $html .= form::input('victim_category_hidden','','class="victim_category_hidden"');
        $html .= '<div id="victim_group_0" class="victim_group hide"><div class="buttons">';
        $html .= "<a href='#' class='add' id='add_victim_group' onClick='addVictimGroup(); return false;'></a>";
        $html .= "<a href='#' class='rem' id='delete_victim_group' onClick='deleteVictimGroup(event); return false;'></a></div>";
        $html .= "<div>";
		$html .= form::label('victim_cant[]', Kohana::lang('av.victim_num'));
		$html .= form::input('victim_cant[]','','class="numbersOnly"');
        $html .= "</div>";
        $html .= "<div class='clear'></div>";
        $html .= "<div class='third'>";
		$html .= form::label('victim_gender_id[]', Kohana::lang('av.gender'));
		$html .= form::dropdown('victim_gender_id[]',$genders);
        $html .= "</div>";
        $html .= "<div class='third'>";
		$html .= form::label('victim_status_id[]', Kohana::lang('av.victim_status'));
		$html .= form::dropdown('victim_status_id[]',$status);
        $html .= "</div>";
        $html .= "<div class='clear'>";
		$html .= form::label('victim_sub_ethnic_group_id[]', Kohana::lang('av.victim_ethnic'));
        $html .= $dropdown_s_e;
        $html .= "</div>";
        $html .= "<div class='clear'>";
		$html .= form::label('victim_age_group_id[]', Kohana::lang('av.victim_age'));
        $html .= $dropdown_a_g;
        $html .= "</div>";
        $html .= "<div class='clear'></div>";
		$html .= form::label('victim_sub_condition_id[]', Kohana::lang('av.victim_condition'));
        $html .= $dropdown_s_c;
		$html .= form::label('victim_occupation_id[]', Kohana::lang('av.victim_occupation'));
		$html .= form::dropdown('victim_occupation_id[]',$occupation);
        $html .= '</div></div>';

		return $html;
	}

    /**
     * Displays actor form in report submit
     */
	public static function aform()
	{

        $acs = Actor_Model::get_dropdown_options();
        $dwn = "<div id='actor_category_div_0' class='av_category_div actor_category_div'><input type='hidden' id='id_actor_0' name='id_actor_0'>";
        $dwn .= "<div id='actor_group_0' class='actor_group hide'>";
                //<div class='search'>
                //    <input type='' id='actor_search_0' class='actor_search_input' placeholder='Buscar...' onkeydown='filterList(event, false)' />&nbsp;
                //    <a href='#' onclick='filterList(event, true); return false;'>Cancelar</a>
                //</div>
        $dwn .= "<ul>";

        // 17 Sept 2012, se muestran solo los papas, arbol igual al de categorias
        foreach ($acs as $_ac){
            $_idp = $_ac['id'];
            $_for = "papa_$_idp";
            $dwn .= "<li data=\"ida:".$_idp."\" class='folder'>
                        ".ucfirst(strtolower($_ac['n']));

            $dwn .= '<ul>';
            foreach ($_ac['h'] as $_ach){
                $_idh = $_ach['id'];
                $_forh = "hijo_$_idh";
                $dwn .= "<li data=\"ida:".$_idh."\" class='folder'>".ucfirst(strtolower($_ach['n']));

                // Tercer nivel
                $dwn .= '<ul>';

                foreach ($_ach['h'] as $_idn => $_nn){
                //foreach ($_ach['h'] as $_acn){

                    //$_idn = $_acn['id'];
                    //$_nn = $_acn['n'];
                    $_forn = "nieto_$_idn";

                    $count = ORM::factory('actor')->where('parent_id',$_idn)->count_all();
                    $lazy = ($count == 0) ? 'false' : 'true';

                    $dwn .= "<li data=\"ida:".$_idn.",isLazy:$lazy\">".ucfirst(strtolower($_nn))."</li>";

                    // Cuarto nivel
                    /*
                    $dwn .= '<ul>';

                    foreach ($_acn['h'] as $_idbn => $_nn){
                        $_forn = "bisnieto_$_idbn";
                        $dwn .= "<li data=\"ida:".$_idbn."\">".ucfirst(strtolower($_nn))."</li>";
                    }
                    $dwn .= '</ul></li>';
                     */
                }
                $dwn .= '</ul></li>';
            }
            $dwn .= '</ul>';


            $dwn .= '</li>';
        }
        $dwn .= '</ul></div></div>';

		return $dwn;
	}

	/**
	 * Displays victim information in view report
     * @param int $id Id Report
	 */
	public static function view($id)
	{

        $incident = new Incident_Model($id);

		$html = '';

        foreach($incident->incident_category as $incident_category){
            $html .= '<div class="victim_category_div clear"><h2>'.$incident_category->category->category_title.'</h2>';
            $victims = ORM::factory('victim')->where('incident_category_id',$incident_category->id)->find_all();
            if (isset($victims[0])){
                foreach ($victims as $victim){
                    $html .= '<div class="victim_group"><ul>';

                    $html .= "<li>";
                    $html .= '<span>'.Kohana::lang('av.victim_num').'</span>: '.$victim->victim_cant;
                    $html .= "</li>";

                    $html .= "<li>";
                    if (!empty($victim->victim_gender_id))
                        $html .= '<span>'.Kohana::lang('av.gender').'</span>: '.$victim->victim_gender->gender;
                    $html .= "</li>";

                    $html .= "<li>";
                    if (!empty($victim->victim_status_id))
                        $html .= '<span>'.Kohana::lang('av.victim_status').'</span>: '.$victim->victim_status->status;
                    $html .= "</li>";

                    $html .= "<li>";
                    if (!empty($victim->victim_sub_ethnic_group_id))
                        $html .= '<span>'.Kohana::lang('av.victim_ethnic').'</span>: '.$victim->victim_sub_ethnic_group->sub_ethnic_group;
                    else if (!empty($victim->victim_ethnic_group_id))
                        $html .= '<span>'.Kohana::lang('av.victim_ethnic').'</span>: '.$victim->victim_ethnic_group->ethnic_group;
                    $html .= "</li>";

                    $html .= "<li>";
                    if (!empty($victim->victim_age_group_id))
                        $html .= '<span>'.Kohana::lang('av.victim_age').'</span>: '.$victim->victim_age_group->age_group;
                    else if (!empty($victim->victim_age_id))
                        $html .= '<span>'.Kohana::lang('av.victim_age').'</span>: '.$victim->victim_age->age;
                    $html .= "</li>";

                    $html .= "<li>";
                    if (!empty($victim->victim_sub_condition_id))
                        $html .= '<span>'.Kohana::lang('av.victim_condition').'</span>: '.$victim->victim_sub_condition->sub_condition;
                    else if (!empty($victim->victim_condition_id))
                        $html .= '<span>'.Kohana::lang('av.victim_condition').'</span>: '.$victim->victim_condition->condition;
                    $html .= "</li>";

                    $html .= "<li>";
                    if (!empty($victim->victim_occupation_id))
                        $html .= '<span>'.Kohana::lang('av.victim_occupation').'</span>: '.$victim->victim_occupation->occupation;
                    $html .= "</li>";
                    $html .= '</div>';
                }
            }
            else    $html .= '<p>'.Kohana::lang('av.victim_no_info').'</p>';
            $html .= '</div>';
        }

		return $html;
	}

}
