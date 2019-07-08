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
            <?php
            if (Auth::instance()->logged_in("superadmin")){ 
            ?>
            <li>
                <a href="?status=n" <?php if ($status == 'n') echo "class=\"active\""; ?>>
                  <?php echo Kohana::lang('quality.show_review');?>
                </a>
            </li>
            <?php } ?>
            <li><a href="?status=r" <?php if ($status == 'r') echo "class=\"active\""; ?>>
              <?php echo Kohana::lang('quality.show_to_fixed');?></a>
            </li>
            <li><a href="?status=f" <?php if ($status == 'f') echo "class=\"active\""; ?>>
              <?php echo Kohana::lang('quality.show_fixed');?></a>
            </li>
            <li><a href="?status=d" <?php if ($status == 'd') echo "class=\"active\""; ?>>
              <?php echo Kohana::lang('quality.show_discarded');?></a>
            </li>
        </ul>
        <!-- tab -->
        <div class="tab">
            
            <div class="right"><b><?php echo Kohana::lang('quality.total_items').': <span id="num_total">'.$total_items; ?></span></b></div>
        </div>
        <div id="filtros">
            <span><?php echo Kohana::lang('ui_main.filters');?></span>
        <?php print form::dropdown('filter_state',$states,$state_id, ' class="select" '); ?>
        <?php print form::dropdown('filter_category',$cats,$category_id, ' class="select" '); ?>
        </div>
    </div>
    <div class="table-holder">
        <table class="table">
            <thead>
                <!--
                <tr>
                    <th class="col-2"><?php echo Kohana::lang('quality.title_reports');?></th>
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
                    <tr class="tr_quality_row">
                        <td>
                            <div class="quality_row">
                                <h4>
                                    <a href="<?php echo url::site() . 'admin/reports/edit/' . $incident_id; ?>" class="more">
                                        <?php echo $incident_title; ?>
                                    </a>
                                </h4>
                            </div>
                            <div class="left">
                                <ul class="info">
                                    <li class="none-separator"><?php echo Kohana::lang('ui_main.date');?> 
                                        <strong><?php echo $incident_date; ?></strong>
                                    </li>
                                    <li><?php echo Kohana::lang('ui_main.submitted_by', array($submit_by, $submit_mode));?>

                                        <strong><?php echo $submit_by; ?></strong> via <strong><?php echo $submit_mode; ?></strong>
                                    </li>
                                </ul>
                                <?php
                                // Action::report_extra_admin - Add items to the report list in admin
                                Event::run('ushahidi_action.report_extra_admin', $incident);
                                ?>
                            </div>
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



