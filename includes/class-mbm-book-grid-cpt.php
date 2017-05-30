<?php

/**
 * The Mooberry Book Manager Book Grid CPT class is the class responsible for creating and managing
 * the mbdb_book_grid Custom Post Type
 *
 * @package MBM
 */

/**
 * The Mooberry Book Manager Book Grid CPT class is the class responsible for creating and managing
 * the mbdb_book_grid Custom Post Type
 *
 * 
 *
 * @since    4.0.0
 */
class Mooberry_Book_Manager_Book_Grid_CPT extends Mooberry_Book_Manager_CPT {
		
	
	
	public function __construct(  ) {

		// initialize
		parent::__construct();	
		
		$this->post_type = 'mbdb_book_grid';
		$this->singular_name = __('Book Grid', 'mooberry-book-manager');
		$this->plural_name = __('Book Grids', 'mooberry-book-manager');
		
				
		$this->args = array(
			'show_in_nav_menus'	=>	false,
			'query_var'	=>	false,
			'menu_icon' => 'dashicons-screenoptions',
			'show_in_admin_bar'	=> true,
			'show_in_nav_menus' => false,
			'publicly_queryable' => false,
			'exclude_from_search'	=> true,
			'can_export'	=> true,
			'capability_type' => array( 'mbdb_book_grid', 'mbdb_book_grids' ),
			'supports' => array( 'title' ),
		);
		
		add_action( 'add_meta_boxes', array( $this, 'placeholder_metabox'), 10 );
		add_action( 'wp_ajax_mbdb_book_grid_placeholder_dismiss', array
		($this, 'placeholder_dismiss' ) );	
		add_action( 'add_meta_boxes', array($this, 'preview_meta_box' ) );
		add_shortcode( 'mbm_book_grid', array( $this, 'shortcode_book_grid'  ) );
		add_action('media_buttons', array( $this, 'add_book_grid_shortcode_button' ), 30, 1);
		add_action( 'wp_ajax_mbdb_update_book_grid_preview', array( $this, 'update_book_grid_preview' ) );
		add_action( 'wp_ajax_save_book_list_order', array( $this, 'save_book_list_order' ) );	
		
	}
	
	protected function set_data_object( $id = 0 ) {
		
		//$this->data_object = new Mooberry_Book_Manager_Book_Grid( $id );
		$this->data_object = MBDB()->grid_factory->create_grid( $id );
	}
	
