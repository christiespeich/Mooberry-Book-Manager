<?php

/**
 * The MBM Helper Functions defines common functions used throughout the plugin
 *
 * @package MBM
 */

/**
 * The MBM Helper defines common functions used throughout the plugin
 *
 * @since    4.0
 */
class Mooberry_Book_Manager_Helper_Functions {

	public static function get_enqueue_version( $file ) {
		return date("ymd-Gis", filemtime( $file ));
	}
	
	function format_date($field) {
		if ($field == null or $field == '') {
			return $field;
		}
		if ( strtotime( $field ) == '' ) {
			return '';
		}
		return apply_filters('mbdb_format_date', date( 'Y/m/d', strtotime( $field ) ));
	}
	
	function uniqueID_generator ( $value = '' ) {
		if ($value=='') {
			$value =  uniqid();
		}
		return $value;
	}

function url_validation_pattern() {
	return apply_filters('mbdb_url_validation_pattern', '^(https?:\/\/)?([\da-zA-Z\.-]+)\.([A-Za-z\.]{2,6}).*');
}

public function set_admin_notice( $message, $type, $key) {
	// type must be one of these
	if (!in_array($type, array('error', 'updated', 'update-nag'))) {
		$type = 'updated';
	}
	
	$notices = get_option( 'mbdb_admin_notices', array() );
	$notices[$key] = array('message' => $message, 'type' => $type);
	update_option( 'mbdb_admin_notices', $notices);
}
	
	public function remove_admin_notice( $key ) {
		$mbdb_admin_notices = get_option('mbdb_admin_notices');
	
		if (is_array($mbdb_admin_notices)) {
			if (array_key_exists($key, $mbdb_admin_notices)) {
				unset($mbdb_admin_notices[$key]);
			}
			update_option('mbdb_admin_notices', $mbdb_admin_notices);
		}
	}
	
