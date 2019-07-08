<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Actionable Hook - Load All Events
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author	   Ushahidi Team <team@ushahidi.com>
 * @package	   Ushahidi - http://source.ushahididev.com
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license	   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL)
 */

class av {

    var $admin = false;

	/**
	 * Registers the main event add method
	 */
	public function __construct()
	{
        // Form
        if (Router::$current_uri == 'reports/submit') {
            $this->post_data = null; //initialize this for later use
            // Hook into routing
            Event::add('system.pre_controller', array($this, 'add'));
        }

        // Json para mapa por conteo o afectacion
        if (preg_match('/^json\/(cluster|index)\/*+/i', Router::$current_uri) &&
            !empty($_GET['afectacion']) && $_GET['afectacion'] == 1
        ) {
            Event::add('system.pre_controller', array($this, 'add_json_cluster'));
        }

        // Json para mapa desagrupado
        if (preg_match('/^json\/index\/*+/i', Router::$current_uri)) {
            Event::add('system.pre_controller', array($this, 'add_json_index'));
        }

        // Json para acceso
        if (preg_match('/^json\/(cluster|index)\/*+/i', Router::$current_uri) &&
            !empty($_GET['acceso']) && $_GET['acceso'] == 1 && !empty($_GET['acceso_cats'])
        ) {
            Event::add('system.pre_controller', array($this, 'add_json_acceso'));
        }

        // Edit report
        if (preg_match('/^admin\/reports\/edit\/[0-9]+/i', Router::$current_uri)) {
            // Hook into routing
            Event::add('system.pre_controller', array($this, 'add_admin'));
        }
	}

	/**
	 * Adds all the events to the main Ushahidi application
	 */
	public function add()
    {

        Event::add('ushahidi_filter.view_pre_render.layout', array($this, 'add_requirements'));

		// Hook into the form itself
		Event::add('ushahidi_action.report_form', array($this, '_report_form'));

        // Hook into the report_submit_admin (post_POST) event right before saving
		Event::add('ushahidi_action.report_submit', array($this, '_post_data'));

		// Hook into the report_edit (post_SAVE) event
		Event::add('ushahidi_action.report_add', array($this, '_report_form_submit'));

		// Hook into the Report view (front end)
		Event::add('ushahidi_action.report_meta_after_time', array($this, '_report_view'));


    }

    /**
	 * Adds all the events to the main Ushahidi application admin section
	 */
	public function add_admin()
    {

        // Se usa este evento, porque en admin, al usar Requirements::js, lo coloca antes del jquery
        //  saca error, esto como esta programado application/Libraries/Thems -> admin_requirements()

        Event::add('ushahidi_filter.view_pre_render.admin_layout', array($this, 'add_requirements'));
        Event::add('ushahidi_filter.view_pre_render.admin_layout', array($this, 'add_requirements_admin'));

		// Hook into the form itself
		Event::add('ushahidi_action.av', array($this, '_report_form'));

		// Hook into the report_submit_admin (post_POST) event right before saving
        Event::add('ushahidi_action.report_submit_admin', array($this, '_post_data_admin'));

        // Hook into the report_edit (post_SAVE) event
        $this->admin = true;
		Event::add('ushahidi_action.report_edit', array($this, '_report_form_submit'));

		// Hook into the Report view (front end)
		Event::add('ushahidi_action.report_meta_after_time', array($this, '_report_view'));


	}

    public function add_json_cluster() {
        Event::add('ushahidi_filter.json_features', array($this, '_json_cluster'));
    }

    public function add_json_index() {
        Event::add('ushahidi_filter.json_features', array($this, '_json_index'));
    }

    public function add_json_acceso() {
        Event::add('ushahidi_filter.json_features', array($this, '_json_acceso'));
    }

    public function _json_cluster() {

        $data = array();

        foreach (Event::$data as $c => $cluster)
        {
            $victimas = 0;
            $_id = $cluster['properties']['id'];

            if (is_array($_id)) {
                foreach($_id as $id) {
                    $victimas += Victim_Model::get_num_victims($id);
                }
            }
            else {
                $victimas += Victim_Model::get_num_victims($_id);
            }

            if ($victimas > 0) {
                Event::$data[$c]['properties']['count'] = $victimas;

                $data[] = Event::$data[$c];
            }
        }

        Event::$data = $data;
    }
    public function _json_index() {
        foreach (Event::$data as $c => $cluster)
        {
            $id = Event::$data[$c]['properties']['id'];
            $incident = ORM::factory('incident', $id);

            $category = $incident->incident_category[0];
            $category_id = $category->category_id;

            $cat = ORM::factory('category', $category_id);
            $category_id = $cat->id;

            $icon = "";
			if ($cat->category_image)
			{
				$icon = url::convert_uploaded_to_abs($cat->category_image);
			}

            Event::$data[$c]['properties']['category'] = array($category_id);
            Event::$data[$c]['properties']['color'] = $cat->category_color;
            Event::$data[$c]['properties']['icon'] = $icon;
        }
    }

