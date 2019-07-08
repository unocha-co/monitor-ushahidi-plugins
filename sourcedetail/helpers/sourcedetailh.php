<?php
/**
 * Victim helper. Displays victims form on the front-end.
 * 
 * @package    Victim
 * @author     Ushahidi Team
 * @copyright  (c) 2008 Ushahidi Team
 * @license    http://www.ushahidi.com/license.html
 */
class sourcedetailh_Core {
	
    /**
     * Displays source list
     */
	public static function form()
	{
        $html = '';
        $ss = Source_Model::get_list();
        $html = "<div id='sd_group_0' class='sd_group hide'>";
        $html .= "<div class='buttons'>
                    <a href='#' class='add' id='add_sd_group' onClick='addSDGroup(); return false;'></a>
                    <a href='#' class='rem' id='delete_sd_group' onClick='deleteSDGroup(event); return false;'></a>
                    <input type='hidden' id='sd_num_0' name='sd_num[]' value='0' />
                 </div>";

        $html .= "<div>
                    <label for='sd_source_id_0'>".Kohana::lang('sourcedetail.source')."</label>
                    <select id='sd_source_id_0' name='sd_source_id_0' class='sd_dd'>
                        <option value=''></option>
                    ";
        foreach ($ss as $_s){
            $_id = $_s['id'];
            $html .= "<optgroup label='".($_s['n'])."'>";

            if (!empty($_s['h'])) {
                foreach ($_s['h'] as $_idn => $_nn){
                    $html .= "<option value='".$_id."|".$_idn."'>".$_nn."</option>";
                }
            }

            $html .= "</optgroup>";
        }
        
        $html .= '</select></div>';
        $html .= '<div>
                    <label for="sd_date_0">'.Kohana::lang('sourcedetail.date').'</label>
                    <input type="text" id="sd_date_0" name="sd_date_0" value="'.date('m/d/Y').'" class="text" />';
        $html .= '</div>';
        $html .= '<div>
                    <label for="sd_desc_0">'.Kohana::lang('sourcedetail.desc').'</label>
                    <textarea id="sd_desc_0" name="sd_desc_0" class="textarea"></textarea>
                 </div>';
        $html .= '<div>
                    <label for="sd_reference_0">'.Kohana::lang('sourcedetail.reference').'</label>
                    <textarea id="sd_reference_0" name="sd_reference_0" class="textarea"></textarea>
                  </div>
                  </div>';
		
        return $html;
	}
    
}
