<?php
 /**
  *  Plugin Name: Mooberry Book Manager
  *  Plugin URI: http://bookmanager.mooberrydreams.com/
  *  Description: An easy-to-use system for authors. Add your new book to your site in minutes, including links for purchase or download, sidebar widgets, and more. 
  *  Author: Mooberry Dreams
  *  Author URI: http://www.mooberrydreams.com/
  *  Donate Link: https://www.paypal.me/mooberrydreams/
  *	 Version: 3.5.4
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
  * @version 3.5.4
  */
  
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
 
 
// Plugin version
if ( ! defined( 'MBDB_PLUGIN_VERSION' ) ) {
	define( 'MBDB_PLUGIN_VERSION', '3.5.4' );
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
require_once MBDB_PLUGIN_DIR . 'includes/mooberry-dreams/software-licensing.php';

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
require_once MBDB_PLUGIN_DIR . 'mbdb-widget.php';
require_once MBDB_PLUGIN_DIR . 'book-widget2.php';
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

/**
 * Activation
 * 
 * Runs on plugin activation
 * - Creates custom tables
 * - Inserts default images
 * - Running the init() functions
 * - flushing the rewrite rules
 *
 * @since 1.0
 * @since 3.1 	Multi-site compatibility
 * @return void
 */
// NOTE: DO NOT change the name of this function because it is required for
// the add ons to check dependency


register_activation_hook(basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ), 'mbdb_activate'  );
function mbdb_activate( $networkwide ) {
	global $blog_id;
	global $wpdb;
	
	
	// create the table for the entire site if multisite
	

	if (function_exists('is_multisite') && is_multisite()) {
        // check if it is a network activation - if so, run the activation function for each blog id
        if ( $networkwide ) {
            $old_blog = $blog_id;
            // Get all blog ids
            $blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
			//$sites = wp_get_sites( array(  'limit' => 1000 ) );
			//error_log(print_r($blogids, true));
			
            foreach ($blogids as $blog) {
                switch_to_blog($blog);
                _mbdb_activate();
				
				if (!wp_is_large_network() ) {
					delete_blog_option( $blog, 'rewrite_rules' );
				}
            }
            switch_to_blog($old_blog);
			//mbdb_flush_rewrite_rules_multisite();
            return;
        }   
    } 
    _mbdb_activate();      
	flush_rewrite_rules();
}

function mbdb_flush_rewrite_rules_multisite() {
	// Much better...
	if ( wp_is_large_network() ) {
		return;
	}

	// ...and we're probably still friends.
	// 4.6 compatibility
	if (function_exists('get_sites')) {
		$sites = get_sites( array( 'limit' => 1000 ) );
	} else {
		$sites = wp_get_sites( array(  'limit' => 1000 ) );
	}
	
	foreach( $sites as $site ) {
		// 4.6 compatibility
		if (function_exists('get_sites')) {
			$blogID = $site->id;
		} else {
			$blogID = $site['blog_id'];
		}
		switch_to_blog( $blogID );
		delete_blog_option( $blogID, 'rewrite_rules' );
		restore_current_blog();
	}
}
// blog-specific activation tasks
// v3.1 split out into separate function for multisite compatibility
function _mbdb_activate() {
	
	MBDB()->books->create_table();
	
	// if this is a fresh 3.1 or higher install, no import necessary
	$current_version = get_option(MBDB_PLUGIN_VERSION_KEY);
	if ($current_version == '' || version_compare($current_version, '3.1', '>=') ) {
		update_option('mbdb_import_books', true);
	}
	
	mbdb_set_up_roles();

	// insert defaults
	
	$mbdb_options = get_option( 'mbdb_options' );
	
	if (!is_array($mbdb_options)) {
		$mbdb_options = array();
	}

	mbdb_insert_default_formats( $mbdb_options );
	mbdb_insert_default_edition_formats( $mbdb_options );
	mbdb_insert_default_social_media ( $mbdb_options );
	mbdb_insert_default_retailers( $mbdb_options );
	
	$path = MBDB_PLUGIN_URL . 'includes/assets/';
	
	$mbdb_options['coming-soon'] = $path . 'coming_soon_blue.jpg';
	$mbdb_options['goodreads'] = $path . 'goodreads.png';
	
	//mbdb_insert_image( 'coming-soon', 'coming_soon_blue.jpg', $mbdb_options );
	//mbdb_insert_image( 'goodreads', 'goodreads.png', $mbdb_options );
	
	
	update_option( 'mbdb_options', $mbdb_options );
	
	
	// SET DEFAULT OPTIONS FOR GRID SLUGS
	mbdb_set_default_tax_grid_slugs();
	
	
	mbdb_init();

	
}

// activate MBM for any new blogs added to multisite
// v3.1
add_action( 'wpmu_new_blog', 'mbdb_new_blog', 10, 6);        
function mbdb_new_blog($blog, $user_id, $domain, $path, $site_id, $meta ) {
	//wp_die('Network Activation Not Supported.');
	
	global $blog_id;

    if (is_plugin_active_for_network('mooberry-book-manager/mooberry-book-manager.php')) {
        $old_blog = $blog_id;
        switch_to_blog($blog);

        _mbdb_activate($blog);
		delete_blog_option( $blog, 'rewrite_rules' );
        switch_to_blog($old_blog);
    }
	
}

/**
 * Deactivation
 * 
 * Runs on plugin deactivation
 * - flushing the rewrite rules
 *
 * @since 1.0
 * @return void
 */
register_deactivation_hook( MBDB_PLUGIN_FILE, 'mbdb_deactivate' );
function mbdb_deactivate( $networkwide ) {
	global $blog_id;
	global $wpdb;
	
	if (function_exists('is_multisite') && is_multisite()) {
        // check if it is a network activation - if so, run the activation function for each blog id
        if ( $networkwide ) {
            $old_blog = $blog_id;
            // Get all blog ids
            $blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
            foreach ($blogids as $blog) {
				switch_to_blog($blog);
				delete_blog_option( $blog, 'rewrite_rules' );
				//flush_rewrite_rules();
			}
			 switch_to_blog($old_blog);
            return;
        }   
    } 
	flush_rewrite_rules();
}



