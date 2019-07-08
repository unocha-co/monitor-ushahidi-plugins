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

class access {
	
	/**
	 * Registers the main event add method
	 */
	public function __construct()
	{
            
        if (Router::$current_uri == 'admin/access') {
            Event::add('system.pre_controller', array($this, 'add'));
        }

        // Link
        if (Router::$current_uri == 'admin/reports') {
            // Hook into routing
            Event::add('system.pre_controller', array($this, 'add_sync_link'));
        }
        
        // Edit report
        if (preg_match('/^admin\/reports\/edit\/[0-9]+/i', Router::$current_uri)) {
            // Hook into routing
            Event::add('system.pre_controller', array($this, 'add_admin'));
        }
	}
	
    /**
	 * Adds sync link in admin/reports page
	 */
	public function add_sync_link()
    {
        // Add a Sub-Nav Link
		Event::add('ushahidi_action.nav_admin_reports', array($this, '_reports_link'));

    }
    
    /***
	 * Adds all the events to the main Ushahidi application
	 */
	public function add()
    {
        Event::add('ushahidi_filter.view_pre_render.admin_layout', array($this, 'add_requirements'));

    }
    
    /**
	 * Adds all the events to the main Ushahidi application admin section
	 */
	public function add_admin()
    {

        // Se usa este evento, porque en admin, al usar Requirements::js, lo coloca antes del jquery
        //  saca error, esto como esta programado application/Libraries/Thems -> admin_requirements()
        
        Event::add('ushahidi_filter.view_pre_render.admin_layout', array($this, 'add_requirements_admin'));

	}

    
    /**
	 * Adds all assets
	 */
	public function add_requirements()
    {
        Requirements::css('plugins/access/css/access.css');
        Requirements::js('plugins/access/js/access.js');
    }
    
    /**
	 * Adds all assets
	 */
	public function add_requirements_admin()
    {
        Requirements::css('plugins/access/css/access_admin.css');
        Requirements::js("plugins/access/js/access_admin.js");
    }

	/**
	 * Add Link in admin reports to synchronize data with sidih
	 */
    public function _reports_link() 
	{
		
        $this_sub_page = Event::$data;
        $tx = Kohana::lang('access.admin_access_menu_link');
		
		echo ($this_sub_page == "upload") ? $tx : 
			"<a href=\"".url::site()."admin/access/\">$tx</a>";	

	}
}//end method

new access;
