<?php defined('SYSPATH') or die('No direct script quality.');
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

class Quality_Controller extends Admin_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->template->this_page = 'reports';
		$this->params = array('incident_quality' => 0);
	}
	
	/**
	 * Lists the reports.
	 *
	 * @param int $page
	 */
	public function index($page = 1) {
	    
        $customforms = '';    
        $this->template->content = new View('quality/admin/index');
        $this->template->content->title = Kohana::lang('quality.title');
		
        // Database table prefix
		$table_prefix = Kohana::config('database.default.table_prefix');

		// Hook into the event for the reports::fetch_incidents() method
		Event::add('ushahidi_filter.fetch_incidents_set_params', array($this,'_add_incident_filters'));


        $status = (Auth::instance()->logged_in("superadmin")) ? 'n' : 'r';

		if ( !empty($_GET['status']))
		{
			$status = $_GET['status'];

		}
        
        switch(strtolower($status)) {
            case 'r':
                $this->params['incident_quality'] = 1;
            break;
            case 'f':
                $this->params['incident_quality'] = 2;
            break;        
            case 'd':
                $this->params['incident_quality'] = 3;
            break;        
        
        }

		// Check, has the form been submitted?
		$form_error = FALSE;
		$form_saved = FALSE;
		$form_action = "";

        
		// Fetch all incidents
        $where = array('incident_quality = '.$this->params['incident_quality']);

        // Only shows the events of the user
        if (Auth::instance()->logged_in("admin") && !Auth::instance()->logged_in("superadmin")) {
            $email = Auth::instance()->get_user()->email;
            $where[] = "person_email = '$email'";
        }

        // No se muestran incidente de categorias complementarias y
        // uso explosivo remanentes de guerra
        //$where[] = 'category_id NOT IN(52,53,54,36,37,38)';

		// Filters
		$category_id = $state_id = 0;
		$filter_category = !empty($_GET['filter_category']);
		$filter_state = !empty($_GET['filter_state']);

		if ($filter_category) {
			$category_id = $_GET['filter_category'];
			$where[] = "category_id IN ($category_id)";
		}
		if ($filter_state) {
			$state_id = $_GET['filter_state'];
			$where[] = 'state_id = '.$state_id;
		}
        
        $sql = "SELECT COUNT(DISTINCT(i.id)) AS num
                FROM $table_prefix.incident i 
                INNER JOIN $table_prefix.incident_category ic ON i.id=ic.incident_id
                INNER JOIN $table_prefix.incident_person ip ON i.id=ip.incident_id
                INNER JOIN $table_prefix.location loc ON loc.id=i.location_id
                WHERE ".implode(' AND ',$where)." LIMIT 1";

        //echo $sql;

		$db = new Database();
        $result = $db->query($sql);
        $num_incidents = $result[0]->num;

		// Pagination
		$pagination = new Pagination(array(
				'style' => 'front-end-reports',
				'query_string' => 'page',
				'items_per_page' => (int) Kohana::config('settings.items_per_page'),
				'total_items' => $num_incidents
				));

		Event::run('ushahidi_filter.pagination',$pagination);

		// Reports
        $incidents = Quality_Model::get_incidents($where,
                                                $pagination, 'incident_date', 'DESC');

		Event::run('ushahidi_filter.filter_incidents',$incidents);
		$this->template->content->countries = Country_Model::get_countries_list();
		$this->template->content->incidents = $incidents;
		$this->template->content->pagination = $pagination;
		$this->template->content->form_error = $form_error;
		$this->template->content->form_saved = $form_saved;
		$this->template->content->form_action = $form_action;
		//$this->template->content->disp_custom_fields = customforms::get_custom_form_fields(false,2, FALSE);
		
        // get form fields and form response for each incident
        foreach($incidents as $_incident) {
            $customforms[$_incident->incident_id] = customforms::get_custom_form_fields($_incident->incident_id,2, FALSE);
        }
		
        $this->template->content->disp_custom_fields = $customforms;

		// Total Reports
		$this->template->content->total_items = $pagination->total_items;

		// Status Tab
		$this->template->content->status = $status;

		//Filters
		$cats_tree = category::get_category_tree_data(FALSE, FALSE);
		$cats = array('0' => Kohana::lang('ui_main.categories'));
		foreach ($cats_tree as $cat_id => $c) {
			
			// Parent category has children's ids
			$cat_id_tmp = array($cat_id);
			foreach ($c['children'] as $children) {
                $cat_id_tmp[] = $children['category_id'];
			}
			
			$cats[implode(',',$cat_id_tmp)] = $c['category_title'];

			foreach ($c['children'] as $children) {
                $cats[$children['category_id']] = '&nbsp;&nbsp;&nbsp;|_'.$children['category_title'];
			}
		}
		$this->template->content->category_id = $category_id;
		$this->template->content->cats = $cats;
		
		$states = array('0' => Kohana::lang('state.states'));
		$_rs = ORM::factory('state')->select('id,state AS n')->orderby('state')->find_all();
        foreach($_rs as $_r) {
            $c['id'] = $_r->id;

            $states[$_r->id] = $_r->n;
        }
		
		$this->template->content->states = $states;
		$this->template->content->state_id = $state_id;
		
		// Javascript Header
		//$this->template->js = new View('admin/reports_js');
    }

	/**
	 * Adds extra filter paramters to the reports::fetch_incidents()
	 * method. This way we can add 'all_reports=>true and other filters
	 * that don't come standard sinc we are on the backend.
	 * Works by simply adding in SQL conditions to the params
	 * array of the reprots::fetch_incidents() method
	 * @return none
	 */
	public function _add_incident_filters()
	{
		$params = Event::$data;
		$params = array_merge($params, $this->params);
		Event::$data = $params;
	}

	/**
	 * Save quality information 
     */
	public function save() {
		$this->template = "";
		$this->auto_render = FALSE;
        
        $incident_id = $_POST['incident_id'];

        $incident = ORM::factory('incident', $incident_id);
		
        // Create validation object
        $post = Validation::factory($_POST);

        $incident->incident_quality = $post->quality; // Por revisar
        $incident->incident_quality_review = $post->review;

        $incident->save();

        $success = 1;
        
        header('Content-type: application/json; charset=utf-8');
		echo json_encode(compact('success'));

    }
    
    /**
	 * Send Email 
     */
	public function email() {
		$this->template = "";
		$this->auto_render = FALSE;
        
        $incident_id = $_POST['incident_id'];
        $email = $_POST['email'];
        $name = $_POST['name'];

		$settings = Kohana::config('settings');

		$url = url::site()."admin/reports/edit/".$incident_id;

		$to = $email;
		$from = array($settings['site_email'], $settings['site_name']);
		$subject = $settings['site_name'].': '.Kohana::lang('quality.email_subject');
		$message = Kohana::lang('quality.email_body',
			array($name, $url));

        
        email::send($to, $from, $subject, $message, TRUE);

        $success = 1;
        
        header('Content-type: application/json; charset=utf-8');
		echo json_encode(compact('success'));

    }
    
}
