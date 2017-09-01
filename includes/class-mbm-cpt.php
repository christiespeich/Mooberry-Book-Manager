<?php

/**
 * The Mooberry Book Manager CPT class is the base class responsible for creating and managing
 * Custom Post Types
 *
 * @package MBM
 */

/**
 * The Mooberry Book Manager CPT class is the base class responsible for creating and managing
 * Custom Post Types
 *
 * 
 *
 * @since    4.0.0
 */
abstract class Mooberry_Book_Manager_CPT {
	
	//protected $columns;
	
	protected $metaboxes;	
	protected $quick_edit_fields;
	protected $bulk_edit_fields;
	protected $post_type;	
	protected $taxonomies;	
	protected $data_object;	
	protected $singular_name;	
	protected $plural_name;
	protected $args;
	
	abstract public function create_metaboxes();
	abstract protected function set_data_object( $id = 0 );
	
	public function __construct() {
		
		$this->args = array();
		$this->taxonomies = array();
		$this->metaboxes = array();
		$this->quick_edit_fields = array();
		$this->bulk_edit_fields = array();
		$this->post_type = '';
		$this->data_object = null;
		$this->singular_name = '';
		$this->plural_name = '';
		$this->default_single_template = 'default';
		
		
		add_action('init', array( $this, 'register' ) );
		add_action('cmb2_admin_init', array($this, 'create_metaboxes') );
		//add_action('add_meta_boxes', array($this, 'create_metaboxes') );
		add_filter('wpseo_metabox_prio', array( $this, 'reorder_wpseo') );
		add_filter('cmb2_override_meta_remove', array( $this, 'save_meta_data' ), 10, 2);
		add_filter('cmb2_override_meta_save', array( $this, 'save_meta_data' ), 10, 2);
		add_filter('cmb2_override_meta_value', array( $this, 'get_meta_data'), 10, 3);
	
		
		add_action( 'quick_edit_custom_box', array( $this, 'quick_edit' ), 1, 2 );
		add_action( 'save_post', array( $this, 'quick_edit_save_post'), 10, 2);
		
		// prioirty 40 to make it run after override_meta_save
		add_action('save_post', array( $this, 'save' ), 40);
		add_action( 'bulk_edit_custom_box', array( $this, 'bulk_edit'), 1, 2);
		add_action( 'wp_ajax_bulk_quick_save_bulk_edit', array( $this, 'bulk_edit_save_post') );
		add_action( 'admin_notices', array( $this, 'admin_notice' ), 0 );
		
	}
	
	public function register() {
	
		$defaults = array(	
			'label' => $this->plural_name,
		//	'public' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'menu_position' => 20,
			'show_in_nav_menus' => true,
			'has_archive' => false,
			'map_meta_cap' => true,
			'hierarchical' => false,
			'rewrite' => false,
			'query_var' => true,
			'labels' => array (
				'name' => $this->plural_name,
				'singular_name' => $this->singular_name,
				'menu_name' => $this->plural_name,
				'all_items' => sprintf(__( 'All %s', 'mooberry-book-manager' ), $this->plural_name),
				'add_new' => __( 'Add New', 'mooberry-book-manager' ),
				'add_new_item' => sprintf(__( 'Add New %s', 'mooberry-book-manager' ), $this->singular_name),
				'edit' => __( 'Edit', 'mooberry-book-manager' ),
				'edit_item' => sprintf( __( 'Edit %s', 'mooberry-book-manager' ), $this->singular_name), 
				'new_item' => sprintf( __( 'New %s', 'mooberry-book-manager' ), $this->singular_name ),
				'view' => sprintf( __( 'View %s', 'mooberry-book-manager' ), $this->singular_name ),
				'view_item' => sprintf( __( 'View %s', 'mooberry-book-manager' ), $this->singular_name ),
				'search_items' => sprintf( __( 'Search %s', 'mooberry-book-manager' ), $this->plural_name ), 
				'not_found' => sprintf( __( 'No %s Found', 'mooberry-book-manager' ), $this->plural_name ), 
				'not_found_in_trash' => sprintf( __( 'No %s Found in Trash', 'mooberry-book-manager' ), $this->plural_name ),
				'parent' => sprintf( __( 'Parent %s', 'mooberry-book-manager' ), $this->singular_name ),
				'filter_items_list'     => sprintf( __( 'Filter $s List', 'mooberry-book-manager' ), $this->singular_name ),
				'items_list_navigation' => sprintf( __( '%s List Navigation', 'mooberry-book-manager' ), $this->singular_name ),
				'items_list'            => sprintf( __( '%s List', 'mooberry-book-manager' ), $this->singular_name ),
				'view items'	=>	sprintf( __('View %s', 'mooberry-book-manager'), $this->plural_name ),
				'attributes'	=>	sprintf( __('%s Attributes', 'mooberry-book-manager'), $this->singular_name ),
			),
		);
		
		$this->args = wp_parse_args( $this->args, $defaults );
		
		register_post_type( $this->post_type, apply_filters( $this->post_type . '_cpt', $this->args )	);
		
		foreach ( $this->taxonomies as $taxonomy ) {
			$taxonomy->register();
		}
		
	}
	
	
	public function add_post_class( $classes ) {
		if ( get_post_type() == $this->post_type ) {
			if (!in_array('post', $classes)) {
				$classes[] = 'post';
			}
		}
		return $classes;
	}
	
	
	public function reorder_wpseo( $priority ) {
		if (get_post_type() == $this->post_type) {
			return 'default';
		} else {
			return $priority;
		}
	}
	
