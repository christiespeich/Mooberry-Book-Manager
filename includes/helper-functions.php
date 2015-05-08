<?php
	
function mbdb_upgrade_versions() {
		
		
		$current_version = get_option(MBDB_PLUGIN_VERSION_KEY);
		
		if (version_compare($current_version, '1.3.1', '<')) {
			// upgrade to 1.3 script
			// add new retailers
			mbdb_upgrade_to_1_3_1();
		} 
		
		if (version_compare($current_version, '2.0', '<')) {
			//mbdb_upgrade_to_2_0();
		}
		
		// update database to the new version
		update_option(MBDB_PLUGIN_VERSION_KEY, MBDB_PLUGIN_VERSION);
	
}

function mbdb_get_default_page_layout() {
	return apply_filters('mbdb_default_book_page','<h3>[book_subtitle blank=""]</h3>[book_cover width="200" align="right"][book_summary blank="' . __('Summary Coming Soon!', 'mooberry-book-manager') . '"] 
	
	[book_links buylabel="' . __('Buy Now:', 'mooberry-book-manager') . '" downloadlabel="' . __('Download Now:', 'mooberry-book-manager') . '" align="horizontal" size="35" blank="" blanklabel=""]
				[book_goodreads  ]
				
				<strong>Published:</strong> [book_published format="short" blank="' . _x('TBA', 'To Be Announced', 'mooberry-book-manager') . '"]
				<strong>Publisher:</strong> [book_publisher  blank="' . _x('TBA', 'To Be Announced', 'mooberry-book-manager') . '"]
				<strong>Number of Pages:</strong> [book_length  blank="' . _x('TBD', 'To Be Determined', 'mooberry-book-manager') . '"]
				<strong>Genres:</strong><span>[book_genre delim="comma" blank="' . __('(uncategorized)', 'mooberry-book-manager') . '"]</span>
				<strong>Tags:</strong><span> [book_tags  delim="comma" blank="' . __('(none)', 'mooberry-book-manager') . '"]</span>

				[book_serieslist before="Part of the " after=" series: " delim="list"]
				<strong>Reviews:</strong><span> [book_reviews  blank="' . __('Coming Soon!', 'mooberry-book-manager') . '"]</span>
				<strong>Excerpt:</strong><span> [book_excerpt  blank="' . __('Coming Soon!', 'mooberry-book-manager') . '"]</span>[book_links buylabel="' . __('Buy Now:', 'mooberry-book-manager') . '" downloadlabel="' . __('Download Now:', 'mooberry-book-manager') . '"  align="horizontal" size="35" blank="" blanklabel=""]');
}

// uploads file at specfied $filename and returns the attachment id of the uploaded file
function mbdb_upload_image($filename) {
	// add images to media library
	// move to uploads folder
	$wp_upload_dir = wp_upload_dir();
	copy( dirname(__FILE__) . '/assets/' . $filename, $wp_upload_dir['path'] . '/' . $filename );
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
}


// NOTE: $mbdb_options passed by reference because it's updated
function mbdb_insert_defaults( $default_values, $options_key, &$mbdb_options) {
	// check each default item
	for( $x = 0; $x < count($default_values); $x++ ) {
		$found = false;
		if ($mbdb_options) {
			if (array_key_exists($options_key, $mbdb_options)) {
				// if an item with this uniqueID already exists, set the flag and exit the loop
				foreach ($mbdb_options[$options_key] as $o ) {
					if ( $o['uniqueID'] == $default_values[$x]['uniqueID']) {
						$found = true;
						break;
					}
				}
			}
		}
		// if it wasn't found, insert it
		if (!$found) {
			// upload the image to the media library 				
			$attachID = mbdb_upload_image( $default_values[$x]['image'] );
			$default_data['name'] = $default_values[$x]['name'];
			$default_data['imageID'] = $attachID;
			$default_data['image'] = wp_get_attachment_url( $attachID );
			$default_data['uniqueID'] = $default_values[$x][ 'uniqueID' ];
			$mbdb_options[$options_key][] = $default_data;
		}
	}
}


function mbdb_get_books_list( $selection, $selection_ids, $sort_field, $sort_order, $genre_ids, $series_ids ) {
	// title is not a custom field so it uses orderby to set the field
	// other sorting is by custom fields and they use orderby = 'meta_value' and meta_key = field
	if ( $sort_field != 'title' ) {
		$meta_key = $sort_field;
		$sort_field = 'meta_value';
	} else {
		$meta_key = '';
		$sort_field = 'post_title';
	}
	if ( $sort_field == '_mbdb_series_order' || $sort_field == '_mbdb_published' ) {
		$sort_field = 'meta_value_num';
	}
		
	$args = array('posts_per_page' => -1,
					'post_type' => 'mbdb_book',
					'post_status'=>	'publish',
			);
	
	// if genre ids and series ids are passed,
	// add a filter for them
	// if 0 is passed, add a filter for NOT IN
	$series_tax_query = mbdb_set_tax_query( $series_ids, 'mbdb_series', $sort_field, $meta_key );
	
	$genre_tax_query = mbdb_set_tax_query( $genre_ids, 'mbdb_genre', $sort_field, $meta_key );
	if ( $series_tax_query != null ) {
		$args['tax_query'][] = $series_tax_query;
	}
	if ( $genre_tax_query != null ) {
		$args['tax_query'][] = $genre_tax_query;
	}
	if ( array_key_exists( 'tax_query', $args ) ) {
		if ( count( $args['tax_query'] ) > 1 ) {
			$args['tax_query']['relation'] = 'AND';
		}
	}
	
	// if custom ids are passed,
	// add a filter for them
	if( $selection_ids ) {
		$args['post__in'] = $selection_ids;
	}
	
	
	// based on selection:
	// all
	//	- no filters
	//	- if sorted by date, will need to get books w/ dates and w/o

	// published
	//	- pubdate <= today
	if ($selection == 'published') {
		$args['meta_query'] = array(
						array(
							'key'	=>	'_mbdb_published',
							'value'	=>	date('Y/m/d'),
							'compare'	=> '<=',
						),
					);
	}
	
	// unpublished
	//	- pubdate > today OR pubdate is blank
	if ($selection == 'unpublished' ) {
		$args['meta_query'] = array(
								'relation' => 'OR',
								array(
									'key'	=>	'_mbdb_published',
									'value'	=>	date('Y/m/d'),
									'compare'	=> '>',
								),
								 array(
									'compare' => 'NOT EXISTS',
									'value' => 'bug #23268',
									'key' => '_mbdb_published'
								),								
							);
	}
	
	// custom
	//	- ids filter already put in
	//	- if sorted by date, will need to get books w/ dates and w/o
	
	// series
	//	- series filter already put in
	//	- if sorted by date, will need to get books w/ dates and w/o
	
	// genre
	//	- genre filter already put in
	//	- if sorted by date, will need to get books w/ dates and w/o
	
	// if not published AND sorted by date, will need to get books w/ dates and w/o
	if ( ( $selection != 'published' ) && $meta_key == '_mbdb_published' ) {
		// copy in the base args
		$args2 = $args;
		// get books w/ no published date
		$args2['meta_query'] = array( array(
										'compare' => 'NOT EXISTS',
										'value' => 'bug #23268',
										'key' => '_mbdb_published',
								),								
							);
	}
	
	// if ordered by series grab the books where series order is empty
	if ( $meta_key == '_mbdb_series_order' && $series_ids != '0' ) {
		// move the existing meta_query to the inner condition
		$args3 = $args;
		if ( array_key_exists('meta_query', $args ) ) {
			$inner_query = $args['meta_query']; 
		} else {
			$inner_query = null;
		}
		
		$args3['meta_query'] = array(
							'relation' => 'AND',
							array(
								'compare' => 'NOT EXISTS',
								'value' => 'bug #23268',
								'key' => '_mbdb_series_order',
							), 
							$inner_query
						);
	}
	
	// add in sort args
	// if NOT ordered by series and these are stand alones
	if ( $series_ids != '0' ) {	
		$args['orderby'] = $sort_field;
		if ($meta_key!= '' ) {
			$args['meta_key'] = $meta_key;
		}
		$args['order'] = $sort_order;
	} 
	
	
	$books = get_posts( apply_filters('mbdb_get_books_main_query', $args ) );
	
	if ( isset( $args2 ) ) {
	
	
		$books2 = get_posts( apply_filters('mbdb_get_books_blank_pubdate', $args2 ) );
		if ( $sort_order == 'ASC' ) {
			// oldest first, so put blanks at the end
			$books = array_merge( $books, $books2 );	
		} else {
			// newest first, so put blanks at the beginning
			$books = array_merge( $books2, $books );
		}
	}
	
	if ( isset( $args3 ) ) {

		$books3 = get_posts( apply_filters( 'mbdb_get_books_blank_series_order', $args3 ) );
		$books = array_merge( $books, $books3 );
	}
	
	wp_reset_postdata();

	return apply_filters('mbdb_get_books_list', $books);
}

	
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

function mbdb_set_tax_query($tax_ids, $taxonomy) {
	// if taxids=null that means all books regardless of taxonomy
	// if taxids=0 that means all books NOT assigned tis taxonomy
	// others, get books in specified taxonomies
	if ( $tax_ids == null ) {
		return null;
	}
	if ( $tax_ids == '0' ) {
			$operator = 'NOT IN';
			$tax_ids = get_terms( $taxonomy, 'fields=ids&hide_empty=1' );   
	} else {
			$operator = 'IN';
	}
	return apply_filters('mbdb_tax_query', array(
			'taxonomy' => $taxonomy,
			'terms' => $tax_ids,
			'operator' => $operator,
		));
}

function mbdb_get_books_in_taxonomy( $tax_slug, $taxonomy ) {
	if ( $taxonomy == 'mbdb_series' ) {
		$sort_field = 'meta_value_num';
		$meta_key = '_mbdb_series_order';
	} else {
		$sort_field = 'title';
		$meta_key = '';
	}
	
	$args = array(
					'posts_per_page' => -1,
					'post_type' => 'mbdb_book',
					'post_status' => 'publish',
					'meta_key' => $meta_key,
					'orderby' => $sort_field,
					'order' => 'ASC',
					'tax_query' => array(
							array(
								'taxonomy' => $taxonomy,
								'terms' => $tax_slug,
								'field' => 'slug',
							)
						)
					);
	$books = get_posts( apply_filters('mbdb_books_by_tax_slug', $args) );
	
	// if a series, get the ones w/o a series order
	if ( $taxonomy == 'mbdb_series' ) {
		unset($args['meta_key']);
		unset($args['orderby']);
		unset($args['order']);
		$args['meta_query'] = array(					
							array('compare' => 'NOT EXISTS',
								'value' => 'bug #23268',
								'key' => '_mbdb_series_order'));					
		$books = array_merge( $books, get_posts( apply_filters('mbdb_books_by_tax_slug_blank_series_order', $args) ) );
	}
	return apply_filters('mbdb_get_books_in_taxonomy', $books);
}

// if it's the last element and both sides of the check are empty, ignore the error
// because CMB2 will automatically delete it from the repeater group
function mbdb_allow_blank_last_elements( $field1, $field2, $fieldname, $key, $flag ) {
	if ( !$field1 && !$field2 ) {
		end( $_POST[$fieldname] );
		if ( $key === key( $_POST[$fieldname] ) ) {
			return false;
		}
	}
	return $flag;
}
	
function mbdb_validate_reviews( $field ) {
	do_action('mbdb_before_validate_reviews', $field);
	$flag = false;
	foreach( $_POST['_mbdb_reviews'] as $reviewID => $review ) {	
		// if the review doesn't exist, then the others can't exist either
		// but if review does exist, then at least one of the others has to also
		// set flag = true if validation fails
		$is_others = (mbdb_check_field('mbdb_reviewer_name', $review) || mbdb_check_field('mbdb_review_url', $review) || mbdb_check_field('mbdb_review_website', $review));
		$is_review = mbdb_check_field('mbdb_review', $review );
		$flag = !($is_review && $is_others);
		
		// if it's the last element and both sides of the check are empty, ignore the error
		// because CMB2 will automatically delete it from the repeater group
		$flag = mbdb_allow_blank_last_elements( $is_review, $is_others, '_mbdb_reviews', $reviewID, $flag);
		
		if ($flag) { break; }
	}
	do_action('mbdb_validate_reviews_before_msg', $field, $flag, $review);
	mbdb_msg_if_invalid( $flag, '_mbdb_reviews', $review, apply_filters('mbdb_validate_reviews_msg', __('Reviews require review text and at least one other field. Please check review #%s.', 'mooberry-book-manager')) );
	do_action('mbdb_validate_reviews_after_msg', $field, $flag, $review);
	return mbdb_sanitize_field( $field);
}

function mbdb_validate_downloadlinks( $field ) {
	mbdb_validate_book_fields( '_mbdb_downloadlinks', '_mbdb_formatID', '_mbdb_downloadlink', __('Download links require all fields filled out. Please check download link #%s.', 'mooberry-book-manager'));
	return mbdb_sanitize_field($field);
}

function mbdb_validate_retailers( $field ) {
	mbdb_validate_book_fields( '_mbdb_buylinks', '_mbdb_retailerID', '_mbdb_buylink', __('Retailer links require all fields filled out. Please check retailer link #%s.', 'mooberry-book-manager'));
	return mbdb_sanitize_field( $field );
}

function mbdb_validate_book_fields( $groupname, $fieldIDname, $fieldname, $message) {
	do_action('mbdb_before_validate' . $groupname);
	$flag = false;
	foreach($_POST[$groupname] as $key => $group) {
		// both fields must be filled in
		$is_field1 = mbdb_check_field($fieldIDname, $group ) && $group[$fieldIDname] != '0';
		$is_field2 = mbdb_check_field( $fieldname, $group );
		$flag = !($is_field1 && $is_field2);
		
		// if it's the last element and both sides of the check are empty, ignore the error
		// because CMB2 will automatically delete it from the repeater group
		$flag = mbdb_allow_blank_last_elements( $is_field1, $is_field2, $groupname, $key, $flag);
		
		if ( $flag ) { break; }
	}
	do_action('mbdb_validate' . $groupname . '_before_msg', $flag, $group);
	mbdb_msg_if_invalid( $flag, $groupname, $group, apply_filters('mbdb_validate' . $groupname . '_msg', $message));
	do_action('mbdb_validate' . $groupname . '_after_msg', $flag, $group);
}

function mbdb_msg_if_invalid( $flag, $fieldname, $group, $message ) {
	 // on attempting to publish - check for completion and intervene if necessary
    if ( ( isset( $_POST['publish'] ) || isset( $_POST['save'] ) ) && $_POST['post_status'] == 'publish' ) {
	    //  don't allow publishing while any of these are incomplete
        if ( $flag ) {
            // set the message
			$itemID = array_search( $group, $_POST[$fieldname] );
			$itemID++;
			mbdb_error_message(sprintf( $message, $itemID ));
	    }
    }
}

function mbdb_check_field( $fieldname, $arrayname) {
	return ( array_key_exists($fieldname, $arrayname ) && isset( $arrayname[$fieldname] ) && trim( $arrayname[$fieldname] ) != '');
}


function mbdb_format_date($field) {
		if ($field == null or $field == '') {
			return $field;
		}
		return apply_filters('mbdb_format_date', date( 'Y/m/d', strtotime( $field ) ));
}
	
function mbdb_error_message(  $message ) {
	 // set the message
	$notice = get_option( 'mbdb_notice' );
	$notice[$_POST['post_ID']] = $message;
	update_option( 'mbdb_notice', $notice);
	
	// change it to pending not updated
	global $wpdb;
	$wpdb->update( $wpdb->posts, array( 'post_status' => 'draft' ), array( 'ID' => $_POST['post_ID'] ) );
	// filter the query URL to change the published message
	add_filter( 'redirect_post_location', create_function( '$location', 'return esc_url_raw(add_query_arg("message", "0", $location));' ) );
}

function mbdb_sanitize_field( $field ) {
	return strip_tags( stripslashes( $field ) );
}

function mbdb_get_retailers() {
	return mbdb_get_list( 'retailers' );
}
	
function mbdb_get_formats() {
	return mbdb_get_list( 'formats' );
}
	
function mbdb_get_list( $options_key ) {
	$mbdb_options = get_option( 'mbdb_options' );
	$list[0] = '';
	
	if ( $mbdb_options ) {
		if ( array_key_exists( $options_key, $mbdb_options ) ) {
			foreach( $mbdb_options[$options_key] as $o ) {
				$list[$o['uniqueID']] = $o['name'];
			}
			// natural sort, case insensitive
			natcasesort($list);
		}
	}
	
	return apply_filters('mbdb_' . $options_key . '_list', $list);
}	
	
function mbdb_get_book_array() {
	$book_query = mbdb_get_books_list( 'all', null, 'title', 'ASC', null, null );
	$books = array();
	foreach( $book_query as $book ) {
		$books[$book->ID] = $book->post_title;
	}
	return apply_filters('mbdb_get_book_array', $books);
}
	

function mbdb_get_book_dropdown( $selected_bookID ) {
	$book_query = apply_filters('mbdb_get_book_downdown_list', mbdb_get_books_list( 'all', null, 'title', 'ASC', null, null ) );
	foreach( $book_query as $book ) {
		$book_title = $book->post_title;
		$book_id = $book->ID;
		$selected = ($selected_bookID == $book_id ? ' selected' : '');
		echo apply_filters('mbdb_get_book_dropdown_option', '<option value="' . esc_attr($book_id) .'"' . $selected . '>' . esc_html($book_title) . '</option>');
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
	if ($mdbd_options == null) {
		$mbdb_options = get_option('mdbd_options');
	}
	if (!isset($mbdb_options['mbdb_default_unit'])) {
		return 'in';
	} 
	return $mbdb_options['mbdb_default_unit'];
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
		'HUF'   => __('Hungarian Forint ', 'mooberry-book-manager'),
		'ILS'   => __('Israeli New Sheqel', 'mooberry-book-manager'),
		'JPY'   => __('Japanese Yen', 'mooberry-book-manager'),
		'MYR'   => __('Malaysian Ringgit', 'mooberry-book-manager'),
		'MXN'   => __('Mexican Peso', 'mooberry-book-manager'),
		'NOK'   => __('Norwegian Krone', 'mooberry-book-manager'),
		'NZD'   => __('New Zealand Dollar', 'mooberry-book-manager'),
		'PHP'   => __('Philippine Peso', 'mooberry-book-manager'),
		'PLN'   => __('Polish Zloty', 'mooberry-book-manager'),
		'SGD'   => __('Singapore Dollar', 'mooberry-book-manager'),
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
		'ILS'   => '₪',
		'JPY'   => '¥',
		'MYR'   => 'RM',
		'MXN'   => '$',
		'NOK'   => 'kr',
		'NZD'   => '$',
		'PHP'   => '₱',
		'PLN'   => 'zł',
		'GBP'   => '£',
		'SGD'   => '$',
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
	if ($mdbd_options == null) {
		$mbdb_options = get_option('mdbd_options');
	}
	if (!isset($mbdb_options['mbdb_default_currency'])) {
		return 'USD';
	} 
	return $mbdb_options['mbdb_default_currency'];
}

function mbdb_get_default_language( $mbdb_options = null) {
	if ($mdbd_options == null) {
		$mbdb_options = get_option('mdbd_options');
	}
	if (!isset($mbdb_options['mbdb_default_language'])) {
		return 'EN';
	} 
	return $mbdb_options['mbdb_default_language'];
}




function mbdb_set_up_roles() {
		
		$contributor_level = array('edit_mbdb_books',
									'edit_mbdb_book',
									'delete_mbdb_books',
									'delete_mbdb_book');
									
		$base_level = array(		'publish_mbdb_books',
									'publish_mbdb_book',
									'edit_published_mbdb_book',
									'edit_published_mbdb_books',
									'delete_published_mbdb_book',
									'delete_published_mbdb_books',
									'upload_files',
									'manage_mbdb_books',
									'read');
									
		$master_level = array(		'edit_others_mbdb_books',
									'edit_others_mbdb_books',
									'delete_others_mbdb_books',
									'delete_others_mbdb_book');
		
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


/***************************************
	
			Update functions

****************************************/

function mbdb_upgrade_to_1_3_1() {
	$default_retailers = array();
	$default_retailers[] = array('name' => 'Audible', 'uniqueID' => 6, 'image' => 'audible.png' );
	$default_retailers[] = array('name' => 'Book Baby', 'uniqueID' => 7, 'image' => 'bookbaby.gif' );
	$default_retailers[] = array('name' => 'Books A Million', 'uniqueID' => 8, 'image' => 'bam.png' );
	$default_retailers[] = array('name' => 'Create Space', 'uniqueID' => 9, 'image' => 'createspace.jpg' );
	$default_retailers[] = array('name' => 'Indie Bound', 'uniqueID' => 10, 'image' => 'indiebound.gif' );
	$default_retailers[] = array('name' => 'Powells', 'uniqueID' => 11, 'image' => 'powells.jpg' );
	$default_retailers[] = array('name' => 'Scribd', 'uniqueID' => 12, 'image' => 'scribd.jpg' );
	$default_retailers[] = array('name' => 'Amazon Kindle', 'uniqueID' => 13, 'image' => 'kindle.jpg' );
	$default_retailers[] = array('name' => 'Barnes and Noble Nook', 'uniqueID' => 14, 'image' => 'nook.png' );
	$mbdb_options = get_option( 'mbdb_options' );
	mbdb_insert_defaults( $default_retailers, 'retailers', $mbdb_options);
	update_option( 'mbdb_options',  $mbdb_options );
}
	
function mbdb_upgrade_to_2_0() {
	// set all pages with a book grid to NOT use the default values
	mbdb_migrate_to_book_grid_defaults();
	
	// set up roles
	mbdb_set_up_roles();
	
	// migrate post_tags to mbdb_tags
	mbdb_migrate_post_tags();
			
			
}

function mbdb_migrate_post_tags() {
		
	//loop through all terms in post_tags
	$post_tags = get_terms('post_tag');
	foreach($post_tags as $tag) {
		// get all objects in each term
		$tagged_posts = get_objects_in_term((int) $tag->term_id, 'post_tag');
		// loop through the objects
		foreach($tagged_posts as $tagged_post) {
			// if one is a book
			if (get_post_type($tagged_post) == 'mbdb_book') {
				// add the term to mbdb_tags
				// if term has already been added, get the ID
				$new_term = term_exists($tag->name, 'mbdb_tag');
				// otherwise insert it
				if ($new_term == 0 || $new_term == null) {					
					$new_term = wp_insert_term($tag->name, 'mbdb_tag', array(
									'description' => $tag->description,
									'slug'	=>	$tag->slug)	);
				}
			
				// add the object to mbdb_tags term
				wp_set_object_terms($tagged_post, (int) $new_term['term_id'], 'mbdb_tag', true);
			}
		}
	}

	// remove post_tag terms from books
	// do this outside of the above loop because it will remove ALL tags from the books
	// and the above loop handles one tag at a time
	// $mbdb_books = mbdb_get_books_list( 'all', null, 'title', 'ASC', null, null );
	foreach($mbdb_books as $mbdb_book) {
		$bookID = $mbdb_book->ID;
		wp_delete_object_term_relationships( $bookID, 'post_tag' );
	}
}

function mbdb_migrate_to_book_grid_defaults() {
	$grid_pages = get_posts(array(
								'posts_per_page' => -1,
								'post_type' => 'page',
								'meta_query'	=>	array(
										array(
											'key'	=>	'_mbdb_book_grid_display',
											'value'	=>	'yes',
											'compare'	=>	'=',
										),
									),	
							)
					);
	foreach($grid_pages as $page) {
		update_post_meta($page->ID, '_mbdb_book_grid_cover_height_default', 'no');
		update_post_meta($page->ID, '_mbdb_book_grid_books_across_default', 'no');
	}
	wp_reset_postdata();
					
	// set the default values
	$mbdb_options = get_option('mbdb_options');
	
	if (!isset($mbdb_options['mbdb_default_cover_height'])) {
		$mbdb_options['mbdb_default_cover_height'] = 200;
	}
	if (!isset($mbdb_options['mbdb_default_books_across'])) {
		$mbdb_options['mbdb_default_books_across'] = 3;
	}
	
	update_option('mbdb_options', $mbdb_options);
	
}