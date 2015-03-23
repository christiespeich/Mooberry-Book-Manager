<?php


	
// set the book's excerpt to a portion of the summary	
add_action('save_post_mbdb_book', 'mbdb_save_excerpt');
function mbdb_save_excerpt($post_id, $post =null, $update=null) {
	if (array_key_exists('_mbdb_summary', $_POST) && $_POST['_mbdb_summary']) {
		$summary = $_POST['_mbdb_summary'];
	} elseif ( array_key_exists('_mbdb_summary', $_GET) && $_GET['_mbdb_summary']) {
		$summary = $_GET['_mbdb_summary'];
	} else {
		return;
	}
	// unhook this function so it doesn't loop infinitely
	remove_action( 'save_post_mbdb_book', 'mbdb_save_excerpt' );

	// update the post, which calls save_post again
	wp_update_post( array( 'ID' => $post_id, 'post_excerpt' =>  substr($summary, 0, 50)) );

	// re-hook this function
	add_action( 'save_post_mbdb_book', 'mbdb_save_excerpt' );	
}


// set up the columns on the book list page
add_filter( 'manage_edit-mbdb_book_columns', 'set_up_mbdb_book_columns' );
function set_up_mbdb_book_columns( $columns ) {
	$columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => 'Title' ,
		'_mbdb_cover' => 'Cover',
		'_mbdb_length' => 'Length' ,
		'mbdb_genre' => 'Genre',
		'_mbdb_published' => 'Published',
		'mbdb_series' => 'Series',
		'_mbdb_series_order' => 'Series Order',
		'_mbdb_publisher' => 'Publisher' ,
		'date' => 'Updated'
	);
	return apply_filters('mbdb_book_columns', $columns);
}

add_action( 'manage_mbdb_book_posts_custom_column', 'populate_mbdb_book_columns', 10, 2 );
function populate_mbdb_book_columns($column, $post_id) {

	switch ($column) {
		case '_mbdb_cover':
			$img_src = get_post_meta($post_id, $column, true);
			do_action('mbdb_book_before_mbdb_cover_column');
			if ($img_src!='') {
				echo apply_filters('mbdb_book_mbdb_cover_column', '<IMG SRC="' . esc_url($img_src) . '" width="100"/>');
			}
			do_action('mbdb_book_after_mbdb_cover_column');
			break;
		case 'mbdb_genre':
		case 'mbdb_series':	 
			do_action('mbdb_book_before_' . $column . '_column');
			echo apply_filters('mbdb_book_' . $column . '_column', get_the_term_list( $post_id, $column, '' , ', ' ));
			do_action('mbdb_book_after_' . $column . '_column');
			break;
		default:
			do_action('mbdb_book_before' . $column . '_column');
			echo apply_filters('mbdb_book' . $column . '_column', get_post_meta( $post_id, $column, true), $post_id);
			do_action('mbdb_book_after' . $column . '_column');
	}	
}

// TO DO: fix the sorting

/* add_filter( 'manage_edit-mbdb_book_sortable_columns', 'mbdb_book_sortable_columns' );
function mbdb_book_sortable_columns($columns) {
	
	// $columns['_mbdb_length'] = '_mbdb_length';
	// $columns['_mbdb_published'] = '_mbdb_published';
	// $columns['_mbdb_publisher'] = '_mbdb_publisher';
	// $columns['_mbdb_series_order'] = '_mbdb_series_order';
	// $columns['mbdb_series'] = 'mbdb_series';
	// $columns['mbdb_genre'] = 'mbdb_genre';
		
	return $columns;
	
} */
	
/* add_action( 'load-edit.php', 'mbdb_edit_load' );
function mbdb_edit_load() {
	add_filter( 'request', 'sort_mbdb_book_columns' );
}

function sort_mbdb_book_columns( $vars) {
	// if ( isset( $vars['post_type'] ) ) {
		// switch($vars['post_type']) {
			// case 'mbdb_book':
				// if ( isset( $vars['orderby'] )) {
					// switch ($vars['orderby']) {
						// case '_mbdb_series_order':
						// case '_mbdb_length':
						// case '_mbdb_published':
							// $vars = array_merge($vars, array('orderby' => 'meta_value_num', 'meta_key' => $vars['orderby']));
							// print_r($vars);
							// break;
					// }
				// }
			// }
		// }
	return $vars;
} */
	
	
add_action('add_meta_boxes_mbdb_book', 'mbdb_mbd_metabox', 10);
function mbdb_mbd_metabox() {
		add_meta_box('mbdb_mbd_metabox', 'Like Mooberry Book Manager?', 'mbdb_display_mbdb_metabox', 'mbdb_book', 'side', 'high');
}

