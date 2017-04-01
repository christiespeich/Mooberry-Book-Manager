<?php

// This file is a collection of depreciated functions that are required
// for backwards-compatibility to Mooberry Book Manager versions below 4.0
// as well as extensions Multi-Author, Additional Images, Advanced Widgets, and Retail Links Redirect

// This function is required for backwards compatibility with the extensions
// that check for this function to exist to determine if Mooberry Book Manager is
// installed.  This function is no longer used in version 4.0 because the activation
// function is now inside a class
// So this function doesn't have to do anything, it just has to exist
//function mbdb_activate() {
	
//}

// if in development, return the time so that it forces a reload
// otherwise return the current plugin version so a reload is only forced if it's an update
function mbdb_get_enqueue_version() {
	if ( WP_DEBUG ) {
		return time();
	} else {
		return MBDB_PLUGIN_VERSION;
	}
}

function mbdb_format_date( $field ) {
	return MBDB()->helper_functions->format_date( $field );
}

// used by Additional Images
function mbdb_get_cover( $image_src, $context ) {
	// get placeholder if necessary
	if ( !$image_src || $image_src == '' ) {
		if ( MBDB()->options->show_placeholder( $context ) ) {
			$image_src =  MBDB()->options->placeholder_image;
		}
	}

	if ( !$image_src || $image_src == '' ) {
		return '';
	} else {
		if (is_ssl()) {
			$image_src = preg_replace('/^http:/', 'https:', $image_src);
		}
		return $image_src;
	}
}

// used by Multi Author
function mbdb_get_metabox_field_position($metabox, $fieldname) {
	
	return MBDB()->helper_functions->get_metabox_field_position($metabox, $fieldname);

}

function mbdb_url_validation_pattern() {
	return MBDB()->helper_functions->url_validation_pattern();
}

function mbdb_dropdown($dropdownID, $options, $selected = null, $include_empty = 'yes', $empty_value = -1, $name = '' ) {
	return MBDB()->helper_functions->make_dropdown( $dropdownID, $options, $selected, $include_empty, $empty_value, $name );
}

function mbdb_set_admin_notice($message, $type, $key) {
	return MBDB()->helper_functions->set_admin_notice( $message, $type, $key );
}


// functions to handle version 3's migration notices
add_action( 'admin_notices', 'mbdb_admin_import_notice' , 0 );
function mbdb_admin_import_notice() {

	// original 3.0 upgrade migration notice
	$current_version = get_option(MBDB_PLUGIN_VERSION_KEY);
	if (version_compare($current_version, '2.4.4', '>') && version_compare($current_version, '3.1', '<')) {
		$import_books = get_option('mbdb_import_books');
		if (!$import_books || $import_books == null) {
			// only need to migrate if there are books
			$args = array('posts_per_page' => -1,
						'post_type' => 'mbdb_book',
			);
			
			$posts = get_posts( $args  );
			
			if (count($posts) > 0) {
				
				$m = __('Upgrading to Mooberry Book Manager version 3.0 requires some data migration before Mooberry Book Manager will operate properly.', 'mooberry-book-manager');
				$m2 = __('Migrate Data Now', 'mooberry-book-manager');
				echo '<div id="message" class="error"><p>' . $m . '</p><p><a href="admin.php?page=mbdb_migrate" class="button">' . $m2 . '</a></p></div>';
			} else {
				update_option('mbdb_import_books', true);
			}
			wp_reset_postdata();
		}
	}		
	
	// v3.1 added amdin notice option
	/*$notices  = get_option('mbdb_admin_notices');
	if (is_array($notices)) {
		foreach ($notices as $key => $notice) {
		  echo "<div class='notice {$notice['type']}' id='{$key}'><p>{$notice['message']}</p></div>";
		}
	}*/	
}
	
add_action( 'wp_ajax_mbdb_admin_3_1_remigrate', 'mbdb_admin_3_1_remigrate'  );
function mbdb_admin_3_1_remigrate() {
	check_ajax_referer( 'mbdb_admin_notice_3_1_remigrate_ajax_nonce', 'security' );

	update_option('mbdb_import_books', false);
	MBDB()->helper_functions->remove_admin_notice( '3_1_remigrate');

	//	wp_redirect(admin_url('admin.php?page=mbdb_migrate'));
	//exit;
	wp_die();
}		

// used in update script
function mbdb_uniqueID_generator( $value ) {
	if ($value=='') {
		$value =  uniqid();
	}
	return $value;
}

function mbdb_remove_admin_notice($key) {
	MBDB()->helper_functions->remove_admin_notice( $key );
}

function mbdb_get_template_list() {
	return MBDB()->helper_functions->get_template_list();
}

function mbdb_get_units_array() {
	return MBDB()->options->units;
}

