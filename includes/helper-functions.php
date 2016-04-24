<?php
	
/**********************************************************

UTILITY FUNCTIONS

***********************************************************/

// if in development, return the time so that it forces a reload
// otherwise return the current plugin version so a reload is only forced if it's an update
function mbdb_get_enqueue_version() {
	if ( WP_DEBUG ) {
		return time();
	} else {
		return MBDB_PLUGIN_VERSION;
	}
}

function mbdb_format_date($field) {
	if ($field == null or $field == '') {
		return $field;
	}
	return apply_filters('mbdb_format_date', date( 'Y/m/d', strtotime( $field ) ));
}


function mbdb_check_field( $fieldname, $arrayname) {
	return ( array_key_exists($fieldname, $arrayname ) && isset( $arrayname[$fieldname] ) && trim( $arrayname[$fieldname] ) != '');
}

function mbdb_sanitize_field( $field ) {
	return strip_tags( stripslashes( $field ) );
}

function mbdb_get_grid_cover_height( $postID = null ) {
	// tax grids always use the default
	if ( get_post_type() != 'mbdb_tax_grid' ) {
		$mbdb_book_grid_cover_height_default = get_post_meta( $postID, '_mbdb_book_grid_cover_height_default', true);
	} else {
		$mbdb_book_grid_cover_height_default = 'yes';
	}
	
	// if getting the default, pull from options
	// otherwise pull from the specific page's settings
	if ($mbdb_book_grid_cover_height_default == 'yes') {
		$mbdb_options = get_option('mbdb_options');
		if (isset($mbdb_options['mbdb_default_cover_height'])) {
			$mbdb_book_grid_cover_height = $mbdb_options['mbdb_default_cover_height'];
		}
	} else {
		$mbdb_book_grid_cover_height = get_post_meta( $postID, '_mbdb_book_grid_cover_height', true);
	}
	
	// if the height isn't set for some reason, default to 200
	if (!isset($mbdb_book_grid_cover_height) || $mbdb_book_grid_cover_height == '') {
		$mbdb_book_grid_cover_height = apply_filters('mbdb_book_grid_cover_height_default', 200);
	}
	return $mbdb_book_grid_cover_height;
}

// generate uniqueIDs for formats and retailers
function mbdb_uniqueID_generator( $value ) {
	if ($value=='') {
		$value =  uniqid();
	}
	return apply_filters('mbdb_settings_uniqid', $value);
}

function mbdb_book_grid_selection_options() {
		return apply_filters('mbdb_book_grid_selection_options', array(
						'all'		=> __('All', 'mooberry-book-manager'),
						'published'	=> __('All Published', 'mooberry-book-manager'),
						'unpublished'	=> __('All Coming Soon', 'mooberry-book-manager'),
						'custom'	=> __('Select Books', 'mooberry-book-manager'),
						'genre'			=> __('Select Genres', 'mooberry-book-manager'),
						'series'	=> __('Select Series', 'mooberry-book-manager'),
						'tag'		=> __('Select Tags', 'mooberry-book-manager'),
						'publisher'	=>	__('Select Publishers', 'mooberry-book-manager'),
						'editor'	=> __('Select Editors', 'mooberry-book-manager'),
						'illustrator'	=> __('Select Illustrators', 'mooberry-book-manager'),
						'cover_artist'	=>	__('Select Cover Artists', 'mooberry-book-manager'),
					)
				);
						
}

function mbdb_book_grid_order_options() {
	return apply_filters('mbdb_book_grid_order_options', array(	
						'pubdateA'	=> __('Publication Date (oldest first)', 'mooberry-book-manager'),
						'pubdateD'	=> __('Publication Date (newest first)', 'mooberry-book-manager'),
						'titleA'	=> __('Title (A-Z)', 'mooberry-book-manager'),
						'titleD'	=> __('Title (Z-A)', 'mooberry-book-manager')));
}


function mbdb_book_grid_group_by_options() {
	return apply_filters('mbdb_book_grid_group_by_options', array(
						'none'		=>	__('None', 'mooberry-book-manager'),
						'genre'		=>	__('Genre', 'mooberry-book-manager'),
						'series'	=>	__('Series', 'mooberry-book-manager'),
						'tag'		=>	__('Tag', 'mooberry-book-manager'),
						'publisher'	=>	__('Publisher', 'mooberry-book-manager'),
						'editor'	=> 	__('Editor', 'mooberry-book-manager'),
						'cover_artist'	=> __('Cover Artist', 'mooberry-book-manager'),
						'illustrator'	=> __('Illustrator', 'mooberry-book-manager'),
				)
			);
}

