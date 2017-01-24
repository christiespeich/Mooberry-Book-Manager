<?php
/**
 *  This file contains code relating to the admin screens for the Custom
 *  Post Type mbdb_book
 *  
 *  Includes generating the columns on the book list and setting up the metaboxes
 *  
 */

 $mbdb_edit_book = null;


/*****************************************************************************
	COLUMNS
*****************************************************************************/

/**
 *  @since 1.0
 *  
 *  set up the columns on the book list page 
 * 
 * 	param [array] $columns 	The columns WP sets by default
 *
 * returns [array] columns to display in the list
 */
add_filter( 'manage_edit-mbdb_book_columns', 'set_up_mbdb_book_columns' );
function set_up_mbdb_book_columns( $columns ) {
	$columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => __( 'Title', 'mooberry-book-manager' ),
		'cover' => __( 'Cover', 'mooberry-book-manager' ),
		'mbdb_genre' => __( 'Genre', 'mooberry-book-manager' ),
		'release_date' => __( 'Published', 'mooberry-book-manager' ),
		'mbdb_series' => __( 'Series', 'mooberry-book-manager'),
		'series_order' => __( 'Series Order', 'mooberry-book-manager' ), 
		'publisher_id' => __( 'Publisher', 'mooberry-book-manager' ),
		'date' => __('Updated', 'mooberry-book-manager' )
	);
	return apply_filters( 'mbdb_book_columns', $columns );
}


/**
 *  
 * Fills the columns in the book list with data  
 *  
 *  
 *  @since 1.0
 *  @param [string] $column specific column to populate
 *  @param [int] $post_id id of specific row to populate
 *  
 *  @return nothing. data is printed directly to screen
 *  
 *  @since 3.3	Added divs around output to support quick/bulk editing
 *  			Used date format from options to format release date
 *  
 *  @access public
 */
add_action( 'manage_mbdb_book_posts_custom_column', 'populate_mbdb_book_columns', 10, 2 );
function populate_mbdb_book_columns( $column, $post_id ) {
	
	// get the book for the current row
	$book = MBDB()->books->get( $post_id );	
	
	// if the book is invalid, exit
	if ( $book == null ) {
		return;
	}
	
	// if this is the genre or series column (taxonomies)
	// just get the term list and display that delimited by commas
	// and then exit. No further processing necessary
	if ( $column == 'mbdb_genre' || $column == 'mbdb_series' ) {
		do_action( 'mbdb_book_pre_' . $column . '_column' );
		echo apply_filters( 'mbdb_book_' . $column . '_column', get_the_term_list( $post_id, $column, '' , ', '  ) );
		do_action( 'mbdb_book_post_' . $column . '_column' );
		return;
	}

	// if the column is a property of book, grab that data
	if ( property_exists( $book, $column ) ) {
		$data = $book->{$column};
	} else {
		$data = '';
	}
	
	switch ($column) {
		// book cover: display as an image
		case 'cover':
			do_action( 'mbdb_book_pre_mbdb_cover_column', $column, $data, $book );
			if ( $data != '' ) {
				// 3.4.4 -- uses get_attachemnt_image_src
				$image_src = '';
				$image_id = $book->cover_id;
				$attachment_src = wp_get_attachment_image_src ( $image_id, 'medium' );
				if ( $attachment_src !== false) {
					$image_src = $attachment_src[0];
				}
				$alt = mbdb_get_alt_text( $image_id, __('Book Cover:', 'mooberry-book-manager') . ' ' . get_the_title($post_id) );
				echo apply_filters( 'mbdb_book_mbdb_cover_column', '<img src="' . esc_url($image_src) . '" width="100" ' . $alt . ' />', $column, $data, $book );
			}
			do_action('mbdb_book_post_mbdb_cover_column', $column, $data, $book );
			break;
		// release date: use short format
		case 'release_date':
			// output 2 hidden fields for fields we want to be able to quick edit even if they aren't shown
			echo '<span id="subtitle-' . $post_id . '" style="display:none;">' . $book->subtitle . '</span>';
			echo '<span id="goodreads-' . $post_id . '" style="display:none;">' . $book->goodreads . '</span>';
			do_action('mbdb_book_pre_mbdb_published_column', $column, $data, $book );
			echo '<div id="release_date-' . $post_id . '">';
			if ( !empty( $data ) ) {
				$format = get_option('date_format');
				echo apply_filters( 'mbdb_book_mbdb_published_column', 	date_i18n($format, strtotime($data)), $column, $data, $book );
				echo '<span id="release_date_format-' . $post_id . '" style="display:none;">' . date('Y-m-d', strtotime( $data)) . '</span>';
				
			}
			echo '</div>';
			
			do_action( 'mbdb_book_post_mbdb_published_column', $column, $data, $book );
			break;
		// publisher: display publisher name
		// does not come from book object
		case 'publisher_id':
			$publisher = mbdb_get_publisher_info( $data );
			do_action('mbdb_book_pre_mbdb_publisher_column', $column, $publisher, $book );
			echo '<div id="publisher-' . $post_id . '">';
			echo apply_filters('mbdb_book_mbdb_publisher_column', $publisher['name'], $column, $publisher, $book );
			echo '<span id="publisher_id-' . $post_id . '" style="display:none;">' . $data . '</span>';
			echo '</div>';
			do_action('mbdb_book_post_mbdb_publisher_column', $column, $publisher, $book );
			break;
		case 'series_order':
			do_action('mbdb_book_pre_mbdb_' . $column . '_column', $column, $data, $book );
			echo '<div id="' . $column . '-' . $post_id . '">';
			if ( $data != '' ) {
				$data = floatval( $data );
			}
			echo apply_filters('mbdb_book_mbdb_' . $column . '_column', $data, $book, $post_id, $column );
			echo '</div>';
			do_action('mbdb_book_post_mbdb_' . $column . '_column', $column, $data, $book );
			break; 
		default:
			do_action('mbdb_book_pre_mbdb_' . $column . '_column', $column, $data, $book );
			echo '<div id="' . $column . '-' . $post_id . '">';
			echo apply_filters('mbdb_book_mbdb_' . $column . '_column', $data, $book, $post_id, $column );
			echo '</div>';
			do_action('mbdb_book_post_mbdb_' . $column . '_column', $column, $data, $book );
	}	
}




