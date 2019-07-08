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

class sourcedetail {
	
	/**
	 * Registers the main event add method
	 */
	public function __construct()
	{
        if (Router::$current_uri == 'reports/submit') {
            $this->post_data = null; //initialize this for later use	
            // Hook into routing
            Event::add('system.pre_controller', array($this, 'add'));
            
        }
        
        // Link
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
		Event::add('ushahidi_action.sourcedetail_form', array($this, '_report_form'));

		// Hook into the report_submit_admin (post_POST) event right before saving
        Event::add('ushahidi_action.report_submit', array($this, '_post_data'));

		// Hook into the report_edit (post_SAVE) event
		Event::add('ushahidi_action.report_add', array($this, '_report_form_submit'));
	
		// Hook into the Report view (front end)
		//Event::add('ushahidi_action.report_meta_after_time', array($this, '_report_view'));
		
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
		Event::add('ushahidi_action.sourcedetail_form', array($this, '_report_form'));

		// Hook into the report_submit_admin (post_POST) event right before saving
        Event::add('ushahidi_action.report_submit_admin', array($this, '_post_data_admin'));

		// Hook into the Report view (front end)
		Event::add('ushahidi_action.report_meta_after_time', array($this, '_report_view'));
		
		
	}
    
    /**
	 * Adds all assets
	 */
	public function add_requirements()
    {
        //set the CSS for this
        Requirements::css('plugins/sourcedetail/css/sourcedetail.css');
        
        //make sure the right java script files are used.
        Requirements::js('plugins/sourcedetail/js/sourcedetail.js');
    }
    
    /**
	 * Adds all assets
	 */
	public function add_requirements_admin()
    {
        //make sure the right java script files are used.
        Requirements::js('plugins/sourcedetail/js/sourcedetail_admin.js');
    }


	/**
	 * Add Actionable Form input to the Report Submit Form
	 */
	public function _report_form()
	{
		// Load the View
		$view = View::factory('sourcedetail/sourcedetail_form');
		// Get the ID of the Incident (Report)
		$id = Event::$data;

		$view->render(TRUE);
	}
	
    /**
	 * Fill post data
	 */
	public function _post_data()
	{
        
        $this->num = Event::$data->sd_num;

        $fields = array('source_id', 'date', 'desc', 'reference');
        foreach($this->num as $_ix) {
            foreach($fields as $_fi) {
                $this->{$_fi.'_'.$_ix} = Event::$data->{'sd_'.$_fi.'_'.$_ix};
            }
        }
	}
    
    /**
	 * Fill post data
	 */
	public function _post_data_admin()
	{
        
        $this->num = Event::$data['sd_num'];

        $fields = array('source_id', 'date', 'desc', 'reference');
        foreach($this->num as $_ix) {
            foreach($fields as $_fi) {
                $this->{$_fi.'_'.$_ix} = Event::$data['sd_'.$_fi.'_'.$_ix];
            }
        }

        $this->_report_form_submit_admin();
    }

	/**
	 * Validate Form Submission
	 */
	public function _report_form_submit()
	{
		$incident = Event::$data;
        foreach($this->num as $_ix) {
            if (!empty($this->{'source_id_'.$_ix})) {
                $sd = new Sourcedetail_Model();

                $sd->incident_id = $incident->id;
                $sd->location_id = $incident->location_id;
                $_sid = explode('|', $this->{'source_id_'.$_ix});
                $sd->source_type_id = $_sid[0];
                $sd->source_id = $_sid[1];
                $sd->source_date = date('Y-m-d', strtotime($this->{'date_'.$_ix}));
                $sd->source_desc = $this->{'desc_'.$_ix};
                $sd->source_reference = $this->{'reference_'.$_ix};

                $sd->save();
            }
        }
	}
    
    /**
	 * Validate Form Submission Admin
	 */
	public function _report_form_submit_admin()
	{
        $incident = Event::$data;

        // Delete Previous Entries
		ORM::factory('sourcedetail')->where('incident_id', $incident['incident_id'])->delete_all();
        
        foreach($this->num as $_ix) {
            if (!empty($this->{'source_id_'.$_ix})) {
                $sd = new Sourcedetail_Model();

                $sd->incident_id = $incident['incident_id'];
                $sd->location_id = $incident['location_id'];
                $_sid = explode('|', $this->{'source_id_'.$_ix});
                $sd->source_type_id = $_sid[0];
                $sd->source_id = $_sid[1];
                $sd->source_date = date('Y-m-d', strtotime($this->{'date_'.$_ix}));
                $sd->source_desc = $this->{'desc_'.$_ix};
                $sd->source_reference = $this->{'reference_'.$_ix};

                //var_dump($sd->source_desc);

                $sd->save();
            }
        }
	}
}//end method

new sourcedetail;