/**
 * Gets a number of terms and displays them as options
 * @param  string       $taxonomy Taxonomy terms to retrieve. Default is category.
 * @param  string|array $args     Optional. get_terms optional arguments
 * @return array                  An array of options that matches the CMB2 options array
 */
function mbdb_get_term_options( $taxonomy = 'category', $args = array() ) {

    $args['taxonomy'] = $taxonomy;
    // $defaults = array( 'taxonomy' => 'category' );
    $args = wp_parse_args( $args, array( 'orderby'           => 'name', 
										'order'             => 'ASC', 
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


function mbdb_get_template_list() {
	// get the list of templates from the theme
	$all_templates = wp_get_theme()->get_page_templates();
	
	// add the default
	$all_templates = array_merge( array('default' => __('Default', 'mooberry-book-manager')), $all_templates);
	
	return $all_templates;
}

function mbdb_tax_grid_objects() {
	$taxonomies = get_object_taxonomies('mbdb_book', 'objects' );
	return apply_filters('mbdb_tax_grid_objects', $taxonomies );
}


function mbdb_get_metabox_field_position($metabox, $fieldname) {

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

function mbdb_get_random( $array ) {
	// does not use array_rand because it's been noted that the randomness
	// is "weird" and it's also slower
	
	if ( count( $array ) == 0 ) {
		return null;
	}
	
	shuffle( $array );
	return $array[0];
}

// TODO put this in all URL fields
function mbdb_url_validation_pattern() {
	return apply_filters('mbdb_url_validation_pattern', '^(https?:\/\/)?([\da-zA-Z\.-]+)\.([A-Za-z\.]{2,6}).*');
}
	

function mbdb_dropdown($dropdownID, $options, $selected = null, $include_empty = 'yes', $empty_value = -1, $name = '' ) {
	$html = '<select id="' . $dropdownID . '"';
	if ($name != '' ) {
		$html .= ' name="' . $name;
	}
	$html .= '">';
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
	
	
function mbdb_set_admin_notice($message, $type, $key) {
	// type must be one of these
	if (!in_array($type, array('error', 'updated', 'update-nag'))) {
		$type = 'updated';
	}
	
	$notices = get_option( 'mbdb_admin_notices', array() );
	$notices[$key] = array('message' => $message, 'type' => $type);
	update_option( 'mbdb_admin_notices', $notices);
	
}

function mbdb_remove_admin_notice($key) {
	$mbdb_admin_notices = get_option('mbdb_admin_notices');

	if (is_array($mbdb_admin_notices)) {
		if (array_key_exists($key, $mbdb_admin_notices)) {
			unset($mbdb_admin_notices[$key]);
		}
		update_option('mbdb_admin_notices', $mbdb_admin_notices);
	}
}
	
	
	// for users with PHP <5.5
if(!function_exists("array_column"))
{

    function array_column($array,$column_name, $key = null)
    {

		if ($key == null) {
			return array_map(function($element) use($column_name){return $element[$column_name];}, $array);
		} else {
			$new_array = array();
			
			foreach ($array as $element) {
				$new_array[$element[$key]] = $element[$column_name];
			}

			return $new_array;
		}

    }

}




/****************************************************
	
	GET DATA
	
*****************************************************/

// not used
function mbdb_get_single_book( $slug ) {
	$args = array('posts_per_page' => -1,
					'post_type' => 'mbdb_book',
					'post_status'=>	'publish',
					'name'=> $slug,
					);
			
	$book = get_posts( apply_filters('mbdb_get_book_by_slug', $args) );
	wp_reset_postdata();
	return apply_filters('mbdb_get_single_book', $book);
}

// used to create drop down for wiget and for multicheck for book grids
// uses get_posts because it only uses data in the posts table
function mbdb_get_book_array($orderby = 'post_title', $direction = 'ASC') {
	$args = array('posts_per_page' => -1,
					'post_type' => 'mbdb_book',
					'post_status'=>	'publish',
					'orderby' => $orderby,
					'order' => $direction
					);
			
	$book_query = get_posts( $args);
	$books = array();
	foreach( $book_query as $book ) {
		$books[$book->ID] = $book->post_title;
	}
	wp_reset_postdata();
	return apply_filters('mbdb_get_book_array', $books);
}
	


// returns array with publisher info
// returns null if no publisherID found
function mbdb_get_publisher_info( $publisherID, $mbdb_options = null ) {
	if ($mbdb_options == null) {
		$mbdb_options = get_option('mbdb_options');
	}
	if (array_key_exists('publishers', $mbdb_options)) {
		
		// turn the publishers array from [0]['unqiueID'] = '',
		//								  [1]['name'] = ''
		// into array like this			[uniqueID] => { ['name'],
		//												['link'] }
										  
		// get an array of uniqueIDs
		$keys = array_column( $mbdb_options['publishers'], 'uniqueID' );
		
		// map uniqueIDs to the rest of the publisher info
		$publishers = array_combine( $keys, $mbdb_options['publishers'] );
		
		// return the publisher with the ID
		if (array_key_exists( $publisherID, $publishers ) ) {
			return $publishers[ $publisherID ];
		} else {
			return null;
		}

	}
	return null;
}

function mbdb_get_book_dropdown( $selected_bookID ) {
	$books = mbdb_get_book_array();
	foreach( $books as $book_id => $book_title ) {
		$selected = ($selected_bookID == $book_id ? ' selected' : '');
		echo apply_filters('mbdb_get_book_dropdown_option', '<option value="' . esc_attr($book_id) .'"' . $selected . '>' . esc_html($book_title) . '</option>');
	}
}

function mbdb_get_publishers($empty_option = 'yes' ) {
		return mbdb_get_list('publishers', $empty_option);
}

function mbdb_get_retailers($empty_option = 'yes' ) {
	return mbdb_get_list( 'retailers', $empty_option );
}
	
function mbdb_get_formats($empty_option = 'yes' ) {
	return mbdb_get_list( 'formats', $empty_option );
}
	
function mbdb_get_editions($empty_option = 'yes' ) {
	return mbdb_get_list ('editions', $empty_option);
}

// since 3.0
function mbdb_get_social_media( $empty_option = 'yes' ) {
	return mbdb_get_list( 'social_media', $empty_option);
}


// create an array from serialized data in options using uniqueID as the key and name as the value
function mbdb_get_list( $options_key, $empty_option = 'yes' ) {
	// creates an array with uniqueID as the key and name as the value
	$list = mbdb_create_array_options_list( $options_key, 'uniqueID', 'name' );
	
	if ($empty_option == 'yes') {
		$list[0] = '';
	}
	// natural sort, case insensitive (sorts by value)
	natcasesort($list);
	
	return apply_filters('mbdb_' . $options_key . '_list', $list);
}	

function mbdb_create_array_options_list( $options_key, $key_field, $value_field, $mbdb_options = null ) {
	if (!$mbdb_options) {
		$mbdb_options = get_option('mbdb_options');
	}
	if (!is_array($mbdb_options)) {
		$mbdb_options = array();
	}
	if (array_key_exists( $options_key, $mbdb_options ) ) {
		// creates an array with key_field as the key and value_field as the value
		return array_column( $mbdb_options[ $options_key ], $value_field, $key_field );
	} else {
		return array();
	}
}
	
// used in mbdb_output_editions
function mbdb_get_format_name( $formatID ) {
	
	// creates an array of editions with uniqueID as the key and name as the value
	$formats = mbdb_create_array_options_list( 'editions', 'uniqueID', 'name' );
	if (array_key_exists($formatID, $formats)) {
		return $formats[$formatID];
	} else {
		return '';
	}
}

function mbdb_get_units_array() {
	return apply_filters('mbdb_get_units_array', array(
			'in'	=>	__('inches (in)', 'mooberry-book-manager'),
			'cm'	=> __('centimeters (cm)', 'mooberry-book-manager'),
			'mm'	=>	__('millimeters (mm)', 'mooberry-book-manager'),
	));
}
		
function mbdb_get_default_unit( $mbdb_options = null) {
	if ($mbdb_options == null) {
		$mbdb_options = get_option('mbdb_options');
	}
	if (!isset($mbdb_options['mbdb_default_unit'])) {
		return 'in';
	} 
	return $mbdb_options['mbdb_default_unit'];
}

function mbdb_get_currency_symbol($currency) {
	$symbols = mbdb_get_currency_symbol_array();
	if (array_key_exists($currency, $symbols)) {
		return $symbols[$currency];
	} else {
		return '';
	}
}

function mbdb_get_currency_array() {
	return apply_filters('mbdb_get_currency_array', array(
		'AUD'   => __('Australian Dollar', 'mooberry-book-manager'),
		'BRL'   => __('Brazilian Real ', 'mooberry-book-manager'),
		'CAD'   => __('Canadian Dollar', 'mooberry-book-manager'),
		'CZK'   => __('Czech Koruna', 'mooberry-book-manager'),
		'DKK'   => __('Danish Krone', 'mooberry-book-manager'),
		'EUR'   => __('Euro', 'mooberry-book-manager'),
		'HKD'   => __('Hong Kong Dollar', 'mooberry-book-manager'),
		'HUF'   => __('Hungarian Forint', 'mooberry-book-manager'),
		'INR'	=> __('Indian Rupee', 'mooberry-book-manager'),
		'ILS'   => __('Israeli New Sheqel', 'mooberry-book-manager'),
		'JPY'   => __('Japanese Yen', 'mooberry-book-manager'),
		'MYR'   => __('Malaysian Ringgit', 'mooberry-book-manager'),
		'MXN'   => __('Mexican Peso', 'mooberry-book-manager'),
		'NOK'   => __('Norwegian Krone', 'mooberry-book-manager'),
		'NZD'   => __('New Zealand Dollar', 'mooberry-book-manager'),
		'PHP'   => __('Philippine Peso', 'mooberry-book-manager'),
		'PLN'   => __('Polish Zloty', 'mooberry-book-manager'),
		'RUB'	=> __('Russian Rube', 'mooberry-book-manager'),
		'SGD'   => __('Singapore Dollar', 'mooberry-book-manager'),
		'ZAR'   => __('South African Rand', 'mooberry-book-manager'),
		'SEK'   => __('Swedish Krona', 'mooberry-book-manager'),
		'CHF'   => __('Swiss Franc', 'mooberry-book-manager'),
		'TWD'   => __('Taiwan New Dollar', 'mooberry-book-manager'),
		'THB'   => __('Thai Baht', 'mooberry-book-manager'),
		'TRY'   => __('Turkish Lira', 'mooberry-book-manager'),
		'GBP'   => __('U.K. Pound Sterling', 'mooberry-book-manager'),
		'USD'   => __('U.S. Dollar', 'mooberry-book-manager'),
	));
}

function mbdb_get_currency_symbol_array() {
	return apply_filters('mbdb_get_currency_symbol_array', array(
		'AUD'   => '$',
		'BRL'   => 'R$',
		'CAD'   => '$',
		'CZK'   => 'Kč',
		'DKK'   => 'kr',
		'EUR'   => '€',
		'HKD'   => '$',
		'HUF'   => 'Ft',
		'INR'	=> '₹',
		'ILS'   => '₪',
		'JPY'   => '¥',
		'MYR'   => 'RM',
		'MXN'   => '$',
		'NOK'   => 'kr',
		'NZD'   => '$',
		'PHP'   => '₱',
		'PLN'   => 'zł',
		'RUB'	=> '₽',
		'GBP'   => '£',
		'SGD'   => '$',
		'ZAR'	=> 'R',
		'SEK'   => 'kr',
		'CHF'   => 'CHF',
		'TWD'   => 'NT$',
		'THB'   => '฿',
		'TRY'   => '₤',
		'USD'   => '$',
		
	));
}

function mbdb_get_language_array() {
	return apply_filters('mbdb_get_language_array', array(
		'AB' => __('Abkhazian', 'mooberry-book-manager'),
		'AA' => __('Afar', 'mooberry-book-manager'),
		'AF' => __('Afrikaans', 'mooberry-book-manager'),
		'SQ' => __('Albanian', 'mooberry-book-manager'),
		'AM' => __('Amharic', 'mooberry-book-manager'),
		'AR' => __('Arabic', 'mooberry-book-manager'),
		'HY' => __('Armenian', 'mooberry-book-manager'),
		'AS' => __('Assamese', 'mooberry-book-manager'),
		'AY' => __('Aymara', 'mooberry-book-manager'),
		'AZ' => __('Azerbaijani', 'mooberry-book-manager'),
		'BA' => __('Bashkir', 'mooberry-book-manager'),
		'EU' => __('Basque', 'mooberry-book-manager'),
		'BN' => __('Bengali, Bangla', 'mooberry-book-manager'),
		'DZ' => __('Bhutani', 'mooberry-book-manager'),
		'BH' => __('Bihari', 'mooberry-book-manager'),
		'BI' => __('Bislama', 'mooberry-book-manager'),
		'BR' => __('Breton', 'mooberry-book-manager'),
		'BG' => __('Bulgarian', 'mooberry-book-manager'),
		'MY' => __('Burmese', 'mooberry-book-manager'),
		'BE' => __('Byelorussian', 'mooberry-book-manager'),
		'KM' => __('Cambodian', 'mooberry-book-manager'),
		'CA' => __('Catalan', 'mooberry-book-manager'),
		'ZH' => __('Chinese', 'mooberry-book-manager'),
		'CO' => __('Corsican', 'mooberry-book-manager'),
		'HR' => __('Croatian', 'mooberry-book-manager'),
		'CS' => __('Czech', 'mooberry-book-manager'),
		'DA' => __('Danish', 'mooberry-book-manager'),
		'NL' => __('Dutch', 'mooberry-book-manager'),
		'EN' => __('English', 'mooberry-book-manager'),
		'EO' => __('Esperanto', 'mooberry-book-manager'),
		'ET' => __('Estonian', 'mooberry-book-manager'),
		'FO' => __('Faeroese', 'mooberry-book-manager'),
		'FJ' => __('Fiji', 'mooberry-book-manager'),
		'FI' => __('Finnish', 'mooberry-book-manager'),
		'FR' => __('French', 'mooberry-book-manager'),
		'FY' => __('Frisian', 'mooberry-book-manager'),
		'GD' => __('Gaelic (Scots Gaelic)', 'mooberry-book-manager'),
		'GL' => __('Galician', 'mooberry-book-manager'),
		'KA' => __('Georgian', 'mooberry-book-manager'),
		'DE' => __('German', 'mooberry-book-manager'),
		'EL' => __('Greek', 'mooberry-book-manager'),
		'KL' => __('Greenlandic', 'mooberry-book-manager'),
		'GN' => __('Guarani', 'mooberry-book-manager'),
		'GU' => __('Gujarati', 'mooberry-book-manager'),
		'HA' => __('Hausa', 'mooberry-book-manager'),
		'IW' => __('Hebrew', 'mooberry-book-manager'),
		'HI' => __('Hindi', 'mooberry-book-manager'),
		'HU' => __('Hungarian', 'mooberry-book-manager'),
		'IS' => __('Icelandic', 'mooberry-book-manager'),
		'IN' => __('Indonesian', 'mooberry-book-manager'),
		'IA' => __('Interlingua', 'mooberry-book-manager'),
		'IE' => __('Interlingue', 'mooberry-book-manager'),
		'IK' => __('Inupiak', 'mooberry-book-manager'),
		'GA' => __('Irish', 'mooberry-book-manager'),
		'IT' => __('Italian', 'mooberry-book-manager'),
		'JA' => __('Japanese', 'mooberry-book-manager'),
		'JW' => __('Javanese', 'mooberry-book-manager'),
		'KN' => __('Kannada', 'mooberry-book-manager'),
		'KS' => __('Kashmiri', 'mooberry-book-manager'),
		'KK' => __('Kazakh', 'mooberry-book-manager'),
		'RW' => __('Kinyarwanda', 'mooberry-book-manager'),
		'KY' => __('Kirghiz', 'mooberry-book-manager'),
		'RN' => __('Kirundi', 'mooberry-book-manager'),
		'KO' => __('Korean', 'mooberry-book-manager'),
		'KU' => __('Kurdish', 'mooberry-book-manager'),
		'LO' => __('Laothian', 'mooberry-book-manager'),
		'LA' => __('Latin', 'mooberry-book-manager'),
		'LV' => __('Latvian, Lettish', 'mooberry-book-manager'),
		'LN' => __('Lingala', 'mooberry-book-manager'),
		'LT' => __('Lithuanian', 'mooberry-book-manager'),
		'MK' => __('Macedonian', 'mooberry-book-manager'),
		'MG' => __('Malagasy', 'mooberry-book-manager'),
		'MS' => __('Malay', 'mooberry-book-manager'),
		'ML' => __('Malayalam', 'mooberry-book-manager'),
		'MT' => __('Maltese', 'mooberry-book-manager'),
		'MI' => __('Maori', 'mooberry-book-manager'),
		'MR' => __('Marathi', 'mooberry-book-manager'),
		'MO' => __('Moldavian', 'mooberry-book-manager'),
		'MN' => __('Mongolian', 'mooberry-book-manager'),
		'NA' => __('Nauru', 'mooberry-book-manager'),
		'NE' => __('Nepali', 'mooberry-book-manager'),
		'NO' => __('Norwegian', 'mooberry-book-manager'),
		'OC' => __('Occitan', 'mooberry-book-manager'),
		'OR' => __('Oriya', 'mooberry-book-manager'),
		'OM' => __('Oromo, Afan', 'mooberry-book-manager'),
		'PS' => __('Pashto, Pushto', 'mooberry-book-manager'),
		'FA' => __('Persian', 'mooberry-book-manager'),
		'PL' => __('Polish', 'mooberry-book-manager'),
		'PT' => __('Portuguese', 'mooberry-book-manager'),
		'PA' => __('Punjabi', 'mooberry-book-manager'),
		'QU' => __('Quechua', 'mooberry-book-manager'),
		'RM' => __('Rhaeto-Romance', 'mooberry-book-manager'),
		'RO' => __('Romanian', 'mooberry-book-manager'),
		'RU' => __('Russian', 'mooberry-book-manager'),
		'SM' => __('Samoan', 'mooberry-book-manager'),
		'SG' => __('Sangro', 'mooberry-book-manager'),
		'SA' => __('Sanskrit', 'mooberry-book-manager'),
		'SR' => __('Serbian', 'mooberry-book-manager'),
		'SH' => __('Serbo-Croatian', 'mooberry-book-manager'),
		'ST' => __('Sesotho', 'mooberry-book-manager'),
		'TN' => __('Setswana', 'mooberry-book-manager'),
		'SN' => __('Shona', 'mooberry-book-manager'),
		'SD' => __('Sindhi', 'mooberry-book-manager'),
		'SI' => __('Singhalese', 'mooberry-book-manager'),
		'SS' => __('Siswati', 'mooberry-book-manager'),
		'SK' => __('Slovak', 'mooberry-book-manager'),
		'SL' => __('Slovenian', 'mooberry-book-manager'),
		'SO' => __('Somali', 'mooberry-book-manager'),
		'ES' => __('Spanish', 'mooberry-book-manager'),
		'SU' => __('Sudanese', 'mooberry-book-manager'),
		'SW' => __('Swahili', 'mooberry-book-manager'),
		'SV' => __('Swedish', 'mooberry-book-manager'),
		'TL' => __('Tagalog', 'mooberry-book-manager'),
		'TG' => __('Tajik', 'mooberry-book-manager'),
		'TA' => __('Tamil', 'mooberry-book-manager'),
		'TT' => __('Tatar', 'mooberry-book-manager'),
		'TE' => __('Tegulu', 'mooberry-book-manager'),
		'TH' => __('Thai', 'mooberry-book-manager'),
		'BO' => __('Tibetan', 'mooberry-book-manager'),
		'TI' => __('Tigrinya', 'mooberry-book-manager'),
		'TO' => __('Tonga', 'mooberry-book-manager'),
		'TS' => __('Tsonga', 'mooberry-book-manager'),
		'TR' => __('Turkish', 'mooberry-book-manager'),
		'TK' => __('Turkmen', 'mooberry-book-manager'),
		'TW' => __('Twi', 'mooberry-book-manager'),
		'UK' => __('Ukrainian', 'mooberry-book-manager'),
		'UR' => __('Urdu', 'mooberry-book-manager'),
		'UZ' => __('Uzbek', 'mooberry-book-manager'),
		'VI' => __('Vietnamese', 'mooberry-book-manager'),
		'VO' => __('Volapuk', 'mooberry-book-manager'),
		'CY' => __('Welsh', 'mooberry-book-manager'),
		'WO' => __('Wolof', 'mooberry-book-manager'),
		'XH' => __('Xhosa', 'mooberry-book-manager'),
		'JI' => __('Yiddish', 'mooberry-book-manager'),
		'YO' => __('Yoruba', 'mooberry-book-manager'),
		'ZU' => __('Zulu', 'mooberry-book-manager'), 
	));

}

function mbdb_get_default_currency( $mbdb_options = null) {
	if ($mbdb_options == null) {
		$mbdb_options = get_option('mbdb_options');
	}
	if (!isset($mbdb_options['mbdb_default_currency'])) {
		return 'USD';
	} 
	return $mbdb_options['mbdb_default_currency'];
}

function mbdb_get_default_language( $mbdb_options = null) {
	if ($mbdb_options == null) {
		$mbdb_options = get_option('mbdb_options');
	}
	if (!isset($mbdb_options['mbdb_default_language'])) {
		return 'EN';
	} 
	return $mbdb_options['mbdb_default_language'];
}

function mbdb_get_language_name($language_code) {
	$language = mbdb_get_language_array();
	if (array_key_exists($language_code, $language)) {
		return $language[$language_code];
	} else {
		return '';
	}
}





 
function mbdb_set_up_roles() {
	
		$contributor_level = apply_filters('mbdb_contributor_level_capabilities', array(
									'edit_mbdb_books',
									'edit_mbdb_book',
									'delete_mbdb_books',
									'delete_mbdb_book',
									'manage_mbdb_books')
								);
									
		$base_level = apply_filters('mbdb_base_level_capabilities', array(		
									'publish_mbdb_books',
									'publish_mbdb_book',
									'edit_published_mbdb_book',
									'edit_published_mbdb_books',
									'delete_published_mbdb_book',
									'delete_published_mbdb_books',
									'upload_files',
									'manage_mbdb_books',
									'read')
								);
									
		$master_level = apply_filters('mbdb_master_level_capabilities', array(		
									'edit_others_mbdb_books',
									'edit_others_mbdb_books',
									'delete_others_mbdb_books',
									'delete_others_mbdb_book')
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
		
}


// uploads file at specfied $filename and returns the attachment id of the uploaded file
// v3.0 added path param to allow it to be used with other plugins
function mbdb_upload_image($filename, $path = '') {
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
function mbdb_insert_defaults( $default_values, $options_key, &$mbdb_options, $path = '') {
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
				/*
				$default_values[$uniqueID]['imageID'] = $attachID;
				if ($attachID != 0) {
					$default_values[$uniqueID]['image'] = wp_get_attachment_url( $attachID );
				} else {
					$default_values[$uniqueID]['image'] = '';
				}
				*/
			}
			
			/* // save each piece of data
			foreach($default_value as $key => $data) {
			// save the name and uniqueID
				$default_data[$key] = $default_value['name'];
			}
			if (array_key_exists('uniqueID', $default_value)) {
				$default_data['uniqueID'] = $default_value[ 'uniqueID' ];
			} */
			
			// add to the options
			$mbdb_options[$options_key][] = $default_values[$uniqueID];
		}
	}		

}

// used by MBM Image Fixer
function mbdb_get_default_retailers() {
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
	$default_retailers[] = array('name' => 'Amazon Kindle', 'uniqueID' => 13, 'image' => 'kindle.png' );
	$default_retailers[] = array('name' => 'Barnes and Noble Nook', 'uniqueID' => 14, 'image' => 'nook.png' );
	
	return apply_filters('mbdb_default_retailers', $default_retailers);

}

function mbdb_insert_default_edition_formats(&$mbdb_options) {
	$default_formats = array();
	$default_formats[] = array('name' => 'Hardcover', 'uniqueID' => 1);
	$default_formats[] = array('name' => 'Paperback', 'uniqueID' => 2);
	$default_formats[] = array('name' => 'ePub', 'uniqueID' => 3);
	$default_formats[] = array('name' => 'Kindle', 'uniqueID' => 4);
	$default_formats[] = array('name' => 'PDF', 'uniqueID' => 5);
	$default_formats[] = array('name' => 'Audiobook', 'uniqueID' => 6);
	$default_formats = apply_filters('mbdb_default_edition_formats', $default_formats);
	
	mbdb_insert_defaults( $default_formats, 'editions', $mbdb_options);
}

// since version 3.0
function mbdb_insert_default_social_media( &$mbdb_options ) {
	$defaults = array();
	$defaults[] = array('name' => 'Facebook', 'uniqueID' => 1, 'image' => 'facebook.png');
	$defaults[] = array('name' => 'Twitter', 'uniqueID' => 2, 'image' => 'twitter.png');
	$defaults[] = array('name' => 'Pinterest', 'uniqueID' => 3, 'image' => 'pinterest.png');
	$defaults[] = array('name' => 'YouTube', 'uniqueID' => 4, 'image' => 'youtube.png');
	$defaults[] = array('name' => 'LinkedIn', 'uniqueID' => 5, 'image' => 'linkedin.png');
	$defaults[] = array('name' => 'Goodreads', 'uniqueID' => 6, 'image' => 'goodreads_logo.png');
	$defaults = apply_filters('mbdb_default_social_media_sites', $defaults);
	
	mbdb_insert_defaults( $defaults, 'social_media', $mbdb_options);
}

function mbdb_insert_default_retailers( &$mbdb_options ) {
// check if default retailers and formats exist in database and add them if necessary
	$default_retailers = mbdb_get_default_retailers();
	
	mbdb_insert_defaults( $default_retailers, 'retailers', $mbdb_options);
}


// used by MBM Image Fixer
function mbdb_get_default_formats() {
	$default_formats = array();
	$default_formats[] = array('name' => 'ePub', 'uniqueID' => 1, 'image' => 'epub.png');
	$default_formats[] = array('name' => 'Kindle', 'uniqueID' => 2, 'image' => 'amazon-kindle.png');
	$default_formats[] = array('name' => 'PDF', 'uniqueID' => 3, 'image' => 'pdficon.png');
	
	return apply_filters('mbdb_default_formats', $default_formats);
	
}

function mbdb_set_default_tax_grid_slugs() {
	$taxonomies = mbdb_tax_grid_objects(); //get_object_taxonomies( 'mbdb_book', 'objects' );
	$mbdb_options = get_option('mbdb_options');
	
	foreach($taxonomies as $name => $taxonomy) {
		$key = 'mbdb_book_grid_' . $name . '_slug';
		$mbdb_options[$key] = mbdb_get_tax_grid_slug( $name, $mbdb_options);
	}
	update_option('mbdb_options', $mbdb_options);
}

function mbdb_get_tax_grid_slug( $taxonomy, $mbdb_options = null ) {
	
	if ($mbdb_options == null) {
		$mbdb_options = get_option('mbdb_options');
	}
	if (!is_array($mbdb_options)) {
		$mbdb_options = array();
	}
	
	$tax = get_taxonomy( $taxonomy );
	if ($tax !== false ) {
		$singular_name = $tax->labels->singular_name;
	} else {
		$singular_name = $taxonomy;
	}
	
	$key = 'mbdb_book_grid_' . $taxonomy . '_slug';
	
	$reserved_terms = mbdb_wp_reserved_terms();
	if (!array_key_exists($key, $mbdb_options) || $mbdb_options[$key] == '') {
		// must be sanitized before checking against reserved terms
		$slug = sanitize_title($singular_name);
		if ( in_array($slug, $reserved_terms) ) {
			$slug = 'book-' . $slug;
		}
	} else {
		$slug = $mbdb_options[$key];
	}
	return sanitize_title($slug);
}



function mbdb_insert_default_formats( &$mbdb_options) {
	$default_formats = mbdb_get_default_formats();
	mbdb_insert_defaults( $default_formats, 'formats', $mbdb_options);
}

function mbdb_insert_image( $key, $file, &$mbdb_options ) {
	// check if the coming soon image exists and add it if necessary
		if (!array_key_exists($key, $mbdb_options)) {
			$attachID = mbdb_upload_image($file);
			$mbdb_options[ $key . '-id'] = $attachID;
			if ( $attachID != 0) {
				$img = wp_get_attachment_url( $attachID );
				$mbdb_options[$key] = $img;
			} else {
				$mbdb_options[$key] = '';
			}
			
		}
}

function mbdb_get_alt_text( $imageID, $default_alt) {
	$alt = get_post_meta( $imageID, '_wp_attachment_image_alt', true);
	if ($alt == '') {
		$alt = $default_alt;
	}	
	return ' alt="' . esc_attr($alt) . '" ';
}


function mbdb_wp_reserved_terms() {
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