    public function _json_acceso() {

        $data = array();

        foreach (Event::$data as $c => $cluster)
        {
            $_id = $cluster['properties']['id'];
            $acceso = false;

            if (is_array($_id)) {
                $_i = 0;
                $_ids_num = count($_id);
                while (!$acceso && $_i < $_ids_num) {
                    $acceso = $this->acceso_test($_id[$_i]);

                    $_i++;
                }
            }
            else {
                $acceso = $this->acceso_test($_id);
            }

            if ($acceso) {
		        $icon = url::base().'media/img/crisis_humanitarian_access.png';
                Event::$data[$c]['properties']['icon'] = $icon;

                $data[] = Event::$data[$c];
            }
        }

        Event::$data = $data;
    }

    private function acceso_test($id) {
        $form_id = 2;
        $form_field_id = 1;
        $acceso = false;
        $acceso_cats = explode(',', $_GET['acceso_cats']);
        $acceso_cats_num = count($acceso_cats);

        $incident = ORM::factory('incident', $id);

        if ($incident->restricting_access == 1) {
            $custom_field = customforms::get_custom_form_fields($id,$form_id,true);

            $_c = 0;
            while (!$acceso && $_c < $acceso_cats_num) {
                if ($acceso_cats[$_c] == $custom_field[$form_field_id]) {
                    $acceso = true;
                }

                $_c++;
            }
        }

        return $acceso;
    }

    /**
	 * Adds all assets
	 */
	public function add_requirements()
    {

        //set the CSS for this
        Requirements::css('plugins/av/css/av.css');
        Requirements::css('plugins/av/css/dynatree/ui.dynatree.css');

        //make sure the right java script files are used.
        Requirements::js("plugins/av/js/av.js");
        Requirements::js("plugins/av/js/jquery.dynatree.min.js");

    }

    /**
	 * Adds all assets
	 */
	public function add_requirements_admin()
    {
        Requirements::js("plugins/av/js/av_admin.js");
    }

    /**
	 * Adds sync link in admin/reports page
	 */
	public function add_sync_link()
    {
        // Add a Sub-Nav Link
		Event::add('ushahidi_action.nav_admin_reports', array($this, '_reports_link'));

    }

	/**
	 * Add Link in admin reports to synchronize data with sidih
	 */
    public function _reports_link()
	{

        $this_sub_page = Event::$data;
        $tx = Kohana::lang('av.admin_sync_link');

		echo ($this_sub_page == "upload") ? $tx :
			"<a href=\"".url::site()."admin/av/sync_sidih\">$tx</a>";
	}

	/**
	 * Add Actionable Form input to the Report Submit Form
	 */
	public function _report_form()
	{
		// Load the View
		$view = View::factory('av/av_form');
		// Get the ID of the Incident (Report)
		$id = Event::$data;

		$view->render(TRUE);
	}

    /**
	 * Fill post data
	 */
	public function _post_data()
	{
		$this->incident_category = Event::$data->incident_category;
        $this->victim_category_hidden = Event::$data->victim_category_hidden;
        $this->victim_cant = Event::$data->victim_cant;
        $this->victim_gender_id = Event::$data->victim_gender_id;
        $this->victim_status_id = Event::$data->victim_status_id;
        $this->victim_occupation_id = Event::$data->victim_occupation_id;
        $this->victim_sub_ethnic_group_id = Event::$data->victim_sub_ethnic_group_id;
        $this->victim_sub_condition_id = Event::$data->victim_sub_condition_id;
        $this->victim_age_group_id = Event::$data->victim_age_group_id;

        foreach($this->incident_category as $_cid) {
            //echo 'Categoria para actor='.$_cid.'<br />';
            if (!empty(Event::$data->{'id_actor_'.$_cid})) {
                //var_dump(Event::$data->{'id_actor_'.$_cid});
                $this->{'id_actor_'.$_cid} = explode(',', Event::$data->{'id_actor_'.$_cid});
            }
        }
    }

