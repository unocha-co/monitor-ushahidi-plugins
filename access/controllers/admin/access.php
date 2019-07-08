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

class Access_Controller extends Admin_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->template->this_page = 'reports';
		$this->params = array('restricting_access' => 0);
        $this->cats_access = array(2,3,4,5,6,7,8,9,11,13,29,30,31,32,34,37,38,40,41,42,43,44,45,46,47); // Sidih Cats
        $this->cats_access_str = implode(',', $this->cats_access);
	}
	
	/**
	 * Lists the reports.
	 *
	 * @param int $page
	 */
	public function index($page = 1) {
	    
        $customforms = '';    
        $this->template->content = new View('access/admin/index');
        $this->template->content->title = Kohana::lang('access.title');
		
        // Database table prefix
		$table_prefix = Kohana::config('database.default.table_prefix');

		// Hook into the event for the reports::fetch_incidents() method
		Event::add('ushahidi_filter.fetch_incidents_set_params', array($this,'_add_incident_filters'));


		$status = "n";

		if ( !empty($_GET['status']))
		{
			$status = $_GET['status'];

            switch(strtolower($status)) {
                case 'c':
			        $this->params['restricting_access'] = 1;
                break;
                case 'd':
			        $this->params['restricting_access'] = -1;
                break;        
                case 'p':
			        $this->params['restricting_access'] = -2;
                break;        
            
			}
		}

		// Check, has the form been submitted?
		$form_error = FALSE;
		$form_saved = FALSE;
		$form_action = "";

        
		// Fetch all incidents
        $where = "incident_active = 1 AND restricting_access = ".$this->params['restricting_access'];
        
        $where = array('restricting_access = '.$this->params['restricting_access'],
                        'incident_active = 1');
        
        if ($status == 'n') {
            $where[] = 'category_id IN ('.$this->cats_access_str.')';
        }

        $sql = "SELECT COUNT(DISTINCT(i.id)) AS num
                FROM $table_prefix.incident i 
                INNER JOIN $table_prefix.incident_category ic ON i.id=ic.incident_id
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
        $incidents = Access_Model::get_incidents($where,
                                                $pagination, 'incident_date', 'DESC');

		Event::run('ushahidi_filter.filter_incidents',$incidents);
        
        $categories = ORM::factory('category')
            ->where('parent_id',55)
            ->find_all();

        $this->template->content->countries = Country_Model::get_countries_list();
		$this->template->content->incidents = $incidents;
        $this->template->content->categories = $categories;
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
	 * Save access information 
     */
	public function save() {
		$this->template = "";
		$this->auto_render = FALSE;
        
        $incident_id = $_POST['incident_id'];

        $incident = ORM::factory('incident')
            ->where('id',$incident_id)
            ->where('incident_active',1)
            ->find();
		
        // Create validation object
        $post = Validation::factory($_POST);

        // Guarda forma si es validar o pre-clasificar
        if (in_array($post->not_access, array(1,-2))) {
            foreach ($post->custom_field as $field_id => $field_response)
            {
                $split = explode("-", $field_id);
                if (isset($split[1]))
                {
                    // The view sets a hidden field for blankhack
                    if ($split[1] == 'BLANKHACK')
                    {
                        if(!isset($custom_fields[$split[0]]))
                        {
                            // then no checkboxes were checked
                            $custom_fields[$split[0]] = '';
                        }
                        // E.Kala - Removed the else {} block; either way continue is still invoked
                        continue;
                    }

                    if (isset($custom_fields[$split[0]]))
                    {
                        $custom_fields[$split[0]] .= ",$field_response";
                    }
                    else
                    {
                        $custom_fields[$split[0]] = $field_response;
                    }
                }
                else
                {
                    $custom_fields[$split[0]] = $field_response;
                }
            }
            
            $post->custom_field = $custom_fields;
            
            // STEP 3: SAVE CATEGORIES
            reports::save_category($post, $incident);
            
            // STEP 5: SAVE CUSTOM FORM FIELDS
            reports::save_custom_fields($post, $incident);
        
        }
        else {
            // Delete Previous Entries
            ORM::factory('form_response')->where('incident_id',$incident_id)->delete_all();
        }

        // Update incident restricting_access field
        $incident->restricting_access = $post->not_access;
        $incident->save();

        $success = 1;
        
        // Editar un evento ya clasificado, editar = cambiar las respuestas
        $edit = (!empty($_POST['status']) && $_POST['status'] == 'c' && $post->not_access == 1) ? 1 : 0;
		
        header('Content-type: application/json; charset=utf-8');
		echo json_encode(compact('success','edit'));

    }
    
    /**
	 * Discarded access incident 
     */
	public function operation($incident_id,$v) {
		$this->template = "";
		$this->auto_render = FALSE;
        
        //$incident_id = $_POST['incident_id'];

        $incident = ORM::factory('incident')
            ->where('id',$incident_id)
            ->where('incident_active',1)
            ->find();
		

        // Update incident restricting_access field
        // -1 Descartado
        // -2 Pre clasificado
        $incident->restricting_access = $v;
        $incident->save();

        $success = 1;
        $edit = 0;
		
        header('Content-type: application/json; charset=utf-8');
		echo json_encode(compact('success','edit'));

    }
}
