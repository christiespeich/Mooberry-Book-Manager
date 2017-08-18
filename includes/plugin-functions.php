<?php
		
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
	 * @since 4.0	Moved to class
	 *
	 * @return void
	 */
 register_activation_hook(MBDB_PLUGIN_FILE,  'mbdb_activate_plugin' );
 function mbdb_activate_plugin( $networkwide ) {
		
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
					mbdb_activate_single_site();
					
					if (!wp_is_large_network() ) {
						delete_blog_option( $blog, 'rewrite_rules' );
					}
				}
				switch_to_blog($old_blog);
				return;
			}   
		} 
		mbdb_activate_single_site();      
		//flush_rewrite_rules();
		global $wp_rewrite;
		$wp_rewrite->flush_rules();
	}
	
	// blog-specific activation tasks
	// v3.1 split out into separate function for multisite compatibility
	// @since 4.0	Moved to class
function mbdb_activate_single_site() {
		
		// register post types on activation so we can flush the rules
		//MBDB()->book_CPT->register();
		MBDB()->book_grid_CPT->register();
		//MBDB()->tax_grid_CPT->register();
		$book_CPT = new Mooberry_Book_Manager_Book_CPT();
		$book_CPT->register();
		
		
		//$book_grid_CPT = new Mooberry_Book_Manager_Book_Grid_CPT();
		//$book_grid_CPT->register();
		
		//$tax_grid_CPT = new Mooberry_Book_Manager_Tax_Grid_CPT();
		//$tax_grid_CPT->register();
		
		
		// creates table
		MBDB_DB_Books::activate();
		
		
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

		MBDB()->helper_functions->insert_default_formats( $mbdb_options );
		MBDB()->helper_functions->insert_default_edition_formats( $mbdb_options );
		MBDB()->helper_functions->insert_default_social_media ( $mbdb_options );
		MBDB()->helper_functions->insert_default_retailers( $mbdb_options );
		
		$path = MBDB_PLUGIN_URL . 'includes/assets/';
		
		$mbdb_options['coming-soon'] = $path . 'coming_soon_blue.jpg';
		$mbdb_options['goodreads'] = $path . 'goodreads.png';
		
		//mbdb_insert_image( 'coming-soon', 'coming_soon_blue.jpg', $mbdb_options );
		//mbdb_insert_image( 'goodreads', 'goodreads.png', $mbdb_options );
		
		$mbdb_options['override_wpseo'] = array_keys( MBDB()->helper_functions->override_wpseo_options() );
		
		
		// set tax grid page			
		if ( MBDB()->options->tax_grid_page == '' ) {
			$template = MBDB()->options->tax_grid_template;
			if ( $template == '' ) {
				$template = 'single.php';
			}
			MBDB()->helper_functions->insert_tax_grid_page ( $template );
		}
		$mbdb_options['mbdb_tax_grid_page'] = $tax_grid_id;
		
		update_option( 'mbdb_options', $mbdb_options );
		
		
		// SET DEFAULT OPTIONS FOR GRID SLUGS
		MBDB()->helper_functions->set_default_tax_grid_slugs();
		

	}
	
	// activate MBM for any new blogs added to multisite
	// v3.1
	// @since 4.0	Moved to class
	
add_action( 'wpmu_new_blog',  'mbdb_new_blog' , 10, 6 );
 function mbdb_new_blog( $blog, $user_id, $domain, $path, $site_id, $meta ) {
	//wp_die('Network Activation Not Supported.');
	
	global $blog_id;

	if ( is_plugin_active_for_network( 'mooberry-book-manager/mooberry-book-manager.php' ) ) {
		$old_blog = $blog_id;
		switch_to_blog( $blog );

		mbdb_activate_single_site();
		delete_blog_option( $blog, 'rewrite_rules' );
		switch_to_blog( $old_blog );
	}
}
	
	


