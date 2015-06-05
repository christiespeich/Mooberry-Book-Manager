<?php


	
// set the book's excerpt to a portion of the summary	
add_action('save_post_mbdb_book', 'mbdb_save_excerpt');
function mbdb_save_excerpt($post_id, $post =null, $update=null) {
	 if ($post == null) {
		if (array_key_exists('_mbdb_summary', $_POST) && $_POST['_mbdb_summary']) {
			$summary = $_POST['_mbdb_summary'];
		} elseif ( array_key_exists('_mbdb_summary', $_GET) && $_GET['_mbdb_summary']) {
			$summary = $_GET['_mbdb_summary'];
		} else {
			$summary = '';
		}
	/*	
		if (array_key_exists('_mbdb_cover', $_POST) && $_POST['_mbdb_cover']) {
			$cover = $_POST['_mbdb_cover'];
		} elseif ( array_key_exists('_mbdb_cover', $_GET) && $_GET['_mbdb_cover']) {
			$cover = $_GET['_mbdb_cover'];
		} else {
			$cover = '';
		}
		
		if (array_key_exists('_mbdb_subtitle', $_POST) && $_POST['_mbdb_subtitle']) {
			$subtitle = $_POST['_mbdb_subtitle'];
		} elseif ( array_key_exists('_mbdb_subtitle', $_GET) && $_GET['_mbdb_subtitle']) {
			$subtitle = $_GET['_mbdb_subtitle'];
		} else {
			$subtitle = '';
		}
	*/
	} else {
		$summary = get_post_meta($post_id, '_mbdb_summary', true);
		//$subtitle = get_post_meta($post_id, '_mbdb_subtitle', true);
		//$cover = get_post_meta($post_id, '_mbdb_cover', true);
	}
	
	//$excerpt = '<div class="mbm-archive"><h3 class="mbm-archive-subtitle">' . $subtitle . '</h3><span class="mbm-archive-cover"><img class="mbm-archive-image" src="' . $cover . '"></span><span class="mbm-archive-summary">' . $summary . '<p><A class="mbm-archive-link" HREF="' . esc_url(get_permalink($post_id)) . '"> LEARN MORE </a></p></span></div><p style="clear:both"></p>';
 

	// unhook this function so it doesn't loop infinitely
	remove_action( 'save_post_mbdb_book', 'mbdb_save_excerpt' );

	// update the post, which calls save_post again
	wp_update_post( array( 'ID' => $post_id, 'post_excerpt' =>  $summary) );

	// re-hook this function
	add_action( 'save_post_mbdb_book', 'mbdb_save_excerpt' );	
}