	public function selection_options() { 
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
	
	public function order_options() {
		return apply_filters('mbdb_book_grid_order_options', array(	
				'pubdateA'	=> __('Publication Date (oldest first)', 'mooberry-book-manager'),
				'pubdateD'	=> __('Publication Date (newest first)', 'mooberry-book-manager'),
				'titleA'	=> __('Title (A-Z)', 'mooberry-book-manager'),
				'titleD'	=> __('Title (Z-A)', 'mooberry-book-manager'),
				'custom'	=>	__('Custom', 'mooberry-book-manager')));
	}
		
		
	public function group_by_options() {
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
	
	public function create_metaboxes() {
		//error_log('book grid metaboxes');
		
			
		$mbdb_book_grid_metabox = new_cmb2_box( array(
			'id'			=> 'mbdb_book_grid_metabox',
			'title'			=> __('Book Grid Settings', 'mooberry-book-manager'),
			'object_types'	=> array( $this->post_type ), //array( 'page' ),
			'context'		=> 'normal',
			'priority'		=> 'default',
			'show_names'	=> true)
		);
		//if ( get_post_type() == $this->post_type ) {
		
		$mbdb_book_grid_metabox->add_field( array(
				'name' 	=> __('Books to Display', 'mooberry-book-manager'),
				'id' 	=> '_mbdb_book_grid_books',
				'type'	=> 'select',
				'options'	=> $this->selection_options(),
				'column'	=> array(
								'position'	=>	2,
							),
				'display_cb'	=>	array($this, 'display_books_column'),
			)
		);
				//print_r('get title list for book grid');
				/*
		$title_list = wp_cache_get( 'title_list', 'mbdb_lists' );
		if ( $title_list === false ) {
			$book_list = new MBDB_Book_List( MBDB_Book_List_Enum::all, 'title', 'ASC');
			$title_list = $book_list->get_title_list();
			wp_cache_set( 'title_list', $title_list, 'mbdb_lists');
		}
		*/
		$title_list = MBDB()->helper_functions->get_all_books();
		
		$mbdb_book_grid_metabox->add_field( array(
				'name' 	=> __('Select Books', 'mooberry-book-manager'),
				'id'	=> '_mbdb_book_grid_custom',
				'type'	=> 'multicheck',
				'options' => $title_list,
				
			)
		);
		
		$mbdb_book_grid_metabox->add_field( array(
				'name'	=> __('Select Genres', 'mooberry-book-manager'),
				'id'	=> '_mbdb_book_grid_genre',
				'type' 	=> 'multicheck',   
				'options' => MBDB()->helper_functions->get_term_options('mbdb_genre'),
			)
		);

		$mbdb_book_grid_metabox->add_field( array(
				'name'	=> __('Select Series', 'mooberry-book-manager'),
				'id'	=> '_mbdb_book_grid_series',
				'type' 	=> 'multicheck',   
				'options'	=>	MBDB()->helper_functions->get_term_options('mbdb_series'),
			)
		);
		
		$mbdb_book_grid_metabox->add_field( array(
				'name'	=>	__('Select Tags', 'mooberry-book-manager'),
				'id'	=>	'_mbdb_book_grid_tag',
				'type' 	=> 'multicheck',   
				'options'	=>	MBDB()->helper_functions->get_term_options('mbdb_tag'),
			)
		);
			
		$publishers = MBDB()->helper_functions->create_array_from_objects( MBDB()->options->publishers, 'name', false );
		$mbdb_book_grid_metabox->add_field( array(
				'name'	=>	__('Select Publishers', 'mooberry-book-manager'),
				'id'	=> '_mbdb_book_grid_publisher',
				'type'	=>	'multicheck',
				'options'	=> $publishers, // mbdb_get_publishers('no'),
			)
		);
				
		$mbdb_book_grid_metabox->add_field( array(
				'name'	=>	__('Select Editors', 'mooberry-book-manager'),
				'id'	=> '_mbdb_book_grid_editor',
				'type'	=>	'multicheck',
				'options'	=> MBDB()->helper_functions->get_term_options('mbdb_editor'),
			)
		);
				
		$mbdb_book_grid_metabox->add_field( array(
				'name'	=>	__('Select Illustrators', 'mooberry-book-manager'),
				'id'	=> '_mbdb_book_grid_illustrator',
				'type'	=>	'multicheck',
				'options'	=> MBDB()->helper_functions->get_term_options('mbdb_illustrator'),
			)
		);
		
		$mbdb_book_grid_metabox->add_field( array(
				'name'	=>	__('Select Cover Artists', 'mooberry-book-manager'),
				'id'	=> '_mbdb_book_grid_cover_artist',
				'type'	=>	'multicheck',
				'options'	=> MBDB()->helper_functions->get_term_options('mbdb_cover_artist'),
			)
		);
		
		
		$group_by_options = $this->group_by_options();
		$count = count($group_by_options);
		
		for($x=1; $x <$count; $x++) {
			$args = array(
					'name'	=>	__('Group Books By', 'mooberry-book-manager'),
					'id'	=>	'_mbdb_book_grid_group_by_level_' . $x,
					'type'	=>	'select',
					'options'	=> $group_by_options,
			);
			// add one column for group by
			if ( $x == 1 ) {
				$args['column'] = array(
								'position'	=>	3,
								);
			}
			
			$mbdb_book_grid_metabox->add_field( $args );
			
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
				'options'	=> $this->order_options(),
				'column'	=> array(
								'position'	=>	4,
							),
				'after'	=> '<div id="_mbdb_bookd_grid_custom_order" style="display:none; font-weight:bold; padding-top: 2em; padding-bottom: 2em;">' . __('Drag and drop the books into the order you want:', 'mooberry-book-manager') . '<ul id="_mbdb_book_grid_book_list">' . $this->output_book_grid_custom_order() . '</ul></div>',
			)
		);
		
		$mbdb_book_grid_metabox->add_field( array(
				'name'	=>	__('Use default cover height?', 'mooberry-book-manager'),
				'id'	=>	'_mbdb_book_grid_cover_height_default',
				'type'	=>	'select',
				'default'	=>	'yes',
				'column'	=> array(
								'position'	=>	5,
							),
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
		
	//	}
		$mbdb_book_grid_metabox = apply_filters('mbdb_book_grid_meta_boxes', $mbdb_book_grid_metabox);
		
	}
	
/***********************************************
*  preview and shortcode meta boxes
*  
*  @since 3.4
*  
**********************************************/

public function preview_meta_box() {
	add_meta_box( 'mbdb_book_grid_preview_metabox',
				__('Preview', 'mooberry-book-manager'),
				array( $this, 'preview_metabox_display' ),
				'mbdb_book_grid',
				'normal',
				'low'
			);

	add_meta_box( 'mbdb_book_grid_shortcode_metabox',
					__('Shortcode', 'mooberry-book-manager'),
					array( $this, 'shortcode_metabox_display' ),
					'mbdb_book_grid',
					'side',
					'default'
				);
 }
 
 public function preview_metabox_display( $post ) {

	//echo '<a class="button" id="mbdb_update_preview">Show Preview</a>';
?>
	<input type="button" class="button button-secondary" id="mbdb_update_preview" value="<?php _e('Show Preview'); ?>" />
	<img id="mbdb_preview_loading" style="display:none;" src="<?php echo MBDB_PLUGIN_URL; ?>includes/assets/ajax-loader.gif"/><div id="mbdb_book_grid_preview">
	<?php 
		// $this->set_data_object( $post->ID );
		
		// $books = $this->data_object->book_list; 
	
		
		// echo  $this->data_object->display_grid( $books, 0 );
		
		?>
	</div>
<?php
 }
 
 public function shortcode_metabox_display( $post ) {
?>
<p><label for="mbdb_book_grid_shortcode"><?php _e('Copy this code into a page, blog post, etc. to display this book grid.', 'mooberry-book-manager'); ?></label></p>
	<input type="text" readonly="readonly" class="widefat" id="mbdb_book_grid_shortcode" value="[mbm_book_grid id='<?php echo $post->ID ?>']" />
<?php
 }
 
 

/**
*  
*  Keep a placholder metabox for book grids on pages
*  but allow user to dismiss it
*  
*  @since 3.4
*/

public function placeholder_metabox() {
	$mbdb_options = get_option('mbdb_options');
	if (array_key_exists('dismiss_book_grids_notice', $mbdb_options) && $mbdb_options['dismiss_book_grids_notice'] == 'yes') {
		return;
	}
	
	add_meta_box( 'mbdb_book_grid_placeholder', 
				__('Book Grid Settings', 'mooberry-book-manager'), 
				array( $this, 'display_placeholder_metabox'), 
				'page', 
				'normal', 
				'default' );
}

public function display_placeholder_metabox($post, $args) {
	?>
	<p><?php _e('Looking for the Book Grid Settings?  As of version 3.4, there is now separate menu in your Dashboard for adding Book Grids.', 'mooberry-book-manager'); ?></p>
	<a class="button" id="mbdb_book_grid_placeholder_dismiss"><?php _e('Dismiss this notice', 'mooberry-book-manager'); ?></a>
	<img id="mbdb_book_grid_dismiss_loader" style="display:none;" src="<?php echo MBDB_PLUGIN_URL; ?>includes/assets/ajax-loader.gif"/>
	<?php

}

// ajax dismiss notice
public function placeholder_dismiss() {

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
	
public function save_book_list_order() {
	

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


private function output_book_grid_custom_order( ) { //$id, $object_id, $a) {
	
	
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



	// column display functions
	public function display_books_column( $field_args, $field ) {
		$grid = $field->args['display_cb'][0]->data_object;
		$data = $field->value;
	}
	
	
	/******************************************************
  *  
  *  AJAX function to update the preview
  *  
  *  @since 3.4
  *  
  ******************************************************/

function update_book_grid_preview() {
	//check_ajax_referer( 'mbdb_book_grid_preview_ajax_nonce', 'security' );
	

	// turn $_POST['grid_options'] into format for mbdb_bookgrid_content:
	// [ id ] = Array(
	//		[ 0 ] = value
	// )
	
	$grid_options = array_map( array( $this, 'make_array' ), $_POST['grid_options']);
	 $grid_options['id'] = $_POST['gridID'];
	//$this->data_object = new Mooberry_Book_Manager_Book_Grid( $grid_options );
	
	$this->set_data_object( $grid_options );
	 //echo mbdb_bookgrid_content( 0, $grid_options);
	//$books = $this->data_object->book_list; 
	
	echo  $this->data_object->display_grid( );
	
	wp_die(); 
	
}

function make_array( $element ) {
	 if (is_array($element) ) {
		 return array(serialize($element));
	 } else {
		return array($element);
	 }
 }



		


	/*****************************************************************************
				DISPLAY GRID
	*******************************************************************************/

	/**************************************************************
	 *  		SHORTCODE
	 *  
	 *  @since 3.4
	 *  
	 *************************************************************/

	public function shortcode_book_grid( $attr, $content ) {
		$attr = shortcode_atts(array('id' => ''), $attr);
		if ($attr['id'] == '') {
			return '';
		}
		// TO DO: This will be in BookGrid object
		// object will return an array of BookList Objects
		// Loop through that array to display it using dispay functions below
		//return mbdb_bookgrid_content( $attr['id'] );
		$this->set_data_object( $attr[ 'id' ] );
		//$books = $this->data_object->book_list;
	
		$content =  $this->data_object->display_grid(); //, $attr[ 'id' ], $this->data_object->default_height, $this->data_object->cover_height);

		// //find all the book grid's postmeta so we can display it in comments for debugging purposes
		// $grid_values = array();
		// foreach ($mbdb_book_grid_meta_data as $key => $data) {
			// if ( substr($key, 0, 5) == '_mbdb' ) {
				// $grid_values[$key] = $data[0];
			// }
		// }
		// $content = '<!-- Grid Parameters:
					// ' . print_r($grid_values, true) . ' -->' . $content;
					
		 return $content; // . $book_grid_description_bottom;
		
		
	}


	// add shortcode button to editor
	function add_book_grid_shortcode_button( $editor_id ) {
		$allowed_post_types =  apply_filters( 'mbdb_types_allowed_to_have_shortcode_button', array( 'page', 'post', 'mbdb_book') );
		if (!in_array(get_post_type(), $allowed_post_types ) ) {
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
		
		include('admin/views/book-grid-shortcode-button.php');
		
	}
	 
	 
	 
	 
		

	

}
	