/**
 *  
 *  Adds Quick/Bulk Edit options to Books menu
 *  
 *  @param [in] $column_name 	The column being edited
 *  @param [in] $post_type 		The post type
 *  
 *  @since 	3.3
 *  
 */
add_action( 'bulk_edit_custom_box', 'mbdb_bulk_edit_books', 1, 2 );
function mbdb_bulk_edit_books( $column_name, $post_type, $quick_edit = false ) {
	if ( $post_type !== 'mbdb_book' ) {
		return;
	}
	
	switch ($column_name) {
		case 'publisher_id':
			if ( $quick_edit ) {
				$publishers = mbdb_get_publishers();
			} else {
				$publishers = array(
									'0'			=>	__('— No Change —', 'mooberry-book-manager'),
									'-1'		=>	__('— No Publisher —', 'mooberry-book-manager'),
								) + mbdb_get_publishers( 'no' );
			}
			
	?>
		<fieldset class="inline-edit-col-left">
						<div class="inline-edit-col">
							<label>
								<span class="title"><?php _e('Publisher', 'mooberry-book-manager'); ?></span>
								<span class="input-text-wrap">
									<?php echo mbdb_dropdown('_mbdb_publisherID', $publishers, '0', 'no',  -1, '_mbdb_publisherID' ); ?>
								</span>
							</label>
						</div>
					</fieldset>
		<?php
			break;
	}
	
}

add_action( 'quick_edit_custom_box', 'mbdb_quick_edit_books', 1, 2 );	
function mbdb_quick_edit_books( $column_name, $post_type ) {
	if ( $post_type !== 'mbdb_book' ) {
		return;
	}
	
	mbdb_bulk_edit_books( $column_name, $post_type, true );
	
	switch ($column_name) {
		
		case 'release_date':
	?>
		<fieldset style="width:50%;">
						<div class="inline-edit-col">
							<label>
								<span class="title"><?php _e('Subtitle', 'mooberry-book-manager'); ?></span>
								<span class="input-text-wrap">
									<input type="text" value="" id="_mbdb_subtitle" name="_mbdb_subtitle"></span>
							</label>
						</div>
		</fieldset>
		<fieldset style="width:50%;">
						<div class="inline-edit-col">
							<label>
								<span class="title"><?php _e('Goodreads', 'mooberry-book-manager'); ?></span>
								<span class="input-text-wrap">
									<input type="text" pattern="^(https?:\/\/)?www.goodreads.com.*" value="" id="_mbdb_goodreads" name="_mbdb_goodreads"></span>
							</label>
						</div>
		</fieldset>
		<fieldset style="width:50%;">
						<div class="inline-edit-col">
							<label>
								<span class="title"><?php _e('Release Date', 'mooberry-book-manager'); ?></span>
								<span class="input-text-wrap">
									<input type="text" value="" id="_mbdb_published" name="_mbdb_published" style="width:10em;">
								(<strong>mm/dd/yyyy</strong> or <strong>dd-mm-yyyy</strong> or <strong>yyyy-mm-dd</strong>)</span>
							</label>
						</div>
					</fieldset>
	<?php
			break;
		case 'series_order':
	?>
		<fieldset style="width:50%;" >
						<div class="inline-edit-col">
							<label>
								<span class="title"><?php _e('Series Order', 'mooberry-book-manager'); ?></span>
								<span class="input-text-wrap">
									<input type="number" value="" step="any" min="0" name="_mbdb_series_order" id="_mbdb_series_order" style="width:3.5em;">
								</span>
							</label>
						</div>
					</fieldset>
	<?php
			break; 
		
	}
}

/**
 * Saving your 'Quick Edit' data is exactly like saving custom data
 * when editing a post, using the 'save_post' hook. With that said,
 * you may have already set this up. If you're not sure, and your
 * 'Quick Edit' data is not saving, odds are you need to hook into
 * the 'save_post' action.
 *
 * The 'save_post' action passes 2 arguments: the $post_id (an integer)
 * and the $post information (an object).
 */
add_action( 'save_post', 'mbdb_quick_edit_save_post', 10, 2 );
function mbdb_quick_edit_save_post( $post_id, $post ) {
	// pointless if $_POST is empty (this happens on bulk edit)
	if ( empty( $_POST ) )
		return $post_id;
		
	// verify quick edit nonce
	if ( isset( $_POST[ '_inline_edit' ] ) && ! wp_verify_nonce( $_POST[ '_inline_edit' ], 'inlineeditnonce' ) )
		return $post_id;
			
	// don't save for autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return $post_id;
		
	// dont save for revisions
	if ( isset( $post->post_type ) && $post->post_type == 'revision' )
		return $post_id;
	
	if ($post->post_type !== 'mbdb_book') {
		return $post_id;
	}
	
	global $mbdb_edit_book;
	
	// format published date
	if ( array_key_exists( '_mbdb_published', $_POST ) ) {
		if ($_POST['_mbdb_published'] != '') {
			$published = strtotime($_POST['_mbdb_published']);
		
			if ($published !== false) {
				$_POST['_mbdb_published'] = date('Y-m-d',$published);
			}
		}
	}
	
	$custom_fields = array( '_mbdb_published', '_mbdb_series_order', '_mbdb_publisherID', '_mbdb_subtitle', '_mbdb_goodreads' );
	
	foreach( $custom_fields as $field ) {
		if ( array_key_exists( $field, $_POST ) ) {
			if (MBDB()->books->in_custom_table( $field ) ) {
				$mbdb_edit_book[$field] = $_POST[ $field ];
			} else {
				update_post_meta( $post_id, $field, sanitize_text_field($_POST[ $field ] ));
			}
		}
	}

	MBDB()->books->save( $mbdb_edit_book, $post_id );	
}

