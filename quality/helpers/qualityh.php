<?php
/**
 * Quality helper. Displays report quality form on the front-end.
 *
 * @package    Quality
 * @author     Ushahidi Team
 * @copyright  (c) 2008 Ushahidi Team
 * @license    http://www.ushahidi.com/license.html
 */
class qualityh_Core {

    /**
     * Displays quality review
     */
	public static function form($incident_id)
	{

        $incident = ORM::factory('incident', $incident_id);
        $review = $incident->incident_quality_review;
        $email = $incident->incident_person->person_email;
        $name = Auth::instance()->get_user()->name;
        $superadmin = Auth::instance()->logged_in("superadmin");
        $html = '';
        $quality = $incident->incident_quality;

        if ($superadmin || (!$superadmin && !empty($review))) {
            $html = '<div id="quality_plugin"><div id="quality_note">
                    <h4>
                        '.Kohana::lang('quality.title').'
                    </h4>';

            if ($superadmin) {
                $html .= '<p class="note">'.Kohana::lang('quality.use_note_superadmin').'</p>';
            }
            else {
                $html .= '<p class="note">'.Kohana::lang('quality.use_note_admin').'</p>';
            }

            $html .= '</div>';

            $html .= '<div id="quality_form">';

            if (Auth::instance()->logged_in("superadmin")){

                $g = Kohana::lang('quality.save');
                $e = Kohana::lang('quality.send_email');
                $c = Kohana::lang('quality.complete');

                if ($quality == 2) {
                    $g = Kohana::lang('quality.again').' '.$g;
                    $e = Kohana::lang('quality.again').' '.$e;
                }

                $html .= '<div><textarea id="quality_review" name="quality_review" class="textarea">'.$review.'</textarea></div>';

                $html .= '<div class="clear"></div>';
                $html .= '<div id="quality_buttons"><button id="quality_save" class="btn" type="button" data-incident="'.$incident_id.'">'.$g.'</button>';

                if ($quality == 2 || $quality == 0) {
                    $html .= '&nbsp;<button id="quality_complete" class="btn" type="button" data-incident="'.$incident_id.'">'.$c.'</button>';
                    $quality = 3;
                }

                $html .= '</div>';
            }

            else if (Auth::instance()->logged_in("admin")){
                if (!empty($review)) {
                    $html .= '<div class="only_view">'.nl2br($review).'</div>';
                    $quality = 2;
                }
            }

            $html .= '</div>';

            $html .= '<input type="hidden" id="incident_quality" name="incident_quality" value="'.$quality.'" />';

            if (Auth::instance()->logged_in("superadmin")){
                $html .= '<div id="quality_success" class="success hide">
                            <div>Revisión guardada con éxito.
                            <br /><br />
                            <button id="quality_email" class="btn" type="button" data-email="'.$email.'" data-name="'.$name.'">'.$e.' '.$email.'</button>
                            <br /><br /><a href="../../../admin/quality">Volver al listado de resportes</a></div>
                            </div>
                        ';

                $html .= '<div id="quality_email_success" class="success hide">
                            <div>Email enviado con éxito.
                            <br /><br /><a href="../../../admin/quality">Volver al listado de resportes</a></div>
                            </div>
                        ';

                $html .= '<div id="quality_complete" class="success hide">
                            <div>Revisión completa!.
                            <br /><br /><a href="../../../admin/quality">Volver al listado de resportes</a></div>
                            </div>
                        ';
            }

            $html .= '<div class="clear"></div>
            </div>';
        }

        return $html;
	}
}
