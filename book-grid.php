<?php

/**
 *  This file handles the meta boxes for the book grid settings on pages
 *  as well as retrieving the daa for and displaying the book grid
 *  
 */



/*******************************************************************
	META BOXES
******************************************************************/

/**
 *  
 *  Keep a placholder metabox for book grids on pages
 *  but allow user to dismiss it
 *  
 *  @since 3.4
 */
 add_action( 'add_meta_boxes', 'mbdb_book_grid_placeholder_metabox', 10 );
function mbdb_book_grid_placeholder_metabox() {
	$mbdb_options = get_option('mbdb_options');
	if (array_key_exists('dismiss_book_grids_notice', $mbdb_options) && $mbdb_options['dismiss_book_grids_notice'] == 'yes') {
		return;
	}
	
	add_meta_box( 'mbdb_book_grid_placeholder', 
				__('Book Grid Settings', 'mooberry-book-manager'), 
				'mbdb_display_book_grid_placeholder_metabox', 
				'page', 
				'normal', 
				'default' );
}

function mbdb_display_book_grid_placeholder_metabox($post, $args) {
	?>
	<p><?php _e('Looking for the Book Grid Settings?  As of version 3.4, there is now separate menu in your Dashboard for adding Book Grids.', 'mooberry-book-manager'); ?></p>
	<a class="button" id="mbdb_book_grid_placeholder_dismiss"><?php _e('Dismiss this notice', 'mooberry-book-manager'); ?></a>
	<img id="mbdb_book_grid_dismiss_loader" style="display:none;" src="<?php echo MBDB_PLUGIN_URL; ?>includes/assets/ajax-loader.gif"/>
	<?php

}

// ajax dismiss notice
add_action( 'wp_ajax_mbdb_book_grid_placeholder_dismiss', 'mbdb_book_grid_placeholder_dismiss' );	
function mbdb_book_grid_placeholder_dismiss() {

	$nonce = $_POST['security'];

	// check to see if the submitted nonce matches with the
	// generated nonce we created earlier
	if ( ! wp_verify_nonce( $nonce, 'mbdb_book_grid_placeholder_dismiss_ajax_nonce' ) ) {
		die ( );
	}
	
	
	// update the option
	$mbdb_options = get_option('mbdb_options');
	$mbdb_options['dismiss_book_grids_notice'] = 'yes';
	update_option('mbdb_options', $mbdb_options);
	
	
	wp_die();
}


/**
 *  Creates metabox to be added to pages for book grids
 *  
 *  
 *  
 *  @since 1.0
 *  @since 3.4 Remove Display Yes/No and Additional Info
 *  
 *  @access public
 */
