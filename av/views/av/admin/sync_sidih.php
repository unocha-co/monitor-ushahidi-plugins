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
                <a href="<?php print url::site() ?>admin/reports/upload"><?php echo Kohana::lang('ui_main.upload_reports');?></a></h2>
	<!-- report-form -->
	<div class="report-form">
		<!-- column -->
		<div class="upload_container">
                            <div class="report_instructions">
                            <?php
                            if (!isset($success)){
                            ?>
                                <p>Esta opci&oacute;n permite sincronizar reportes con la base de datos de SIDIH:</p>
                                <p><b>Status servidor OCHA:</b></p>
                                <ul>
                                    <li><?php echo $status_server ?>
                                    <li>Periodo importaci&oacute;n: <?php echo $periodo ?>
                                    <li><b><?php echo $num_total ?> reportes para importar</li>
                                </ul>
                                </b>
                                <p>Recuerde que los reportes importados desde SIDIH entran al sistema <b>APROBADOS</b> y <b>VERIFICADOS</b>, por favor, sea cuidadoso en revisar el listado del siguiente paso en busca de duplicidades.<br /><br />
                                Se considera duplicidad  a 2 eventos que pertenece a la misma <b>categoria</b> y tienen la misma <b>fecha</b> y <b>localización</b>.
                                </p>
                            <?php 
                            }
                            ?>
                            </div>
													
                            <div class="sync_sidih">
                            <?php print form::open(NULL, array('id' => 'syncForm', 'name' => 'syncForm')); ?>
                            <?php 
                            if (isset($server_ok)){
                                if (!isset($success) && $num_total > 0){
                                    ?>
                                    <div class="btns">
                                        <ul>
                                            <li><a href="<?php echo url::site().'admin/av/sync_sidih/1';?>" class="btn_save"><?php echo strtoupper(Kohana::lang('ui_main.continue'));?></a></li>
                                        </ul>
                                    </div>						
                                    <?php 
                                }
                                else if (isset($success)){
                                    if (!isset($import_summary)){
                                        echo "<p>Eventos a sincronizar: </p><p class='success_summary'>$success_summary</p><ul>";
                                        foreach ($success as $key => $t){
                                            echo "<li>$t</li>";

                                            if(isset($confirm_import[$key])){
                                                
                                                echo '<span class="confirm_import">Importar evento?&nbsp;';
                                                echo "<input type='radio' name='import_$key' value=1>&nbsp;Si&nbsp;";
                                                echo "<input type='radio' name='import_$key' value=0 checked>&nbsp;No&nbsp;</span>";

                                            }
                                        }
                                        echo '</ul>';
                                        ?>
                                        <div class="btns">
                                            <ul>
                                                <li><a href="#" class="btn_save" onclick="if (confirm('Está seguro que desea continuar?')){document.getElementById('syncForm').submit()} else{return false;}"><?php echo strtoupper(Kohana::lang('ui_main.continue'));?></a></li>
                                                <li><a href="<?php echo url::site().'admin/av/sync_sidih';?>" class="btns_red"><?php echo strtoupper(Kohana::lang('ui_main.cancel'));?></a></li>
                                            </ul>
                                        </div>						
                                    <?php
                                    }
                                    else{
                                        echo "<p>Resumen sincronización: </p>$import_summary";
                                    }
                                }
                            }
                            print form::hidden('sync_submit',1);
                            print form::close(); 
                            ?>
                        </div>
                    </div>
	</div>
</div>