	// makes each property of the object a key in the array
	public function object_to_array( $object ) {
		$array = array();
		foreach ( get_object_vars( $object ) as $key => $value ) {
			$array[$key] = $value;
		}
		return $array;
	}
	public function array_to_object( $array, $object ) {
		foreach ( $array as $key => $value ) {
			$object->{$key} = $value;
		}
		return $object;
	}
		
	
	public function create_array_from_objects( $objects, $value_property, $add_empty = false, $empty_key = '0', $empty_value = '', $id_property = '' ) { 
		$new_array = array();
		
		foreach ( $objects as $id => $object) {
			if ( $id_property != '' ) {
				if ( property_exists( $object, $id_property ) ) {
					$id = $object->{$id_property};
				}
			}
			if ( property_exists( $object, $value_property ) ) {
				$new_array[ $id ] = $object->{$value_property};
			}
		}
		natcasesort( $new_array );
		if ( $add_empty ) {
			$new_array = array( $empty_key => $empty_value ) + $new_array;
		}
	
		
		return $new_array;		
	}
	/*
	// turn the publishers array from [0]['unqiueID'] = '',
	//								  [0]['name'] = '',
	//								  [0]['link'] = ''
	// into array like this			[uniqueID] => { ['name'],
	//												['link'], }
	//			
	public function create_array_with_ids( $array, $id_key ) {
		// get an array of uniqueIDs
		$keys = array_column( $array, $id_key );
		// map uniqueIDs to the rest of the publisher info
		return array_combine( $keys, $array );
	}

	public function create_array_from_options( $options_key, $id_key, $mbdb_options = null ) {
		if ($mbdb_options == null) {
			$mbdb_options = get_option('mbdb_options');
		}
		if (array_key_exists( $options_key, $mbdb_options ) ) {
			return self::create_array_with_ids( $mbdb_options[ $options_key ], $id_key );
		}
		return array();
	}
	*/
	// 3.5
	public function affiliate_fields( $group, $metabox ) {
		$metabox->add_group_field( $group, array(
					'id' => 'affiliate_code',
					'name'	=>	__('Affiliate Code','mooberry-book-manager'),
					'type' => 'text',
					'description' => __('If you are an Affiliate for this retailer, enter the exact code that needs to be added to the URL to use your affiliate link.', 'mooberry-book-manager'),
					'attributes'	=>	array(
								'style'	=>	'width:100em;',
								),
				)
			);
			
		$info_button = '<img onClick="window.open(\'' . MBDB_PLUGIN_URL . 'includes/admin/views/affiliate-code-position.html' . '\', \'' . __('Affiliate Code Position', 'mooberry-book-manager') . '\',  \'width=800, height=300, left=550, top=250, scrollbars=yes\'); return false;"	class="mbdb_info_icon mbdb_affiliate_position_info" src="' . MBDB_PLUGIN_URL . 'includes/assets/info.png">';
		
		$metabox->add_group_field( $group, array(
					'id'	=> 'affiliate_position',
					'name'	=>	__('Affiliate Code Position', 'mooberry-book-manager') . $info_button,
					'type'	=>	'radio_inline',
					'options'	=> array(
								'after'	=>	__('After Book Link', 'mooberry-book-manager'),
								'before' => __('Before Book Link', 'mooberry-book-manager'),
								),
							)
						);
						
		return $metabox;
	}


		
	public function get_template_list() {
		// get the list of templates from the theme
		$all_templates = wp_get_theme()->get_page_templates();
		
		// add the default
		$all_templates = array_merge( array('default' => __('Default', 'mooberry-book-manager')), $all_templates);
		
		return $all_templates;
	}
	
	
	public function wp_reserved_terms() {
		return array(
			'attachment', 
			'attachment_id', 
			'author', 
			'author_name', 
			'calendar', 
			'cat', 
			'category', 
			'category__and', 
			'category__in', 
			'category__not_in', 
			'category_name', 
			'comments_per_page', 
			'comments_popup', 
			'customize_messenger_channel', 
			'customized', 
			'cpage', 
			'day', 
			'debug', 
			'error', 
			'exact', 
			'feed', 
			'hour', 
			'link_category', 
			'm', 
			'minute', 
			'monthnum', 
			'more', 
			'name', 
			'nav_menu', 
			'nonce', 
			'nopaging', 
			'offset', 
			'order', 
			'orderby', 
			'p', 
			'page', 
			'page_id', 
			'paged', 
			'pagename', 
			'pb', 
			'perm', 
			'post', 
			'post__in', 
			'post__not_in', 
			'post_format', 
			'post_mime_type', 
			'post_status', 
			'post_tag', 
			'post_type', 
			'posts', 
			'posts_per_archive_page', 
			'posts_per_page', 
			'preview', 
			'robots', 
			's', 
			'search', 
			'second', 
			'sentence', 
			'showposts', 
			'static', 
			'subpost', 
			'subpost_id', 
			'tag', 
			'tag__and', 
			'tag__in', 
			'tag__not_in', 
			'tag_id', 
			'tag_slug__and', 
			'tag_slug__in', 
			'taxonomy', 
			'tb', 
			'term', 
			'terms', 
			'theme', 
			'title', 
			'type', 
			'w', 
			'withcomments', 
			'withoutcomments', 
			'year', 
		);
	}
	
	public function get_tax_grid_slug( $taxonomy, $mbdb_options = null ) {
	
	/* 	if ($mbdb_options == null) {
			$mbdb_options = get_option('mbdb_options');
		}
		if (!is_array($mbdb_options)) {
			$mbdb_options = array();
		}
		 */
	//	$tax = get_taxonomy( $taxonomy );
	//	if ($taxonomy !== false ) {
			$singular_name = $taxonomy->labels->singular_name;
	//	} else {
	//		$singular_name = $taxonomy;
	//	}
		
		$key = 'mbdb_book_grid_' . $taxonomy->name . '_slug';
		return MBDB()->options->get_tax_grid_slug( $taxonomy->name );
		/* $reserved_terms = $this->wp_reserved_terms();
		if (!array_key_exists($key, $mbdb_options) || $mbdb_options[$key] == '') {
			// must be sanitized before checking against reserved terms
			$slug = sanitize_title($singular_name);
			if ( in_array($slug, $reserved_terms) ) {
				$slug = 'book-' . $slug;
			}
		} else {
			$slug = $mbdb_options[$key];
		}
		return sanitize_title($slug); */
	}

	public function placeholder_cover_options() {
		return apply_filters('mbdb_placeholder_cover_options', array(
				'page' 		=> 	__('Book Page', 'mooberry-book-manager'),
				'widget'	=>	__('Widgets', 'mooberry-book-manager'),
				)
			);
	}
	