add_action( 'cmb2_admin_init', 'mbdb_book_grid_meta_boxes' );
function mbdb_book_grid_meta_boxes( ) {
	
	
	$mbdb_book_grid_metabox = new_cmb2_box( array(
		'id'			=> 'mbdb_book_grid_metabox',
		'title'			=> __('Book Grid Settings', 'mooberry-book-manager'),
		'object_types'	=> array('mbdb_book_grid'), //array( 'page' ),
		'context'		=> 'normal',
		'priority'		=> 'default',
		'show_names'	=> true)
	);
	
	//3.4
		/*
	$mbdb_book_grid_metabox->add_field( array(
			'name'	=> __('Display Books on This Page?', 'mooberry-book-manager'),
			'id'	=> '_mbdb_book_grid_display',
			'type'	=> 'select',
			'default'	=> 'no',
			'options'	=> array(
				'yes'	=> __('Yes', 'mooberry-book-manager'),
				'no'	=> __('No', 'mooberry-book-manager'),
			),
		)
	);
	*/
	$mbdb_book_grid_metabox->add_field( array(
			'name' 	=> __('Books to Display', 'mooberry-book-manager'),
			'id' 	=> '_mbdb_book_grid_books',
			'type'	=> 'select',
			'options'	=> mbdb_book_grid_selection_options(),
		)
	);
			
	$mbdb_book_grid_metabox->add_field( array(
			'name' 	=> __('Select Books', 'mooberry-book-manager'),
			'id'	=> '_mbdb_book_grid_custom',
			'type'	=> 'multicheck',
			'options' => mbdb_get_book_array(),
			
		)
	);
	
	$mbdb_book_grid_metabox->add_field( array(
			'name'	=> __('Select Genres', 'mooberry-book-manager'),
			'id'	=> '_mbdb_book_grid_genre',
			'type' 	=> 'multicheck',   
			'options' => mbdb_get_term_options('mbdb_genre'),
		)
	);

	$mbdb_book_grid_metabox->add_field( array(
			'name'	=> __('Select Series', 'mooberry-book-manager'),
			'id'	=> '_mbdb_book_grid_series',
			'type' 	=> 'multicheck',   
			'options'	=>	mbdb_get_term_options('mbdb_series'),
		)
	);
	
	$mbdb_book_grid_metabox->add_field( array(
			'name'	=>	__('Select Tags', 'mooberry-book-manager'),
			'id'	=>	'_mbdb_book_grid_tag',
			'type' 	=> 'multicheck',   
			'options'	=>	mbdb_get_term_options('mbdb_tag'),
		)
	);
		
	$mbdb_book_grid_metabox->add_field( array(
			'name'	=>	__('Select Publishers', 'mooberry-book-manager'),
			'id'	=> '_mbdb_book_grid_publisher',
			'type'	=>	'multicheck',
			'options'	=> mbdb_get_publishers('no'),
		)
	);
			
	$mbdb_book_grid_metabox->add_field( array(
			'name'	=>	__('Select Editors', 'mooberry-book-manager'),
			'id'	=> '_mbdb_book_grid_editor',
			'type'	=>	'multicheck',
			'options'	=> mbdb_get_term_options('mbdb_editor'),
		)
	);
			
	$mbdb_book_grid_metabox->add_field( array(
			'name'	=>	__('Select Illustrators', 'mooberry-book-manager'),
			'id'	=> '_mbdb_book_grid_illustrator',
			'type'	=>	'multicheck',
			'options'	=> mbdb_get_term_options('mbdb_illustrator'),
		)
	);
	
	$mbdb_book_grid_metabox->add_field( array(
			'name'	=>	__('Select Cover Artists', 'mooberry-book-manager'),
			'id'	=> '_mbdb_book_grid_cover_artist',
			'type'	=>	'multicheck',
			'options'	=> mbdb_get_term_options('mbdb_cover_artist'),
		)
	);
	
	
	$group_by_options = mbdb_book_grid_group_by_options();
	$count = count($group_by_options);
	
	for($x=1; $x <$count; $x++) {
		$mbdb_book_grid_metabox->add_field( array(
				'name'	=>	__('Group Books By', 'mooberry-book-manager'),
				'id'	=>	'_mbdb_book_grid_group_by_level_' . $x,
				'type'	=>	'select',
				'options'	=> $group_by_options,
			)
		);
		
		// put a warning at the 5th level
		if ( $x == 5 ) {
			$mbdb_book_grid_metabox->add_field( array(
					'name'	=> __('Warning: Setting more than 5 levels could cause the page to timeout and not display.', 'mooberry-book-manager'),
					'type'	=>	'title',
					'id'	=> '_mbdb_book_grid_warning',
					'attributes'	=> array(
							'display'	=> 'none'
					)
				)
			);
		}
		
	}
	
	$mbdb_book_grid_metabox->add_field( array(
			'name'	=> __('Order By', 'mooberry-book-manager'),
			'id'	=> '_mbdb_book_grid_order',
			'type'	=> 'select',
			'options'	=> mbdb_book_grid_order_options(),
			'after'	=> '<div id="_mbdb_bookd_grid_custom_order" style="display:none; font-weight:bold; padding-top: 2em; padding-bottom: 2em;">' . __('Drag and drop the books into the order you want:', 'mooberry-book-manager') . '<ul id="_mbdb_book_grid_book_list">' . mbdb_output_book_grid_custom_order() . '</ul></div>',
		)
	);
	
	$mbdb_book_grid_metabox->add_field( array(
			'name'	=>	__('Use default cover height?', 'mooberry-book-manager'),
			'id'	=>	'_mbdb_book_grid_cover_height_default',
			'type'	=>	'select',
			'default'	=>	'yes',
			'options'	=>	array(
				'yes'	=> __('Yes','mooberry-book-manager'),
				'no'	=>	__('No','mooberry-book-manager'),
			),
		)
	);
	
	$mbdb_book_grid_metabox->add_field( array(
			'name'	=> __('Book Cover Height (px)', 'mooberry-book-manager'),
			'id'	=> '_mbdb_book_grid_cover_height',
			'type'	=> 'text_small',
			'attributes' => array(
					'type' => 'number',
					'pattern' => '\d*',
					'min' => 50,
			),
		)
	);
	
	//3.4
	/*
	$mbdb_book_grid_metabox->add_field( array(
			'name'	=> __('Additional Content (bottom)', 'mooberry-book-manager'),
			'id'	=> '_mbdb_book_grid_description_bottom',
			'type'	=> 'wysiwyg',
			'description' => __('This displays under the book grid.', 'mooberry-book-manager'), 
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
*/	
	$mbdb_book_grid_metabox = apply_filters('mbdb_book_grid_meta_boxes', $mbdb_book_grid_metabox);
		
}