    /**
	 * Fill post data
	 */
	public function _post_data_admin()
	{
		$this->incident_category = Event::$data['incident_category'];
        $this->victim_category_hidden = Event::$data['victim_category_hidden'];
        $this->victim_cant = Event::$data['victim_cant'];
        $this->victim_gender_id = Event::$data['victim_gender_id'];
        $this->victim_status_id = Event::$data['victim_status_id'];
        $this->victim_occupation_id = Event::$data['victim_occupation_id'];
        $this->victim_sub_ethnic_group_id = Event::$data['victim_sub_ethnic_group_id'];
        $this->victim_sub_condition_id = Event::$data['victim_sub_condition_id'];
        $this->victim_age_group_id = Event::$data['victim_age_group_id'];

        foreach($this->incident_category as $_cid) {
            if (!empty(Event::$data['id_actor_'.$_cid])) {
                $this->{'id_actor_'.$_cid} = explode(',', Event::$data['id_actor_'.$_cid]);
            }
        }
    }

	/**
	 * Validate Form Submission
	 */
	public function _report_form_submit()
	{
        $incident = Event::$data;

        //$incident_id = ($this->admin) ? $incident['id'] : $incident->id;
        $incident_id = $incident->id;

        if ($this->admin) {
            // Delete Previous Entries
            ORM::factory('victim')->where('incident_id', $incident_id)->delete_all();
        }

        $ini = 0;
        $vict_cat_h = explode('|',$this->victim_category_hidden);

        //var_dump($this->victim_cant);
        foreach($this->incident_category as $c => $item)
        {
            $incident_category = ORM::factory('incident_category')
                                ->where(array('incident_id' => $incident->id,
                                              'category_id' => $item
                                             ))
                                ->find();

            if ($this->admin) {
                // Delete Previous Entries
                ORM::factory('actor_incident_category')->where('incident_category_id', $incident_category->id)->delete_all();
            }

            // ACTORS
            if (!empty($this->{'id_actor_'.$item})) {
                foreach($this->{'id_actor_'.$item} as $_aid) {
                    $actor = new Actor_Incident_Category_Model();
                    $actor->incident_category_id = $incident_category->id;
                    $actor->actor_id = $_aid;
                    //echo "Actor Id = $_aid <br />";
                    $actor->save();
                }
            }

            // VICTIMS X CATEGORY
            $fin = $ini + $vict_cat_h[$c];
            for ($i=$ini;$i<$fin;$i++){
            //var_dump($this->victim_cant[$i]);
                if (isset($this->victim_cant[$i])){
                    $vict = new Victim_Model();
                    $vict->victim_cant = $this->victim_cant[$i];
                    $vict->incident_category_id = $incident_category->id;
                    $vict->incident_id = $incident->id;

                    if (!empty($this->victim_gender_id[$i])) $vict->victim_gender_id = $this->victim_gender_id[$i];
                    if (!empty($this->victim_status_id[$i])) $vict->victim_status_id = $this->victim_status_id[$i];
                    if (!empty($this->victim_occupation_id[$i])) $vict->victim_occupation_id = $this->victim_occupation_id[$i];

                    if (!empty($this->victim_sub_ethnic_group_id[$i])){
                        $id_tmp = explode('|',$this->victim_sub_ethnic_group_id[$i]);

                        $vict->victim_ethnic_group_id = $id_tmp[0];
                        if (isset($id_tmp[1]))  $vict->victim_sub_ethnic_group_id = $id_tmp[1];
                    }

                    if (!empty($this->victim_sub_condition_id[$i])){
                        $id_tmp = explode('|',$this->victim_sub_condition_id[$i]);

                        $vict->victim_condition_id = $id_tmp[0];
                        if (isset($id_tmp[1]))  $vict->victim_sub_condition_id = $id_tmp[1];
                    }

                    if (!empty($this->victim_age_group_id[$i])){
                        $id_tmp = explode('|',$this->victim_age_group_id[$i]);

                        $vict->victim_age_id = $id_tmp[0];
                        if (isset($id_tmp[1]))  $vict->victim_age_group_id = $id_tmp[1];
                    }

                    $vict->save();
                }
            }
            $ini = $fin;
        }
	}

}//end method

new av;