/**
 * Deactivation
 * 
 * Runs on plugin deactivation
 * - flushing the rewrite rules
 *
 * @since 1.0
 * @since 4.0	Moved to class
 *
 * @return void
 */
 register_deactivation_hook( MBDB_PLUGIN_FILE, 'mbdb_deactivate' );
 function mbdb_deactivate( $networkwide ) {
	global $blog_id;
	global $wpdb;
	
	if ( function_exists( 'is_multisite' ) && is_multisite() ) {
		// check if it is a network activation - if so, run the activation function for each blog id
		if ( $networkwide ) {
			$old_blog = $blog_id;
			// Get all blog ids
			$blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
			foreach ( $blogids as $blog ) {
				switch_to_blog( $blog );
				
				delete_blog_option( $blog, 'rewrite_rules' );
			}
			 switch_to_blog( $old_blog );
			return;
		}   
	} 
	
	flush_rewrite_rules();
}



 function mbdb_set_up_roles() {
	$contributor_level = apply_filters('mbdb_contributor_level_capabilities', array(
			'edit_mbdb_books',
			'edit_mbdb_book',
			'edit_mbdb_book_grid',
			'edit_mbdb_book_grids',
			'delete_mbdb_books',
			'delete_mbdb_book',
			'delete_mbdb_book_grid',
			'delete_mbdb_book_grids',
			'manage_mbdb_books',
			'manage_mbdb_book_grids',
			'assign_genre_terms',
			'assign_tag_terms',
			'assign_cover_artist_terms',
			'assign_series_terms',
			'assign_illustrator_terms',
			'assign_editor_terms')
	);
	
	$base_level = apply_filters('mbdb_base_level_capabilities', array(		
				'publish_mbdb_books',
				'publish_mbdb_book',
				'publish_mbdb_book_grid',
				'publish_mbdb_book_grids',
				'edit_published_mbdb_book',
				'edit_published_mbdb_books',
				'edit_published_mbdb_book_grid',
				'edit_published_mbdb_book_grids',
				'delete_published_mbdb_book',
				'delete_published_mbdb_books',
				'delete_published_mbdb_book_grid',
				'delete_published_mbdb_book_grids',
				'upload_files',
				'manage_mbdb_books',
				'manage_mbdb_book_grids',
				'read',
			'manage_genre_terms',
			'manage_series_terms',
			'manage_tag_terms',
			'manage_cover_artist_terms',
			'manage_illustrator_terms',
			'manage_editor_terms')
	);
	
	$master_level = apply_filters('mbdb_master_level_capabilities', array(		
				'edit_others_mbdb_books',
				'edit_others_mbdb_book',
				'delete_others_mbdb_books',
				'delete_others_mbdb_book',
				'edit_others_mbdb_book_grids',
				'edit_others_mbdb_book_grid',
				'delete_others_mbdb_book_grids',
				'delete_others_mbdb_book_grid')
	);
	
	remove_role('mbdb_librarian');
	add_role('mbdb_librarian', 'MBM ' . __('Librarian','mooberry-book-manager'));
	remove_role('mbdb_master_librarian');
	add_role('mbdb_master_librarian', 'MBM' . __('Master Librarian','mooberry-book-manager'));
	$base_roles = array('mbdb_librarian', 'author');
	$master_roles = array('administrator', 'editor',  'mbdb_master_librarian');
	$contributor = get_role('contributor');
	foreach ($contributor_level as $capability) {
		$contributor->add_cap($capability);
	}
	foreach (array_merge($base_level, $contributor_level) as $capability) {
		foreach (array_merge($base_roles, $master_roles) as $each_role ) {
			$role = get_role($each_role);
			$role->add_cap($capability);
		}
	}
	foreach ($master_level as $capability) {
		foreach ($master_roles as $each_role) {
			$role = get_role($each_role);
			$role->add_cap($capability);
		}
	}
	
	$admin = get_role('administrator');
	$admin->add_cap('manage_mbm');

	
}