	public function override_wpseo_options() {
		return apply_filters('mbdb_override_wpseo_options', array(
				'og'	=>	__('Open Graph', 'mooberry-book-manager'),
				'twitter'	=>	__('Twitter Card', 'mooberry-book-manager'),
				'description'	=>	__('Meta Description', 'mooberry-book-manager'),
			)
		);
	}
	
	function get_alt_attr( $imageID, $default_alt) {
		$alt = get_post_meta( $imageID, '_wp_attachment_image_alt', true);
		if ($alt == '') {
			$alt = $default_alt;
		}	
		return ' alt="' . esc_attr($alt) . '" ';
	}

	
	function make_dropdown($dropdownID, $options, $selected = null, $include_empty = 'yes', $empty_value = -1, $name = '', $args = array() ) {
		$html = '<select id="' . $dropdownID . '"';
		if ($name != '' ) {
			$html .= ' name="' . $name . '"';
		}
		if ( is_array($args) && count($args) > 0 ) {
			foreach ( $args as $attr => $value ) {
				$html .= ' ' . $attr . '="' . esc_attr( $value ) . '" ';
			}
		}
		$html .= '>';
		if ($include_empty == 'yes') {
			$html .= '<option value="' . esc_attr($empty_value) . '"></option>';
		}
		foreach ( $options as $id => $option) {
			$html .= '<option value="' . esc_attr($id) . '"';
			if ($selected == $id) {
				$html .= ' selected ';
			}
			$html .= '>' . $option . '</option>';
		}
		$html .= '</select>';
		return $html;
	}
	
	function get_all_books( ) {
		$title_list = wp_cache_get( 'title_list', 'mbdb_lists' );
		if ( $title_list === false ) {
			//print_r('getting all books from database');
			$book_list = new MBDB_Book_List( MBDB_Book_List_Enum::all, 'title', 'ASC');
			$title_list = $book_list->get_title_list();
			wp_cache_set( 'title_list', $title_list, 'mbdb_lists');
		}
		return $title_list;
	}
	
	function get_random_element( $array ) {
		// does not use array_rand because it's been noted that the randomness
		// is "weird" and it's also slower
		
		if ( count( $array ) == 0 ) {
			return null;
		}
		
		shuffle( $array );
		return $array[0];
	}
	
	/**
	* Gets a number of terms and displays them as options
	* @param  string       $taxonomy Taxonomy terms to retrieve. Default is category.
	* @param  string|array $args     Optional. get_terms optional arguments
	* @return array                  An array of options that matches the CMB2 options array
	*/
	function get_term_options( $taxonomy = 'category', $args = array() ) {

		$args['taxonomy'] = $taxonomy;
		// $defaults = array( 'taxonomy' => 'category' );
		$args = wp_parse_args( $args, array( 'orderby'           => 'name', 
											'order'             => 'ASC', 
											'hide_empty'	=>	'false',
											) 
								);
		
		$taxonomy = $args['taxonomy'];
		
		$terms = (array) get_terms( $taxonomy, $args );
		
		// Initate an empty array
		$term_options = array();
		if ( ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				$term_options[ $term->term_id ] = $term->name;
			}
		}
		
