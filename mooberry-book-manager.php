<?php
 /*
    Plugin Name: Mooberry Book Manager
    Plugin URI: http://www.mooberrydreams.com/products/mooberry-book-manager/
    Description: An easy-to-use system for authors to add books their Wordpress website
    Author: Mooberry Dreams
    Version: 1.0
    Author URI: http://www.mooberrydreams.com/
	
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
	update_option(MBDB_PLUGIN_VERSION_KEY, '1.0');

	
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
		add_options_page( 'Mooberry Book Manager Settings', 'Book Manager', 'manage_options', 'mbdb_settings', 'mbdb_settings_page');
	}
		
	function mbdb_settings_page() {
		include('admin-settings-page.php');
	}

	add_action( 'init', 'mbdb_init' );	
	function mbdb_init() {
		// create Book Post Type
		register_post_type('mbdb_book',
			apply_filters('mbdb_book_cpt', array(	
			'label' => 'Books',
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
				'name' => 'Books',
				'singular_name' => 'Book',
				'menu_name' => 'Books',
				'all_items' => 'All Books',
				'add_new' => 'Add New',
				'add_new_item' => 'Add New Book',
				'edit' => 'Edit',
				'edit_item' => 'Edit Book',
				'new_item' => 'New Book',
				'view' => 'View Book',
				'view_item' => 'View Book',
				'search_items' => 'Search Books',
				'not_found' => 'No Books Found',
				'not_found_in_trash' => 'No Books Found in Trash',
				'parent' => 'Parent Book'
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
					'name' => 'Genres',
					'singular_name' => 'Genre',
					'search_items' => 'Search Genres' ,
					'all_items' =>  'All Genres' ,
					'parent_item' =>  'Parent Genre' ,
					'parent_item_colon' =>  'Parent Genre:' ,
					'edit_item' =>  'Edit Genre' ,
					'update_item' =>  'Update Genre' ,
					'add_new_item' =>  'Add New Genre' ,
					'new_item_name' =>  'New Genre Name' ,
					'menu_name' =>  'Genres' ,
					'popular_items' => 'Popular Genres',
					'separate_items_with_commas' => 'Separate genres with commas',
					'add_or_remove_items' => 'Add or remove genres',
					'choose_from_most_used' => 'Choose from the most used genres',
					'not_found' => 'No genres found'
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
					'name' => 'Series',
					'singular_name' => 'Series',
					'search_items' => 'Search Series' ,
					'all_items' =>  'All Series' ,
					'parent_item' =>  'Parent Series' ,
					'parent_item_colon' =>  'Parent Series:' ,
					'edit_item' =>  'Edit Series' ,
					'update_item' =>  'Update Series' ,
					'add_new_item' =>  'Add New Series' ,
					'new_item_name' =>  'New Series Name' ,
					'menu_name' =>  'Series' ,
					'popular_items' => 'Popular Series',
					'separate_items_with_commas' => 'Separate series with commas',
					'add_or_remove_items' => 'Add or remove series',
					'choose_from_most_used' => 'Choose from the most used series',
					'not_found' => 'No Series found'
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