function mbdb_output_book_grid_custom_order( ) { //$id, $object_id, $a) {
	
	
	if (!isset($_POST['post']) ) {
		if (!isset($_GET['post']) ) {
			return '';
		} else {
			$post_id = $_GET['post'];
		}
	} else {
		$post_id = $_POST['post'];
	}
	
	$output = '';
	
	
	$custom_order = get_post_meta( $post_id , '_mbdb_book_grid_order_custom', true);
	
	if ($custom_order) {
		foreach ( $custom_order as $book ) {
	
			$output .= '<li id="mbdb_custom_book_order_book_' . $book . '" class="ui-state-default"><span class="ui-icon"></span>' . 
			get_the_title( $book ) . '</li>';
		}
	}
	return $output;
	
}




// ajax save book list order
add_action( 'wp_ajax_save_book_list_order', 'mbdb_save_book_list_order' );	
function mbdb_save_book_list_order() {
	

	$nonce = $_POST['security'];

	// check to see if the submitted nonce matches with the
	// generated nonce we created earlier
	if ( ! wp_verify_nonce( $nonce, 'mbdb_book_grid_ajax_nonce' ) ) {
		die ( );
	}

	// check for books to be blank
	if (isset($_POST['books']) && $_POST['books'] != '') {
	
		// $_POST['posts']  = "mbdb_custom_book_order_book[]=2131&mbdb_custom_book_order_book[]=2135&mbdb_custom_book_order_book[]=2133&mbdb_custom_book_order_book[]=2243&mbdb_custom_book_order_book[]=2245&mbdb_custom_book_order_book[]=2247&mbdb_custom_book_order_book[]=2249&mbdb_custom_book_order_book[]=2251&mbdb_custom_book_order_book[]=2253&mbdb_custom_book_order_book[]=2255&mbdb_custom_book_order_book[]=2257&mbdb_custom_book_order_book[]=2259"
		
		// parse_str($_POST['books']) creates variable $mbdb_custom_book_order_book which is an array of book ids
		parse_str($_POST['books']);
	
		update_post_meta($_POST['pageID'], '_mbdb_book_grid_order_custom', $mbdb_custom_book_order_book);
	}
	
	wp_die();
}

/***********************************************
 *  preview meta box
 *  
 *  @since 3.4
 *  
 **********************************************/
 add_action( 'add_meta_boxes', 'mbdb_book_grid_preview_meta_box' );
 function mbdb_book_grid_preview_meta_box() {
	 add_meta_box( 'mbdb_book_grid_preview_metabox',
					__('Preview', 'mooberry-book-manager'),
					'mbdb_book_grid_preview_metabox_display',
					'mbdb_book_grid',
					'normal',
					'low'
				);
	
	add_meta_box( 'mbdb_book_grid_shortcode_metabox',
					__('Shortcode', 'mooberry-book-manager'),
					'mbdb_book_grid_shortcode_metabox_display',
					'mbdb_book_grid',
					'side',
					'default'
				);
 }
 
 function mbdb_book_grid_preview_metabox_display( $post ) {

	//echo '<a class="button" id="mbdb_update_preview">Show Preview</a>';
?>
	<input type="button" class="button button-secondary" id="mbdb_update_preview" value="<?php _e('Show Preview'); ?>" />
	<img id="mbdb_preview_loading" style="display:none;" src="<?php echo MBDB_PLUGIN_URL; ?>includes/assets/ajax-loader.gif"/><div id="mbdb_book_grid_preview">
	<?php echo mbdb_bookgrid_content( $post->ID ); ?>
	</div>
<?php
 }
 
 function mbdb_book_grid_shortcode_metabox_display( $post ) {
?>
<p><label for="mbdb_book_grid_shortcode"><?php _e('Copy this code into a page, blog post, etc. to display this book grid.', 'mooberry-book-manager'); ?></label></p>
	<input type="text" readonly="readonly" class="widefat" id="mbdb_book_grid_shortcode" value="[mbm_book_grid id='<?php echo $post->ID ?>']" />
<?php
 }
 
  
 
 /******************************************************
  *  
  *  AJAX function to update the preview
  *  
  *  @since 3.4
  *  
  ******************************************************/
add_action( 'wp_ajax_mbdb_update_book_grid_preview', 'mbdb_update_book_grid_preview' );
function mbdb_update_book_grid_preview() {
	//check_ajax_referer( 'mbdb_book_grid_preview_ajax_nonce', 'security' );
	

	// turn $_POST['grid_options'] into format for mbdb_bookgrid_content:
	// [ id ] = Array(
	//		[ 0 ] = value
	// )
	
	$grid_options = array_map( 'mbdb_make_array', $_POST['grid_options']);
	
	
	 echo mbdb_bookgrid_content( 0, $grid_options);
	
	wp_die(); 
	
}
 
 function mbdb_make_array( $element ) {
	 if (is_array($element) ) {
		 return array(serialize($element));
	 } else {
		return array($element);
	 }
 }