function mbdb_display_mbdb_metabox($post, $args) {
	echo '<p>Check out <a target="_new" href="http://www.mooberrydreams.com/">our website</a> to learn more about the available add-ons so Mooberry Book Manager can save you more time!</p>';
	echo '<img style="width:225px" src="' . plugins_url('/views/images/logo.png', __FILE__) . '">';
}


add_filter( 'cmb2_meta_boxes', 'mbdb_book_metaboxes', 30 );
function mbdb_book_metaboxes( array $meta_boxes ) {
	$meta_boxes['mbdb_summary_metabox'] = array(
		'id'            => 'mbdb_summary_metabox',
		'title'         => 'Summary',
		'object_types'  => array( 'mbdb_book', ), // Post type
		'context'       => 'normal',
		'priority'      => 'high',	
		'show_names'    => false, // Show field names on the left
		'fields' => array(
			array(
				'name'    => 'Summary',
				'id'      => '_mbdb_summary',
				'type'    => 'wysiwyg',
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
			),
		),
	);
	
	$meta_boxes['mbdb_excerpt'] = array(
		'id'            => 'mbdb_excerpt',
		'title'         => 'Excerpt',
		'object_types'  => array( 'mbdb_book', ), // Post type
		'context'       => 'normal',
		'priority'      => 'default',	
		'show_names'    => false, // Show field names on the left
		'fields' => array(
			array(
				'name'    => 'Excerpt',
				'id'      => '_mbdb_excerpt',
				'type'    => 'wysiwyg',
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
				),
			),
		),
	);
	
	$meta_boxes['mbdb_reviews'] = array(
		'id'            => 'mbdb_reviews',
		'title'         => 'Reviews',
		'object_types'  => array( 'mbdb_book', ), // Post type
		'context'       => 'normal',
		'priority'      => 'default',
		
		'show_names'    => true, // Show field names on the left
		'fields' => array(
			array(
			'id'          => '_mbdb_reviews',
			'type'        => 'group',
			'description' => 'Add reviews of your book',
			'options'     => array(
				'group_title'   => 'Review {#}', // {#} gets replaced by row number
				'add_button'    =>  'Add Review',
				'remove_button' =>  'Remove Review',
				'sortable'      => false, // beta
				),
			
			// Fields array works the same, except id's only need to be unique for this group. Prefix is not needed.
			'fields'      => array(
				array(
					'name' => 'Reviewer Name',
					'id'   => 'mbdb_reviewer_name',
					'type' => 'text_medium',
					'sanitization_cb' => 'mbdb_validate_reviews', 
				),
				array(
					'name' => 'Review Link',
					'id'   => 'mbdb_review_url',
					'type' => 'text_url',
					'desc' => 'http://www.someWebsite.com/',
					'attributes' =>  array(
						//'pattern' => '^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})\/?([\/\w \.=\?&\-]*)*\/?',
						'pattern' => '^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6}).*',
					),
				),
				array(
					'name' => 'Review Website Name',
					'id'   => 'mbdb_review_website',
					'type' => 'text_medium',
					
				),
				array(
					'name'    => 'Review',
					'id'      => 'mbdb_review',
					'type'	=>	'textarea',
					),
				),
			),
		),
	);
	
	$meta_boxes['mbdb_buylinks_metabox'] = array(
		'id'            => 'mbdb_buylinks_metabox',
		'title'         => 'Retailer Links',
		'object_types'  => array( 'mbdb_book', ), // Post type
		'context'       => 'normal',
		'priority'      => 'default',
			
		
		'show_names'    => true, // Show field names on the left
		'fields' => array(
			array(
				'id'          => '_mbdb_buylinks',
				'type'        => 'group',
				'description' => 'Add links where readers can purchase your book',
				'options'     => array(
					'group_title'   => 'Retailer Link {#}', // {#} gets replaced by row number
					'add_button'    => 'Add Retailer Link',
					'remove_button' => 'Remove Retailer Link',
					'sortable'      => false, // beta
				),
				// Fields array works the same, except id's only need to be unique for this group. Prefix is not needed.
				'fields'      => array(
					array( 
						'name'    => 'Retailer',
						'id'      => '_mbdb_retailerID',
						'type'    => 'select',
						'options' => 'mbdb_get_retailers',
						'sanitization_cb' => 'mbdb_validate_retailers',
					),
					array(
						'name'	=> 'Link',
						'id'	=> '_mbdb_buylink',
						'type'	=> 'text_url',
						'desc' => 'http://www.someWebsite.com/',
						'attributes' =>  array(
							//'pattern' => '^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})\/?([\/\w \.=\?&\-%]*)*\/?',
							'pattern' => '^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6}).*',
					
						),
					),
				),
			),
		),
	);
	
	$meta_boxes['mbdb_downloadlinks_metabox'] = array(
		'id'            => 'mbdb_downloadlinks_metabox',
		'title'         => 'Download Links',
		'object_types'  => array( 'mbdb_book', ), // Post type
		'context'       => 'side',
		'priority'      => 'low',
			
		'show_names'    => true, // Show field names on the left
		'fields' => array(
			array(
				'id'          => '_mbdb_downloadlinks',
				'type'        => 'group',
				'description' => 'If your book is available to download for free, add the links for each format.',
				'options'     => array(
					'group_title'   => 'Download Link {#}',  // {#} gets replaced by row number
					'add_button'    => 'Add Download Link', 
					'remove_button' =>  'Remove Download Link',
					'sortable'      => false, // beta
				),
				// Fields array works the same, except id's only need to be unique for this group. Prefix is not needed.
				'fields'      => array(
					array( 
						'name'    => 'Format',
						'id'      => '_mbdb_formatID',
						'type'    => 'select',
						'options' => 'mbdb_get_formats',
						'sanitization_cb' => 'mbdb_validate_downloadlinks',
					),
					array(
						'name'	=> 'Link',
						'id'	=> '_mbdb_downloadlink',
						'type'	=> 'text_url',
						'attributes' =>  array(
							//'pattern' => '^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})\/?([\/\w \.=\?&\-%]*)*\/?',
							'pattern' => '^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6}).*',
						),
					),
				),
			),
		),
	);
	
	$meta_boxes['mbdb_bookinfo_metabox'] = array(
		'id'            => 'mbdb_bookinfo_metabox',
		'title'         => 'Book Details',
		'object_types'  => array( 'mbdb_book', ), // Post type
		'context'       => 'side',
		'priority'      => 'low',
			
		'show_names'    => true, // Show field names on the left
		'fields' => array(
			array(
				'name' => 'Subtitle', 
				'id'   => '_mbdb_subtitle',
				'type' => 'text_small',
			),
			array(
				'name' => 'Publisher', 
				'id'   => '_mbdb_publisher',
				'type' => 'text_medium',
			),
			array(
				'name' 	=> 'Publisher Website',
				'id'	=> '_mbdb_publisherwebsite',
				'type'	=> 'text_url',
				'desc' => 'http://www.someWebsite.com/',
				'attributes' =>  array(
					//'pattern' => '^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})\/?([\/\w \.=\?&\-%]*)*\/?',
					'pattern' => '^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6}).*',
					
				),
			),
			array(
				'name' 	=> 'Release Date',
				'id'	=> '_mbdb_published',
				'type' => 'text_date',
				'date_format' => 'Y/m/d',
				'sanitization_cb' => 'mbdb_format_date'
			),
			array(
				'name'	=> 'Goodreads Link',
				'id'	=> '_mbdb_goodreads',
				'type'	=> 'text_url',
				'desc' => 'http://www.goodreads.com/your/Unique/Text/',
				'attributes' =>  array(
				//	'pattern' => '^(https?:\/\/)?www.goodreads.com([\/\w \.=\?&\-]*)*\/?',
					'pattern' => '^(https?:\/\/)?www.goodreads.com.*',
				),
			),
			array(
				'name'	=> 'Number of Pages',
				'id'	=> '_mbdb_length',
				'type'	=> 'text_small',
				'attributes' => array(
						'type' => 'number',
						'pattern' => '\d*',
						'min' => 1
					),
			),
			array(
				'name'	=> 'Series Order',
				'id'	=> '_mbdb_series_order',
				'desc'	=> '(leave blank if not part of a series)',
				'type'	=> 'text_small',
				'attributes' => array(
						'type' => 'number',
						//'pattern' => '\d*.?\d*',
						'step' => 'any',
						'min' => 0
					),
			),
		),
	);
	
	$meta_boxes['mbdb_cover_image'] = array(
		'id'            => 'mbdb_cover_image',
		'title'         => 'Cover',
		'object_types'  => array( 'mbdb_book', ), // Post type
		'context'       => 'side',
		'priority'      => 'low',
			
		'show_names'    => false, // Show field names on the left
		'allow'			=> array( 'attachment'),
		'fields' => array(
			array(
				 'name' => 'Book Cover',
				'id' => '_mbdb_cover',
				'type' => 'file',
				'allow' => array(  'attachment' ) // limit to just attachments with array( 'attachment' )
			),
		),
	);

	
	return apply_filters('mbdb_book_meta_boxes', $meta_boxes);
	
}
	


?>