	public function quick_edit( $column_name, $post_type ) {
		if ( array_key_exists( $column_name, $this->quick_edit_fields ) ) {
			$field = $this->quick_edit_fields[ $column_name ];
			$this->quick_bulk_edit( $column_name, $post_type, $field );
		}
	}

	public function bulk_edit( $column_name, $post_type ) {
		if ( array_key_exists( $column_name, $this->bulk_edit_fields ) ) {
			$field = $this->bulk_edit_fields[ $column_name ];
			$this->quick_bulk_edit( $column_name, $post_type, $field );
		}
	}
	
	private function quick_bulk_edit( $column_name, $post_type, $field ) {
		if ( $post_type != $this->post_type ) {
			return;
		}
		
		$defaults = array( 
			'fieldset_class' => '',
			'fieldset_style' => '',
			'label'	=>	'',
			'field_class' => '',
			'field'	=>	'',
			'description' => '',
			);
		$field = wp_parse_args( $field, $defaults );
	
			?>
			
			<fieldset style="<?php echo $field['fieldset_style']; ?>" class="<?php echo $field['fieldset_class']; ?>">
						<div class="inline-edit-col">
							<label>
								<span class="title"><?php echo $field['label']; ?></span>
								<span class="<?php echo $field['field_class']; ?>">
										<?php echo $field['field']; ?>
										<?php echo $field['description']; ?>
								</span>
							</label>
						</div>
		</fieldset>
			<?php
	}
	
	public function quick_edit_save_post( $post_id, $post ) {
		
		// pointless if $_POST is empty (this happens on bulk edit)
		if ( empty( $_POST ) ) {
			return $post_id;
		}
		
		// bail if not a quick edit
		if ( !isset($_POST['_inline_edit']) ) {
			return $post_id;
		}
		
		// verify quick edit nonce
		if ( isset( $_POST[ '_inline_edit' ] ) && ! wp_verify_nonce( $_POST[ '_inline_edit' ], 'inlineeditnonce' ) ) {
			return $post_id;
		}
		
				
		// don't save for autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id;
			
		// dont save for revisions
		if ( isset( $post->post_type ) && $post->post_type == 'revision' )
			return $post_id;
		
		if ($post->post_type !== $this->post_type) {
			return $post_id;
		}
		
		$fields = array_keys( $this->quick_edit_fields );
		
		$this->set_data_object( $post_id );//new Mooberry_Book_Manager_Book( $post_id );
		foreach ( $fields as $postmeta ) {
			if ( array_key_exists( $postmeta, $_POST ) ) {
				$value = $_POST[ $postmeta ];
				$value = $this->handle_quick_edit_data( $postmeta, $value );
				$this->data_object->set_by_postmeta( $postmeta, $value );
			}
		}
		
		return $this->data_object->save();
		
	}
	
	public function bulk_edit_save_post() {
	
		
		// we need the post IDs
		$post_ids = ( isset( $_POST[ 'post_ids' ] ) && !empty( $_POST[ 'post_ids' ] ) ) ? $_POST[ 'post_ids' ] : NULL;
			
		// if we have post IDs
		if ( empty( $post_ids ) || !is_array( $post_ids ) ) {
			return;
		}
		
		// get the custom fields
		$custom_fields = array_keys( $this->bulk_edit_fields );
		// update for each post ID
		foreach( $post_ids as $post_id ) {
			if ( get_post_type( $post_id ) != $this->post_type ) {
				return;
			}
			
			$this->set_data_object( $post_id); //new Mooberry_Book_Manager_Book( $post_id );
			foreach( $custom_fields as $field ) {
				// if it has a value, doesn't update if empty on bulk
				if ( isset( $_POST[ $field ] ) && !empty( $_POST[ $field ] ) ) {
					$this->data_object->set_by_postmeta( $field, $_POST[ $field ] );
				}
				
			}
	
			$this->data_object->save();	
		}
	
	}
	