/**
 * Saving the 'Bulk Edit' data is a little trickier because we have
 * to get JavaScript involved. WordPress saves their bulk edit data
 * via AJAX so, guess what, so do we.
 *
 * Your javascript will run an AJAX function to save your data.
 * This is the WordPress AJAX function that will handle and save your data.
 */
add_action( 'wp_ajax_bulk_quick_save_bulk_edit', 'mbdb_save_bulk_edit' );
function mbdb_save_bulk_edit() {
	// we need the post IDs
	$post_ids = ( isset( $_POST[ 'post_ids' ] ) && !empty( $_POST[ 'post_ids' ] ) ) ? $_POST[ 'post_ids' ] : NULL;
		
	// if we have post IDs
	if ( empty( $post_ids ) || !is_array( $post_ids ) ) {
		return;
	}
	
	// get the custom fields
	$custom_fields = array( '_mbdb_publisherID' );
	
	global $mbdb_edit_book;
	
	// update for each post ID
	foreach( $post_ids as $post_id ) {
		foreach( $custom_fields as $field ) {
			// if it has a value, doesn't update if empty on bulk
			if ( isset( $_POST[ $field ] ) && !empty( $_POST[ $field ] ) ) {			
				if (MBDB()->books->in_custom_table( $field ) ) {
					$mbdb_edit_book[$field] = $_POST[ $field ];
				} else {
					update_post_meta( $post_id, $field, sanitize_text_field( $_POST[ $field ] ) );
				}
			}
			
		}
		MBDB()->books->save( $mbdb_edit_book, $post_id );	
	}
	
	
}


	

	

/**********************************************************
 * 
 * Saving the book post
 *
 *******************************************************/

/**
 *  Set the book's excerpt to a portion of the summary
 *  Also make sure the post_content has the shortcode
 *  
 *  
 *  @since 
 *  @since 3.0 Added shortcode
 *  
 *  @param [int] $post_id id of post being saved
 *  @param [object] $post    post object of post being saved
 * 
 *  
 *  @access public
 */
add_action('save_post_mbdb_book', 'mbdb_save_book');
 function mbdb_save_book( $post_id, $post = null ) {
	 
	// if the post object is null then we are creating a new book
	// and must pull values from the GET/POST vars ???
	 if ( $post == null ) {
		if ( array_key_exists('_mbdb_summary', $_POST ) && $_POST['_mbdb_summary'] ) {
			$summary = $_POST['_mbdb_summary'];
		} elseif ( array_key_exists('_mbdb_summary', $_GET ) && $_GET['_mbdb_summary'] ) {
			$summary = $_GET['_mbdb_summary'];
		} else {
			$summary = '';
		}
	} else {
		// the post has been saved already so pull from the database
		$book = MBDB()->books->get($post_id);
		if ( $book != null ) {
			$summary = $book->summary;
		} else {
			$summary = '';
		}
	}
	
	
	
	// unhook this function so it doesn't loop infinitely
	// and mbdb_save_book_custom_table so it doesn't run twice
	remove_action( 'save_post_mbdb_book', 'mbdb_save_book' );
	remove_action('save_post', 'mbdb_save_book_custom_table');

	// update the post, which calls save_post again
	wp_update_post( array( 'ID' => $post_id, 'post_excerpt' =>  strip_tags($summary), 'post_content' => '[mbdb_book]' ) );

	// re-hook this function and mbdb_save_book_custom_table 
	add_action( 'save_post_mbdb_book', 'mbdb_save_book' );	
	 add_action('save_post', 'mbdb_save_book_custom_table', 20);
}

 add_action('save_post', 'mbdb_save_book_custom_table', 20);
function mbdb_save_book_custom_table( $post_id, $post = null ) {
	
	if (!get_post_type() == 'mbdb_book') {
		return;
	}

	// save custom database fields to database
	global $mbdb_edit_book;
	
	
	MBDB()->books->save( $mbdb_edit_book, $post_id );

}	

/******************************************************************************
	SAVE DATA FOR META BOXES
******************************************************************************/

/**
 * Save the post meta data. Some goes in the custom table  
 *  and some goes in the post_meta table
 *  
 *  
 *  @since 3.0
 *  @param [string] $override Set to something else to override CMB2's saving
 *  @param [array] $a        arguments
 *  
 *  @return whether or not to override CMB2's saving procedure
 *  
 *  @access public
 */
 add_filter('cmb2_override_meta_remove', 'mbdb_save_meta_data', 10, 2);
 add_filter('cmb2_override_meta_save', 'mbdb_save_meta_data', 10, 2);
 function mbdb_save_meta_data( $override, $a ) {
	// if not a book post type, return what we got in
	if ( get_post_type() != 'mbdb_book' ) {
		return $override;
	}
	
	// v3.1 by adding meta_remove filter, now this is sometimes called without the value element
	// add the element as blank text
	// this is necessary (vs just exiting the function) because otherwise if a field is left blank
	// it won't get saved as such without the meta_remove filter
	if (!array_key_exists('value', $a)) {
		$a['value'] = null;
	}

	global $mbdb_edit_book;
	if (MBDB()->books->in_custom_table( $a['field_id'] ) ) {
		$mbdb_edit_book[$a['field_id']] = $a['value'];
		return 'override';
	} else {
		return $override;
	}
	
	
	/*
	$column = MBDB()->books->post_meta_to_column( $a['field_id'] );	
	if ($column !== false) {
		error_log('column ' . $column);
		$mbdb_edit_book[$column] = MBDB()->books->sanitize_field( $column, $a['value'] );
			
		return 'override';
	} else {
		return $override;
	}
	
	
	
	// returns false if $a['id'] is not valid book id or if $a['field_id'] is not a valid column in custom table
	// or if save fails at the datbase level for any reason
	// otherwise returns number of rows saved (which could be 0 so use ===/!== to test for false)
	$success = MBDB()->books->save_data_by_post_meta( $a['field_id'], $a['value'], $a['id'] );
	if ( $success !== false ) {
		return 'override';
	} 
	
	// if doesn't match the columns in the table, return what we got in
	// so that CMB2 handles the save as post_meta
	return $override;
	*/
}


	
/******************************************************************************
	RETRIEVE DATA FOR META BOXES
******************************************************************************/
/**
 *  Retrieves the data for the post meta fields
 *  either from the custom table or the post meta table
 *  
 *  
 *  @since 3.0
 *  @param [string] $override  whether to override CMB2's retrieval
 *  @param [string] $object_id id of post meta field we're getting
 *  @param [array] $a         args
 *  
 *  @return whether to override CMB2's retrieval
 *  
 *  @access public
 */
