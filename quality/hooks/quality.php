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

class quality {
	
	/**
	 * Registers the main event add method
	 */
	public function __construct()
	{
        // Link
        if (Router::$current_uri == 'admin/reports') {
            // Hook into routing
            Event::add('system.pre_controller', array($this, 'add_quality_link'));
        }

        // Index quality
        if (Router::$current_uri == 'admin/quality')
        {
            Event::add('ushahidi_filter.view_pre_render.admin_layout', array($this, 'add_requirements'));
        }
        
        // Add quality fields
        if (preg_match('/^admin\/reports\/edit\/[0-9]+/i', Router::$current_uri)) {
            // Hook into routing
            Event::add('system.pre_controller', array($this, 'add'));
        }
        
	}
    
    /**
	 * Adds sync link in admin/reports page
	 */
	public function add_quality_link()
    {
        // Add a Sub-Nav Link
		Event::add('ushahidi_action.nav_admin_reports', array($this, '_reports_link'));

    }
    
    /**
	 * Add Link in admin reports section
	 */
    public function _reports_link() 
	{
		
        $this_sub_page = Event::$data;
        $tx = Kohana::lang('quality.admin_menu_link');
		
		echo ($this_sub_page == "upload") ? $tx : 
			"<a href=\"".url::site()."admin/quality/\">$tx</a>";	

	}
	
    /**
	 * Adds all the events to the main Ushahidi application admin section
	 */
	public function add()
    {

        // Se usa este evento, porque en admin, al usar Requirements::js, lo coloca antes del jquery
        //  saca error, esto como esta programado application/Libraries/Thems -> admin_requirements()
        
        Event::add('ushahidi_filter.view_pre_render.admin_layout', array($this, 'add_requirements'));

		// Hook into the form itself
		Event::add('ushahidi_action.quality_form', array($this, '_report_form'));
        
        // Hook into the report_submit_admin (post_POST) event right before saving
        Event::add('ushahidi_action.report_submit_admin', array($this, '_post_data'));

	}
    
    /**
	 * Adds all assets
	 */
	public function add_requirements()
    {
        //set the CSS for this
        Requirements::css('plugins/quality/css/quality.css');
        
        //make sure the right java script files are used.
        Requirements::js('plugins/quality/js/quality.js');
    }
    
	/**
	 * Add Actionable Form input to the Report Submit Form
	 */
	public function _report_form()
	{
		// Get the ID of the Incident (Report)
		$id = Event::$data;

        // Load the View
        $view = View::factory('quality/quality_form')
                ->set('incident_id', $id);

		$view->render(TRUE);
	}
	
    /**
	 * Fill post data
	 */
	public function _post_data()
	{
        
        $this->incident_quality = (empty(Event::$data['incident_quality'])) ? 0 : Event::$data['incident_quality'];

        $this->_report_form_submit(Event::$data['incident_id']);
    }
    
    /**
	 * Process form
	 */
	public function _report_form_submit($incident_id)
	{
        $incident = ORM::factory('incident', $incident_id);

        $incident->incident_quality = $this->incident_quality;

        $incident->save();
	}

}//end method

new quality;
