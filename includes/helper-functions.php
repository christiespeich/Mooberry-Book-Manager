<?php
	
function mbdb_get_default_page_layout() {
	return apply_filters('mdbd_default_book_page', '[book_cover width="200" align="right"][book_summary blank="Summary Coming Soon!"]
				<strong>Order Now:</strong> [book_buylinks  align="horizontal" size="35" blank="Coming Soon!"]
				[book_goodreads  ]
				
				<strong>Published:</strong> [book_published format="short" blank="TBA"]
				<strong>Publisher:</strong> [book_publisher  blank="TBA"]
				<strong>Number of Pages:</strong> [book_length  blank="TBD"]
				<strong>Genres:</strong><span>[book_genre delim="comma" blank="(uncategorized)"]</span>
				<strong>Tags:</strong><span> [book_tags  delim="comma" blank="(none)"]</span>

				[book_serieslist before="Part of the " after=" series: " delim="list"]
				<strong>Reviews:</strong><span> [book_reviews  blank="Coming Soon!"]</span>
				<strong>Excerpt:</strong><span> [book_excerpt  blank="Coming Soon!"]</span>

				<strong>Order Now:</strong> [book_buylinks  align="horizontal" size="30" blank="Coming Soon!"]');
	
	
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
		$args['meta_key'] = $meta_key;
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
	mbdb_msg_if_invalid( $flag, '_mbdb_reviews', $review, apply_filters('mbdb_validate_reviews_msg', 'Reviews require review text and at least one other field. Please check review #%s.') );
	do_action('mbdb_validate_reviews_after_msg', $field, $flag, $review);
	return mbdb_sanitize_field( $field);
}

function mbdb_validate_downloadlinks( $field ) {
	mbdb_validate_book_fields( '_mbdb_downloadlinks', '_mbdb_formatID', '_mbdb_downloadlink', 'Download links require all fields filled out. Please check download link #%s.');
	return mbdb_sanitize_field($field);
}

function mbdb_validate_retailers( $field ) {
	mbdb_validate_book_fields( '_mbdb_buylinks', '_mbdb_retailerID', '_mbdb_buylink', 'Retailer links require all fields filled out. Please check retailer link #%s.');
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
	add_filter( 'redirect_post_location', create_function( '$location', 'return add_query_arg("message", "0", $location);' ) );
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



?>