add_filter('cmb2_override_meta_value', 'mbdb_get_meta_data', 10, 3);
function mbdb_get_meta_data( $override, $object_id, $a) {
	// if not a book post type, return what we got in
	if ( get_post_type() != 'mbdb_book' ) {
		return $override;
	}
	

	
	// returns false if $object_id is not a valid book or field is not column
	// in custom table
	// otherwise returns book field data
	$book_data = MBDB()->books->get_data_by_post_meta( $a['field_id'], $object_id); //, $mbdb_edit_book );
	
	
	// only override the fields in the table
	if ( $book_data !== false ) {
		return $book_data;
	}
	
	// if doesn't match one of the columsn, return what we got in
	// so that CMB2 will retrieve the value from post meta
	return $override;
}



	
	
/**********************************************************************
	DISPLAY POST META BOXES
*********************************************************************/

// woocommerce
// add_action( 'add_meta_boxes', 'mbdb_integrate_wc' );
// function mbdb_integrate_wc() {
// add_meta_box( 'woocommerce-product-data', __( 'Product Data', 'woocommerce' ), 'WC_Meta_Box_Product_Data::output', 'mbdb_book', 'normal', 'low' );
	
// }

	
 

/**
 * Reorder the taxonomy boxes so that they are in the order genre, series, tag,  
 *  editor, illustrator, cover artist
 * and put the cover image above all the taxonomies
 *  
 *  @since 2.0
 *  
 *  
 *  @access public
 */
add_action('add_meta_boxes_mbdb_book', 'mbdb_reorder_taxonomy_boxes');
 function mbdb_reorder_taxonomy_boxes() {
	
    global $wp_meta_boxes;
	
	$taxonomies = array(  'tagsdiv-mbdb_tag', 'tagsdiv-mbdb_series', 'tagsdiv-mbdb_genre' );
	
	// remove the cover to be readded before the taxonomies
	$cover = $wp_meta_boxes['mbdb_book']['side']['default']['mbdb_cover_image_metabox'];
	unset( $wp_meta_boxes['mbdb_book']['side']['default']['mbdb_cover_image_metabox'] );
	
	foreach ( $taxonomies as $taxID ) {
		 $tax = $wp_meta_boxes['mbdb_book']['side']['core'][$taxID];
		 unset( $wp_meta_boxes['mbdb_book']['side']['core'][$taxID] );
		 
		 if (array_key_exists('default', $wp_meta_boxes['mbdb_book']['side'])) {
			$wp_meta_boxes['mbdb_book']['side']['default'] = array( $taxID => $tax ) + $wp_meta_boxes['mbdb_book']['side']['default'];
		 } else {
			$wp_meta_boxes['mbdb_book']['side']['default'] = array( $taxID => $tax );
		 }
	}

	// move these to the bottom
	$taxonomies = array( 'tagsdiv-mbdb_cover_artist', 'tagsdiv-mbdb_illustrator',  'tagsdiv-mbdb_editor');	
	foreach ($taxonomies as $taxID) {
		 $tax = $wp_meta_boxes['mbdb_book']['side']['core'][$taxID];
		 unset( $wp_meta_boxes['mbdb_book']['side']['core'][$taxID] );
		 
		 if (array_key_exists('low', $wp_meta_boxes['mbdb_book']['side'])) {
			$wp_meta_boxes['mbdb_book']['side']['low'][$taxID] = $tax ;
		 } else {
			$wp_meta_boxes['mbdb_book']['side']['low'] = array( $taxID => $tax );
		 }
	}
	
	// now add cover above the taxonomies
	$wp_meta_boxes['mbdb_book']['side']['default'] = 
			array( 'mbdb_cover_image_metabox' => $cover ) + 
				$wp_meta_boxes['mbdb_book']['side']['default'];

	
}