function mbdb_get_default_unit( $mbdb_options = null ) {
	return MBDB()->options->default_unit;
}

function mbdb_get_currency_array() {
	return MBDB()->options->currencies;
}

function mbdb_get_default_currency( $mbdb_options = null) {
	return MBDB()->options->default_currency;
}

function mbdb_get_language_array() { 
	return MBDB()->options->languages;
}

function mbdb_get_default_language( $mbdb_options = null) {
	return MBDB()->options->default_language;
}

// called by Advanced Widgets
function mbdb_tax_grid_objects() {
	$taxonomies = get_object_taxonomies('mbdb_book', 'objects' );
	return apply_filters('mbdb_tax_grid_objects', $taxonomies );
}

function mbdb_wp_reserved_terms() {
	return MBDB()->helper_functions->wp_reserved_terms();
}
		
function mbdb_get_tax_grid_slug( $taxonomy, $mbdb_options = null ) {
	return MBDB()->options->get_tax_grid_slug( $taxonomy ); // MBDB()->helper_functions->get_tax_grid_slug( $taxonomy, $mbdb_options );
}

function mbdb_placeholder_cover_options() {
	return MBDB()->helper_functions->placeholder_cover_options();
}

function mbdb_sanitize_field( $field ) {
	return strip_tags( stripslashes( $field ) );
}

function mbdb_add_social_media_page( $pages ) {
	return MBDB()->settings->add_social_media_page( $pages );
}

function mbdb_get_alt_text( $imageID, $default_alt) {
	return MBDB()->helper_functions->get_alt_attr( $imageID, $default_alt );
}

function mbdb_get_random ( $array ) {
	return MBDB()->helper_functions->get_random_element( $array );
}

function mbdb_get_book_dropdown( $selected_bookID ) {
	//print_r('mbdb_get_book_dropdown');
	$book_list = new MBDB_Book_List( MBDB_Book_List_Enum::all, 'title', 'ASC');
	
	foreach( $book_list as $book ) {
		$selected = ($selected_bookID == $book->id ? ' selected' : '');
		echo apply_filters('mbdb_get_book_dropdown_option', '<option value="' . esc_attr($book->id) .'"' . $selected . '>' . esc_html($book->title) . '</option>');
	}
}

function mbdb_get_book_array($orderby = 'post_title', $direction = 'ASC') {
	//print_r('mbdb_get_book_array');
	$book_list = new MBDB_Book_List( MBDB_Book_List_Enum::all, 'title', 'ASC');
	
	return apply_filters('mbdb_get_book_array', $book_list->get_title_list() );
		
	 
}

function mbdb_get_term_options( $taxonomy = 'category', $args = array() ) {
	return MBDB()->helper_functions->get_term_options( $taxonomy, $args );
}

function mbdb_get_publishers( $empty_option = 'yes' ) {
	$empty_option = ( $empty_option == 'yes' );
	return  MBDB()->helper_functions->create_array_from_objects( MBDB()->options->publishers, 'name', $empty_option  );
}

function mbdb_get_retailers( $empty_option = 'yes' ) {
	$empty_option = ( $empty_option == 'yes' );
	return  MBDB()->helper_functions->create_array_from_objects( MBDB()->options->retailers, 'name', $empty_option  );
}

function mbdb_get_formats( $empty_option = 'yes' ) {
	$empty_option = ( $empty_option == 'yes' );
	return  MBDB()->helper_functions->create_array_from_objects( MBDB()->options->download_formats, 'name', $empty_option  );
}

function mbdb_get_editions( $empty_option = 'yes' ) {
	$empty_option = ( $empty_option == 'yes' );
	return  MBDB()->helper_functions->create_array_from_objects( MBDB()->options->edition_formats, 'name', $empty_option  );
}

function mbdb_get_social_media( $empty_option = 'yes' ) {
	$empty_option = ( $empty_option == 'yes' );
	return  MBDB()->helper_functions->create_array_from_objects( MBDB()->options->social_media_sites, 'name', $empty_option  );
}

function mbdb_get_currency_symbol_array() {
	return MBDB()->options->currency_symbols;
}

// TODO add to helper functions

function mbdb_book_grid_selection_options() {
	return MBDB()->book_grid_CPT->selection_options();
	
}
	
function mbdb_book_grid_order_options() {
	
	return MBDB()->book_grid_CPT->order_options();
}
	
	
function mbdb_book_grid_group_by_options() {
	return MBDB()->book_grid_CPT->group_by_options();
}

function mbdb_get_wysiwyg_output( $content ) {
	return MBDB()->helper_functions->get_wysiwyg_output( $content );
}

function mbdb_upload_image($filename, $path = '') {
	return MBDB()->helper_functions->upload_image( $filename, $path );
}

