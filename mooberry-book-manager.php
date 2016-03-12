<?php
 /**
  *  Plugin Name: Mooberry Book Manager
  *  Plugin URI: http://www.mooberrydreams.com/products/mooberry-book-manager/
  *  Description: An easy-to-use system for authors. Add your new book to your site in minutes, including links for purchase or download, sidebar widgets, and more. 
  *  Author: Mooberry Dreams
  *  Author URI: http://www.mooberrydreams.com/
  *	 Version: 3.0
  *	 Text Domain: mooberry-book-manager
  *	 Domain Path: languages
  *
  *	 Copyright 2015  Mooberry Dreams  (email : bookmanager@mooberrydreams.com)
  *
  *  This program is free software; you can redistribute it and/or modify
  *  it under the terms of the GNU General Public License, version 2, as 
  *  published by the Free Software Foundation.
  *
  *  This program is distributed in the hope that it will be useful,
  *  but WITHOUT ANY WARRANTY; without even the implied warranty of
  *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  *  GNU General Public License for more details.
  *
  *  You should have received a copy of the GNU General Public License
  *  along with this program; if not, write to the Free Software
  *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
  *
  * @package MBDB
  * @author Mooberry Dreams
  * @version 3.0
  */
  
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
 
 
// Plugin version
if ( ! defined( 'MBDB_PLUGIN_VERSION' ) ) {
	define( 'MBDB_PLUGIN_VERSION', '3.0' );
}

if ( ! defined( 'MBDB_PLUGIN_VERSION_KEY' ) ) {
	define('MBDB_PLUGIN_VERSION_KEY', 'mbdb_version');
}

// Plugin Folder Path
if ( ! defined( 'MBDB_PLUGIN_DIR' ) ) {
	define( 'MBDB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

// Plugin Folder URL
if ( ! defined( 'MBDB_PLUGIN_URL' ) ) {
	define( 'MBDB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

// Plugin Root File
if ( ! defined( 'MBDB_PLUGIN_FILE' ) ) {
	define( 'MBDB_PLUGIN_FILE', __FILE__ );
} 

// Load in CMB2
if ( file_exists( MBDB_PLUGIN_DIR . 'includes/cmb2/init.php' ) ) {
	require_once MBDB_PLUGIN_DIR . 'includes/cmb2/init.php';
} elseif ( file_exists( MBDB_PLUGIN_DIR . 'includes/CMB2/init.php' ) ) {
	require_once MBDB_PLUGIN_DIR . 'includes/CMB2/init.php';
}

require_once MBDB_PLUGIN_DIR . 'includes/plugin-functions.php';
require_once MBDB_PLUGIN_DIR . 'includes/mooberry-dreams/moobd-database.php';
require_once MBDB_PLUGIN_DIR . 'includes/class-mbdb-db-cpt.php';
require_once MBDB_PLUGIN_DIR . 'includes/class-mbdb-db-books.php';
require_once MBDB_PLUGIN_DIR . 'includes/class-mbdb-cpt.php';
require_once MBDB_PLUGIN_DIR . 'includes/class-mbdb-book.php';

require_once MBDB_PLUGIN_DIR . 'includes/helper-functions.php';
require_once MBDB_PLUGIN_DIR . 'includes/helper-functions-updates.php';
require_once MBDB_PLUGIN_DIR . 'includes/helper-functions-validation.php';
require_once MBDB_PLUGIN_DIR . 'includes/scripts-and-styles.php';

require_once MBDB_PLUGIN_DIR . 'book.php';
require_once MBDB_PLUGIN_DIR . 'single-book.php';
require_once MBDB_PLUGIN_DIR . 'book-grid.php';
require_once MBDB_PLUGIN_DIR . 'book-widget.php';
require_once MBDB_PLUGIN_DIR . 'admin-settings-page.php';
require_once MBDB_PLUGIN_DIR . 'tax-grid.php';


if ( ! class_exists( 'Mooberry_Book_Manager' ) ) :

/**
 * Main Mooberry_Book_Manager Class
 *
 * @since 3.0
 */
final class Mooberry_Book_Manager {
	/** Singleton *************************************************************/

	/**
	 * @var Mooberry_Book_Manager The one true Mooberry_Book_Manager
	 * @since 3.0
	 */
	private static $instance;

	/**
	 * MBDB Books Object
	 *
	 * @var object
	 * @since 3.0
	 */
	public $books;

	/**
	 * Main Mooberry_Book_Manager Instance
	 *
	 * Insures that only one instance of Mooberry_Book_Manager exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since 3.0
	 * @static
	 * @staticvar array $instance
	 * @see MBDB()
	 * @return The one true Mooberry_Book_Manager
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Mooberry_Book_Manager ) ) {
			self::$instance = new Mooberry_Book_Manager;
			self::$instance->books  = new MBDB_Books();			
		}
		return self::$instance;
	}

	/**
	 * Throw error on object clone
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since 3.0
	 * @return void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__,  'Cheatin&#8217; huh?', '3.0' );
	}

	/**
	 * Disable unserializing of the class
	 *
	 * @since 3.0
	 * @return void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, 'Cheatin&#8217; huh?', '3.0' );
	}

}

endif; // End if class_exists check



/**
 * The main function responsible for returning the one true Mooberry Book Manager
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $mbdb = MBDB(); ?>
 *
 * @since 3.0
 * @return object The one true Mooberry_Book_Manager Instance
 */
function MBDB() {
	return Mooberry_Book_Manager::instance();
}

// Get MBDB Running
MBDB();