// 3.4.12
// when taxonomies are displayed as checkboxes instead of like tags, they take
// ids as input but when they are like tags, they take names as input
// because the taxonomies have been displayed like categories evn though they
// are not hierarchical, the id to name has to be translated upon saving
add_action( 'admin_init', 'mbdb_switch_tax_ids_and_name');
function mbdb_switch_tax_ids_and_name() {
	if ( !isset( $_POST['post_type'] ) || $_POST['post_type'] != 'mbdb_book') {
		return;
	}
	if ( isset( $_POST[ '_inline_edit' ] ) ) {
		return;
	}
	
	if( isset( $_POST['tax_input'] ) && is_array( $_POST['tax_input'] ) ) {
		$new_tax_input = array();
		foreach( $_POST['tax_input'] as $tax => $terms) {
			if( is_array( $terms ) ) {
			  $taxonomy = get_taxonomy( $tax );
			 
			  //if( !$taxonomy->hierarchical ) {
				if ( in_array($taxonomy->name, array('mbdb_genre', 'mbdb_series', 'mbdb_cover_artist', 'mbdb_tag', 'mbdb_editor', 'mbdb_illustrator'))) {
					
				 // $terms = array_map( 'intval', array_filter( $terms ) );
				 $term_names = array();
				 foreach( $terms as $term_id ) {
					 $term = get_term($term_id);
					 if ( !is_wp_error($term) ) {
						 $term_names[] = $term->name;
					 }
				 }
				 //$terms = array_map( 'get_term', $terms );
			  }
			}	
			
		
			$new_tax_input[$tax] = $term_names;
			
		}
		$_POST['tax_input'] = $new_tax_input;
	}
	
}



/**
 *  Add meta box for "Need help with Mooberry Book Manager?"
 *  
 *  
 *  
 *  @since 2.0 ?
 *  
 *  
 *  @access public
 */
add_action( 'add_meta_boxes_mbdb_book', 'mbdb_mbd_metabox', 10 );
function mbdb_mbd_metabox() {
	add_meta_box( 'mbdb_mbd_metabox', __('Need help with Mooberry Book Manager?', 'mooberry-book-manager'), 'mbdb_display_mbdb_metabox', 'mbdb_book', 'side', 'core' );
}

function mbdb_display_mbdb_metabox($post, $args) {
	include "views/admin-about-mooberry.php";

}


/**
 *  Create the metaboxes for the custom post meta data
 *  
 *  
 *  
 *  @since 1.0
 *  
 *  
 *  @access public
 */