		return $term_options;
	}
		
	
	
	/*
	// 3.3.6
	// add autoembed to CMB2 fields
	function get_wysiwyg_output( $content ) {
		global $wp_embed;

		$content = $wp_embed->autoembed( $content );
		$content = $wp_embed->run_shortcode( $content );
		$content = wpautop( $content );
		
		$content = do_shortcode( $content );
	   

		return $content;
	}
	*/
	
	
	function sanitize_wysiwyg( $content ) {
		return apply_filters( 'content_save_pre', $content );
	}
	
	
	// uploads file at specfied $filename and returns the attachment id of the uploaded file
	// v3.0 added path param to allow it to be used with other plugins
	function upload_image($filename, $path = '') {
		// add images to media library
		// move to uploads folder
		$wp_upload_dir = wp_upload_dir();
		
		// check for path
		if ($path == '') {
			$path = dirname( __FILE__ ) . '/assets/';
			
		}
		
		if (file_exists($path . $filename)) {
			$success = copy( $path . $filename, $wp_upload_dir['path'] . '/' . $filename );
			// v 2.4.2 -- bail out if something goes wrong
			if (!$success) {
				return 0;
			}
			$wp_filetype = wp_check_filetype( basename( $filename ), null );
			$attachment = array (
			'guid' => $wp_upload_dir['url'] . '/' . basename( $filename ),
			'post_mime_type' => $wp_filetype['type'],
			'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
			'post_content' => '',
			'post_status' => 'inherit');
			$attach_id = wp_insert_attachment( $attachment, $wp_upload_dir['path'] . '/' . $filename );
			$attach_data = wp_generate_attachment_metadata( $attach_id,  $wp_upload_dir['path'] . '/' . $filename );
			wp_update_attachment_metadata( $attach_id, $attach_data );
			return $attach_id;
		} else {
			return 0;
		}
	}

	// NOTE: $mbdb_options passed by reference because it's updated
	// v3.0 added path param to allow it to be used with other plugins
	function insert_defaults( $default_values, $options_key, &$mbdb_options, $path = '') {
		if ( ! array_key_exists($options_key, $mbdb_options ) ) {
			//return;
			$mbdb_options[$options_key] = array();
		}
		
		// Create an array of uniqueIDs
		$default_uniqueIDs = array_column( $default_values, 'uniqueID' );
		// Create an array wih uniqueIDs as the key and the default info as the value
		$default_values = array_combine($default_uniqueIDs, $default_values);
		
		// create an array of uniqueIDs
		$existing_uniqueIDs = array_column( $mbdb_options[$options_key], 'uniqueID' );
		
		// loop through each default value
		foreach ($default_values as $uniqueID => $default_value) {
			if ( array_search( $uniqueID, $existing_uniqueIDs ) === false ) {
				// uniqueID doesn't already exist, so add this default value to the options 
				if (array_key_exists('image', $default_value)) {
					// upload the image to the media library 	
					// and save both the URL and the ID
					//$attachID = mbdb_upload_image( $default_value['image'], $path );
					$path = MBDB_PLUGIN_URL . 'includes/assets/' . $default_value['image']; //dirname( __FILE__ ) . '/assets/';
					$default_values[$uniqueID]['image'] = $path;
					
				}
				
				// add to the options
				$mbdb_options[$options_key][] = $default_values[$uniqueID];
			}
		}		
		
	}

	// used by MBM Image Fixer
	function get_default_retailers() {
		// v 2.4.2 updated file names
		$default_retailers = array();
		
		$default_retailers[] = array('name' => 'Amazon', 'uniqueID' => 1, 'image' => 'amazon.png');
		$default_retailers[] = array('name' => 'Barnes and Noble', 'uniqueID' => 2, 'image' => 'bn.png');
		$default_retailers[] = array('name' => 'Kobo', 'uniqueID' => 3, 'image' => 'kobo.png');
		$default_retailers[] = array('name' => 'iBooks', 'uniqueID' => 4, 'image' => 'ibooks.png');
		$default_retailers[] = array('name' => 'Smashwords', 'uniqueID' => 5, 'image' => 'smashwords.png');
		$default_retailers[] = array('name' => 'Audible', 'uniqueID' => 6, 'image' => 'audible.png' );
		$default_retailers[] = array('name' => 'Book Baby', 'uniqueID' => 7, 'image' => 'bookbaby.png' );
		$default_retailers[] = array('name' => 'Books A Million', 'uniqueID' => 8, 'image' => 'bam.png' );
		$default_retailers[] = array('name' => 'Create Space', 'uniqueID' => 9, 'image' => 'createspace.png' );
		$default_retailers[] = array('name' => 'Indie Bound', 'uniqueID' => 10, 'image' => 'indiebound.png' );
		$default_retailers[] = array('name' => 'Powells', 'uniqueID' => 11, 'image' => 'powells.png' );
		$default_retailers[] = array('name' => 'Scribd', 'uniqueID' => 12, 'image' => 'scribd.png' );
		// 3.5.6 kindle.jpg
		$default_retailers[] = array('name' => 'Amazon Kindle', 'uniqueID' => 13, 'image' => 'kindle.jpg' );
		$default_retailers[] = array('name' => 'Barnes and Noble Nook', 'uniqueID' => 14, 'image' => 'nook.png' );
		
		return apply_filters('mbdb_default_retailers', $default_retailers);
		
	}

	function insert_default_edition_formats(&$mbdb_options) {
		$default_formats = array();
		$default_formats[] = array('name' => 'Hardcover', 'uniqueID' => 1);
		$default_formats[] = array('name' => 'Paperback', 'uniqueID' => 2);
		$default_formats[] = array('name' => 'ePub', 'uniqueID' => 3);
		$default_formats[] = array('name' => 'Kindle', 'uniqueID' => 4);
		$default_formats[] = array('name' => 'PDF', 'uniqueID' => 5);
		$default_formats[] = array('name' => 'Audiobook', 'uniqueID' => 6);
		$default_formats = apply_filters('mbdb_default_edition_formats', $default_formats);
		
		$this->insert_defaults( $default_formats, 'editions', $mbdb_options);
	}

	// since version 3.0
	function insert_default_social_media( &$mbdb_options ) {
		$defaults = array();
		$defaults[] = array('name' => 'Facebook', 'uniqueID' => 1, 'image' => 'facebook.png');
		$defaults[] = array('name' => 'Twitter', 'uniqueID' => 2, 'image' => 'twitter.png');
		$defaults[] = array('name' => 'Pinterest', 'uniqueID' => 3, 'image' => 'pinterest.png');
		$defaults[] = array('name' => 'YouTube', 'uniqueID' => 4, 'image' => 'youtube.png');
		$defaults[] = array('name' => 'LinkedIn', 'uniqueID' => 5, 'image' => 'linkedin.png');
		$defaults[] = array('name' => 'Goodreads', 'uniqueID' => 6, 'image' => 'goodreads_logo.png');
		$defaults = apply_filters('mbdb_default_social_media_sites', $defaults);
		
		$this->insert_defaults( $defaults, 'social_media', $mbdb_options);
	}

	function insert_default_retailers( &$mbdb_options ) {
		// check if default retailers and formats exist in database and add them if necessary
		$default_retailers = $this->get_default_retailers();
		
		$this->insert_defaults( $default_retailers, 'retailers', $mbdb_options);
	}


	// used by MBM Image Fixer
	function get_default_formats() {
		$default_formats = array();
		$default_formats[] = array('name' => 'ePub', 'uniqueID' => 1, 'image' => 'epub.png');
		// 3.5.6 kindle.jpg
		$default_formats[] = array('name' => 'Kindle', 'uniqueID' => 2, 'image' => 'kindle.jpg');
		$default_formats[] = array('name' => 'PDF', 'uniqueID' => 3, 'image' => 'pdficon.png');
		
		return apply_filters('mbdb_default_formats', $default_formats);
		
	}

	function set_default_tax_grid_slugs() {
		$taxonomies = get_object_taxonomies( 'mbdb_book', 'objects' );
		$mbdb_options = get_option('mbdb_options');
		
		foreach($taxonomies as $name => $taxonomy) {
			$key = 'mbdb_book_grid_' . $name . '_slug';
			$mbdb_options[$key] = MBDB()->options->get_tax_grid_slug( $name ); //$this->get_tax_grid_slug( $name, $mbdb_options);
		}
		update_option('mbdb_options', $mbdb_options);
	}

	function insert_default_formats( &$mbdb_options) {
		$default_formats = $this->get_default_formats();
		$this->insert_defaults( $default_formats, 'formats', $mbdb_options);
	}

	function get_metabox_field_position($metabox, $fieldname) {
		
		// create an array of field ids
		$fields = array_keys($metabox->meta_box['fields']);
		
		// get the index of the  field
		$position = array_search($fieldname, $fields);
		
		if ($position === false) {
			// return 0 if not found
			return 0;
		} else {
			// add 1 because the first position = 1 and array_search is a 0-based result
			return $position + 1;
		}
	}

	public function insert_tax_grid_page( $template ) {
		// create new page
		return wp_insert_post( array(
						'post_content' => '[mbdb_tax_grid]',
						'post_title' => __('MBM Tax Grid Page. Do NOT remove this page or edit it except to change template.', 'mooberry-book-manager'),
						'post_excerpt' => '[mbdb_tax_grid]',
						'post_status' => 'publish',
						'post_type' => 'page',
						'comment_status' => 'closed',
						'ping_status' => 'closed',
						)
				);
		
	}
	
	public function create_tax_grid_page( $template = 'single.php' ) {
		$id = $this->insert_tax_grid_page( $template );
		if ( $id != 0 ) {
			// assign to setting
			MBDB()->options->set_tax_grid_page($id);
		}
		return $id;
	}
	
}
	
	