/**************************************************************
 *  		SHORTCODE
 *  
 *  @since 3.4
 *  
 *************************************************************/
add_shortcode( 'mbm_book_grid', 'mbdb_shortcode_book_grid'  );
function mbdb_shortcode_book_grid( $attr, $content ) {
	$attr = shortcode_atts(array('id' => ''), $attr);
	if ($attr['id'] == '') {
		return '';
	}
	return mbdb_bookgrid_content( $attr['id'] );
}
 
// add shortcode button to editor
add_action('media_buttons', 'mbdb_add_book_grid_shortcode_button', 30);
function mbdb_add_book_grid_shortcode_button() {
	if (!in_array(get_post_type(), array( 'page', 'post') ) ) {
		return;
	}	
	$args = array('posts_per_page' => -1,
				'post_type' => 'mbdb_book_grid',
				'post_status'=>	'publish',
				'orderby' => 'post_title',
				'order' => 'ASC'
			);
	
	$results = get_posts(  $args );
	$grids = array();
	foreach( $results as $grid ) {
		$grids[$grid->ID] = $grid->post_title;
	}
	wp_reset_postdata();
?><style>
	.ui-dialog { z-index: 99999 !important; }
   
	.ui-dialog .ui-dialog-titlebar-close span { margin-left: -8px; margin-top: -8px; }
  </style>
	<a href="#" id="mbdb_add_book_grid" class="button"><?php _e('Add Book Grid', 'mooberry-book-manager'); ?></a>
	<div id="mbdb_book_grid_shortcode_dialog" title="<?php _e('Add Book Grid', 'mooberry-book-manager'); ?>">
  
 
      <label for="mbdb_book_grids">Book Grid:</label>
	  <?php echo mbdb_dropdown( 'mbdb_book_grids', $grids, null, 'no' ); ?>
      
 
      <!-- Allow form submission with keyboard without duplicating the dialog button -->
      <input type="submit" tabindex="-1" style="position:absolute; top:-1000px">
 </div>
	<?php
	
}
 

/****************************************************************************
		GET DATA
*****************************************************************************/

