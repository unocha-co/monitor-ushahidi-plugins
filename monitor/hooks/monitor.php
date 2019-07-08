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

class monitor {
	
	/**
	 * Registers the main event add method
	 */
	public function __construct()
	{
        // Agrega link a monitoreo de medio al crear reporte y está logueado
        if (Router::$current_uri == 'reports/submit') {
            Event::add('system.pre_controller', array($this, 'add_link_monitoreo_medios'));
        }
	}
	
    /**
	 * Adds monitoreo de medios link
	 */
	public function add_link_monitoreo_medios()
    {
		Event::add('ushahidi_action.nav_main_top', array($this, 'mm_link'));
    }
    
    /**
	 * Adds monitoreo de medios link
	 */
    public function mm_link() 
    {
        // Solo lo muestra si esta logueado
        if (isset($_SESSION['auth_user'])) {
		    echo "<li><a href='http://sidih.salahumanitaria.co/monitoreo_medios' target='_blank'>MONITOREO DE MEDIOS</a></li>";
        }
	}
	
}	

new monitor;
