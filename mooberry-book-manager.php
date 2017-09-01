<?php
 /**
  *  Plugin Name: Mooberry Book Manager
  *  Plugin URI: http://bookmanager.mooberrydreams.com/
  *  Description: An easy-to-use system for authors. Add your new book to your site in minutes, including links for purchase or download, sidebar widgets, and more. 
  *  Author: Mooberry Dreams
  *  Author URI: http://www.mooberrydreams.com/
  *  Donate Link: https://www.paypal.me/mooberrydreams/
  *	 Version: 4.0.15
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
  * @version 4.0.15
  */
  
 // Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
 
 //error_log('starting');
// Plugin version
if ( ! defined( 'MBDB_PLUGIN_VERSION' ) ) {
	define( 'MBDB_PLUGIN_VERSION', '4.0.15' );
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

// plugin setting constants
if ( !defined('MBDB_GRID_COVER_HEIGHT_DEFAULT') ) {
	define( 'MBDB_GRID_COVER_HEIGHT_DEFAULT', apply_filters( 'mbdb_book_grid_cover_height_default', 200 ) );
}
if ( !defined('MBDB_GRID_COVER_HEIGHT_MIN') ) {
	define( 'MBDB_GRID_COVER_HEIGHT_MIN', apply_filters('mbdb_book_grid_cover_min_height', 50 ) );
}

// This function is required for backwards compatibility with the extensions
// that check for this function to exist to determine if Mooberry Book Manager is
// installed.  This function is no longer used in version 4.0 because the activation
// function is now inside a class
// So this function doesn't have to do anything, it just has to exist
function mbdb_activate() {
	
}

/**
 * Include the core class responsible for loading all necessary components of the plugin.
 */
//require_once MBDB_PLUGIN_DIR . 'includes/class-mbm-loader.php';
//require_once MBDB_PLUGIN_DIR . 'includes/class-mooberry-book-manager.php';
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
	
	// this is strictly for backwards compatibilty with original 4 extension plugins
	public $books;
	
	public $books_db;
	public $book_grid_db;
	public $book_CPT;
	public $book_grid_CPT;
	public $tax_grid_page;
	public $book_factory;
//	public $widget_factory;
	public $grid_factory;
	
	public $settings_menu;
	
	public $helper_functions;
	public $options;
	

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
		////error_log('MBDB');
	/* 	$trace = debug_backtrace();
      if (isset($trace[1])) {
          // $trace[0] is ourself
          // $trace[1] is our caller
          // and so on...
          ////error_log(var_dump($trace[1]));

          //error_log( "called by {$trace[1]['file']} :: {$trace[1]['function']} on line {$trace[1]['line']}" );

      } */
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Mooberry_Book_Manager ) ) {
			self::$instance = new Mooberry_Book_Manager;
			
		// //error_log('making instance');
			add_action( 'plugins_loaded', array( self::$instance, 'plugins_loaded' ) );
			add_action( 'admin_notices', array( self::$instance, 'admin_notices' ) );
			
			// require files
			self::$instance->require_plugin_files();
			
			self::$instance->options = new Mooberry_Book_Manager_Options();
			self::$instance->helper_functions = new Mooberry_Book_Manager_Helper_Functions();
			self::$instance->books_db = new MBDB_DB_Books();
			self::$instance->book_grid_db = new MBDB_DB_Book_Grid();
			self::$instance->book_factory = new Mooberry_Book_Manager_Simple_Book_Factory();
			
			// strictly for backwards compatibility
			self::$instance->books  = new MBDB_Books();
			////error_log('create CPT object');
			
			//self::$instance->book_CPT = new Mooberry_Book_Manager_Book_CPT();
			
			$book_CPT = new Mooberry_Book_Manager_Book_CPT();
			
			self::$instance->grid_factory = apply_filters( 'mbdb_grid_factory', new Mooberry_Book_Manager_Simple_Grid_Factory() );
			
			self::$instance->book_grid_CPT = new Mooberry_Book_Manager_Book_Grid_CPT(  );
			//$book_grid_CPT = new Mooberry_Book_Manager_Book_Grid_CPT();
			
			//self::$instance->tax_grid_CPT = new Mooberry_Book_Manager_Tax_Grid_CPT( $grid_factory );
			//$tax_grid_CPT = new Mooberry_Book_Manager_Tax_Grid_CPT();
			self::$instance->tax_grid_page = new Mooberry_Book_Manager_Tax_Grid_Page( );
			
			//self::$instance->widget_factory = new Mooberry_Book_Manager_Simple_Widget_Factory();
			if ( is_admin() ) {
				// set up menus
				//self::$instance->settings_menu = new Mooberry_Book_Manager_Settings_Menu();
				add_action(  'admin_menu', array( self::$instance, 'add_options_page' ), 8 );
				self::$instance->settings_menu = self::$instance->mbm_admin();
				//$settings_menu = self::$instance->mbm_admin();
			}
		//	//error_log(print_r( self::$instance, true) );
		
		}
		
		return self::$instance;
	}

	/**
	 * Helper function to get/return the Myprefix_Admin object
	 * @since  0.1.0
	 * @return Myprefix_Admin object
	 */
	public function mbm_admin() {
		static $object = null;
		if ( is_null( $object ) ) {
			$object = new Mooberry_Book_Manager_Core_Settings();
		}

		return $object;
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
	
	public static function plugins_loaded() {
		load_plugin_textdomain( 'mooberry-book-manager', FALSE, basename( MBDB_PLUGIN_DIR ) . '/languages/' );
		
		// check if SuperCache is installed
		define('MBDB_SUPERCACHE', function_exists('wp_cache_manager'));
		define('MBDB_WPSEO_INSTALLED', defined( 'WPSEO_FILE' ) );
		
		if ( defined( 'MBDBMA_PLUGIN_VERSION' ) && version_compare( MBDBMA_PLUGIN_VERSION, '1.7', '<' ) ) {
			$message = __('You must update MBM Multi-Author to be compatible with MBM version 4.0', 'mooberry-book-manager');
			MBDB()->helper_functions->set_admin_notice( $message, 'error', 'mbdb_update_mbdbma');
		}
	}
	
	public static function admin_notices() {
		$notices  = get_option('mbdb_admin_notices');
		if (is_array($notices)) {
			foreach ($notices as $key => $notice) {
			  echo "<div class='notice {$notice['type']}' id='{$key}'><p>{$notice['message']}</p></div>";
			}
		}
	}
	
	/**
	 * Init
	 *
	 * Registers Custom Post Types and Taxonomies
	 * Verifies Tax Grid is installed correctly
	 * Does upgrade routines
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */

	public static function init() {
		
		// let CPTs register themselves
		// MBDB()->book_CPT->register();
		// MBDB()->book_grid_CPT->register();
		// MBDB()->tax_grid_CPT->add_tax_grid();
		
	/*	
		mbdb_register_cpts();
		mbdb_register_taxonomies();
		
		
		
		
		mbdb_upgrade_versions();
	*/
		
	}
		
	public function add_options_page() {
		$this->options_page = add_menu_page( __( 'Mooberry Book Manager Settings', 'mooberry-book-manager' ), __( 'Mooberry Book Manager Settings', 'mooberry-book-manager' ), 'manage_mbm', 'mbdb_options', array( self::$instance->settings_menu , 'admin_page_display' ) );
	}

	
	private function require_plugin_files() {
		// Load in CMB2
		if ( file_exists( MBDB_PLUGIN_DIR . 'includes/cmb2/init.php' ) ) {
			require_once MBDB_PLUGIN_DIR . 'includes/cmb2/init.php';
		} elseif ( file_exists( MBDB_PLUGIN_DIR . 'includes/CMB2/init.php' ) ) {
			require_once MBDB_PLUGIN_DIR . 'includes/CMB2/init.php';
		}
		
		require_once MBDB_PLUGIN_DIR . 'includes/wp-background-processing/wp-background-processing.php';
		require_once MBDB_PLUGIN_DIR . 'includes/depreciated-functions.php';
		require_once MBDB_PLUGIN_DIR . 'includes/class-mbdb-books.php';
		
		
		require_once MBDB_PLUGIN_DIR . 'includes/class-mbm-helper-functions.php';
		require_once MBDB_PLUGIN_DIR . 'includes/updates.php';
		
		require_once MBDB_PLUGIN_DIR . 'includes/class-mbm-object.php';
		require_once MBDB_PLUGIN_DIR . 'includes/class-mbm-cpt.php';
		require_once MBDB_PLUGIN_DIR . 'includes/class-mbm-taxonomy.php';
		require_once MBDB_PLUGIN_DIR . 'includes/class-mbm-book-cpt.php';
		//require_once MBDB_PLUGIN_DIR . 'includes/class-mbm-grid-cpt.php';
		require_once MBDB_PLUGIN_DIR . 'includes/class-mbm-book-grid-cpt.php';
		require_once MBDB_PLUGIN_DIR . 'includes/class-mbm-tax-grid-page.php';
		require_once MBDB_PLUGIN_DIR . 'includes/class-mbm-cpt-object.php';
		require_once MBDB_PLUGIN_DIR . 'includes/class-mbm-book-basic.php';
		require_once MBDB_PLUGIN_DIR . 'includes/class-mbm-book.php';
		require_once MBDB_PLUGIN_DIR . 'includes/class-mbm-grid.php';
		require_once MBDB_PLUGIN_DIR . 'includes/class-mbm-book-grid.php';
		require_once MBDB_PLUGIN_DIR . 'includes/class-mbm-tax-grid.php';
		require_once MBDB_PLUGIN_DIR . 'includes/class-mbm-simple-grid-factory.php';
		require_once MBDB_PLUGIN_DIR . 'includes/class-mbm-simple-book-factory.php';
		
		require_once MBDB_PLUGIN_DIR . 'includes/class-mbm-download-format.php';
		require_once MBDB_PLUGIN_DIR . 'includes/class-mbm-edition-format.php';
		require_once MBDB_PLUGIN_DIR . 'includes/class-mbm-retailer.php';
		require_once MBDB_PLUGIN_DIR . 'includes/class-mbm-book-link.php';
		require_once MBDB_PLUGIN_DIR . 'includes/class-mbm-buy-link.php';
		require_once MBDB_PLUGIN_DIR . 'includes/class-mbm-download-link.php';
		require_once MBDB_PLUGIN_DIR . 'includes/class-mbm-review.php';
		require_once MBDB_PLUGIN_DIR . 'includes/class-mbm-publisher.php';
		require_once MBDB_PLUGIN_DIR . 'includes/class-mbm-edition.php';
		require_once MBDB_PLUGIN_DIR . 'includes/class-mbm-social-media-site.php';
		require_once MBDB_PLUGIN_DIR . 'includes/class-mbm-options.php';
		
		require_once MBDB_PLUGIN_DIR . 'includes/class-mbm-book-list.php';
		
		require_once MBDB_PLUGIN_DIR . 'includes/mooberry-dreams/moobd-database.php';
		require_once MBDB_PLUGIN_DIR . 'includes/interface-mbm-data-storage-behavior.php';
		require_once MBDB_PLUGIN_DIR . 'includes/class-mbm-db-cpt.php';
		require_once MBDB_PLUGIN_DIR . 'includes/class-mbm-cmb-cpt.php';
		require_once MBDB_PLUGIN_DIR . 'includes/class-mbm-db-books.php';
		require_once MBDB_PLUGIN_DIR . 'includes/class-mbm-db-book-grid.php';
		
		require_once MBDB_PLUGIN_DIR . 'includes/class-mbm-widget.php';
		require_once MBDB_PLUGIN_DIR . 'includes/class-mbm-book-widget.php';
		//require_once MBDB_PLUGIN_DIR . 'includes/class-mbm-simple-widget-factory.php';
		
		require_once MBDB_PLUGIN_DIR . 'includes/class-mbdb-cpt.php';
		
		require_once MBDB_PLUGIN_DIR . 'includes/mooberry-dreams/software-licensing.php';		
		
		
		//require_once MBDB_PLUGIN_DIR . 'includes/class-mbm-rest-books-controller.php';
		
		require_once MBDB_PLUGIN_DIR . 'includes/plugin-functions.php';
		
		require_once MBDB_PLUGIN_DIR . 'includes/admin/class-mbm-import-process.php';
		require_once MBDB_PLUGIN_DIR . 'includes/admin/class-mbm-settings.php';
		require_once MBDB_PLUGIN_DIR . 'includes/admin/class-mbm-core-settings.php';
		
		
		
		//require_once MBDB_PLUGIN_DIR . 'includes/CMB2-grid/Cmb2GridPlugin.php';
		
		//require_once MBDB_PLUGIN_DIR . 'mooberry-book-manager-custom-fields.php';
		//require_once MBDB_PLUGIN_DIR . 'includes/custom-fields/custom-fields.php';
	}
	
	
	

} // class



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