/**
 *  Get the data and generate output content for the book grid
 *  
 *  
 *  
 *  @since 1.0
 *  @since 3.4 added book grid id and otpions parameters
 *  
 *  @return content to be displayed
 *  
 *  @access public
 */
 function mbdb_bookgrid_content( $book_grid_id = null, $options = null ) {
	global $post;
	$content ='';
	
	// if book_grid_id is null this is coming from an old page with a book grid on it
	// not from the shortcode. This shouldn't happen any more
	if ($book_grid_id === null) {
		global $post;
		$book_grid_id = $post->ID;
	} 
	
	// if book_grid_id is 0 this is coming from the preview on the book grid screen
	// and the settings should come from $options not the database
	if ( $book_grid_id === 0  ) {
		$mbdb_book_grid_meta_data = $options;
	} else {
		$mbdb_book_grid_meta_data = get_post_meta( $book_grid_id  );
	}
	
	if ($mbdb_book_grid_meta_data == null) {
		return;
	}
	
	
	// VALIDATE THE INPUTS
	// make sure the group value is valid. ie it could be "author" but the author plugin has since been deactivated.
	// loop through the group by levels
	// set up the group arrays
	// stop at the first one that is none
	// if there is a series, add none after that and stop
	$groups = array();
	$current_group = array();
	$group_by_levels = mbdb_book_grid_group_by_options();

	for($x = 1; $x< count($group_by_levels); $x++) {
		$key = '_mbdb_book_grid_group_by_level_' . $x;
		
		// if there the key doesn't exist for whatever reason, default to none
		if (!array_key_exists($key, $mbdb_book_grid_meta_data) ) {
			$groups[$x] = 'none';
			$current_group['none'] = 0;
			break;
		}
		
		// if the group by level doesn't match one of the options, default to none
		if (!array_key_exists($mbdb_book_grid_meta_data[$key][0], $group_by_levels)) {
			$groups[$x] = 'none';
			$current_group['none'] = 0;
			break;
		}
		
		$group_by_dropdown = $mbdb_book_grid_meta_data[$key][0];
		$groups[$x] = $group_by_dropdown;
		$current_group[$group_by_dropdown] = 0;
		if ( $group_by_dropdown == 'none' ) {
			break;
		} 
		if ($group_by_dropdown == 'series' ) {
			$groups[$x+1] = 'none';
			$current_group['none'] = 0;
			break;
		}
	}
	
	// set the sort
	if (array_key_exists('_mbdb_book_grid_order', $mbdb_book_grid_meta_data)) {
		$sort = mbdb_set_sort( $groups, $mbdb_book_grid_meta_data['_mbdb_book_grid_order'][0]);
	} else {
		$sort = mbdb_set_sort( $groups, 'titleA' );
	}
	
	// if selection isn't set, default to "all"
	if (array_key_exists('_mbdb_book_grid_books', $mbdb_book_grid_meta_data) ) {
		$selection = $mbdb_book_grid_meta_data['_mbdb_book_grid_books'][0];
	} else {
		$selection = 'all';
	}
	
	// turn selected_ids into an array
	// or null if there aren't any
	if (array_key_exists('_mbdb_book_grid_' . $selection, $mbdb_book_grid_meta_data)) {
		$selected_ids = unserialize($mbdb_book_grid_meta_data['_mbdb_book_grid_' . $selection][0]);
	} else {
		$selected_ids = null;
	}
	
	// start off the recursion by getting the first group
	$level = 1;
	
	$books = mbdb_get_group($level, $groups, $current_group, $selection, $selected_ids, $sort, null); 
	
	// $books now contains the complete array of books to display in the grid
	
	// if the sort is custom, we have to manually sort the books now
	if ($sort == 'custom') {
		if (array_key_exists('_mbdb_book_grid_order_custom', $mbdb_book_grid_meta_data) ) {
			
			$sort_order = unserialize($mbdb_book_grid_meta_data['_mbdb_book_grid_order_custom'][0]);
			
			$sorted_books = array();
			$book_ids = array();
			foreach ($books as $key => $book) {
				$book_ids[$book->book_id] = $key;
			}
			foreach ($sort_order as $book_id) {
				$sorted_books[] = $books[$book_ids[$book_id]];
				
			}
			$books = $sorted_books; 
		}
	}
	
	
		// get the display output content
	if (!array_key_exists('_mbdb_book_grid_cover_height_default', $mbdb_book_grid_meta_data)) {
		$default = 'yes';
	} else {
		$default = $mbdb_book_grid_meta_data['_mbdb_book_grid_cover_height_default'][0];
	}
	if (!array_key_exists('_mbdb_book_grid_cover_height', $mbdb_book_grid_meta_data)) {
		$height = 200;
	} else {
		$height = $mbdb_book_grid_meta_data['_mbdb_book_grid_cover_height'][0];
	}
	
	$content =  mbdb_display_grid($books, 0, $book_grid_id, $default, $height);

	// find all the book grid's postmeta so we can display it in comments for debugging purposes
	$grid_values = array();
	foreach ($mbdb_book_grid_meta_data as $key => $data) {
		if ( substr($key, 0, 5) == '_mbdb' ) {
			$grid_values[$key] = $data[0];
		}
	}
	$content = '<!-- Grid Parameters:
				' . print_r($grid_values, true) . ' -->' . $content;
				
/*
	// add on bottom text
	if (array_key_exists('_mbdb_book_grid_description_bottom', $mbdb_book_grid_meta_data)) {
		$book_grid_description_bottom = $mbdb_book_grid_meta_data[ '_mbdb_book_grid_description_bottom'][0];
	} else {
		$book_grid_description_bottom = '';
	}
	*/
	return $content; // . $book_grid_description_bottom;
	
}

/**
 *  Return one group of books for the grid
 *  This is called recursively until the group "none" is found
 *  
 *  @since 1.0
 *  @since 3.0 re-factored
 *  
 *  @param [int] $level         the nested level of the grid we're currently one
 *  @param [array] $groups       the groups in grid
 *  @param [array] $current_group  the id of the current group. Could be if of a
 *  								 series, genre, publisher, illustrator, etc.
 *  @param [string] $selection     what selection of books for the grid ('all',
 *  								 'unpublished', 'series', etc.)
 *  @param [array] $selected_ids  ids of the selection
 *  @param [string] $sort          represents sort, ie 'titleA', 'titleD', 
 *  								'series, etc.
 *  @param [array] $book_ids      optional list of book_ids to filter by, useful
 *  								for add-on plugins to add on to grid (ie MA)
 *  
 *  @return array of books for this group
 *  
 *  @access public
 */
function mbdb_get_group($level, $groups, $current_group, $selection, $selected_ids, $sort, $book_ids) { 
	
	do_action('mbdb_book_grid_pre_get_group', $level, $groups, $current_group, $selection, $selected_ids, $sort, $book_ids ); 
	
	$books = array();
	$taxonomies = get_object_taxonomies( 'mbdb_book', 'objects' );
	$tax_names = array_keys($taxonomies);
	
	switch ( $groups[$level] ) {
		// break the recursion by actually getting the books
		case 'none':
			$books =  MBDB()->books->get_ordered_selection($selection, $selected_ids, $sort, $book_ids, $current_group ); 
			break;
		case 'publisher':
			$books = mbdb_get_books_by_publisher($level, $groups, $current_group, $selection, $selected_ids, $sort, $book_ids ); 
			break;
		default:
			// see if it's a taxonomy
			// don't just assume it's a taxonomy because it could be
			// that there's an add-on plugin (ie MA) that's added
			// a new group
			if (in_array('mbdb_' . $groups[$level], $tax_names)) {
				$books = mbdb_get_books_by_taxonomy($level, $groups, $current_group, $selection, $selected_ids, $sort, $book_ids ); 
			}
	}
	
	do_action('mbdb_book_grid_post_get_group', $level, $groups, $current_group, $selection, $selected_ids, $sort, $book_ids ); 
	
	return apply_filters('mbdb_book_grid_get_group_books', $books, $level, $groups, $current_group, $selection, $selected_ids, $sort, $book_ids ); 
}
			
