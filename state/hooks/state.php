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

class state {
	
	/**
	 * Registers the main event add method
	 */
	public function __construct()
	{
        if (Router::$current_uri == 'reports/submit') {
            
            $this->state_id = null; //initialize this for later use	
		    $this->city_id = null; //initialize this for later use	
            
            // Hook into routing
            Event::add('system.pre_controller', array($this, 'add'));
            
        }
        
        // Busqueda de reportes frontend
        if (Router::$current_uri == 'reports') {
            
            $this->state_id = null; //initialize this for later use	
            $this->city_id = null; //initialize this for later use	

            // Hook into routing
            Event::add('system.pre_controller', array($this, 'add'));
            
        }
        
        // Edit report
        if (preg_match('/^admin\/reports\/edit[\/0-9]*/i', Router::$current_uri)) {
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
		Event::add('ushahidi_action.state', array($this, '_state_combo'));

		// Hook into the report_submit_admin (post_POST) event right before saving
        Event::add('ushahidi_action.report_submit', array($this, '_post_data'));
        
        // Hook into the report_edit (post_SAVE) event
		Event::add('ushahidi_action.report_add', array($this, '_report_form_submit'));
	}
    
    /**
	 * Adds all the events to the main Ushahidi admin application
	 */
	public function add_admin()
    {
        Event::add('ushahidi_filter.view_pre_render.admin_layout', array($this, 'add_requirements'));
        Event::add('ushahidi_filter.view_pre_render.admin_layout', array($this, 'add_requirements_admin'));
    
        // Hook into the form itself
		Event::add('ushahidi_action.state', array($this, '_state_combo'));
		// Hook into the report_submit_admin (post_POST) event right before saving
		Event::add('ushahidi_action.report_submit_admin', array($this, '_post_data_admin'));
        // Hook into the report_edit (post_SAVE) event
		Event::add('ushahidi_action.report_edit', array($this, '_report_form_submit'));
	}
    
    /**
	 * Adds all assets
	 */
	public function add_requirements()
    {
        //set the CSS for this
        Requirements::css('plugins/state/css/state.css');
        Requirements::js('plugins/state/js/state.js');
    }
    
    /**
	 * Adds all assets
	 */
	public function add_requirements_admin()
    {
        Requirements::js('plugins/state/js/state_admin.js');
    }
	
	/**
	 * Add Actionable Form input to the Report Submit Form
	 */
	public function _state_combo()
	{
		// Load the View
		$view = View::factory('state/state_form');

        $states = array('' => Kohana::lang('state.select_one')) + ORM::factory('state')->orderby('state')->select_list('id','state');
        $view->states = $states;

		$view->render(TRUE);
	}
	
    /**
	 * Fill post data
	 */
	public function _post_data()
	{
		$this->state_id = Event::$data->select_state;
		$this->city_id = Event::$data->select_city;
	}
    
    /**
	 * Fill post data
	 */
	public function _post_data_admin()
    {
		$this->state_id = Event::$data['select_state'];
		$this->city_id = Event::$data['select_city'];
    }
	
    /**
	 * Handle Form Submission and Save Data
	 */
	public function _report_form_submit()
	{
		$incident = Event::$data;
		$location_id = $incident->location_id;
		
		if ($this->state_id)
		{
			$location = ORM::factory('location')
				->where('id', $location_id)
				->find();

		
            $location->state_id = $this->state_id;
            $location->city_id = $this->city_id;
			$location->save();
			
		}
	}
}	

new state;