add_action( 'cmb2_admin_init', 'mbdb_book_metaboxes', 30 );
function mbdb_book_metaboxes(  ) {
	
	
	$mbdb_options = get_option( 'mbdb_options' );
	
	// SUMMARY
	
	$mbdb_summary_metabox = new_cmb2_box( array(
			'id'            => 'mbdb_summary_metabox',
			'title'         => __( 'Summary', 'mooberry-book-manager' ),
			'object_types'  => array( 'mbdb_book', ),
			'context'       => 'normal',
			'priority'      => 'high',	
			'show_names'    => false, 
		) 
	);
	
	$mbdb_summary_metabox->add_field( array(
			'name'    => __('Summary', 'mooberry-book-manager'),
			'id'      => '_mbdb_summary',
			'type'    => 'wysiwyg',
			'sanitization_cb'	=> false,
			'options' => array(  
				'wpautop' => true, // use wpautop?
				'media_buttons' => true, // show insert/upload button(s)
				'textarea_rows' => 10, // rows="..."
				'tabindex' => '',
				'editor_css' => '', // intended for extra styles for both visual and HTML editors buttons, needs to include the `<style>` tags, can use "scoped".
				'editor_class' => '', // add extra class(es) to the editor textarea
				'teeny' => false, // output the minimal editor config used in Press This
				'dfw' => false, // replace the default fullscreen with DFW (needs specific css)
				'tinymce' => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
				'quicktags' => true // load Quicktags, can be used to pass settings directly to Quicktags using an array()   
			),
		)
	);
	
	
	
	// EXCERPT 	
	$mbdb_excerpt_metabox = new_cmb2_box( array(
		'id'            => 'mbdb_excerpt_metabox',
		'title'         => __('Excerpt', 'mooberry-book-manager'),
		'object_types'  => array( 'mbdb_book', ), // Post type
		'context'       => 'normal',
		'priority'      => 'high',	
		'show_names'    => true, // Show field names on the left
		)
	);
	
	$info_button = '<img onClick="window.open(\'' . MBDB_PLUGIN_URL . 'includes/excerpt_type.html' . '\', \'' . __('Excerpt Type', 'mooberry-book-manager') . '\',  \'width=800, height=900, left=550, top=50, scrollbars=yes\'); return false;"	class="mbdb_info_icon mbdb_excerpt_type_info" src="' . MBDB_PLUGIN_URL . 'includes/assets/info.png">';
	
	$mbdb_excerpt_metabox->add_field( array(
		'name'	=>	__('Excerpt Style', 'mooberry-book-manager') . $info_button,
		'id'	=>	'_mbdb_excerpt_type',
		'default'	=>	'text',
		'type'	=>	'select',
		'options'	=> array(
						'text'	=> __('Enter Excerpt Directly', 'mooberry-book-manager'),
						'kindle'	=>	__( 'Use Kindle Live Preview', 'mooberry-book-manager'),
					),
		)
	);
	
	$mbdb_excerpt_metabox->add_field( array(
		'name'    => __('Excerpt', 'mooberry-book-manager'),
		'id'      => '_mbdb_excerpt',
		'type'    => 'wysiwyg',
		'sanitization_cb'	=> false,
		'options' => array(  
			'wpautop' => true, // use wpautop?
			'media_buttons' => true, // show insert/upload button(s)
			'textarea_rows' => 15, // rows="..."
			'tabindex' => '',
			'editor_css' => '', // intended for extra styles for both visual and HTML editors buttons, needs to include the `<style>` tags, can use "scoped".
			'editor_class' => '', // add extra class(es) to the editor textarea
			'teeny' => false, // output the minimal editor config used in Press This
			'dfw' => false, // replace the default fullscreen with DFW (needs specific css)
			'tinymce' => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
			'quicktags' => true // load Quicktags, can be used to pass settings directly to Quicktags using an array()   
			)
		)
	);
	
	$mbdb_excerpt_metabox->add_field( array(
		'name'    => __('Kindle Live Preview Code', 'mooberry-book-manager'),
		'id'      => '_mbdb_kindle_preview',
		'type'    => 'textarea',
		'sanitization_cb'	=> false,
		)
	);
	
	
	// REVIEWS
	$mbdb_reviews_metabox = new_cmb2_box( array(
		'id'            => 'mbdb_reviews_metabox',
		'title'         => _x('Reviews', 'noun: book reviews', 'mooberry-book-manager'),
		'object_types'  => array( 'mbdb_book', ), // Post type
		'context'       => 'normal',
		'priority'      => 'high',
		
		'show_names'    => true, // Show field names on the left
		)
	);
		
	$mbdb_reviews_metabox->add_field( array(
		'id'          => '_mbdb_reviews',
		'type'        => 'group',
		'description' => __('Add reviews of your book', 'mooberry-book-manager'),
		'options'     => array(
			'group_title'   => _x('Reviews', 'noun', 'mooberry-book-manager') . ' {#}', // {#} gets replaced by row number
			'add_button'    =>  __('Add Review', 'mooberry-book-manager'),
			'remove_button' =>  __('Remove Review', 'mooberry-book-manager'),
			'sortable'      => true, // beta
			)
		)
	);
			
	$mbdb_reviews_metabox->add_group_field( '_mbdb_reviews', array(
			'name' => __('Reviewer Name', 'mooberry-book-manager'),
			'id'   => 'mbdb_reviewer_name',
			'type' => 'text_medium',
			'sanitization_cb' => 'mbdb_validate_reviews', 
		)
	);
	
	$mbdb_reviews_metabox->add_group_field( '_mbdb_reviews', array(
			'name' => _x('Review Link', 'noun: URL to book review', 'mooberry-book-manager'),
			'id'   => 'mbdb_review_url',
			'type' => 'text_url',
			'desc' => 'http://www.someWebsite.com/',
			'attributes' =>  array(
				'pattern' => '^(https?:\/\/)?([\da-zA-Z\.-]+)\.([a-zA-Z\.]{2,6}).*',
			),
		)
	);
		
	$mbdb_reviews_metabox->add_group_field( '_mbdb_reviews', array(
		'name' => _x('Review Website Name', 'noun: name of website of book review', 'mooberry-book-manager'),
		'id'   => 'mbdb_review_website',
		'type' => 'text_medium',
		)
	);
	
	$mbdb_reviews_metabox->add_group_field( '_mbdb_reviews', array(
		'name'    => _x('Review', 'noun: book review', 'mooberry-book-manager'),
		'id'      => 'mbdb_review',
		'type'	=>	'textarea',
		)
	);
	
	// EDITIONS
	
	$mbdb_editions_metabox = new_cmb2_box( array(
			'id'            => 'mbdb_editions_metabox',
			'title'         => __('Formats and Editions', 'mooberry-book-manager'),
			'object_types'  => array( 'mbdb_book', ), // Post type
			'context'       => 'normal',
			'priority'      => 'high',
			'show_names'    => true, // Show field names on the left
		)
	);
		
	$mbdb_editions_metabox->add_field( array(
			'id'          => '_mbdb_editions',
			'type'        => 'group',
			'description' => __("List the details of your book's hardcover, paperback, and e-book editions. Everything is optional except the format.", 'mooberry-book-manager'),
			'options'     => array(
				'group_title'   => __('Edition', 'mooberry-book-manager') . ' {#}', // {#} gets replaced by row number
				'add_button'    =>  __('Add New Edition', 'mooberry-book-manager'),
				'remove_button' =>  __('Remove Edition', 'mooberry-book-manager'),
				'sortable'      => true, // beta
			),
		)
	);
	
	$mbdb_editions_metabox->add_group_field( '_mbdb_editions', array(
			'name'	=>	_x('Format', 'noun: format of a book', 'mooberry-book-manager'),
			'id'	=>	'_mbdb_format',
			'type'	=>	'select',
			'sanitization_cb' => 'mbdb_validate_editions', 
			'options'	=> mbdb_get_editions(),
			'description'	=> __('Add more formats in Settings', 'mooberry-book-manager'),
		)
	);
	
	$mbdb_editions_metabox->add_group_field( '_mbdb_editions', array(
			'name'	=> __('EAN/ISBN', 'mooberry-book-manager'),
			'id'	=>	'_mbdb_isbn',
			'type'	=>	'text_medium',
		)
	);
			
	$mbdb_editions_metabox->add_group_field( '_mbdb_editions', array(
			'name'	=> __('Language', 'mooberry-book-manager'),
			'id'	=>	'_mbdb_language',
			'type'	=> 'select',
			'options'	=> mbdb_get_language_array(),
			'default'	=>	mbdb_get_default_language($mbdb_options),
		)
	);
			
	$mbdb_editions_metabox->add_group_field( '_mbdb_editions', array(
			'name'	=> __('Number of Pages', 'mooberry-book-manager'),
			'id'	=> '_mbdb_length',
			'type'	=> 'text_small',
			'attributes' => array(
					'type' => 'number',
					'pattern' => '\d*',
					'min' => 1
			)
		)
	);
			
	$mbdb_editions_metabox->add_group_field( '_mbdb_editions', array(
			'name'	=>	__('Height', 'mooberry-book-manager'),
			'id'	=>	'_mbdb_height',
			'type'	=>	'text_small',
			'attributes' => array(
				'type' => 'number',
				'step' => 'any',
				'min' => 0
			),
		)
	);
			
	$mbdb_editions_metabox->add_group_field( '_mbdb_editions', array(
			'name'	=>	__('Width', 'mooberry-book-manager'),
			'id'	=>	'_mbdb_width',
			'type'	=> 'text_small',
			'attributes' => array(
				'type' => 'number',
				'step' => 'any',
				'min' => 0
			),
		)
	);
			
	$mbdb_editions_metabox->add_group_field( '_mbdb_editions', array(
			'name'	=> _x('Unit', 'units of measurement', 'mooberry-book-manager'),
			'id'	=>	'_mbdb_unit',
			'type'	=> 'select',
			'options'	=> mbdb_get_units_array(),
			'default'	=>	mbdb_get_default_unit($mbdb_options),
		)
	);
			
	$mbdb_editions_metabox->add_group_field( '_mbdb_editions', array(
			'name'	=>	__('Suggested Retail Price', 'mooberry-book-manager'),
			'id'	=>	'_mbdb_retail_price',
			'type'	=> 'text_small',
			'attributes' => array(
					'pattern' => '^\d*([.,]\d{2}$)?',
					'min' => 0
			),
		)
	);
			
	$mbdb_editions_metabox->add_group_field( '_mbdb_editions', array(
			'name'	=>	__('Currency', 'mooberry-book-manager'),
			'id'	=>	'_mbdb_currency',
			'type'	=>	'select',
			'options'	=>	mbdb_get_currency_array(),
			'default'	=> mbdb_get_default_currency($mbdb_options),
		)
	);
			
	$mbdb_editions_metabox->add_group_field( '_mbdb_editions', array(
			'name'	=>	__('Edition Title', 'mooberry-book-manager'),
			'id'	=>	'_mbdb_edition_title',
			'type'	=> 'text_medium',
			'desc' => __('First Edition, Second Edition, etc.', 'mooberry-book-manager'),
		)
	);
		
	// ADDITIONAL INFORMATION
	
	$mbdb_additional_info_metabox  = new_cmb2_box( array(
		'id'            => 'mbdb_additional_info_metabox',
		'title'         => __('Additional Information', 'mooberry-book-manager'),
		'object_types'  => array( 'mbdb_book', ), // Post type
		'context'       => 'normal',
		'priority'      => 'high',
		'show_names'    => false, // Show field names on the left
		)
	);
	
	$mbdb_additional_info_metabox->add_field( array(
		'name'    => __('Additional Information', 'mooberry-book-manager'),
		'id'      => '_mbdb_additional_info',
		'type'    => 'wysiwyg',
		'sanitization_cb'	=> false,
		'description' => __('Any additional information you want to display on the page. Will be shown at the bottom of the page, after the reviews.', 'mooberry-book-manager'),
		'options' => array(  
			'wpautop' => true, // use wpautop?
			'media_buttons' => true, // show insert/upload button(s)
			'textarea_rows' => 15, // rows="..."
			'tabindex' => '',
			'editor_css' => '', // intended for extra styles for both visual and HTML editors buttons, needs to include the `<style>` tags, can use "scoped".
			'editor_class' => '', // add extra class(es) to the editor textarea
			'teeny' => false, // output the minimal editor config used in Press This
			'dfw' => false, // replace the default fullscreen with DFW (needs specific css)
			'tinymce' => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
			'quicktags' => true, // load Quicktags, can be used to pass settings directly to Quicktags using an array()   
			),
		)
	);	
	
	// COVER

	$mbdb_cover_image_metabox  = new_cmb2_box( array(
		'id'            => 'mbdb_cover_image_metabox',
		'title'         => _x('Cover', 'noun', 'mooberry-book-manager'),
		'object_types'  => array( 'mbdb_book', ), // Post type
		'context'       => 'side',
		'priority'      => 'default',
		'show_names'    => false, // Show field names on the left
		'allow'			=> array( 'attachment')
		)
	);
	
	$mbdb_cover_image_metabox->add_field( apply_filters('mbdb_cover_image_metabox_args', array(
		 'name' => _x('Book Cover', 'noun', 'mooberry-book-manager'),
		'id' => '_mbdb_cover',
		'type' => 'file',
		'allow' => array(  'attachment' ) // limit to just attachments with array( 'attachment' )
		) 
		)
	);
	
	// BOOK INFO
	
	$mbdb_bookinfo_metabox  = new_cmb2_box( array(
		'id'            => 'mbdb_bookinfo_metabox',
		'title'         => __('Book Details', 'mooberry-book-manager'),
		'object_types'  => array( 'mbdb_book', ), // Post type
		'context'       => 'side',
		'priority'      => 'default',
		'show_names'    => true, // Show field names on the left
		)
	);
	
	$mbdb_bookinfo_metabox->add_field( array(
		'name' => __('Subtitle', 'mooberry-book-manager'),
		'id'   => '_mbdb_subtitle',
		'type' => 'text_small',
		)
	);
	
	$mbdb_bookinfo_metabox->add_field( array(
		'name' 	=> __('Release Date', 'mooberry-book-manager'),
		'id'	=> '_mbdb_published',
		'type' => 'text_date',
		'desc' => 'yyyy/mm/dd',
		'date_format' => 'Y/m/d',
		'sanitization_cb' => 'mbdb_format_date',
		)
	);
		
	$mbdb_bookinfo_metabox->add_field( array(
			'name' => __('Publisher', 'mooberry-book-manager'),
			'id'   => '_mbdb_publisherID',
			'type' => 'select',
			'options' => mbdb_get_publishers(),
			'desc' 	=> __('Set up Publishers in Settings.', 'mooberry-book-manager'),
			)
	);
		
	$mbdb_bookinfo_metabox->add_field( array(
			'name'	=> __('Goodreads Link', 'mooberry-book-manager'),
			'id'	=> '_mbdb_goodreads',
			'type'	=> 'text_url',
			'desc' => 'http://www.goodreads.com/your/Unique/Text/',
			'attributes' =>  array(
				'pattern' => '^(https?:\/\/)?www.goodreads.com.*',
			)
		)
	);
		
	$mbdb_bookinfo_metabox->add_field( array(
			'name'	=> __('Series Order', 'mooberry-book-manager'),
			'id'	=> '_mbdb_series_order',
			'desc'	=> __('(leave blank if not part of a series)', 'mooberry-book-manager'),
			'type'	=> 'text_small',
			'attributes' => array(
					'type' => 'number',
					'step' => 'any',
					'min' => 0
			),
		)
	);
		
	// BUYLINKS
	$mbdb_buylinks_metabox  = new_cmb2_box( array(
		'id'            => 'mbdb_buylinks_metabox',
		'title'         => _x('Retailer Links', 'noun: URLs to book retailers', 'mooberry-book-manager'),
		'object_types'  => array( 'mbdb_book', ), // Post type
		'context'       => 'side',
		'priority'      => 'default',	
		'show_names'    => true, // Show field names on the left
		)
	);
		
	$mbdb_buylinks_metabox->add_field( array(
			'id'          => '_mbdb_buylinks',
			'type'        => 'group',
			'description' => __('Add links where readers can purchase your book', 'mooberry-book-manager'),
			'options'     => array(
				'group_title'   => _x('Retailer Link', 'noun', 'mooberry-book-manager') . ' {#}', // {#} gets replaced by row number
				'add_button'    => __('Add Retailer Link', 'mooberry-book-manager'),
				'remove_button' => __('Remove Retailer Link', 'mooberry-book-manager'),
				'sortable'      => true, // beta
			),
		)
	);
	
	$mbdb_buylinks_metabox->add_group_field( '_mbdb_buylinks', array(
			'name'    => __('Retailer', 'mooberry-book-manager'),
			'id'      => '_mbdb_retailerID',
			'type'    => 'select',
			'options' => mbdb_get_retailers(),
			'sanitization_cb' => 'mbdb_validate_retailers',
			'description'	=> __('Add more retailers in Settings', 'mooberry-book-manager'),
		)
	);
			
	$mbdb_buylinks_metabox->add_group_field( '_mbdb_buylinks', array(
			'name'	=> _x('Link', 'noun: URL', 'mooberry-book-manager'),
			'id'	=> '_mbdb_buylink',
			'type'	=> 'text_url',
			'desc' => 'http://www.someWebsite.com/',
			'attributes' =>  array(
				'pattern' => mbdb_url_validation_pattern(),
			),
		)
	);
	
	// DOWNLOAD LINKS
	$mbdb_downloadlinks_metabox = new_cmb2_box( array(
		'id'            => 'mbdb_downloadlinks_metabox',
		'title'         => _x('Download Links', 'noun', 'mooberry-book-manager'),
		'object_types'  => array( 'mbdb_book', ), // Post type
		'context'       => 'side',
		'priority'      => 'low',
		'show_names'    => true, // Show field names on the left
		
		)
	);
	
	$mbdb_downloadlinks_metabox->add_field( array(
			'id'          => '_mbdb_downloadlinks',
			'type'        => 'group',
			'description' => __('If your book is available to download for free, add the links for each format.', 'mooberry-book-manager'),
			'options'     => array(
				'group_title'   => _x('Download Link', 'noun', 'mooberry-book-manager') . ' {#}',  // {#} gets replaced by row number
				'add_button'    => __('Add Download Link', 'mooberry-book-manager'),
				'remove_button' =>  __('Remove Download Link', 'mooberry-book-manager'),
				'sortable'      => true, // beta
			),
		)
	);
	
	$mbdb_downloadlinks_metabox->add_group_field( '_mbdb_downloadlinks', array(
			'name'    => _x('Format', 'noun', 'mooberry-book-manager'),
			'id'      => '_mbdb_formatID',
			'type'    => 'select',
			'options' => mbdb_get_formats(),
			'sanitization_cb' => 'mbdb_validate_downloadlinks',
			'description'	=> __('Add more formats in Settings', 'mooberry-book-manager'),
		)
	);
	
	$mbdb_downloadlinks_metabox->add_group_field( '_mbdb_downloadlinks', array(
			'name'	=> _x('Link', 'noun', 'mooberry-book-manager'),
			'id'	=> '_mbdb_downloadlink',
			'type'	=> 'text_url',
			'desc' => 'http://www.someWebsite.com/',
			'attributes' =>  array(
				'pattern' => mbdb_url_validation_pattern(),
			),
		)
	);
	

	
	$mbdb_summary_metabox = apply_filters('mbdb_summary_metabox', $mbdb_summary_metabox);
	$mbdb_editions_metabox = apply_filters('mbdb_editions_metabox', $mbdb_editions_metabox);
	$mbdb_excerpt_metabox = apply_filters('mbdb_excerpt_metabox', $mbdb_excerpt_metabox);
	$mbdb_reviews_metabox = apply_filters('mbdb_reviews_metabox', $mbdb_reviews_metabox);
	$mbdb_additional_info_metabox = apply_filters('mbdb_additional_info_metabox', $mbdb_additional_info_metabox);
	$mbdb_cover_image_metabox = apply_filters('mbdb_cover_image_metabox', $mbdb_cover_image_metabox);
	$mbdb_bookinfo_metabox = apply_filters('mbdb_bookinfo_metabox', $mbdb_bookinfo_metabox);
	$mbdb_buylinks_metabox = apply_filters('mbdb_buylinks_metabox', $mbdb_buylinks_metabox);
	$mbdb_downloadlinks_metabox = apply_filters('mbdb_downloadlinks_metabox', $mbdb_downloadlinks_metabox);
	
	/*
	global $mbdb_edit_book;
	// load the book data
	if (array_key_exists('post', $_GET) && $_GET['post'] != '') {
		$book_id = $_GET['post'];
		$mbdb_edit_book = MBDB()->books->get($book_id);
		
	}
	*/
}	


add_filter('wpseo_metabox_prio', 'mbdb_reorder_wpseo');
function mbdb_reorder_wpseo( $priority ) {
	if (get_post_type() == 'mbdb_book') {
		return 'default';
	} else {
		return $priority;
	}
}