if ( !isset($mbdb) ) {
	
	$mbdb = MBDB();
}



function mbdb_convert($size)
{
    $unit=array('b','kb','mb','gb','tb','pb');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
}




// for users with PHP <5.5
// (this can't be in a class)
if(!function_exists("array_column")) {
	
	function array_column($array, $column_name, $key = null) {
		if ( !is_array( $array ) ) {
			return null;
		}
		
		$new_array = array();
		foreach ( $array as $element ) {
			if ( array_key_exists( $column_name, $element ) ) {
				if ($key == null) {
						$new_array[] = $element[$column_name];
				} else {
					if ( array_key_exists( $key, $element ) ) {
						$new_array[$element[$key]] = $element[$column_name];
					}
				}		
			}
		}
		return $new_array;
	}	
}
/* add_filter('posts_where','mbdb_search_where' );
function mbdb_search_where ( $where ) {
	print_r($where);
	return $where;
} */


add_filter('wp_nav_menu_objects',  'remove_tax_grid_page_from_menu' , 99, 2);
 function remove_tax_grid_page_from_menu($sorted_menu_objects, $args) {
 
    // check for the right menu to remove the menu item from
    // here we check for theme location of 'secondary-menu'
    // alternatively you can check for menu name ($args->menu == 'menu_name')
    // if ($args->theme_location != 'secondary-menu')  
        // return $sorted_menu_objects;
	$page_id = MBDB()->options->tax_grid_page;
    // remove the menu item that has a title of 'Uncategorized'
    foreach ($sorted_menu_objects as $key => $menu_object) {

        // can also check for $menu_object->url for example
        // see all properties to test against:
         
        if ($menu_object->object_id == $page_id) {
            unset($sorted_menu_objects[$key]);
            break;
        }
    }

    return $sorted_menu_objects;
}

add_filter('wp_page_menu_args', 'remove_tax_grid_from_page_links');
function remove_tax_grid_from_page_links( $args ) {
	
	$page_id = MBDB()->options->tax_grid_page;
	
	if ( array_key_exists('exclude', $args) ) {
		$args['exclude'] = $args['exclude'] . ',' . $page_id;
	} else {
		$args['exclude'] = $page_id;
	}
	return $args;
}
	