function mbdb_enqueue_admin_header_styles() {
		// options page styles
		$file = 'includes/admin/css/admin-options.css';
		wp_enqueue_style( 'mbdb-admin-options-styles', MBDB_PLUGIN_URL . $file, '', Mooberry_Book_Manager_Helper_Functions::get_enqueue_version( MBDB_PLUGIN_DIR .$file )  );
		
		// book grid styles
		$file = 'css/book-grid.css';
		wp_enqueue_style( 'mbdb-admin-book-grid-styles', MBDB_PLUGIN_URL . $file, '', Mooberry_Book_Manager_Helper_Functions::get_enqueue_version( MBDB_PLUGIN_DIR .$file )  );
		
		// theme for custom sort
		wp_enqueue_style('mbdb-jquery-ui-css', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css');
	}
	
/**
 * Enqueues the style sheet responsible for styling the contents of this
 * meta box.
 */
add_action( 'admin_enqueue_scripts', 'mbdb_enqueue_scripts', 99);
function mbdb_enqueue_scripts( $hook ) {
	global $post;
	
	mbdb_enqueue_admin_header_styles();
	
	// widgets javascript
	if ($hook == 'widgets.php') {
		$file = 'includes/admin/js/admin-widget.js';
		wp_enqueue_script( 'mbdb-admin-widget', MBDB_PLUGIN_URL . $file, '', Mooberry_Book_Manager_Helper_Functions::get_enqueue_version( MBDB_PLUGIN_DIR .$file ));		
	}
	
	// book edit javascript
	$file = 'includes/admin/js/admin-book-quick-bulk-edit.js';
	if ( isset( $post ) && $post->post_type == 'mbdb_book' ) {
		wp_enqueue_script('mbdb-admin-book-quick-bulk-edit', MBDB_PLUGIN_URL . $file, array( 'jquery', 'inline-edit-post' ), Mooberry_Book_Manager_Helper_Functions::get_enqueue_version( MBDB_PLUGIN_DIR .$file ) );
	}
	
			
	
	/* // load styles on all admin pages 
	$file = 'includes/admin/css/admin-styles.css';
	wp_enqueue_style( 'mbdb-admin-styles', MBDB_PLUGIN_URL . $file, '', Mooberry_Book_Manager_Helper_Functions::get_enqueue_version( MBDB_PLUGIN_DIR . $file )  );
	
	

	wp_enqueue_style('mbds-jquery-ui-css', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css'); */
		
}

add_action( 'admin_head', 'mbdb_register_admin_styles', 90);
function mbdb_register_admin_styles() {
	$file = 'includes/admin/css/admin-styles.css';
	wp_enqueue_style( 'mbdb-admin-styles', MBDB_PLUGIN_URL . $file, '', Mooberry_Book_Manager_Helper_Functions::get_enqueue_version( MBDB_PLUGIN_DIR . $file )  );
	
		$file = 'css/book-grid.css';
	wp_enqueue_style( 'mbdb-book-grid-styles', MBDB_PLUGIN_URL . $file , '', Mooberry_Book_Manager_Helper_Functions::get_enqueue_version( MBDB_PLUGIN_DIR . $file ) );
	
	wp_enqueue_style('mbds-jquery-ui-css', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css');
}



add_action( 'admin_footer', 'mbdb_register_footer_scripts' );
function mbdb_register_footer_scripts() {

	$current_screen = get_current_screen();
	if (!$current_screen) {
		return;
	}

	$parent_base = $current_screen->parent_base;
	$post_type = $current_screen->post_type;
	$base = $current_screen->base;

	if ($parent_base == 'edit' && $post_type == 'mbdb_book_grid' && $base == 'post') {
		
		// admin-book-grid
		$file = 'includes/admin/js/admin-book-grid.js';
		$group_by_options = MBDB()->book_grid_CPT->group_by_options();
		$text_to_translate = array(
							'label1' => __('Group Books Within', 'mooberry-book-manager'),
							'label2' => __('By', 'mooberry-book-manager'),
							'groupby' => $group_by_options,
							'custom_sort' => __('Custom', 'mooberry-book-manager') );
		wp_register_script( 'mbdb-admin-book-grid', MBDB_PLUGIN_URL . $file, array('jquery'), Mooberry_Book_Manager_Helper_Functions::get_enqueue_version(MBDB_PLUGIN_DIR .  $file ) ); 
		wp_localize_script( 'mbdb-admin-book-grid', 'text_to_translate', $text_to_translate );
		wp_localize_script( 'mbdb-admin-book-grid', 'book_grid_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'security' => wp_create_nonce( 'mbdb_book_grid_ajax_nonce' ) ) );

		wp_enqueue_script( 'mbdb-admin-book-grid' ); 
		// in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
		
		wp_enqueue_script('jquery-ui-sortable');
	}

		
	
	if ($post_type == 'mbdb_book' && $base == 'post') {
		// admin-book
		$file = 'includes/admin/js/admin-book.js';
		wp_register_script( 'mbdb-admin-book', MBDB_PLUGIN_URL . $file, '', Mooberry_Book_Manager_Helper_Functions::get_enqueue_version( MBDB_PLUGIN_DIR . $file ) );
		wp_localize_script( 'mbdb-admin-book', 'display_editions', apply_filters('mbdb_display_editions', 'no' ) );
		wp_enqueue_script( 'mbdb-admin-book');
		
	}
	
	if ($parent_base == 'mbdb_options') {
		// admin-settings
		$file = 'includes/admin/js/admin-options.js';
		wp_enqueue_script('mbdb-admin-options', MBDB_PLUGIN_URL . $file, '', Mooberry_Book_Manager_Helper_Functions::get_enqueue_version( MBDB_PLUGIN_DIR . $file ));
		
		wp_localize_script( 'mbdb-admin-options', 
							'mbdb_admin_options_ajax', 
							array( 
								'translation'	=>	__('Are you sure you want to reset the Book Edit page?', 'mooberry-book-manager'),
								'cancel_import_translation'	=>	__('Are you sure your want to cancel the book import?', 'mooberry-book-manager'),
								'create_tax_grid_page_fail_translation' => sprintf(__('Tax Grid Page creation failed. Manually create a page with  this shortcode in the content %s. Then come back here and choose that page from the drop down list.', 'mooberry-book-manager'), '<b>[mbdb_tax_grid]</b>'),
								'create_tax_grid_page_success_translation'	=>	__('Tax Grid Page created.', 'mooberry-book-manager'),
								'ajax_url' => admin_url( 'admin-ajax.php' ),
								'ajax_nonce' => wp_create_nonce('mbdb_admin_options_ajax_nonce'),
								'ajax_cancel_import_nonce'	=>	wp_create_nonce( 'mbdb_admin_options_cancel_import_nonce'),
								'ajax_create_tax_grid_page_nonce'	=>	wp_create_nonce( 'mbdb_admin_options_create_tax_grid_page_nonce'),
							) 
						);
						
		$file = 'includes/admin/js/admin-import-export.js';
		wp_register_script('mbdb-admin-import-export', MBDB_PLUGIN_URL . $file, '', Mooberry_Book_Manager_Helper_Functions::get_enqueue_version( MBDB_PLUGIN_DIR . $file )  );
		wp_localize_script( 'mbdb-admin-import-export', 
							'mbdb_admin_options_import_export_ajax', 
							array( 
								'ajax_url' => admin_url( 'admin-ajax.php' ), 
								'export_nonce' => wp_create_nonce( 'mbdb_export_nonce' ),
								'import_nonce' => wp_create_nonce( 'mbdb_import_nonce' )	
							) 
						);
		wp_enqueue_script( 'mbdb-admin-import-export');
	}
	
	// show on all admin pages
	$file = 'includes/admin/js/admin.js';
	wp_enqueue_script('mbdb-admin-ajax',
							MBDB_PLUGIN_URL . $file,
							array('jquery'),
							Mooberry_Book_Manager_Helper_Functions::get_enqueue_version( MBDB_PLUGIN_DIR . $file ) );
						
	wp_localize_script( 'mbdb-admin-ajax', 
						'mbdb_admin_notice_ajax', 
						array( 
							'ajax_url' => admin_url( 'admin-ajax.php' ),
							'dismiss_ajax_nonce' => wp_create_nonce('mbdb_admin_notice_dismiss_ajax_nonce'),
							'remigrate_ajax_nonce' => wp_create_nonce('mbdb_admin_notice_3_1_remigrate_ajax_nonce'),
							'book_grid_placeholder_dismiss_nonce' => wp_create_nonce('mbdb_book_grid_placeholder_dismiss_ajax_nonce'),
							'redirect_url' => admin_url('admin.php?page=mbdb_migrate')
						) 
					);
}
	
	
add_action( 'wp_enqueue_media', 'mbdb_insert_shortcode_button' );
function mbdb_insert_shortcode_button() {
		$button_label = array(
				'add_button' => __('Insert Shortcode', 'mooberry-book-manager'),
				'cancel_button'	=> __('Cancel', 'mooberry-book-manager'),
				);
		wp_enqueue_script('jquery-ui-dialog');
		$file = 'includes/admin/js/media-buttons.js';
		wp_enqueue_script('mbdb-media-button', MBDB_PLUGIN_URL . $file, array('jquery'), Mooberry_Book_Manager_Helper_Functions::get_enqueue_version( MBDB_PLUGIN_DIR . $file ), true);
		wp_localize_script( 'mbdb-media-button', 'button_label', $button_label );	
		wp_enqueue_style('mbds-jquery-ui-css-dialog', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css');
}


add_action( 'wp_ajax_mbdb_admin_notice_dismiss', 'mbdb_admin_notice_dismiss' );
function mbdb_admin_notice_dismiss() {
		check_ajax_referer( 'mbdb_admin_notice_dismiss_ajax_nonce', 'security' );
		$key = $_POST['admin_notice_key'];
		MBDB()->helper_functions->remove_admin_notice($key);
		wp_die();
}



add_action( 'wp_enqueue_scripts', 'mbdb_enqueue_styles' );
function mbdb_enqueue_styles() {
	$file = 'css/styles.css';
	wp_enqueue_style( 'mbdb-styles', MBDB_PLUGIN_URL . $file, '', Mooberry_Book_Manager_Helper_Functions::get_enqueue_version( MBDB_PLUGIN_DIR . $file )  ) ;
					
	$file = 'css/book-grid.css';
	wp_enqueue_style( 'mbdb-book-grid-styles', MBDB_PLUGIN_URL . $file , '', Mooberry_Book_Manager_Helper_Functions::get_enqueue_version( MBDB_PLUGIN_DIR . $file ) );

	$file = 'js/single-book.js';
	wp_enqueue_script('single-book', MBDB_PLUGIN_URL . $file, array('jquery'), Mooberry_Book_Manager_Helper_Functions::get_enqueue_version( MBDB_PLUGIN_DIR . $file ) );
}



add_action( 'widgets_init', 'register_widgets');	
function register_widgets() {
	return register_widget( 'mbdb_book_widget2' );
	//global  $wp_widget_factory;
	//print_r('widget_facotry = ' . $wp_widget_factory);
	//MBDB()->widget_factory = $wp_widget_factory;
			
	//if ( MBDB()->widget_factory == null ) {
//		return register_widget( 'mbdb_book_widget2' );
	//} else {
		// print_r('registering simple widgets');
		// print_r(MBDB()->widget_factory);
	//	return MBDB()->widget_factory->register( 'mbdb_book_widget2' );
//	}
}
		