// NOTE: $mbdb_options passed by reference because it's updated
function mbdb_insert_defaults( $default_values, $options_key, &$mbdb_options, $path = '') {
	return MBDB()->helper_functions->insert_defaults( $default_values, $options_key, $mbdb_options, $path);
}

// used by MBM Image Fixer
function mbdb_get_default_retailers() {
	return MBDB()->helper_functions->get_default_retailers();
}

function mbdb_insert_default_edition_formats(&$mbdb_options) {
	return MBDB()->helper_functions->insert_default_edition_formats( $mbdb_options );
}
	
function mbdb_insert_default_social_media( &$mbdb_options ) {
	return MBDB()->helper_functions->insert_default_social_media( $mbdb_options );
}
	
function mbdb_insert_default_retailers( &$mbdb_options ) {
	return MBDB()->helper_functions->insert_default_retailers( $mbdb_options );
}

function mbdb_get_default_formats() {
	return MBDB()->helper_functions->get_default_formats();
}
	
function mbdb_set_default_tax_grid_slugs() {
	return MBDB()->helper_functions->set_default_tax_grid_slugs();
}
	
function mbdb_insert_default_formats( &$mbdb_options) {
	return MBDB()->helper_functions->insert_default_formats( $mbdb_options);
}

function mbdb_get_books_list( $selection, $selection_ids, $sort_field, $sort_order, $genre_ids, $series_ids, $tag_ids ) { 
	return MBDB()->books->get_ordered_selection( $selection, $selection_ids,  'titleA');
}

function mbdb_save_exerpt( $post_id, $post = null ) {
	$book_CPT = new Mooberry_Book_Manager_Book_CPT();
	//MBDB()->book_CPT->save_book( $post_id, $post );
	$book_CPT->save_book( $post_id, $post );
}

// MA compatibility 
function mbdb_get_group( $level, $groups, $current_group, $selection, $selected_ids, $sort, $book_ids ) {
/*	$options = array( 
				'_mbdb_book_grid_books' => array($selection),
				'_mbdb_book_grid_order' => array($sort),
				'_mbdb_book_grid_author'	=>	array($selected_ids),
			);
	$book_grid = new Mooberry_Book_Manager_Book_Grid( $options );
	$book_grid->group_by = $groups;
	print_r($book_grid);
	return $book_grid->get_group( $level, $current_group, $book_ids);
	*/
	//print_r('You must update MBM Multi-Author to be compatible with MBM version 4.0');
	return array();
}

function mbdb_set_sort( $groups, $sort ) {
	if ( in_array('series', $groups )) {
		return 'series_order';
	} else {
		return $sort;
	}
}


function mbdb_display_grid( $books, $level ) {
	$book_grid = new Mooberry_Book_Manager_Book_Grid();
	$book_grid->book_list = $books;
	//MBDB()->book_grid_CPT->display_grid( $books, $level);
	return $book_grid->display_grid();
}

function mbdb_admin() {
	return MBDB()->mbm_admin();
}

function mbdb_get_book_ID( $slug = '' ) {
	global $post;
	if ( $slug == '' ) {
		if ($post) {
			return $post->ID;
		} 
	} else {
	//	$book = MBDB()->books->get_by_slug($slug); 
$book = null;
		if ( $book ) {
			return $book->book_id;
		} 
	}
	return 0;
}

function mbdb_blank_output($classname, $blank_output) {
	return apply_filters('mbdb_shortcode_' . $classname, '<span class="mbm-book-' . $classname . '"><span class="mbm-book-' . $classname . '-blank">' . esc_html($blank_output) . '</span></span>');
}

function mbdb_affiliate_fields( $key, $metabox ) {
	return MBDB()->helper_functions->affiliate_fields( $key, $metabox );
}

function mbdb_validate_book_fields( $groupname, $fieldIDname, $fields, $message ) {
	$book_CPT = new Mooberry_Book_Manager_Book_CPT();
	$book_CPT->validate_all_group_fields( $groupname, $fieldIDname, $fields, $message);
	//MBDB()->book_CPT->validate_all_group_fields( $groupname, $fieldIDname, $fields, $message);
}

function mbdb_error_message( $message, $post_id = null ) {
	if ( !$post_id ) {
		$post_id = $_POST['post_ID'];
	}
	 // set the message
	$notice = get_option( 'mbdb_notice' );
	$notice[$post_id] = $message;
	update_option( 'mbdb_notice', $notice);
	
	// change it to pending not updated
	global $wpdb;
	$wpdb->update( $wpdb->posts, array( 'post_status' => 'draft' ), array( 'ID' => $post_id ) );
	
	// filter the query URL to change the published message
	add_filter( 'redirect_post_location', create_function( '$location', 'return esc_url_raw(add_query_arg("message", "0", $location));' ) );
}