/**
 *  Get books by publisher
 *  
 *  @since 
 *  @param [int] $level         the nested level of the grid we're currently one
 *  @param [array] $groups       the groups in grid
 *  @param [array] $current_group  the id of the current group. Could be if of a
 *  								 series, genre, publisher, illustrator, etc.
 *  @param [string] $selection     what selection of books for the grid ('all',
 *  								 'unpublished', 'series', etc.)
 *  @param [array] $selected_ids  ids of the selection
 *  @param [string] $sort          represents sort, ie 'titleA', 'titleD', 
 *  								'series, etc.
 *  @param [array] $book_ids      optional list of book_ids to filter by, useful
 *  								for add-on plugins to add on to grid (ie MA)
 *  
 *  @return array of books
 *  
 *  @access public
 */
 function mbdb_get_books_by_publisher($level, $groups, $current_group, $selection, $selected_ids, $sort, $book_ids ) { 	
 
	$books = array();
	
	// Get ones w/o publishers first
	$current_group[ $groups[ $level ] ] = -1;
	
	// recursively get the next nested group of books
	$results = mbdb_get_group( $level + 1, $groups, $current_group, $selection, $selected_ids, $sort, $book_ids ); 
	
	// only return results if are any so that headers of empty groups
	// aren't displayed
	if ( count($results) > 0 ) {
		$books[ apply_filters('mbdb_book_grid_no_publisher_heading', __('No Publisher Specified', 'mooberry-book-manager')) ] = $results;
	}
	
	// loop through each publisher
	// and recursively get the next nested group of books for that publisher
	//$mbdb_options = get_option('mbdb_options');
	$mbdb_options = get_option('mbdb_options'); //mbdb_get_options('mbdb_options');//'mbdb_options');
	if (array_key_exists('publishers', $mbdb_options)) {
		$publishers = $mbdb_options['publishers'];
		foreach($publishers as $publisher) {
			$current_group[ $groups [ $level ] ] = $publisher['uniqueID'];
			$results = mbdb_get_group( $level + 1, $groups, $current_group, $selection, $selected_ids, $sort, $book_ids ); 
			
			// only return results if are any so that headers of empty groups
			// aren't displayed
			if (count($results)>0) {
				$books[ apply_filters('mbdb_book_grid_heading', __('Published by ', 'mooberry-book-manager') . $publisher['name'])] = $results;
			}
		}
	}
	return $books;
}

/**
 *  Get books by taxonomy
 *  
 *  @since 
 *  @param [int] $level         the nested level of the grid we're currently one
 *  @param [array] $groups       the groups in grid
 *  @param [array] $current_group  the id of the current group. Could be id of a
 *  								 series, genre, publisher, illustrator, etc.
 *  @param [string] $selection     what selection of books for the grid ('all',
 *  								 'unpublished', 'series', etc.)
 *  @param [array] $selected_ids  ids of the selection
 *  @param [string] $sort          represents sort, ie 'titleA', 'titleD', 
 *  								'series, etc.
 *  @param [array] $book_ids      optional list of book_ids to filter by, useful
 *  								for add-on plugins to add on to grid (ie MA)
 *  
 *  @return array of books
 *  
 *  @access public
 */			