// set up the columns on the book list page
add_filter( 'manage_edit-mbdb_book_columns', 'set_up_mbdb_book_columns' );
function set_up_mbdb_book_columns( $columns ) {
	$columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => __('Title', 'mooberry-book-manager') ,
		'_mbdb_cover' => __('Cover', 'mooberry-book-manager'),
		'_mbdb_length' => __('Length', 'mooberry-book-manager') ,
		'mbdb_genre' => __('Genre', 'mooberry-book-manager'),
		'_mbdb_published' => __('Published', 'mooberry-book-manager'),
		'mbdb_series' => __('Series', 'mooberry-book-manager'),
		'_mbdb_series_order' => __('Series Order', 'mooberry-book-manager'), 
		'_mbdb_publisherID' => __('Publisher', 'mooberry-book-manager'),
		'date' => __('Updated', 'mooberry-book-manager')
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
		case '_mbdb_published':
			do_action('mbdb_book_before_mbdb_published_column');
			$mbdb_published = get_post_meta( $post_id, $column, true);
			if (!empty($mbdb_published)) {
				/* translators: short date format. see http://php.net/date */
				echo apply_filters('mbdb_book_mbdb_published_column', date(__('m/d/Y'),strtotime($mbdb_published)), $post_id);
			}
			do_action('mbdb_book_after_mbdb_published_column');
			break;
		case '_mbdb_publisherID':
			$publisher = mbdb_get_publisher_info(get_post_meta( $post_id, $column, true));
			echo apply_filters('mbdb_book' . $column . '_column', $publisher['name']);
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
	
// reorder taxonomy boxes
add_action('add_meta_boxes_mbdb_book', 'mbdb_reorder_taxonomy_boxes');
function mbdb_reorder_taxonomy_boxes() {
	
    global $wp_meta_boxes;
	$taxonomies = array(  'tagsdiv-mbdb_tag', 'tagsdiv-mbdb_series', 'tagsdiv-mbdb_genre' );
	// remove the cover to be readded before the taxonomies
	$cover = $wp_meta_boxes['mbdb_book']['side']['default']['mbdb_cover_image'];
	unset($wp_meta_boxes['mbdb_book']['side']['default']['mbdb_cover_image']);
	foreach ($taxonomies as $taxID) {
		 $tax = $wp_meta_boxes['mbdb_book']['side']['core'][$taxID];
		 unset($wp_meta_boxes['mbdb_book']['side']['core'][$taxID]);
		 if (array_key_exists('default', $wp_meta_boxes['mbdb_book']['side'])) {
			$wp_meta_boxes['mbdb_book']['side']['default'] = array($taxID => $tax) + $wp_meta_boxes['mbdb_book']['side']['default'];
		 } else {
			$wp_meta_boxes['mbdb_book']['side']['default'] = array($taxID => $tax);
		 }
	}
	
	// now add cover above the taxonomies
	$wp_meta_boxes['mbdb_book']['side']['default'] = array('mbdb_cover_image' => $cover) + $wp_meta_boxes['mbdb_book']['side']['default'];
}


	
add_action('add_meta_boxes_mbdb_book', 'mbdb_mbd_metabox', 10);
function mbdb_mbd_metabox() {
		add_meta_box('mbdb_mbd_metabox', 'Need help with Mooberry Book Manager?', 'mbdb_display_mbdb_metabox', 'mbdb_book', 'side', 'core');
}

function mbdb_display_mbdb_metabox($post, $args) {
//	echo '<p>Check out <a target="_new" href="http://www.mooberrydreams.com/">our website</a> to learn more about the available add-ons so Mooberry Book Manager can save you more time!</p><h4>Need help with Mooberry Book Manager?</h4>
//		<p><a target="_new" href="http://www.mooberrydreams.com/wp/wp-content/uploads/2015/02/Mooberry-Book-Manager-User-Manual-v1.0.pdf">Download the User Manual</a></p>';
//	echo '<img style="width:225px" src="' . plugins_url('/views/images/logo.png', __FILE__) . '">';
include "views/admin-about-mooberry.php";

}


add_filter( 'cmb2_meta_boxes', 'mbdb_book_metaboxes', 30 );
function mbdb_book_metaboxes( array $meta_boxes ) {
	$mbdb_options = get_option('mbdb_options');
	
	$meta_boxes['mbdb_summary_metabox'] = array(
		'id'            => 'mbdb_summary_metabox',
		'title'         => __('Summary', 'mooberry-book-manager'),
		'object_types'  => array( 'mbdb_book', ), // Post type
		'context'       => 'normal',
		'priority'      => 'high',	
		'show_names'    => false, // Show field names on the left
		'fields' => array(
			array(
				'name'    => __('Summary', 'mooberry-book-manager'),
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
	$meta_boxes['mbdb_editions'] = array(
		'id'            => 'mbdb_editions',
		'title'         => __('Formats and Editions', 'mooberry-book-manager'),
		'object_types'  => array( 'mbdb_book', ), // Post type
		'context'       => 'normal',
		'priority'      => 'default',
		
		'show_names'    => true, // Show field names on the left
		'fields' => array(
			array(
			'id'          => '_mbdb_editions',
			'type'        => 'group',
			'description' => __("List the details of your book's hardcover, paperback, and e-book editions. Everything is optional except the format.", 'mooberry-book-manager'),
			'options'     => array(
				'group_title'   => __('Edition', 'mooberry-book-manager') . ' {#}', // {#} gets replaced by row number
				'add_button'    =>  __('Add New Edition', 'mooberry-book-manager'),
				'remove_button' =>  __('Remove Edition', 'mooberry-book-manager'),
				'sortable'      => false, // beta
				),
			
				// Fields array works the same, except id's only need to be unique for this group. Prefix is not needed.
				'fields'      => array(
					array(
						'name'	=>	_x('Format', 'noun', 'mooberry-book-manager'),
						'id'	=>	'_mbdb_format',
						'type'	=>	'select',
						'sanitization_cb' => 'mbdb_validate_editions', 
						'options'	=> mbdb_get_editions(),
					),
					array(
						'name'	=> __('EAN/ISBN', 'mooberry-book-manager'),
						'id'	=>	'_mbdb_isbn',
						'type'	=>	'text_medium',
					),
					array(
						'name'	=> __('Language', 'mooberry-book-manager'),
						'id'	=>	'_mbdb_language',
						'type'	=> 'select',
						'options'	=> mbdb_get_language_array(),
						'default'	=>	mbdb_get_default_language($mbdb_options),
					),
					array(
						'name'	=> __('Number of Pages', 'mooberry-book-manager'),
						'id'	=> '_mbdb_length',
						'type'	=> 'text_small',
						'attributes' => array(
								'type' => 'number',
								'pattern' => '\d*',
								'min' => 1
							),
					),
					array(
						'name'	=>	__('Height', 'mooberry-book-manager'),
						'id'	=>	'_mbdb_height',
						'type'	=>	'text_small',
						'attributes' => array(
							'type' => 'number',
							'step' => 'any',
							'min' => 0
						),
					),
					array(
						'name'	=>	__('Width', 'mooberry-book-manager'),
						'id'	=>	'_mbdb_width',
						'type'	=> 'text_small',
						'attributes' => array(
							'type' => 'number',
							'step' => 'any',
							'min' => 0
						),
					),
					array(
						'name'	=> _x('Unit', 'units of measurement', 'mooberry-book-manager'),
						'id'	=>	'_mbdb_unit',
						'type'	=> 'select',
						'options'	=> mbdb_get_units_array(),
						'default'	=>	mbdb_get_default_unit($mbdb_options),
					),
					array(
						'name'	=>	__('Suggested Retail Price', 'mooberry-book-manager'),
						'id'	=>	'_mbdb_retail_price',
						'type'	=> 'text_small',
						'attributes' => array(
								'pattern' => '^\d*([.,]\d{2}$)?',
								'min' => 0
							),
						
					),
					array(	
						'name'	=>	__('Currency', 'mooberry-book-manager'),
						'id'	=>	'_mbdb_currency',
						'type'	=>	'select',
						'options'	=>	mbdb_get_currency_array(),
						'default'	=> mbdb_get_default_currency($mbdb_options),
					),
					array(
						'name'	=>	__('Edition Title', 'mooberry-book-manager'),
						'id'	=>	'_mbdb_edition_title',
						'type'	=> 'text_medium',
						'desc' => __('First Edition, Second Edition, etc.', 'mooberry-book-mananger'),
					),
				),
			),
		),
	);
				
				
	$meta_boxes['mbdb_excerpt'] = array(
		'id'            => 'mbdb_excerpt',
		'title'         => __('Excerpt', 'mooberry-book-manager'),
		'object_types'  => array( 'mbdb_book', ), // Post type
		'context'       => 'normal',
		'priority'      => 'default',	
		'show_names'    => false, // Show field names on the left
		'fields' => array(
			array(
				'name'    => __('Excerpt', 'mooberry-book-manager'),
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
		'title'         => _x('Reviews', 'noun', 'mooberry-book-manager'),
		'object_types'  => array( 'mbdb_book', ), // Post type
		'context'       => 'normal',
		'priority'      => 'default',
		
		'show_names'    => true, // Show field names on the left
		'fields' => array(
			array(
			'id'          => '_mbdb_reviews',
			'type'        => 'group',
			'description' => __('Add reviews of your book', 'mooberry-book-manager'),
			'options'     => array(
				'group_title'   => _x('Reviews', 'noun', 'mooberry-book-manager') . ' {#}', // {#} gets replaced by row number
				'add_button'    =>  __('Add Review', 'mooberry-book-manager'),
				'remove_button' =>  __('Remove Review', 'mooberry-book-manager'),
				'sortable'      => false, // beta
				),
			
			// Fields array works the same, except id's only need to be unique for this group. Prefix is not needed.
			'fields'      => array(
				array(
					'name' => __('Reviewer Name', 'mooberry-book-manager'),
					'id'   => 'mbdb_reviewer_name',
					'type' => 'text_medium',
					'sanitization_cb' => 'mbdb_validate_reviews', 
				),
				array(
					'name' => _x('Review Link', 'noun', 'mooberry-book-manager'),
					'id'   => 'mbdb_review_url',
					'type' => 'text_url',
					'desc' => 'http://www.someWebsite.com/',
					'attributes' =>  array(
						//'pattern' => '^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})\/?([\/\w \.=\?&\-]*)*\/?',
						'pattern' => '^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6}).*',
					),
				),
				array(
					'name' => _x('Review Website Name', 'noun', 'mooberry-book-manager'),
					'id'   => 'mbdb_review_website',
					'type' => 'text_medium',
					
				),
				array(
					'name'    => _x('Review', 'noun', 'mooberry-book-manager'),
					'id'      => 'mbdb_review',
					'type'	=>	'textarea',
					),
				),
			),
		),
	);
	
		$meta_boxes['mbdb_cover_image'] = array(
		'id'            => 'mbdb_cover_image',
		'title'         => _x('Cover', 'noun', 'mooberry-book-manager'),
		'object_types'  => array( 'mbdb_book', ), // Post type
		'context'       => 'side',
		'priority'      => 'default',
			
		'show_names'    => false, // Show field names on the left
		'allow'			=> array( 'attachment'),
		'fields' => array(
			array(
				 'name' => _x('Book Cover', 'noun', 'mooberry-book-manager'),
				'id' => '_mbdb_cover',
				'type' => 'file',
				'allow' => array(  'attachment' ) // limit to just attachments with array( 'attachment' )
			),
		),
	);
	
	$meta_boxes['mbdb_bookinfo_metabox'] = array(
		'id'            => 'mbdb_bookinfo_metabox',
		'title'         => __('Book Details', 'mooberry-book-manager'),
		'object_types'  => array( 'mbdb_book', ), // Post type
		'context'       => 'side',
		'priority'      => 'default',
			
		'show_names'    => true, // Show field names on the left
		'fields' => array(
			array(
				'name' => __('Subtitle', 'mooberry-book-manager'),
				'id'   => '_mbdb_subtitle',
				'type' => 'text_small',
			),
			array(
				'name' 	=> __('Release Date', 'mooberry-book-manager'),
				'id'	=> '_mbdb_published',
				'type' => 'text_date',
				'desc' => 'yyyy/mm/dd',
				'date_format' => 'Y/m/d',
				'sanitization_cb' => 'mbdb_format_date'
			),
		/*	array(
				'name' => __('Publisher', 'mooberry-book-manager'),
				'id'   => '_mbdb_publisher',
				'type' => 'text_medium',
			),
			array(
				'name' 	=> __('Publisher Website', 'mooberry-book-manager'),
				'id'	=> '_mbdb_publisherwebsite',
				'type'	=> 'text_url',
				'desc' => 'http://www.someWebsite.com/',
				'attributes' =>  array(
					//'pattern' => '^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})\/?([\/\w \.=\?&\-%]*)*\/?',
					'pattern' => '^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6}).*',
					
				),
			), */
			array(
				'name' => __('Publisher', 'mooberry-book-manager'),
				'id'   => '_mbdb_publisherID',
				'type' => 'select',
				'options' => 'mbdb_get_publishers',
				'desc' 	=> 'Set up Publishers in Settings.',
			),
			array(
				'name'	=> __('Goodreads Link', 'mooberry-book-manager'),
				'id'	=> '_mbdb_goodreads',
				'type'	=> 'text_url',
				'desc' => 'http://www.goodreads.com/your/Unique/Text/',
				'attributes' =>  array(
				//	'pattern' => '^(https?:\/\/)?www.goodreads.com([\/\w \.=\?&\-]*)*\/?',
					'pattern' => '^(https?:\/\/)?www.goodreads.com.*',
				),
			),
			
			array(
				'name'	=> __('Series Order', 'mooberry-book-manager'),
				'id'	=> '_mbdb_series_order',
				'desc'	=> __('(leave blank if not part of a series)', 'mooberry-book-manager'),
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
	

	$meta_boxes['mbdb_buylinks_metabox'] = array(
		'id'            => 'mbdb_buylinks_metabox',
		'title'         => _x('Retailer Links', 'noun', 'mooberry-book-manager'),
		'object_types'  => array( 'mbdb_book', ), // Post type
		'context'       => 'side',
		'priority'      => 'default',
			
		
		'show_names'    => true, // Show field names on the left
		'fields' => array(
			array(
				'id'          => '_mbdb_buylinks',
				'type'        => 'group',
				'description' => __('Add links where readers can purchase your book', 'mooberry-book-manager'),
				'options'     => array(
					'group_title'   => _x('Retailer Link', 'noun', 'mooberry-book-manager') . ' {#}', // {#} gets replaced by row number
					'add_button'    => __('Add Retailer Link', 'mooberry-book-manager'),
					'remove_button' => __('Remove Retailer Link', 'mooberry-book-manager'),
					'sortable'      => false, // beta
				),
				// Fields array works the same, except id's only need to be unique for this group. Prefix is not needed.
				'fields'      => array(
					array( 
						'name'    => __('Retailer', 'mooberry-book-manager'),
						'id'      => '_mbdb_retailerID',
						'type'    => 'select',
						'options' => 'mbdb_get_retailers',
						'sanitization_cb' => 'mbdb_validate_retailers',
					),
					array(
						'name'	=> _x('Link', 'noun', 'mooberry-book-manager'),
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
		'title'         => _x('Download Links', 'noun', 'mooberry-book-manager'),
		'object_types'  => array( 'mbdb_book', ), // Post type
		'context'       => 'side',
		'priority'      => 'low',
			
		'show_names'    => true, // Show field names on the left
		'fields' => array(
			array(
				'id'          => '_mbdb_downloadlinks',
				'type'        => 'group',
				'description' => __('If your book is available to download for free, add the links for each format.', 'mooberry-book-manager'),
				'options'     => array(
					'group_title'   => _x('Download Link', 'noun', 'mooberry-book-manager') . ' {#}',  // {#} gets replaced by row number
					'add_button'    => __('Add Download Link', 'mooberry-book-manager'),
					'remove_button' =>  __('Remove Download Link', 'mooberry-book-manager'),
					'sortable'      => false, // beta
				),
				// Fields array works the same, except id's only need to be unique for this group. Prefix is not needed.
				'fields'      => array(
					array( 
						'name'    => _x('Format', 'noun', 'mooberry-book-manager'),
						'id'      => '_mbdb_formatID',
						'type'    => 'select',
						'options' => 'mbdb_get_formats',
						'sanitization_cb' => 'mbdb_validate_downloadlinks',
					),
					array(
						'name'	=> _x('Link', 'noun', 'mooberry-book-manager'),
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
	
	

	
	return apply_filters('mbdb_book_meta_boxes', $meta_boxes);
	
}