	protected function handle_quick_edit_data( $field, $value ) {
		return $value;
	}
	
	protected function is_array_element_set( $fieldname, $arrayname) {
		return ( array_key_exists($fieldname, $arrayname ) && isset( $arrayname[$fieldname] ) && trim( $arrayname[$fieldname] ) != '');
	}
	
	// if it's the last element and both sides of the check are empty, ignore the error
	// because CMB2 will automatically delete it from the repeater group
	protected function allow_blank_last_elements( $field1, $field2, $fieldname, $key, $flag ) {
		if ( !$field1 && !$field2 ) {
			// to the end of the array
			end( $_POST[ $fieldname ] );
			if ( $key === key( $_POST[ $fieldname ] ) ) {
				return false;
			}
		}
		return $flag;
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
	function get_meta_data( $override, $object_id, $a) {
		
		// if not a book post type, return what we got in
		if ( get_post_type() != $this->post_type ) {
			return $override;
		}
		
		// this is called for columns as well as the edit screen
		// so we have to get a different data_object for each row
		global $post;
		
		if ( $this->data_object == null || $object_id != $post->ID || $this->data_object->id != $post->ID) {
			$this->set_data_object( $post->ID );
		}
		
		// only override the fields in the table
		$data = $this->data_object->get_by_postmeta( $a['field_id'] );
		
		if ( $data !== false ) {
			return $data;
		} else {
			return $override;
		}
		
		
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
	
	 public function save_meta_data( $override, $a ) {
		// if not a book post type, return what we got in
		if ( get_post_type() != $this->post_type ) {
			return $override;
		}
		
		// v3.1 by adding meta_remove filter, now this is sometimes called without the value element
		// add the element as blank text
		// this is necessary (vs just exiting the function) because otherwise if a field is left blank
		// it won't get saved as such without the meta_remove filter
		if (!array_key_exists('value', $a)) {
			$a['value'] = null;
		}

		//global $mbdb_edit_book;
		// returns true if field requires overriding CMB
		$is_override = $this->data_object->set_by_postmeta( $a['field_id'], $a['value'] );
		if ( $is_override ) {
			return 'override';
		} else {
			return $override;
		}
		
	}

	public function save( $id ) {
		
		if ( get_post_type() != $this->post_type ) {
			return;
		}
		
		if ( wp_is_post_revision( $id ) || wp_is_post_autosave( $id ) ) {
			return;
		}
		
		 if ( 'trash' == get_post_status( $id ) ) {
			 return;
		 }
		 
		 // data_object will be null when restoring a post from trash
		 // nothing needs to be saved anyway, the status just changes
		if ( $this->data_object != null ) {
			return $this->data_object->save( );
		}
	}
	
	
	protected function display_msg_if_invalid( $flag, $fieldname, $group, $message ) {
		 // on attempting to publish - check for completion and intervene if necessary
		if ( ( isset( $_POST['publish'] ) || isset( $_POST['save'] ) ) && $_POST['post_status'] == 'publish' ) {
			//  don't allow publishing while any of these are incomplete
			if ( $flag ) {
				// set the message
				$itemID = array_search( $group, $_POST[ $fieldname ] );
				$itemID++;
				$message = sprintf( $message, $itemID );
				$this->error_message( $message );
			}
		}
	}
	
	// this takes post_id as a null for book shop book limits errors
	protected function error_message ( $message, $post_id = null ) {
		if ( !$post_id ) {
			$post_id = $_POST['post_ID'];
		}
		 // set the message
			$notice = get_option( 'mbdb_notice' );
			$notice[ $post_id ] = '<span class="mbm-validation-error">' . $message . '</span>';
			update_option( 'mbdb_notice', $notice);
			
			// change it to pending not updated
			global $wpdb;
			$wpdb->update( $wpdb->posts, array( 'post_status' => 'draft' ), array( 'ID' => $post_id ) );
			
			// filter the query URL to change the published message
			add_filter( 'redirect_post_location', create_function( '$location', 'return esc_url_raw( add_query_arg( "message", "0", $location ) );' ) );
	}
	
	// public for backwards comaptibility for MA
	public function validate_all_group_fields( $groupname, $fieldIDname, $fields, $message) {
		do_action('mbdb_before_validate' . $groupname);
		
		$flag = false;
	
		foreach( $_POST[$groupname] as $key => $group ) {
			// both fields must be filled in
			$is_field1 = $this->is_array_element_set($fieldIDname, $group ) && $group[$fieldIDname] != '0';
			$is_others = true;
			foreach ( $fields as $field ) {
				if ( !$this->is_array_element_set( $field, $group ) ) {
					$is_others = false;
					break;
				}
			}
			$flag = !($is_field1 && $is_others);
			
			// if it's the last element and both sides of the check are empty, ignore the error
			// because CMB2 will automatically delete it from the repeater group
			$flag = $this->allow_blank_last_elements( $is_field1, $is_others, $groupname, $key, $flag);
			
			if ( $flag ) { break; }
		}
		do_action('mbdb_validate' . $groupname . '_before_msg', $flag, $group);
		
		$this->display_msg_if_invalid( $flag, $groupname, $group, apply_filters('mbdb_validate' . $groupname . '_msg', $message));
		do_action('mbdb_validate' . $groupname . '_after_msg', $flag, $group);
	}

	protected function get_wysiwyg_output( $content ) {
		global $wp_embed;

		$content = $wp_embed->autoembed( $content );
		$content = $wp_embed->run_shortcode( $content );
		$content = wpautop( $content );
		$content = do_shortcode( $content );


		return $content;
	}
	
	protected function sanitize_field( $field ) {
		return strip_tags( stripslashes( $field ) );
	}

	
	/**
	 * Grab the template set in the options for the book page and tax grid
	 *
	 *
	 * Attempts to pull the template from the options 
	 *
	 * In the case that the options aren't set or the template selected
	 * doesn't exist, default to the theme's single template
	 * 
	 * 
	 * @access public
	 * @since 2.1
	 * @since 3.0 Added support for tax grid template as well. Changed from single_template to template_include filter
	 * @since 3.5.4 Checks if this is a search and bails if so
	 *
	 * @param string $template
	 * @return string $template
	 */
	public function single_template( $template ) {
		// if a search, return what we got in
		global $wp_query;
		if ( $wp_query->is_search() ) {
			return $template;
		}
		
		if ( get_post_type() != $this->post_type  ) {
			return $template;
		}
		
		// make sure it's the main query and not on the admin
		if ( is_main_query() && !is_admin() ) {		
			$default_template = $this->default_single_template;
		} else {
			return $template;
		}
		
		// if it's the default template, use the single.php template
		if ( $default_template == 'default' ) {
			$default_template = 'single.php';
		}
		
		// now get the file
		if ( isset($default_template) && $default_template != '' && $default_template != 'default' ) {
			
			// first check if there's one in the child theme
			$child_theme = get_stylesheet_directory();
		
			if ( file_exists( $child_theme . '/' . $default_template ) ) {
				return $child_theme . '/' . $default_template;
			} else {
				// if not get the parent theme
				$parent_theme = get_template_directory();

				if ( file_exists( $parent_theme . '/' . $default_template ) ) {
					return $parent_theme . '/' . $default_template;
				}
			}
		}
		
		// if everything fails, just return whatever came in
		return $template;
		
	}
	

	/**
	 * Admin Notices for Posts
	 *
	 * Displays error message generated by editing posts
	 * Uses options to save error messages between page loads
	 * Expects format option['mbdb_notice'] = { $postID => $message }
	 * 
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	function admin_notice() {
	  
		global $post;
		
		// only show on admin pages where there is a post id (ie editing a cpt)
		if ( $post ) {
			$notice = get_option( 'mbdb_notice' );
			
			if ( empty( $notice ) ) {
				return '';
			}
			
			foreach ( $notice as $pid => $m ){		
				if ( $post->ID == $pid ){
					echo apply_filters( 'mbdb_post_admin_notice', '<div id="message" class="error"><p>' . $m . '</p></div>' );
					
					//make sure to remove notice after its displayed so its only displayed when needed.
					unset( $notice[$pid] );
					
					update_option( 'mbdb_notice' , $notice );
					
					break;
				}
			}
		}
	}
	
		
	/**
	 * Magic __get function to dispatch a call to retrieve a private property
	 *
	 * @since 1.0
	 */
	public function __get( $key ) {

		if( method_exists( $this, 'get_' . $key ) ) {

			return call_user_func( array( $this, 'get_' . $key ) );

		} else {
			
			$ungettable_properties = array( );
			
			if ( property_exists( $this, $key ) ) {
				
				if ( !in_array( $key, $ungettable_properties ) ) {
				
					return $this->$key;

				}
		
			}
		
		}
	
		return new WP_Error( 'mbdb-invalid-property', sprintf( __( 'Can\'t get property %s', 'mooberry-book-manager' ), $key ) );
				
	}
	
	

}