function mbdb_get_books_by_taxonomy($level, $groups, $current_group, $selection, $selected_ids, $sort, $book_ids ) { 
		
	$books = array();
		
	// Get ones not in the taxonomy first
	$current_group[ $groups[ $level ] ] = -1;
	
	// recursively get the next nested group of books
	$results = mbdb_get_group($level + 1, $groups, $current_group, $selection, $selected_ids, $sort, $book_ids ); 
	
	// only return results if are any so that headers of empty groups
	// aren't displayed
	if (count($results)>0) {
		switch ($groups[$level]) {
			case 'genre':
				$empty = apply_filters('mbdb_book_grid_uncategorized_heading', __('Uncategorized', 'mooberry-book-manager'));
				break;
			case 'series':
				$empty = apply_filters('mbdb_book_grid_standalones_heading', __('Stand-Alone Books', 'mooberry-book-manager'));
				break;
			case 'tag':
				$empty = apply_filters('mbdb_book_grid_untagged_heading', __('Untagged', 'mooberry-book-manager'));
				break;
			case 'editor':
				$empty = apply_filters('mbdb_book_grid_uncategorized_heading', __('No Editor Specified', 'mooberry-book-manager'));
				break;
			case 'illustrator':
				$empty = apply_filters('mbdb_book_grid_uncategorized_heading', __('No Illustrator Specified', 'mooberry-book-manager'));
				break;
			case 'cover_artist':
				$empty = apply_filters('mbdb_book_grid_uncategorized_heading', __('No Cover Artist Specified', 'mooberry-book-manager'));
				break;
		}	
		$books[ $empty] = $results;
	}
	
	// loop through each term
	// and recursively get the next nested group of books for that term
	$terms_query = array('orderby' => 'slug',
				'hide_empty' => true);

	// if we're grouping by what we're filtering by, only get terms that we're filtering on
	if ($groups[$level] == $selection) {
		$terms_query['include'] = $selected_ids;
	}
	
	$all_terms = get_terms( 'mbdb_' . $groups[$level], $terms_query);
	$taxonomy = get_taxonomy('mbdb_' . $groups[$level]);

	// loop through all the terms
	foreach ($all_terms as $term) {
		$current_group[$groups[$level]] = $term->term_id;
		
		$results = mbdb_get_group($level+1, $groups, $current_group, $selection, $selected_ids, $sort, $book_ids ); 
		
		// only return results if are any so that headers of empty groups
		// aren't displayed
		if (count($results)>0) {
			/*
			if (in_array($groups[$level], array('genre', 'series', 'tag'))) {
				$heading = $term->name . ' ' . $taxonomy->labels->singular_name;
			} else {
				*/
				$heading = $taxonomy->labels->singular_name . ': ' . $term->name;	
		//	}
			$books[ apply_filters('mbdb_book_grid_heading', $heading )] = $results;
		}
	}
	return $books;
}

/**
 *  
 *  If any of the groups is a series, order by series
 *  otherwise order by whatever came in
 *  
 *  
 *  @since 3.0
 *  @param [array] $groups list of groups for the grid
 *  @param [string] $sort   sort setting
 *  
 *  @return sort setting
 *  
 *  @access public
 */
function mbdb_set_sort($groups, $sort) {
	if (in_array('series', $groups)) {
		return 'series_order';
	} else {
		return $sort;
	}
}


/*****************************************************************************
			DISPLAY GRID
*******************************************************************************/
	
/**
 *  Loop through the $books array and generate the HTML output for the
 *  grid, including printing out the headings and indenting at each
 *  nested level
 *  
 *  Recursively called for each level
 *  
 *  @since 1.0
 *  @since 2.0 made responsive
 *  @since 3.0 re-factored
 *  @since 3.4 added book_grid_id and cover_height parameters
 *  
 *  @param [array] $mbdb_books nested array of books in grid
 *  @param [int] $l           current level to display
 *  
 *  @return Return_Description
 *  
 *  @access public
 */
function mbdb_display_grid($mbdb_books,  $l, $book_grid_id = null, $cover_height_default = null, $cover_height = null ) {
	
			
	// grab the coming soon image
	$mbdb_options = get_option('mbdb_options');
	//$mbdb_options = mbdb_get_options('mbdb_options'); //('mbdb_options');
	$coming_soon_image = $mbdb_options['coming-soon'];
	
	// indent the grid by 15px per depth level of the array
	do_action('mbdb_book_grid_pre_div', $l);
	
	$content = '<div class="mbm-book-grid-div" style="padding-left:' . (15 * $l) . 'px;">';
	
	if (count($mbdb_books)>0) {
	
		// because the index of the array could be a genre or series name and not a sequential index use array_keys to get the index
		// if the first element in the array is an object that means there's NOT another level in the array
		// so just print out the grid and skip the rest
		 $the_key = array_keys($mbdb_books);
		 if (count($the_key)>0) {
		
			// this breaks the recursion
			if ( gettype( $mbdb_books[$the_key[0]] ) == 'object') {
				foreach ($mbdb_books as $book) {
					do_action('mbdb_book_grid_pre_div',  $l);
					$content .= mbdb_output_grid_book($book, $coming_soon_image, $book_grid_id, $cover_height_default, $cover_height );
				}
				$content .= '</div>'; 
				do_action('mbdb_book_grid_post_div', $l);
				return apply_filters('mbdb_book_grid_content', $content, $l);
			}
		 }
		 
		 // loop through each book
		foreach ($mbdb_books as $key => $set) {
			// If a label is set and there's at least one book, print the label
			if ( $key && count( $set ) > 0 ) {
				// set the heading level based on the depth level of the array
				do_action('mbdb_book_grid_pre_heading',  $l, $key);
				// start the headings at H2
				$heading_level = $l + 2;
				// Headings can only go to H6
				if ($heading_level > 6) {
					$heading_level = 6;
				}
				// display the heading
				$content .= '<h' . $heading_level . ' class="mbm-book-grid-heading' . ( $l + 1 ) . '">' . esc_html($key) . '</h' . $heading_level .'>';
				do_action('mbdb_book_grid_post_heading', $l, $key);
			}	
			if ( gettype( $set ) != 'object') {
				do_action('mbdb_book_grid_pre_recursion',$set,  $l+1);
				$content .= mbdb_display_grid($set,  $l+1, $book_grid_id, $cover_height_default, $cover_height );
				do_action('mbdb_book_grid_post_recursion', $set,  $l+1);
			} 
		}
	} else {
		do_action('mbdb_book_grid_no_books_found');
		$content = apply_filters('mbdb_book_grid_books_not_found', $content . __('Books not found', 'mooberry-book-manager'));
	}
	$content .= '</div>'; 
	do_action('mbdb_book_grid_post_div', $l);
	return apply_filters('mbdb_book_grid_content', $content, $l);
}

