<?php

// if it's the last element and both sides of the check are empty, ignore the error
// because CMB2 will automatically delete it from the repeater group
function mbdb_allow_blank_last_elements( $field1, $field2, $fieldname, $key, $flag ) {
	if ( !$field1 && !$field2 ) {
		// to the end of the array
		end( $_POST[$fieldname] );
		if ( $key === key( $_POST[$fieldname] ) ) {
			return false;
		}
	}
	return $flag;
}


function mbdb_validate_editions( $field ) {
	do_action('mbdb_before_validate_editions', $field);
	$flag = false;
	foreach( $_POST['_mbdb_editions'] as $editionID => $edition) {
		$message = __('Editions require at least the format. Please check edition #%s.', 'mooberry-book-manager');
		// if any field is filled out, format must be selected
		// only the format field is required
		// set flag = true if validation fails
		$width = mbdb_check_field('_mbdb_width', $edition) ;
		$height = mbdb_check_field('_mbdb_height', $edition);
		$is_format = mbdb_check_field('_mbdb_format', $edition) && $edition['_mbdb_format'] != '0';
		$is_others = (mbdb_check_field('_mbdb_isbn', $edition) || mbdb_check_field('_mbdb_length', $edition) || $width || $height || mbdb_check_field('_mbdb_retail_price', $edition) || mbdb_check_field('_mbdb_edition_title', $edition));
		$flag = !$is_format;
		
		// if width or height is filled in, the other one must be also
		if (($width || $height) && !($width && $height)) {
			$flag = true;
			$message =  __('If width or height is specified, both must be. Please check edition #%s.', 'mooberry-book-manager');
		}
		
		// if it's the last element and both sides of the check are empty, ignore the error
		// because CMB2 will automatically delete it from the repeater group
		$flag = mbdb_allow_blank_last_elements( $is_format, $is_others, '_mbdb_editions', $editionID, $flag);
		
		if ($flag) { break; }
	}
	do_action('mbdb_validate_editions_before_msg', $field, $flag, $edition);
	mbdb_msg_if_invalid( $flag, '_mbdb_editions', $edition, apply_filters('mbdb_validate_editions_msg', $message) );
	do_action('mbdb_validate_editions_after_msg', $field, $flag, $edition);
	return mbdb_sanitize_field( $field);
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
	
function mbdb_error_message( $message ) {
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