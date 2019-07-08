<?php 
/**
 * Reports submit view page.
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module     API Controller
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */
?>
<div class="bg">
    <h2>
        <?php print $title; ?> <span></span>
        <a href="<?php print url::site() ?>admin/reports"><?php echo Kohana::lang('ui_main.view_reports');?></a>
        <a href="<?php print url::site() ?>admin/reports/download"><?php echo Kohana::lang('ui_main.download_reports');?></a>
        <a href="<?php print url::site() ?>admin/reports/upload"><?php echo Kohana::lang('ui_main.upload_reports');?></a>
    </h2>
    <!-- tabs -->
    <div class="tabs">
        <!-- tabset -->
        <ul class="tabset">
            <li>
                <a href="?status=n" <?php if ($status == 'n') echo "class=\"active\""; ?>>
                  <?php echo Kohana::lang('access.show_not_classified');?>
                </a>
            </li>
            <li><a href="?status=p" <?php if ($status == 'p') echo "class=\"active\""; ?>>
              <?php echo Kohana::lang('access.show_preclassified');?></a>
            </li>
            <li><a href="?status=c" <?php if ($status == 'c') echo "class=\"active\""; ?>>
              <?php echo Kohana::lang('access.show_classified');?></a>
            </li>
            <li><a href="?status=d" <?php if ($status == 'd') echo "class=\"active\""; ?>>
              <?php echo Kohana::lang('access.show_discarded');?></a>
            </li>
        </ul>
        <!-- tab -->
        <div class="tab">
            
            <div class="right"><b><?php echo Kohana::lang('access.total_items').': <span id="num_total">'.$total_items; ?></span></b></div>
        </div>
    </div>
    <div class="table-holder">
        <table class="table">
            <thead>
                <!--
                <tr>
                    <th class="col-2"><?php echo Kohana::lang('access.title_reports');?></th>
                </tr>
                -->
            </thead>
            <tfoot>
                <tr class="foot">
                    <td colspan="4">
                        <?php echo $pagination; ?>
                    </td>
                </tr>
            </tfoot>
            <tbody>
            <?php if ($total_items == 0): ?>
                <tr>
                    <td colspan="4" class="col">
                        <h3><?php echo Kohana::lang('ui_main.no_results');?></h3>
                    </td>
                </tr>
            <?php endif; ?>
            <?php
                foreach ($incidents as $incident)
                {
                    $incident_id = $incident->incident_id;
                    $incident_title = strip_tags($incident->incident_title);
                    $incident_description = strip_tags($incident->incident_description);
                    $incident_date = $incident->incident_date;
                    $incident_date = date('d M Y', strtotime($incident->incident_date));
                    
                    // Mode of submission... WEB/SMS/EMAIL?
                    $incident_mode = $incident->incident_mode;
                    
                    // Get the incident ORM
                    $incident_orm = ORM::factory('incident', $incident_id);
                    
                    // Get the person submitting the report
                    $incident_person = $incident_orm->incident_person;
                    
                    //XXX incident_Mode will be discontinued in favour of $service_id
                    if ($incident_mode == 1)	// Submitted via WEB
                    {
                        $submit_mode = "WEB";
                        // Who submitted the report?
                        if ($incident_person->loaded)
                        {
                            // Report was submitted by a visitor
                            $submit_by = $incident_person->person_first . " " . $incident_person->person_last;
                        }
                        else
                        {
                            if ($incident_orm->user_id)					// Report Was Submitted By Administrator
                            {
                                $submit_by = $incident_orm->user->name;
                            }
                            else
                            {
                                $submit_by = Kohana::lang('ui_admin.unknown');
                            }
                        }
                    }
                    elseif ($incident_mode == 2) 	// Submitted via SMS
                    {
                        $submit_mode = "SMS";
                        $submit_by = $incident_orm->message->message_from;
                    }
                    elseif ($incident_mode == 3) 	// Submitted via Email
                    {
                        $submit_mode = "EMAIL";
                        $submit_by = $incident_orm->message->message_from;
                    }
                    elseif ($incident_mode == 4) 	// Submitted via Twitter
                    {
                        $submit_mode = "TWITTER";
                        $submit_by = $incident_orm->message->message_from;
                    }
                    elseif ($incident_mode == 5) 	// Submitted sidih sync
                    {
                        $submit_mode = "SIDIH";
                        $submit_by = 'Admin';
                    }
                    
                    // Retrieve Incident Categories
                    $incident_category = "";
                    foreach($incident_orm->incident_category as $_c => $category)
                    {
                        if ($_c > 0) {
                            $incident_category .= ', ';
                        }
                        $incident_category .= $category->category->category_title;
                    }

                    // Incident Status
                    $incident_approved = $incident->incident_active;
                    $incident_verified = $incident->incident_verified;
                    
                    ?>
                    <tr class="tr_access_row">
                        <td>
                            <div class="access_row">
                                <h4>
                                    <a href="<?php echo url::site() . 'admin/reports/edit/' . $incident_id; ?>" class="more" target="_blank" >
                                        <?php echo $incident_title; ?>
                                    </a>
                                </h4>
                                <p>
                                    <?php echo $incident_description; ?> 
                                </p>
                            </div>
                            <div class="left">
                                <ul class="info">
                                    <li class="none-separator"><?php echo Kohana::lang('ui_main.location');?>: 
                                        <strong><?php echo $incident->location_name; ?></strong>, <strong><?php echo $incident->state; ?></strong>
                                    </li>
                                    <li><?php echo Kohana::lang('ui_main.date');?> 
                                        <strong><?php echo $incident_date; ?></strong>
                                    </li>
                                    <li><?php echo Kohana::lang('ui_main.submitted_by', array($submit_by, $submit_mode));?>

                                        <strong><?php echo $submit_by; ?></strong> via <strong><?php echo $submit_mode; ?></strong>
                                    </li>
                                </ul>
                                <ul class="links">
                                    <li class="none-separator"><?php echo Kohana::lang('ui_main.categories');?>:
                                        <strong><?php echo $incident_category;?></strong>
                                    </li>
                                </ul>
                                <?php
                                // Action::report_extra_admin - Add items to the report list in admin
                                Event::run('ushahidi_action.report_extra_admin', $incident);
                                ?>
                            </div>
                            <div class="right">
                                <div class="hide message left">Evento actualizado con &eacute;xito</div>
                                <button type="button" class="btn btn_classify"><?php echo Kohana::lang('access.classify')?></button>
                            </div>
                            <div class="access_form">
                                <form id="form_<?php echo $incident_id ?>">
                                <div>
                                    <h4>Categorias de restricción al acceso</h4>
                                    <?php
                                    $html = '';
                                    foreach($categories as $cat) {
                                        $iid = "category_".$cat->id."_".$incident_id;
                                        
                                        $set_default = '';
                                        foreach($incident_orm->incident_category as $ic) {
                                            if ($cat->id == $ic->category_id) {
                                                $set_default = ' checked ';
                                                continue;
                                            }
                                        }

                                        $html .= '<div>';
                                        $html .= form::checkbox(array("name" => "incident_category[]", "id" => $iid,"data-incident" => $incident_id), $cat->id, $set_default );
                                        $html .= form::label($iid, $cat->category_title);
                                        $html .= '</div>';
                                    }

                                    echo $html;

                                    ?>    
                                </div>

                                <?php
                                // Ahora muestra categorias

                                // If the user has insufficient permissions to edit report fields, we flag this for a warning message
                                $show_permission_message = FALSE;
                                foreach ($disp_custom_fields[$incident_id] as $field_id => $field_property)
                                {
                                    // Is the field required
                                    $isrequired = ($field_property['field_required'])
                                        ? "<font color=red> *</font>"
                                        : "";

                                    // Private field
                                    $isprivate = ($field_property['field_ispublic_visible'])
                                        ? '<font style="color:gray;font-size:70%">(' . Kohana::lang('ui_main.private') . ')</font>'
                                        : '';

                                    // Workaround for situations where admin can view, but doesn't have sufficient perms to edit.
                                    if (isset($custom_field_mismatch))
                                    {
                                        if(isset($custom_field_mismatch[$field_id]))
                                        {
                                            if($show_permission_message == FALSE)
                                            {
                                                echo '<small>'.Kohana::lang('ui_admin.custom_forms_insufficient_permissions').'</small><br/>';
                                                $show_permission_message = TRUE;
                                            }

                                            echo '<strong>'.$field_property['field_name'].'</strong><br/>';
                                            if (isset($form['custom_field'][$field_id]))
                                            {
                                                echo $form['custom_field'][$field_id];
                                            }
                                            else
                                            {
                                                echo Kohana::lang('ui_main.no_data');;
                                            }
                                            echo '<br/><br/>';
                                            //echo "</div>";
                                            continue;
                                        }
                                    }

                                    // Give all the elements an id so they can be accessed easily via javascript
                                    $id_name = 'id="custom_field_'.$field_id.'"';

                                    // Get the field value
                                    $field_value = ( ! empty($form['custom_field'][$field_id]))
                                        ? $form['custom_field'][$field_id]
                                        : $field_property['field_default'];

                                        // Multiple-selector Fields
                                        echo "<div class=\"report_row\" id=\"custom_field_row_" . $field_id ."\">";
                                        echo "<h4>" . $field_property['field_name'] . $isrequired . " " . $isprivate . "</h4>";

                                        $defaults = explode('::',$field_property['field_default']);

                                        $default = (isset($defaults[1])) ? $defaults[1] : 0;

                                        if (isset($form['custom_field'][$field_id]))
                                        {
                                            if($form['custom_field'][$field_id] != '')
                                            {
                                                $default = $form['custom_field'][$field_id];
                                            }
                                        }

                                        $options = explode('|',$defaults[0]);
                                        $html ='';
                                        switch ($field_property['field_type'])
                                        {
                                            case 6:
                                                $multi_defaults = !empty($field_property['field_response']) ? explode(',', $field_property['field_response']) : NULL;
                                                $cnt = 0;
                                                foreach($options as $option)
                                                {
                                                    $html .= "<div>";
                                                    $set_default = FALSE;
                                                    
                                                    $option = trim($option);

                                                    // MONITOR :: Split por ¬ para obtener value¬label, para
                                                    // no almacenar el value igual a label, muy largo

                                                    if (strpos($option, '¬') !== false) {
                                                        $_t = explode('¬', $option);
                                                        $value = $_t[0];
                                                        $option = $_t[1];
                                                    }
                                                    else {
                                                        $value = $option;
                                                    }

                                                    if (!empty($multi_defaults))
                                                    {
                                                        foreach($multi_defaults as $key => $def)
                                                        {
                                                            $set_default = (trim($value) == trim($def));
                                                            if ($set_default)
                                                                break;
                                                        }
                                                    }

                                                    $html .= "<span style=\"margin-right: 15px\">";
                                                    $namef = "custom_field[".$field_id."-".$cnt."]";
                                                    $html .= form::checkbox(array("name" => $namef, "id" => "custom_field_".$field_id."_".$cnt,"data-incident" => $incident_id), $value, $set_default );
                                                    $html .= form::label($namef," ".$option);
                                                    $html .= "</span>";

                                                    $html .= "</div>";

                                                    $cnt++;
                                                }
                                                // XXX Hack to deal with required checkboxes that are submitted with nothing checked
                                                $html .= form::hidden("custom_field[".$field_id."-BLANKHACK]",'',$id_name);
                                                break;

                                        }

                                        echo $html;
                                        echo "</div>";
                                }
                            if (!empty($_GET['status']) && in_array($_GET['status'],array('c','p'))) { ?>
                                <div class="right"><button type="button" class="btn btn_delete"><?php echo Kohana::lang('access.not_access_incident')?></button></div>
                            <?php
                            }
                            ?>
                            <div class="right"><button type="button" class="btn btn_save" data-incident="<?php echo $incident_id ?>" data-access="1"><?php echo Kohana::lang('access.save')?></button></div>
                            <?php
                            if (empty($_GET['status']) || $_GET['status'] == 'n') { ?>
                                <div class="right"><button type="button" class="btn btn_pre" data-incident="<?php echo $incident_id ?>" data-access="-2"><?php echo Kohana::lang('access.pre')?></button></div>
                            <?php
                            }
                            ?>
                            <div class="right"><button type="button" class="btn btn_discard" data-incident="<?php echo $incident_id ?>"><?php echo Kohana::lang('access.discard')?></button></div>
                            <div class="clear"></div>
                            <input type="hidden" name="incident_id" value="<?php echo $incident_id ?>" />
                            <input type="hidden" name="status" value="<?php if (!empty($_GET['status'])) echo $_GET['status']; ?>" />
                            <input type="hidden" id="access_<?php echo $incident_id ?>" name="not_access" value="0" class="not_access" />
                            </form>
                        </div> <!-- end access form -->
                    </td>
                </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
    </div>
    <!-- end reports list -->
</div>



