<?php

add_action( 'admin_enqueue_scripts', 'mbdb_register_admin_styles', 5 );	 
function mbdb_register_admin_styles() {
	wp_register_style( 'mbdb-admin-styles', MBDB_PLUGIN_URL .  'css/admin-styles.css', '', mbdb_get_enqueue_version()  );
	wp_enqueue_style( 'mbdb-admin-styles' );
	
	wp_register_style( 'mbdb-admin-book-grid-styles', MBDB_PLUGIN_URL .  'css/book-grid.css', '', mbdb_get_enqueue_version()  );
	wp_enqueue_style( 'mbdb-admin-book-grid-styles' );
	
	wp_enqueue_style('mbds-jquery-ui-css', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css');
}

add_action( 'admin_enqueue_scripts', 'mbdb_register_widget_script');
function mbdb_register_widget_script( $page_hook ) {

	if ($page_hook == 'widgets.php') {
		wp_enqueue_script( 'mbdb-admin-widget',  MBDB_PLUGIN_URL . 'includes/js/admin-widget.js', '', mbdb_get_enqueue_version());		
		
	}
	
	$current_screen = get_current_screen();
	if (!$current_screen) {
		return;
	}
	$post_type = $current_screen->post_type;
	$base = $current_screen->base;
	
	if ($base == 'edit' && $post_type == 'mbdb_book') {
		
		
		wp_enqueue_script('mbdb-admin-book-quick-bulk-edit', MBDB_PLUGIN_URL . 'includes/js/admin-book-quick-bulk-edit.js', array( 'jquery', 'inline-edit-post' ), mbdb_get_enqueue_version());
	}
}


add_action( 'admin_footer', 'mbdb_register_script');
function mbdb_register_script() {
	
	$current_screen = get_current_screen();
	if (!$current_screen) {
		return;
	}
	
	$parent_base = $current_screen->parent_base;
	$post_type = $current_screen->post_type;
	$base = $current_screen->base;
	
	if ($parent_base == 'edit' && $post_type == 'mbdb_book_grid' && $base == 'post') {
		// admin-book-grid
		$group_by_options = mbdb_book_grid_group_by_options();
		$text_to_translate = array(
							'label1' => __('Group Books Within', 'mooberry-book-manager'),
							'label2' => __('By', 'mooberry-book-manager'),
							'groupby' => $group_by_options,
							'custom_sort' => __('Custom', 'mooberry-book-manager') );
		wp_register_script( 'mbdb-admin-book-grid', MBDB_PLUGIN_URL .  'includes/js/admin-book-grid.js', array('jquery'), mbdb_get_enqueue_version()); 
		wp_localize_script( 'mbdb-admin-book-grid', 'text_to_translate', $text_to_translate );
		wp_localize_script( 'mbdb-admin-book-grid', 'book_grid_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'security' => wp_create_nonce( 'mbdb_book_grid_ajax_nonce' ) ) );
	
		wp_enqueue_script( 'mbdb-admin-book-grid' ); 
		// in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
		
		wp_enqueue_script('jquery-ui-sortable');
	}

	
	
	if ($post_type == 'mbdb_book' && $base == 'post') {
		// admin-book
		wp_register_script( 'mbdb-admin-book', MBDB_PLUGIN_URL .   'includes/js/admin-book.js', '', mbdb_get_enqueue_version());
		wp_localize_script( 'mbdb-admin-book', 'display_editions', 'no');
		wp_enqueue_script( 'mbdb-admin-book');
		
	}
	
	if ($parent_base == 'mbdb_options') {
		// admin-settings
		wp_enqueue_script('mbdb-admin-options', MBDB_PLUGIN_URL . 'includes/js/admin-options.js', '', mbdb_get_enqueue_version());
		
		wp_localize_script( 'mbdb-admin-options', 
							'mbdb_admin_options_ajax', 
							array( 
								'translation'	=>	__('Are you sure you want to reset the Book Edit page?', 'mooberry-book-manager'),
								'ajax_url' => admin_url( 'admin-ajax.php' ),
								'ajax_nonce' => wp_create_nonce('mbdb_admin_options_ajax_nonce'),
							) 
						);
	}
	
	// show on all admin pages
	wp_enqueue_script('mbdb-admin-ajax',
							plugins_url('js/admin.js', __FILE__),
							array('jquery'),
							mbdb_get_enqueue_version());
						
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

// woocommerce
// add_filter('woocommerce_screen_ids', 'mbdb_woocommerce_screens');
// function mbdb_woocommerce_screens( $screens) {
	// $screens[] = 'edit-mbdb_book';
	// $screens[] = 'mbdb_book';
	// return $screens;
// }

add_action( 'wp_enqueue_scripts', 'mbdb_register_styles', 15 );
function mbdb_register_styles() {
	wp_register_style( 'mbdb-styles', MBDB_PLUGIN_URL .  'css/styles.css', '', mbdb_get_enqueue_version() ) ;
	wp_enqueue_style( 'mbdb-styles' );
	
	wp_register_style( 'mbdb-book-grid-styles', MBDB_PLUGIN_URL .  'css/book-grid.css', '', mbdb_get_enqueue_version()  );
	wp_enqueue_style( 'mbdb-book-grid-styles' );
	
	wp_enqueue_script('single-book', MBDB_PLUGIN_URL . 'includes/js/single-book.js', array('jquery'), mbdb_get_enqueue_version());
	
	
}

add_action('wp_enqueue_media', 'mbdb_include_media_button');
function mbdb_include_media_button() {
	$button_label = array(
			'add_button' => __('Insert Shortcode', 'mooberry-book-manager'),
			'cancel_button'	=> __('Cancel', 'mooberry-book-manager'),
			);
	wp_enqueue_script('jquery-ui-dialog');
    wp_enqueue_script('mbdb-media-button', MBDB_PLUGIN_URL . 'includes/js/media-buttons.js', array('jquery'), mbdb_get_enqueue_version(), true);
	wp_localize_script( 'mbdb-media-button', 'button_label', $button_label );	
	wp_enqueue_style('mbds-jquery-ui-css-dialog', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css');
}


//add_action('wp_head', 'mbdb_grid_styles');
function mbdb_grid_styles() {
	global $post;
	if ($post) {
		$grid = get_post_meta($post->ID, '_mbdb_book_grid_display', true);
		if ( (get_post_type() == 'mbdb_tax_grid' || $grid == 'yes') && is_main_query() && !is_admin() ) {
			$mbdb_book_grid_cover_height = mbdb_get_grid_cover_height($post->ID);
			include MBDB_PLUGIN_DIR . 'css/grid-styles.php';
		}
	}
}

// add post class so it looks good on the theme
add_filter( 'post_class', 'mbdb_add_post_class' );
function mbdb_add_post_class( $classes ) {
	return mbdb_add_post_class_cpt( $classes, 'mbdb_book' );
}

function mbdb_add_post_class_cpt( $classes, $type ) {
	if (get_post_type()==$type) {
		if (!in_array('post', $classes)) {
			$classes[] = 'post';
		}
	}
	return $classes;
}