/**
 *  Generate the HTML to display a book and its cover image
 *  coming soon object passed as parameter because it's stored in 
 *  the options and this function is called several times
 *  
 *  @since 1.0
 *  @since 2.0 made responsive
 *  @since 3.0 re-factored, added alt text
 *  @since 3.4 added book grid id and height parameters and cover height into specific HTML tags
 *  
 *  @param [obj] $book              book object
 *  @param [string] $coming_soon_image coming soon image
 *  
 *  @return html output
 *  
 *  @access public
 */
function mbdb_output_grid_book($book, $coming_soon_image, $book_grid_id = null, $cover_height_default = null, $cover_height = null ) {

	if ($book_grid_id === null) {
		global $post;
		$mbdb_book_grid_cover_height = mbdb_get_grid_cover_height($post->ID);
	} else {
		$mbdb_book_grid_cover_height = mbdb_get_grid_cover_height2( $cover_height_default, $cover_height);
	}
	
	
	
	
	// 3.4.4 -- uses get_attachemnt_image_src
	//$image = $book->cover; 
	$image = wp_get_attachment_image_src ($book->cover_id, 'large');
	$default_alt = __('Book Cover:', 'mooberry-book-manager') . ' ' . $book->post_title;
	
	$content = '<span class="mbdb_float_grid" style="height: ' . ($mbdb_book_grid_cover_height + 50) . 'px; width: ' . $mbdb_book_grid_cover_height . 'px;">';
	if ($image) {
		$alt = mbdb_get_alt_text( $book->cover_id, $default_alt );
		$content .= '<div class="mbdb_grid_image">';
		$content = apply_filters('mbdb_book_grid_pre_image', $content, $book->book_id, $image[0]);
		$content .= '<a class="mbm-book-grid-title-link" href="' . esc_url(get_permalink($book->book_id)) . '"><img style="height: ' . $mbdb_book_grid_cover_height . 'px;" src="' . esc_url($image[0]) . '" ' . $alt . ' /></a>';
		$content = apply_filters('mbdb_book_grid_post_image', $content, $book->book_id, $image[0]);
		$content .= '</div>';
		
	} else {
		if (isset($coming_soon_image)) {
			$alt = mbdb_get_alt_text( 0, $default_alt );
			$content .= '<div class="mbdb_grid_image">';
			$content = apply_filters('mbdb_book_grid_pre_placeholder_image', $content, $book->book_id, $coming_soon_image);
			$content .= '<a class="mbm-book-grid-title-link" href="' . esc_url(get_permalink($book->book_id)) . '"><img style="height: ' . $mbdb_book_grid_cover_height . 'px;" src="' . esc_url($coming_soon_image) . '" ' . $alt . ' /></a></div>';
			$content = apply_filters('mbdb_book_grid_post_placeholder_image', $content, $book->book_id, $coming_soon_image);
		} else {
			$content .= '<div class="mbdb_grid_no_image" style="height: ' . $mbdb_book_grid_cover_height . 'px; width: ' . $mbdb_book_grid_cover_height . ';">';
			$content = apply_filters('mbdb_book_grid_no_image', $content, $book->book_id);
			$content .= '</div>';
		}
	}

	
	$content .= '<span class="mbdb_grid_title">';
	$content = apply_filters('mbdb_book_grid_pre_link', $content, $book->book_id, $book->post_title);
	$content .= '<a class="mbm-book-grid-title-link" href="' . esc_url(get_permalink($book->book_id)) . '">' . esc_html($book->post_title) . '</a>';
	$content = apply_filters('mbdb_book_grid_post_link', $content, $book->book_id, $book->post_title);
	$content .= '</span></span>';

	return $content;
}