<?php
/**
 * Admin Settings Page
 * This class is taken directly from the CMB2 example
 * and customized for MBDB
 */
class mbdb_Admin_Settings {
	
	/**
	 *  Keeps track of what the current screen tab is
	 *  @var string
	 *  @since 3.0
	 */
	private $tab = '';

	/**
 	 * Option key, and option page slug
 	 * @var string
	 * @since 3.0
 	 */
	private $key = 'mbdb_options';

	/**
 	 * Options page metabox id
 	 * @var string
	 * @since 3.0
 	 */
	private $metabox_id = 'mbdb_settings_metabox';

	/**
	 * Options Page title
	 * @var string
	 * @since 3.0
	 */
	protected $title = '';

	/**
	 * Options Page hook
	 * @var string
	 * @since 3.0
	 */
	protected $options_page = '';
	
	protected $sub_pages = array();

	/**
	 * Constructor
	 * @since 3.0
	 */
	public function __construct() {
		// Set our title
		$this->title = __( 'Mooberry Book Manager Settings', 'mooberry-book-manager' );
	}

	/**
	 * Initiate our hooks
	 * @since 3.0
	 */
	public function hooks() {
		
		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'admin_menu', array( $this, 'add_options_page' ) );
		add_action( 'cmb2_admin_init', array( $this, 'add_options_page_metabox' ) );
		add_action( 'update_option_mbdb_options', array($this, 'options_updated'), 10, 2 );
	}


	/**
	 * Register our setting to WP
	 * @since  3.0
	 */
	public function init() {
		register_setting( $this->key, $this->key );
	}

	/**
	 * Add menu options page
	 * @since 3.0
	 */
	public function add_options_page() {
		$this->options_page = add_menu_page( $this->title, $this->title, 'manage_options', $this->key, array( $this, 'admin_page_display' ) );
	
    	
		$pages = array( 'mbdb_options'		=>	array( 'page_title' =>  __( 'Mooberry Book Manager General Settings', 'mooberry-book-manager' ), 
																'menu_title'	=>	__( 'General Settings', 'mooberry-book-manager' ) 
															), 
							'mbdb_book_page_options'	=>	array( 'page_title' =>  __('Mooberry Book Manager Book Page Settings', 'mooberry-book-manager'), 
																'menu_title'	=>	__('Book Page', 'mooberry-book-manager') 
															),
							'mbdb_grid_options'			=>	array( 'page_title' =>  __('Mooberry Book Manager Book Grid Settings', 'mooberry-book-manager'), 
																'menu_title'	=>	__('Book Grid', 'mooberry-book-manager') 
															),
							'mbdb_publishers_options' 	=> 	array( 'page_title' =>  __('Mooberry Book Manager Publishers', 'mooberry-book-manager'),
																'menu_title'	=>	__('Publishers', 'mooberry-book-manager')
															),
							'mbdb_retailers_options' 	=> 	array( 'page_title' =>  __('Mooberry Book Manager Retailers', 'mooberry-book-manager'),
																'menu_title'	=>	__('Retailers', 'mooberry-book-manager')
															),
							'mbdb_formats_options' 		=> 	array( 'page_title' =>  __('Mooberry Book Manager E-book Formats', 'noun', 'mooberry-book-manager'),
																'menu_title'	=>	_x('E-book Formats', 'noun', 'mooberry-book-manager')
															),
							'mbdb_editions_options'	 	=> 	array( 'page_title' =>  __('Mooberry Book Manager Edition Formats', 'mooberry-book-manager'),
																'menu_title'	=>	__('Edition Formats', 'mooberry-book-manager')
															)
					);
		/*			
		$import_books = get_option('mbdb_import_books');
		error_log($import_books); 
		if ( !$import_books || $import_books == null ) {  */
		// show migrate page if version is 3.x
		$current_version = get_option(MBDB_PLUGIN_VERSION_KEY);
		if (version_compare($current_version, '2.4.4', '>') && version_compare($current_version, '4.0', '<')) {
			$pages['mbdb_migrate'] = array ( 'page_title'	=>	__('Mooberry Book Manager v3.0 Data Migration', 'mooberry-book-manager'),
												'menu_title'	=>	__('Migrate Data', 'mooberry-book-manager')
												);
		}
		$pages = apply_filters('mbdb_settings_pages', $pages);
		
															
		foreach($pages as $key => $subpage) {												
			$sub_page_hook = add_submenu_page( 'mbdb_options', $subpage['page_title'], $subpage['menu_title'], 'manage_options', $key, array( $this, 'admin_page_display') );
			$this->sub_pages[] = $sub_page_hook;
			add_action( "admin_print_styles-{$sub_page_hook}", array( 'CMB2_hookup', 'enqueue_cmb_css' ) );
		}
		
		// Include CMB CSS in the head to avoid FOUC
		add_action( "admin_print_styles-{$this->options_page}", array( 'CMB2_hookup', 'enqueue_cmb_css' ) );
				
	}

	/**
	 * Admin page markup.
	 * @since  3.0
	 */
	public function admin_page_display() {
		// title
	
		echo '<h2>' . esc_html( get_admin_page_title() ) . '</h2>';

		// instructions
		do_action('mbdb_settings_before_instructions');
			
		/*echo '<p>';
		echo '<b>' . __('NOTE:', 'mooberry-book-manager') . '</b> ';
		echo __('You must click the SAVE button to save your changes before switching tabs.', 'mooberry-book-manager');
		echo '</p>';
*/
		do_action('mbdb_settings_after_instructions');
				
		// tabs
		do_action('mbdb_settings_pre_tab_display', $this->tab);
		
		//$this->tab_display();
		
		do_action('mbdb_settings_post_tab_display', $this->tab);
		
		// About Mooberry boxes
		echo '<div id="mbdb_about_mooberry"><div class="mbdb_box">			<h3>' . __('Need help with Mooberry Book Manager?', 'mooberry-book-manager') . '</h3>';
		include('views/admin-about-mooberry.php');
		echo '</div>';
		include('views/admin-about-mooberry-story.php');
		echo '</div>';
			
		// metabox
		if ($this->tab == 'mbdb_migrate') {
			$this->migrate_data();
			return;
		}
		do_action('mbdb_settings_before_metabox', $this->tab, $this->metabox_id);
		echo '<div class="wrap cmb2-options-page ' . $this->key . '">';
		echo '<div id="icon-options-general" class="icon32"></div>';
		cmb2_metabox_form( $this->metabox_id, $this->key ); 
		
		do_action('mbdb_settings_after_metabox', $this->tab, $this->metabox_id);
		
		echo '</div>';
	
	
	}

	/**
	 * Add the options metabox to the array of metaboxes
	 * Choose which metabox based on $tab
	 * @since  3.0
	 */
	function add_options_page_metabox() {

		// hook in our save notices
		add_action( "cmb2_save_options-page_fields_{$this->metabox_id}", array( $this, 'settings_notices' ), 10, 2 );

		$mbdb_settings_metabox = new_cmb2_box( array(
			'id'         => $this->metabox_id,
			'hookup'     => false,
			'cmb_styles' => false,
			'show_on'    => array(
				// These are important, don't remove
				'key'   => 'options-page',
				'value' => array( $this->key, )
			),
		) );
	
		// if tab isn't set, default to general
		if ( isset ( $_GET['page'] ) ) {
			$this->tab = $_GET['page'];
		} else {
			$this->tab = 'general';
		}
	
		// load the metabox based on what tab is set to
		switch ($this->tab) {
			case 'mbdb_options':
				$mbdb_settings_metabox = $this->mbdb_general_settings($mbdb_settings_metabox);
				break;
			case 'mbdb_book_page_options':
				$mbdb_settings_metaxbox = $this->mbdb_book_page_settings($mbdb_settings_metabox);
				break;
			case 'mbdb_grid_options':
				$mbdb_settings_metaxbox = $this->mbdb_grid_settings($mbdb_settings_metabox);
				break;
			case 'mbdb_publishers_options':
				$mbdb_settings_metabox = $this->mbdb_publishers($mbdb_settings_metabox);
				break;
			case 'mbdb_retailers_options' :
			   $mbdb_settings_metabox =  $this->mbdb_retailers($mbdb_settings_metabox);
				break;
			case 'mbdb_formats_options' :
				$mbdb_settings_metabox = $this->mbdb_formats($mbdb_settings_metabox);
				break;
			case 'mbdb_social_media_options' :
				$mbdb_settings_metabox = $this->mbdb_social_media($mbdb_settings_metabox);
				break;
			// case 'output':
				// mbdb_print_book_list();
				// break;
			case 'mbdb_editions_options' :
				$mbdb_settings_metabox = $this->mbdb_editions($mbdb_settings_metabox);
				//mbdb_meta_fields($fields);
				break;
			
		}
		
		$mbdb_settings_metabox = apply_filters('mbdb_settings_metabox', $mbdb_settings_metabox, $this->tab);
	}
	
	function migrate_data() {
	
		$import_books = get_option('mbdb_import_books');
		if ($import_books ) {
			echo '<h3>' . __('Note: This page for users of previous versions of Mooberry Book Manager only.', 'mooberry-book-manager') . '</h3>';
			echo '<h4>' . __('Data already migrated. Mooberry Book Manager 3.0 is ready to use!', 'mooberry-book-manager') . '</h4>';
			echo __('You may choose to re-migrate your data from version 2 if you\'ve noticed issues with your books\' information.  ', 'mooberry-book-manager');
			echo '<b>' . __('Changes you\'ve made since migrating may be lost.', 'mooberry-book-manager') . '</b>';
			echo '<p><a href="#"  id="mbdb_3_1_remigrate" class="button">' . __('Re-Migrate Data Now', 'mooberry-book-manager'). '</a></p> ';
			echo '<div id="results"></div>';
			return;
		}
		echo '<h4>' . __('Migrating Data...', 'mooberry-book-manager') . '</h4>';
		$success = MBDB()->books->import();
		if ($success === true) {
			echo '<h4>' . __('Success! Mooberry Book Manager version 3.0 is ready to use!', 'mooberry-book-manager') . '</h4>';
			update_option('mbdb_import_books', true);
		} else { 
			global $wpdb;
			echo '<h4>' . sprintf(__('An error occured while migrating data to version 3.0. Please contact Mooberry Dreams at %s with the following error message.', 'mooberry-book-manager'), '<A HREF="mailto:bookmanager@mooberrydreams.com">bookmanager@mooberrydreams.com</A>') . '</h4><p><h4><b>' . __('Error:', 'mooberry-book-manager') . '</b></h3> <h3>' . $wpdb->last_error . '</h4></p>';
		}
	}
	
	function mbdb_book_page_settings( $mbdb_settings_metabox ) {
		
		$mbdb_settings_metabox->add_field(array(
				'id'	=> 'mbdb_book_book_page_settings_title',
				'name'	=>	__('BOOK PAGE SETTINGS', 'mooberry-book-manager'),
				'type'	=>	'title',
			)
		);
		$mbdb_settings_metabox->add_field(array(
				'id'	=>	'mbdb_default_template',
				'name'	=> __('Page Template', 'mooberry-book-manager'),
				'type'	=> 'select',
				'default'	=>	'default',
				'options'	=> mbdb_get_template_list(),
			)
		);
		// split up the descriptions to keep the html out of the
		// translatable text
		$description1 = __('If you need to restore the image that came with Mooberry Book Manager, download the ', 'mooberry-book-manager');
		$description2 = __('Mooberry Book Manager Image Fixer plugin', 'mooberry-book-manager');
		$description = '<span style="font-style:italic">' .$description1 . ' <a target="_new" href="http://localhost/wordpress/wp-admin/plugins.php?s=mooberry+book+manager+image+fixer">' . $description2 . '</a></span>.';
		
		$mbdb_settings_metabox->add_field(array(
				'id'	=> 	'goodreads',
				'name'	=>	__('Add to Goodreads Image', 'mooberry-book-manager'),
				'type' => 'file',
				'attributes' => array(
					'style'	=>	'width:300px',
				),
				'after_row'=> $description,
				'options'	=> array(
					'url'	=> false,
					'add_upload_file_text' => __('Choose or Upload File', 'mooberry-book-manager'),
				),
			)
		);
		
		$mbdb_settings_metabox->add_field(array(
				'id'	=>	'mbdb_reset_meta_boxes',
				'name'	=> __('Reset Book Edit Page', 'mooberry-book-manager'),
				'type'	=> 'text',
				'after_row'	=>	"<span style='font-style:italic'>" . __("If you've reordered the boxes on the Book Edit page, this will revert them to their default positions.", "mooberry-book-manager") .  "</span>",
				'before'	=>	'<a href="#" class="button" id="reset_meta_boxes">' . __('Reset', 'mooberry-book-manager') . '</a><img id="reset_progress" src="' . MBDB_PLUGIN_URL . 'includes/assets/ajax-loader.gif" style="display:none;padding-left:10px;"/><img id="reset_complete" src="' . MBDB_PLUGIN_URL . 'includes/assets/check.png" style="display:none;padding-left:10px;"/>',
				'attributes'	=>	 array(
						'type'	=>	'hidden'
						),
			)
		);
		
		$mbdb_settings_metabox->add_field(array(
				'id'	=> 'mbdb_book_default_settings_title',
				'name'	=>	__('DEFAULT SETTINGS', 'mooberry-book-manager'),
				'type'	=>	'title',
			)
		);
		$mbdb_settings_metabox->add_field(array(
				'id'	=>	'mbdb_default_unit',
				'name'	=>	__('Default Unit of Measurement', 'mooberry-book-manager'),
				'type'	=> 'select',
				'default'	=> 'in',
				'options'	=> mbdb_get_units_array(),
			)
		);
		$mbdb_settings_metabox->add_field(array(
				'id'	=>	'mbdb_default_currency',
				'name'	=>	__('Default Currency', 'mooberry-book-manager'),
				'type'	=> 'select',
				'default'	=> 'USD',
				'options'	=> mbdb_get_currency_array(),
			)
		);
		$mbdb_settings_metabox->add_field(array(
				'id'	=>	'mbdb_default_language',
				'name'	=>	__('Default Language', 'mooberry-book-manager'),
				'type'	=> 'select',
				'default'	=> 'EN',
				'options'	=> mbdb_get_language_array(),
			)
		);
		
		return apply_filters('mbdb_settings_book_page_settings', $mbdb_settings_metabox);
		
	}
	
	/**
	 *  Sets up the metabox for the grid settings
	 *  
	 *  
	 *  
	 *  @since 3.0
	 *  @param object $mbdb_settings_metabox 
	 *  
	 *  @return metabox object with grid settings fields
	 *  
	 *  @access public
	 */
	function mbdb_grid_settings($mbdb_settings_metabox) {
	
		$mbdb_settings_metabox->add_field(array(
					'id'	=> 'mbdb_book_grid_default_settings_title',
					'name'	=>	__('BOOK GRID DEFAULT SETTINGS', 'mooberry-book-manager'),
					'type'	=>	'title',
					'desc'	=>	__('This setting will be used on all Taxonomy Grids. It\'s also used on any grids that do not override the default setting.', 'mooberry-book-manager'),
				)
			);
			
		$mbdb_settings_metabox->add_field(array(
					'id'	=>	'mbdb_default_cover_height',
					'name'	=> __('Default Cover Height (px)', 'mooberry-book-manager'),
					'type'	=> 'text_small',
					'default'	=> apply_filters('mbdb_book_grid_cover_height_default', 200),
					'attributes' => array(
							'type' => 'number',
							'pattern' => '\d*',
							'min' => apply_filters('mbdb_book_grid_cover_min_height', 50),
					),
				)
			);
			
		$mbdb_settings_metabox->add_field(array(
					'id'	=> 'mbdb_book_grid_template_settings_title',
					'name'	=>	__('TAXONOMY GRID TEMPLATE SETTINGS', 'mooberry-book-manager'),
					'type'	=>	'title',
					'desc' =>__('This template will be used to display the Taxonomy Book Grids.', 'mooberry-book-manager'),
				)
			);
		$mbdb_settings_metabox->add_field(array(
					'id'	=>	'mbdb_tax_grid_template',
					'name'	=> __('Page Template', 'mooberry-book-manager'),
					'type'	=> 'select',
					'default'	=>	'default',
					'options'	=> mbdb_get_template_list(),
				)
			);
			
		// break up the description into multiple sections to keep the HTML
		// out of the translatable text
		$description1 = __('These will be used to build website URL for the Taxonomy Book Grids.  Text entered in these fields will be converted to "friendly URLs" by making them lower-case, removing the spaces, etc.', 'mooberry-book-manager');
		/* translators: %s represents HTML for bolding. Please leave them in */
		$description2 = '<b>' . __('NOTE:', 'mooberry-book-manager') . '</b> ' . __('Wordpress reserved terms are not allowed here.', 'mooberry-book-manager');
		$description4 = __('Reserved Terms', 'mooberry-book-manager');
		$description5 = __('See a list of reserved terms.', 'mooberry-book-manager');
		
		$description = $description1 . 
						'<br><br>' . 
						$description2 . 
						' <a href="" onClick="window.open(\'' . plugins_url( 'includes/reserved_terms.php' , __FILE__ ) . '\', \'' . $description4 . 
						'\',  \'width=460, height=300, left=550, top=250, scrollbars=yes\'); return false;">' . 
						$description5 . 
						'</a>';
		
		$mbdb_settings_metabox->add_field(array(
				'id'	=> 'mbdb_book_grid_slug_settings_title',
				'name'	=>	__('TAXONOMY BOOK GRID URL SETTINGS', 'mooberry-book-manager'),
				'type'	=>	'title',
				'desc' =>  $description,
			)
		);
		
		// get all taxonomies on a book
		$taxonomies = mbdb_tax_grid_objects(); 
		
		// add a text field for each taxonomy
		foreach($taxonomies as $name => $taxonomy) {
			$id = 'mbdb_book_grid_' . $name . '_slug';
			$singular_name = $taxonomy->labels->singular_name;
			$mbdb_settings_metabox->add_field(array(
				'id'	=> $id,
				'name'	=>	$singular_name,
				'default'	=>	mbdb_get_tax_grid_slug($name),
				'sanitization_cb'	=> array( $this, 'sanitize_slug'),
				'type'	=> 'text',
				)
			);
		}
		
		return apply_filters('mbdb_settings_grid_settings', $mbdb_settings_metabox);	
			
	}
	
	/**
	 *  Sets up the metabox for the Publishers settings
	 *  
	 *  
	 *  
	 *  @since 3.0
	 *  @param object $mbdb_settings_metabox 
	 *  
	 *  @return metabox object with Publishers fields
	 *  
	 *  @access public
	 */
	function mbdb_publishers($mbdb_settings_metabox) {

		$mbdb_settings_metabox->add_field(array(
				'id'          => 'publishers',
				'type'        => 'group',
				'desc'			=>	__('Add your publishers.', 'mooberry-book-manager'),
				'options'     => array(
					'group_title'   => __('Publisher', 'mooberry-book-manager') . ' {#}',  // since version 1.1.4, {#} gets replaced by row number
					'add_button'    =>  __('Add New Publisher', 'mooberry-book-manager'),
					'remove_button' =>  __('Remove Publisher', 'mooberry-book-manager') ,
					'sortable'      => false, // beta
				),
			) 
		);
		 	
		$mbdb_settings_metabox->add_group_field( 'publishers', array(
				'name' => __('Publisher', 'mooberry-book-manager'),
				'id'   => 'name',
				'type' => 'text_medium',
				'attributes' => array(
					'required' => 'required',
				),
			)
		);
			
		$mbdb_settings_metabox->add_group_field( 'publishers', array(
				'name' 	=> __('Publisher Website', 'mooberry-book-manager'),
				'id'	=> 'website',
				'type'	=> 'text_url',
				'desc' => 'http://www.someWebsite.com/',
				'attributes' =>  array(
					'pattern' => mbdb_url_validation_pattern(), 
					'style'	=>	'width:300px',	
				),
			)
		);
		
		$mbdb_settings_metabox->add_group_field( 'publishers', array(
				'id' => 'uniqueID',
				'type' => 'text',
				'show_names' => false,
				'sanitization_cb' => 'mbdb_uniqueID_generator',
				'attributes' => array(
					'type' => 'hidden',
				),
			)
		);
		
		return apply_filters('mbdb_settings_publishers_settings',$mbdb_settings_metabox);
	}

	/**
	 *  Sets up the metabox for the Editions settings
	 *  
	 *  
	 *  
	 *  @since 3.0
	 *  @param object $mbdb_settings_metabox 
	 *  
	 *  @return metabox object with Editions fields
	 *  
	 *  @access public
	 */
	function mbdb_editions($mbdb_settings_metabox) {
	
		$mbdb_settings_metabox->add_field(array(
				'id'          => 'editions',
				'type'        => 'group',
				'desc'			=>	__('Add any additional formats your books are available in.', 'mooberry-book-manager'),
				'options'     => array(
					'group_title'   => __('Format', 'mooberry-book-manager') . ' {#}',  // since version 1.1.4, {#} gets replaced by row number
					'add_button'    =>  __('Add New Format', 'mooberry-book-manager'),
					'remove_button' =>  __('Remove Format', 'mooberry-book-manager') ,
					'sortable'      => false, // beta
				)
			)
		);
			
		$mbdb_settings_metabox->add_group_field( 'editions', array(
				'name' => __('Format Name', 'mooberry-book-manager'),
				'id'   => 'name',
				'type' => 'text_medium',
				'attributes' => array(
					'required' => 'required',
				),
			)
		);
			
		$mbdb_settings_metabox->add_group_field( 'editions', array(
				'id' => 'uniqueID',
				'type' => 'text',
				'show_names' => false,
				'sanitization_cb' => 'mbdb_uniqueID_generator',
				'attributes' => array(
					'type' => 'hidden',
				),
			)
		);
				
		return apply_filters('mbdb_settings_editions_settings', $mbdb_settings_metabox);
	}

	/**
	 *  Sets up the metabox for the General settings
	 *  
	 *  
	 *  
	 *  @since 3.0
	 *  @param object $mbdb_settings_metabox 
	 *  
	 *  @return metabox object with General fields
	 *  
	 *  @access public
	 */
	function mbdb_general_settings($mbdb_settings_metabox) {
	
		
			
		
		
		$mbdb_settings_metabox->add_field(array(
				'id'	=> 'mbdb_book_image_settings_title',
				'name'	=>	__('IMAGE SETTINGS', 'mooberry-book-manager'),
				'type'	=>	'title',
			)
		);
	
		
		$mbdb_settings_metabox->add_field(array(
				'id'	=>	'show_placeholder_cover',
				'name'	=>	__('Show Placeholder Cover On', 'mooberry-book-manager'),
				'type'	=> 'multicheck',
				'select_all_button' => false,
				'desc'	=>	__('The placeholder cover is used for books that do not have a cover selected.', 'mooberry-book-manager'),
				'options'	=> array(
								'page' => __('Book Page', 'mooberry-book-manager'),
								'widget'	=>	__('Widgets', 'mooberry-book-manager'),
				),
			)
		);
		
		// split up the descriptions to keep the html out of the
		// translatable text
		$description1 = __('If you need to restore the image that came with Mooberry Book Manager, download the ', 'mooberry-book-manager');
		$description2 = __('Mooberry Book Manager Image Fixer plugin', 'mooberry-book-manager');
		$description = '<span style="font-style:italic">' .$description1 . ' <a target="_new" href="http://localhost/wordpress/wp-admin/plugins.php?s=mooberry+book+manager+image+fixer">' . $description2 . '</a></span>.';
		
		$mbdb_settings_metabox->add_field(array(
				'id'	=> 	'coming-soon',
				'name'	=>	__('Placeholder Cover Image', 'mooberry-book-manager'),
				'type' => 'file',
				'attributes' => array(
					'style'	=>	'width:300px',
				),
				'after_row'	=>	$description,
				'options'	=> array(
					'url'	=> false,
					'add_upload_file_text' => __('Choose or Upload File', 'mooberry-book-manager'),
				),
			)
		);
		
		
		
		return apply_filters('mbdb_settings_general_settings', $mbdb_settings_metabox);
	}

	/**
	 *  Sets up the metabox for the Retailers settings
	 *  
	 *  
	 *  
	 *  @since 3.0
	 *  @param object $mbdb_settings_metabox 
	 *  
	 *  @return metabox object with Retailers fields
	 *  
	 *  @access public
	 */
	function mbdb_retailers($mbdb_settings_metabox) {
		
		// split up the descriptions to keep the html out of the
		// translatable text
		$description1 = __('Add any additional retailers that sell your books. If you need to restore images that came with Mooberry Book Manager, download the ', 'mooberry-book-manager');
		$description2 = __('Mooberry Book Manager Image Fixer plugin', 'mooberry-book-manager');
		$description = $description1 . ' <a target="_new" href="http://localhost/wordpress/wp-admin/plugins.php?s=mooberry+book+manager+image+fixer">' . $description2 . '</a>.';
		
		$mbdb_settings_metabox->add_field(array(
				'id'          => 'retailers',
				'type'        => 'group',
				'desc'			=>	$description,
				'options'     => array(
					'group_title'   => __('Retailer', 'mooberry-book-manager') . ' {#}',  // since version 1.1.4, {#} gets replaced by row number
					'add_button'    =>  __('Add Retailer', 'mooberry-book-manager'),
					'remove_button' =>  __('Remove Retailer', 'mooberry-book-manager') ,
					'sortable'      => false, // beta
				)
			)
		);
		$mbdb_settings_metabox->add_group_field( 'retailers', array(
				'name' => __('Retailer Name', 'mooberry-book-manager'),
				'id'   => 'name',
				'type' => 'text_medium',
				'attributes' => array(
					'required' => 'required',
				),
			)
		);
		$mbdb_settings_metabox->add_group_field( 'retailers', array(
				'name' => __('Retailer Logo Image', 'mooberry-book-manager'),
				'id'   => 'image',
				'type' => 'file',
				'attributes' => array(
					'size'	=> 45
				),
				'options'	=> array(
					'add_upload_file_text' => __('Choose or Upload File', 'mooberry-book-manager'),
				),
			)
		);
		$mbdb_settings_metabox->add_group_field( 'retailers', array(
				'id' => 'uniqueID',
				'type' => 'text',
				'show_names' => false,
				'sanitization_cb' => 'mbdb_uniqueID_generator',
				'attributes' => array(
					'type' => 'hidden',
				),
			)
		);
		
		
		return apply_filters('mbdb_settings_retailer_fields', $mbdb_settings_metabox);
	}

	/**
	 *  Sets up the metabox for the Formats settings
	 *  
	 *  
	 *  
	 *  @since 3.0
	 *  @param object $mbdb_settings_metabox 
	 *  
	 *  @return metabox object with Formats fields
	 *  
	 *  @access public
	 */
	function  mbdb_formats($mbdb_settings_metabox) {
		// split up the descriptiosn to keep the HTML out of the 
		// translatable text
		$description1 = __('If you have free books for download, add any additional formats your books are available in. If you need to restore images that came with Mooberry Book Manager, download the', 'mooberry-book-manager');
		$description2 = __('Mooberry Book Manager Image Fixer plugin', 'mooberry-book-manager');
		$description = $description1 . ' <a target="_new" href="http://localhost/wordpress/wp-admin/plugins.php?s=mooberry+book+manager+image+fixer">' . $description2 . '</a>.';
  
		$mbdb_settings_metabox->add_field(array(
				'id'          => 'formats',
				'type'        => 'group',
				'desc'			=> $description,
				'options'     => array(
					'group_title'   => _x('Format', 'noun: the format of a book', 'mooberry-book-manager') . ' {#}',  // since version 1.1.4, {#} gets replaced by row number
					'add_button'    => __('Add Format', 'mooberry-book-manager'),
					'remove_button' => __('Remove Format', 'mooberry-book-manager'),
					'sortable'      => false, // beta
				)
			)	
		);
		$mbdb_settings_metabox->add_group_field( 'formats', array(
				'name' => _x('Format', 'noun: the format of a book', 'mooberry-book-manager'),
				'id'   => 'name',
				'type' => 'text',
				'attributes' => array(
					'required' => 'required',
				),
			)
		);
		$mbdb_settings_metabox->add_group_field( 'formats', array(
				'name' => _x('Format Image', 'noun: the image that represents the format of a book', 'mooberry-book-manager'),
				'id'   => 'image',
				'type' => 'file',
				'options'	=> array(
					'add_upload_file_text' => __('Choose or Upload File', 'mooberry-book-manager'),
				),
			)
		);
		$mbdb_settings_metabox->add_group_field( 'formats', array(
				'id' => 'uniqueID',
				'type' => 'text',
				'show_names' => false,
				'sanitization_cb' => 'mbdb_uniqueID_generator',
				'attributes' => array(
					'type' => 'hidden',
				),
			)
		);
	
		return apply_filters('mbdb_settings_format_fields', $mbdb_settings_metabox);
	}
	
	
	/**
	 *  Sets up the metabox for the Social Media settings
	 *  
	 *  This is only shown by the add-ons that need it (MA and MK)
	 *  because the tab is not added except by those add-ons
	 *  
	 *  @since 3.0
	 *  @param object $mbdb_settings_metabox 
	 *  
	 *  @return metabox object with Social Media fields
	 *  
	 *  @access public
	 */
	function  mbdb_social_media($metabox) {	
		$metabox->add_field(array(
				'id'	=>	'social_media',
				'type'	=> 	'group',
				'desc'	=>	__('Add Social Media Sites With Whom You Have Accounts.', 'mooberry-book-manager-social-media-list'),
				'options'	=> array(
					'group_title'	=>	__('Social Media Site', 'mooberry-book-manager-social-media-list') . ' {#}', // {#} gets replaced by row number
					'add_button'	=>	__('Add New Social Media Site', 'mooberry-book-manager-social-media-list'),
					'remove_button'	=>	__('Remove Social Media Site', 'mooberry-book-manager-social-media-list'),
					'sortable'	=>false,
				),
			)
		);
		
		$metabox->add_group_field( 'social_media', array(
				'name'	=> __('Social Media Site Name', 'mooberry-book-manager-social-media-list'),
				'id'	=>	'name',
				'type'	=>	'text_medium',
				'attributes'	=>	array(	
					'required'	=>	'required',
				),
			)
		);
		
		$metabox->add_group_field( 'social_media', array(
				'name'	=>	__( 'Social Media Logo Image', 'mooberry-book-manager-social-media-list'),
				'id'	=>	'image',
				'type' => 'file',
				'attributes' => array(
					'size'	=> 45
				),
				'options'	=> array(
					'add_upload_file_text' => __('Choose or Upload File', 'mooberry-book-manager-social-media-list'),
				),
			)
		);
		$metabox->add_group_field( 'social_media', array(
				'id' => 'uniqueID',
				'type' => 'text',
				'show_names' => false,
				'sanitization_cb' => 'mbdb_uniqueID_generator',
				'attributes' => array(
					'type' => 'hidden',
				),
			)
		);
		
		return apply_filters('mbdb_settings_social_media_fields', $metabox);
					
	}

	// this is called with an add_filter('mbdb_settings_pages') by the
	// add ons that need it.
	function mbdb_add_social_media_page( $pages ) {
		
		$pages['mbdb_social_media_options'] = array( 'page_title' =>  __( 'Mooberry Book Manager Social Media Options', 'mooberry-book-manager' ), 
																'menu_title'	=>	__( 'Social Media', 'mooberry-book-manager' ) 
															); 
		return $pages;
	}

	/**
	 *  
	 *  Verifies the Grid URL slug is not a WP reserved term
	 *  
	 *  
	 *  @since 3.0
	 *  @param [string] $meta_value value the user entered
	 *  @param [array] $args       	contains field id
	 *  @param [obj] $object     	contains original value before user input
	 *  
	 *  @return sanitized value. Either the inputted value if it checks out
	 *  							or the original value if not
	 *  
	 *  @access public
	 */
	function sanitize_slug($meta_value, $args, $object) {
		
		// make sure none of the fields are blank
		if (!isset($meta_value) || trim($meta_value) == '') {
			// default to the field id as a last resort
			$meta_value = $args['id'];
			
			// pull the singular name from the field id
			$field_id = $args['id'];
			$results = preg_match( '/mbdb_book_grid_(mbdb_.*)_slug/', $field_id, $matches );
			if ($results) {
				$taxonomy = get_taxonomy($matches[1]);
				if ($taxonomy) {
					$meta_value = sanitize_title($taxonomy->labels->singular_name);
				}
			} 
			
		}
		$reserved_terms = mbdb_wp_reserved_terms();
		if ( in_array($meta_value, $reserved_terms) ) {
			//show a message
			$msg = '"' . $meta_value . '" ' . __('is a reserved term and not allowed. This field was not saved.', 'mooberry-book-manager');
			add_settings_error( $this->key . '-error', '', $msg , 'error');
			settings_errors( $this->key . '-error' );
			
			// return the original value
			return sanitize_title($object->value);
		}
		
		// entered value is OK. Sanitize it and return it
		return sanitize_title($meta_value);
	}


	/**
	 *  
	 *  If any of the tag slugs were changed, the rewrite rules
	 *  need to be flushed.
	 *  This function runs if ANY of the fields were updated.
	 *  
	 *  
	 *  @since 3.0
	 *  @param [string] $old_value 
	 *  @param [string] $new_value
	 *  
	 *  @access public
	 */
	function options_updated( $old_value, $new_value ) {
	
		// if any of the tax slugs change, flush the rewrite rules
		$taxonomies = mbdb_tax_grid_objects(); 
		foreach($taxonomies as $name => $taxonomy) {
			$key = 'mbdb_book_grid_' . $name . '_slug';
			if ( (!array_key_exists($key, $old_value)) || ($old_value[$key] != $new_value[$key]) ) {
			/*	// multi-site compatible
				global $wp_rewrite;
				$wp_rewrite->init(); //important...
				$wp_rewrite->flush_rules(); */
				flush_rewrite_rules();
				break;
			}
		}
	}

	

	/**
	 * Register settings notices for display
	 *
	 * @since  3.0
	 * @param  int   $object_id Option key
	 * @param  array $updated   Array of updated fields
	 * @return void
	 */
	public function settings_notices( $object_id, $updated ) {
		
		// validate inputs
		if ( $object_id !== $this->key || empty( $updated ) ) {
			return;
		}
	
		// show updated notice
		add_settings_error( $this->key . '-notices', '', __( 'Settings updated.', 'mooberry-book-manager' ), 'updated' );
		settings_errors( $this->key . '-notices' );
	}
	
	/**
	 * Public getter method for retrieving protected/private variables
	 * @since  3.0
	 * @param  string  $field Field to retrieve
	 * @return mixed          Field value or exception is thrown
	 */
	public function __get( $field ) {
		// Allowed fields to retrieve
		if ( in_array( $field, array( 'key', 'metabox_id', 'title', 'options_page', 'tab' ), true ) ) {
			return $this->{$field};
		}

		throw new Exception( 'Invalid property: ' . $field );
	}
	
} // end class

/**
 * Helper function to get/return the Myprefix_Admin object
 * @since  0.1.0
 * @return Myprefix_Admin object
 */
function mbdb_admin() {
	static $object = null;
	if ( is_null( $object ) ) {
		$object = new mbdb_Admin_Settings();
		$object->hooks();
	}

	return $object;
}

/**
 * Wrapper function around cmb2_get_option
 * @since  0.1.0
 * @param  string  $key Options array key
 * @return mixed        Option value
 */
/*function mbdb_get_option( $key = '' ) {
	return cmb2_get_option( mbdb_admin()->key, $key );
}
*/


// ajax function to reset meta boxes
add_action( 'wp_ajax_mbdb_reset_meta_boxes', 'mbdb_reset_meta_boxes' );
function mbdb_reset_meta_boxes() {
	check_ajax_referer( 'mbdb_admin_options_ajax_nonce', 'security' );
	$user_id = get_current_user_id();
	delete_user_meta( $user_id, 'meta-box-order_mbdb_book');
}


// Get it started
$admin_settings = mbdb_admin();

