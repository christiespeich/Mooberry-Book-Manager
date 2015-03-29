<?php
 /*
    Plugin Name: Mooberry Book Manager
    Plugin URI: http://www.mooberrydreams.com/products/mooberry-book-manager/
    Description: An easy-to-use system for authors to add books their Wordpress website
    Author: Mooberry Dreams
    Version: 1.1
    Author URI: http://www.mooberrydreams.com/
	Text Domain: mooberry-book-manager
	
	Copyright 2015  Mooberry Dreams  (email : bookmanager@mooberrydreams.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
    */

	define('MBDB_PLUGIN_DIR', plugin_dir_path( __FILE__ )); 
	define('MBDB_PLUGIN_VERSION_KEY', 'mbdb_version');
	update_option(MBDB_PLUGIN_VERSION_KEY, '1.1');

	
	
	// Load in CMB2
	if ( file_exists( dirname( __FILE__ ) . '/includes/cmb2/init.php' ) ) {
		require_once dirname( __FILE__ ) . '/includes/cmb2/init.php';
	} elseif ( file_exists( dirname( __FILE__ ) . '/includes/CMB2/init.php' ) ) {
		require_once dirname( __FILE__ ) . '/includes/CMB2/init.php';
	}
	
	
	
	// add in additional files
	require_once dirname( __FILE__ ) . '/includes/helper-functions.php';
	require_once dirname( __FILE__ ) . '/book.php';
	require_once dirname( __FILE__ ) . '/single-book.php';
	require_once dirname( __FILE__ ) . '/book-widget.php';
	require_once dirname( __FILE__ ) . '/book-grid.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	add_action( 'plugins_loaded', 'mbdb_load_plugin_textdomain' );
	function mbdb_load_plugin_textdomain() {
		load_plugin_textdomain( 'mooberry-book-manager', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
	}
	
	
	
	// NOTE: DO NOT change the name of this function because it is required for
	// the add ons to check dependency
	register_activation_hook( __FILE__, 'mbdb_activate' );
	function mbdb_activate() {
		
		$mbdb_options = get_option( 'mbdb_options' );
		
		// check if default retailers and formats exist in database and add them if necessary
		$default_retailers = array();
		$default_retailers[] = array('name' => 'Amazon', 'uniqueID' => 1, 'image' => 'amazon.jpg');
		$default_retailers[] = array('name' => 'Barnes and Noble', 'uniqueID' => 2, 'image' => 'bn.jpg');
		$default_retailers[] = array('name' => 'Kobo', 'uniqueID' => 3, 'image' => 'kobo.png');
		$default_retailers[] = array('name' => 'iBooks', 'uniqueID' => 4, 'image' => 'ibooks.png');
		$default_retailers[] = array('name' => 'Smashwords', 'uniqueID' => 5, 'image' => 'smashwords.png');
		$default_retailers = apply_filters('mbdb_default_retailers', $default_retailers);
		
		$default_formats = array();
		$default_formats[] = array('name' => 'ePub', 'uniqueID' => 1, 'image' => 'epub.gif');
		$default_formats[] = array('name' => 'Kindle', 'uniqueID' => 2, 'image' => 'kindle.jpg');
		$default_formats[] = array('name' => 'PDF', 'uniqueID' => 3, 'image' => 'pdficon.png');
		$default_formats = apply_filters('mbdb_default_formats', $default_formats);
		
		mbdb_insert_defaults( $default_retailers, 'retailers', $mbdb_options);
		mbdb_insert_defaults( $default_formats, 'formats', $mbdb_options);
		
		
		// check if the coming soon image exists and add it if necessary
		if (!array_key_exists('coming-soon', $mbdb_options)) {
			$attachID = mbdb_upload_image('coming_soon_blue.jpg');
			$img = wp_get_attachment_url( $attachID );
			$mbdb_options['coming-soon'] = $img;
			$mbdb_options['coming-soon-id'] = $attachID;
		}
			
		// check if goodreads image exists and add it if necessary
		if (!array_key_exists('goodreads', $mbdb_options)) {
			$attachID = mbdb_upload_image('goodreads.png');
			$img = wp_get_attachment_url( $attachID );
			$mbdb_options['goodreads'] = $img;
			$mbdb_options['goodreads-id'] = $attachID;
		}
		
		// check if default book page exists and add it if necessary
		// $mbdb_book_page_options = get_option('mbdb_book_page_options');	
		// if (!$mbdb_book_page_options || !array_key_exists('_mbdb_book_page_layout', $mbdb_book_page_options)) {
			// $content = mbdb_get_default_page_layout();
			// update_option('mbdb_book_page_options', array('_mbdb_book_page_layout' => $content));
		// }

		update_option( 'mbdb_options', apply_filters('mbdb_options', $mbdb_options));

		// add test tax grid if necessary
		// this is never seen and just needed for the series/tag/genre shortcut URLS (hack)
		// wp_count_posts doesn't work here because the custom post type hasn't been created yet
		// and the 1st thing wp_count_posts does is check if it exists
		$tax_grids = get_posts( apply_filters('mbdb_tax_grids', array(
					'posts_per_page' => -1,
					'post_type' => 'mbdb_tax_grid',
					'post_status' => 'publish' 
					))
				);
		// if there's more than one already in the database, delete them all but one
		if ( count( $tax_grids > 1 ) ) {
			for ( $x=1; $x<count($tax_grids); $x++ ) {
				wp_delete_post( $tax_grids[$x]->ID, true );
			}
		}
		// if there aren't any, add one
		if ( count( $tax_grids ) == 0 ) { 
					$new_post_id = wp_insert_post( apply_filters('mbdb_insert_tax_grid', array(
							'post_title' => 'Test',
							'post_type' => 'mbdb_tax_grid',
							'post_status' => 'publish',
							'name' => 'test',
							'comment_status' => 'closed'
							) )
					);
		} 
		
		mbdb_init();
	
		global $wp_rewrite;
		$wp_rewrite->flush_rules();
	}
	
	register_deactivation_hook( __FILE__, 'mbdb_deactivate' );
	function mbdb_deactivate() {
		global $wp_rewrite;
		$wp_rewrite->flush_rules();
	}
	
	add_action( 'admin_head', 'mbdb_register_admin_styles' );	 
	function mbdb_register_admin_styles() {
		wp_register_style( 'mbdb-admin-styles', plugins_url( 'css/admin-styles.css', __FILE__)  );
		wp_enqueue_style( 'mbdb-admin-styles' );
	}
	
	add_action( 'wp_enqueue_scripts', 'mbdb_register_styles' );
	function mbdb_register_styles() {
		wp_register_style( 'mbdb-styles', plugins_url( 'css/styles.css', __FILE__) ) ;
		wp_enqueue_style( 'mbdb-styles' );
	}

	add_action( 'admin_footer', 'mbdb_register_script');
	function mbdb_register_script() {
		wp_enqueue_script( 'admin-book-grid',  plugins_url( 'includes/js/admin-book-grid.js', __FILE__));
		wp_enqueue_script( 'admin-widget',  plugins_url( 'includes/js/admin-widget.js', __FILE__));		
	}

	add_action('widgets_init', 'mbdb_register_widgets');
	function mbdb_register_widgets() {
		return register_widget('mbdb_book_widget');
	}
	
	
	// Set up redirects to series/{series-name} based on query vars
	// same for genres and tags
	// this is so the book grid can be displayed instead of 
	// using a template file that is reliant on theme
	add_action('generate_rewrite_rules',  'mbdb_rewrite_rules');
	function mbdb_rewrite_rules( $rules ) {
		global $wp_rewrite;
		$new_rules['series/([^/]*)/?$'] =  'mbdb_tax_grid/?x=x&the-taxonomy=mbdb_series&the-term=$matches[1]&post_type=mbdb_tax_grid';
		$new_rules['genre/([^/]*)/?$'] =  'mbdb_tax_grid/?x=x&the-taxonomy=mbdb_genre&the-term=$matches[1]&post_type=mbdb_tax_grid';
		$new_rules['tag/([^/]*)/?$'] =  'mbdb_tax_grid/?x=x&the-taxonomy=post_tag&the-term=$matches[1]&post_type=mbdb_tax_grid';
		
		$wp_rewrite->rules = $new_rules + $wp_rewrite->rules;		
	}

	// Add query vars to be used for the redirection for series, genres, and tags
	add_filter('query_vars', 'mbdb_add_query_vars');
	function mbdb_add_query_vars($query_vars) {
		$query_vars[] = "the-term"; 
		$query_vars[] = "the-taxonomy";
		return $query_vars;
	}

	add_action( 'admin_menu', 'mbdb_settings_menu');
	function mbdb_settings_menu() {
		add_options_page( 'Mooberry Book Manager ' . __('Settings', 'mooberry-book-manager'), __('Book Manager', 'mooberry-book-manager'), 'manage_options', 'mbdb_settings', 'mbdb_settings_page');
	}
		
	function mbdb_settings_page() {
		include('admin-settings-page.php');
	}

	add_action( 'init', 'mbdb_init' );	
	function mbdb_init() {
		// create Book Post Type
		register_post_type('mbdb_book',
			apply_filters('mbdb_book_cpt', array(	
			'label' => _x('Books', 'noun', 'mooberry-book-manager'),
			'public' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'menu_icon' => 'dashicons-book-alt',
			'menu_position' => 20,
			'show_in_nav_menus' => true,
			'has_archive' => false,
			'capability_type' => 'post',
			'hierarchical' => false,
			'rewrite' => array( 'slug' => 'book' ),
			'query_var' => true,
			'supports' => array( 'title' ),
			'taxonomies' => array( 'post_tag', 'mbdb_genre', 'mbdb_series' ),
			'labels' => array (
				'name' => _x('Books', 'noun', 'mooberry-book-manager'),
				'singular_name' => _x('Book', 'noun', 'mooberry-book-manager'),
				'menu_name' => _x('Books', 'noun', 'mooberry-book-manager'),
				'all_items' => __('All Books', 'mooberry-book-manager'),
				'add_new' => __('Add New', 'mooberry-book-manager'),
				'add_new_item' => __('Add New Book', 'mooberry-book-manager'),
				'edit' => __('Edit', 'mooberry-book-manager'),
				'edit_item' => __('Edit Book', 'mooberry-book-manager'),
				'new_item' => __('New Book', 'mooberry-book-manager'),
				'view' => __('View Book', 'mooberry-book-manager'),
				'view_item' => __('View Book', 'mooberry-book-manager'),
				'search_items' => __('Search Books', 'mooberry-book-manager'),
				'not_found' => __('No Books Found', 'mooberry-book-manager'),
				'not_found_in_trash' => __('No Books Found in Trash', 'mooberry-book-manager'),
				'parent' => __('Parent Book', 'mooberry-book-manager')
				),
			) )
		);
		
		register_post_type('mbdb_tax_grid',
			apply_filters('mbdb_tax_grid_cpt', array(	
				'label' => 'Tax Grid',
				'public' => true,
				'show_in_menu' => false,
				'show_ui' => false,
				'exclude_from_search' => true,
				'publicly_queryable' => true,
				'show_in_nav_menus' => false,
				'has_archive' => false,
				'capability_type' => 'post',
				'hierarchical' => false,
				'rewrite' => array( 'slug' => 'mbdb_tax_grid' ),
				'query_var' => true,
				'supports' => array( 'title' ),
				) 
			)
		);
		
		register_taxonomy('mbdb_genre', 'mbdb_book', 
			apply_filters('mdbd_genre_taxonomy', array(
				'rewrite' => array(	'slug' => 'mbdb_genres' ),
				'show_admin_column' => true,
				'update_count_callback' => '_update_post_term_count',
				'labels' => array(
					'name' => __('Genres', 'mooberry-book-manager'),
					'singular_name' => __('Genre', 'mooberry-book-manager'),
					'search_items' => __('Search Genres' , 'mooberry-book-manager'),
					'all_items' =>  __('All Genres' , 'mooberry-book-manager'),
					'parent_item' =>  __('Parent Genre' , 'mooberry-book-manager'),
					'parent_item_colon' =>  __('Parent Genre:' , 'mooberry-book-manager'),
					'edit_item' =>  __('Edit Genre' , 'mooberry-book-manager'),
					'update_item' =>  __('Update Genre' , 'mooberry-book-manager'),
					'add_new_item' =>  __('Add New Genre' , 'mooberry-book-manager'),
					'new_item_name' =>  __('New Genre Name' , 'mooberry-book-manager'),
					'menu_name' =>  __('Genres' , 'mooberry-book-manager'),
					'popular_items' => __('Popular Genres', 'mooberry-book-manager'),
					'separate_items_with_commas' => __('Separate genres with commas', 'mooberry-book-manager'),
					'add_or_remove_items' => __('Add or remove genres', 'mooberry-book-manager'),
					'choose_from_most_used' => __('Choose from the most used genres', 'mooberry-book-manager'),
					'not_found' => __('No genres found', 'mooberry-book-manager')
				)
			)
			)
		);

		register_taxonomy('mbdb_series', 'mbdb_book', 
			apply_filters('mbdb_series_taxonomy', array( 
				'rewrite' => array( 'slug' => 'mbdb_series' ),
				'show_admin_column' => true,
				'update_count_callback' => '_update_post_term_count',
				'labels' => array(
					'name' => __('Series', 'mooberry-book-manager'),
					'singular_name' => __('Series', 'mooberry-book-manager'),
					'search_items' => __('Search Series' , 'mooberry-book-manager'),
					'all_items' =>  __('All Series' , 'mooberry-book-manager'),
					'parent_item' =>  __('Parent Series' , 'mooberry-book-manager'),
					'parent_item_colon' =>  __('Parent Series:' , 'mooberry-book-manager'),
					'edit_item' =>  __('Edit Series' , 'mooberry-book-manager'),
					'update_item' =>  __('Update Series' , 'mooberry-book-manager'),
					'add_new_item' =>  __('Add New Series' , 'mooberry-book-manager'),
					'new_item_name' =>  __('New Series Name' , 'mooberry-book-manager'),
					'menu_name' =>  __('Series' , 'mooberry-book-manager'),
					'popular_items' => __('Popular Series', 'mooberry-book-manager'),
					'separate_items_with_commas' => __('Separate series with commas', 'mooberry-book-manager'),
					'add_or_remove_items' => __('Add or remove series', 'mooberry-book-manager'),
					'choose_from_most_used' => __('Choose from the most used series', 'mooberry-book-manager'),
					'not_found' => __('No Series found', 'mooberry-book-manager')
				)
			))
		);
	}

	add_filter('the_content', 'mbdb_content');
	function mbdb_content($content) {
		global $wp_query;

		// make sure it's the post type 'book'
		if (get_post_type() == 'mbdb_book' && is_main_query() && !is_admin()) {
			$content .= mbdb_book_content($content);
		}
		
		if (get_post_type() == 'page' && is_main_query() && !is_admin()) {
			$content .= mbdb_bookgrid_content();
		}
		
		// if on a tax grid and there's query vars set, display the special grid
		if ( get_post_type() == 'mbdb_tax_grid' && is_main_query() && !is_admin() ) {
			if ( isset($wp_query->query_vars['the-term'] ) ) {
				$mbdb_series = trim( urldecode( $wp_query->query_vars['the-term'] ), '/' );
				if ( isset( $wp_query->query_vars['the-taxonomy'] ) ) {
					$taxonomy = trim( urldecode( $wp_query->query_vars['the-taxonomy'] ), '/' );
					$mbdb_books = mbdb_get_books_in_taxonomy( $mbdb_series, $taxonomy );
				
					$content = mbdb_display_grid( array( $mbdb_books ), 200, 3, 0 );
				}
			} 
		}
		return apply_filters('mbdb_content', $content);
	}
	
add_action('admin_notices', 'mbdb_admin_notice',0);
function mbdb_admin_notice(){
    //print the message
    global $post;
    $notice = get_option('mbdb_notice');
    if (empty($notice)) return '';
    foreach($notice as $pid => $m){
        if ($post->ID == $pid ){
            echo apply_filters('mbdb_admin_notice', '<div id="message" class="error"><p>'.$m.'</p></div>');
            //make sure to remove notice after its displayed so its only displayed when needed.
            unset($notice[$pid]);
            update_option('mbdb_notice',$notice);
            break;
        }
    }
}
