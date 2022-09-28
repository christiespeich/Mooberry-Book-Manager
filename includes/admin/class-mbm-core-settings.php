<?php
/**
 * Admin Settings Page
 * This class is taken directly from the CMB2 example
 * and customized for MBDB
 *
 * @since 3.0
 */
class Mooberry_Book_Manager_Core_Settings extends Mooberry_Book_Manager_Settings {

	private $import_process;
	private $import_novelist_books_process;
	private $update_apple_books_links_process;
	private $import_books_csv_process;
	private $admin_notice_manager;

	/**
	 * Constructor
	 *
	 * @since 3.0
	 */
	public function __construct() {
		parent::__construct();

		$this->metabox_id = 'mbdb_settings_metabox';
		$this->key        = 'mbdb_options';


		add_action( 'update_option_mbdb_options', array( $this, 'options_updated' ), 10, 2 );
		add_action( 'wp_ajax_mbdb_reset_meta_boxes', array( $this, 'reset_meta_boxes' ) );
		add_action( 'wp_ajax_mbdb_cancel_import', array( $this, 'cancel_import' ) );
		add_action( 'wp_ajax_mbdb_add_tax_grid_page', array( $this, 'create_tax_grid_page_ajax' ) );
		add_action( 'mbdb_settings_before_metabox', array( $this, 'do_migrate_pages' ) );
		add_filter( 'mbdb_settings_metabox', array( $this, 'set_up_metabox' ) );

		add_action( 'mbdb_settings_before_metabox', array( $this, 'import_export' ) );
		add_action( 'wp_ajax_mbdb_export', array( $this, 'export' ) );
		add_action( 'wp_ajax_mbdb_export_csv', array( $this, 'export_csv_file' ) );
		add_action( 'wp_ajax_mbdb_import', array( $this, 'import' ) );
		add_action( 'wp_ajax_mbdb_import_novelist', array( $this, 'import_novelist' ) );
		add_action( 'wp_ajax_mbdb_update_apple_books_links', array( $this, 'update_apple_books_links' ) );
		add_action( 'admin_notices', array( $this, 'import_export_notices' ) );

		add_action( 'cmb2_save_options-page_fields_mbdb_settings_metabox', array(
			$this,
			'update_featured_images'
		), 10, 3 );

		add_action( 'init', array( $this, 'init_import_process' ) );

		add_action('wp_ajax_mbdb_save_popup_card_field_list', array( $this, 'save_popup_card_fields'));

	}

	public function init_import_process() {
		$this->import_process                   = new Mooberry_Book_Manager_Import_Process();
		$this->import_novelist_books_process    = new Mooberry_Book_Manager_Novelist_Import_Process();
		$this->update_apple_books_links_process = new Mooberry_Book_Manager_Apple_Books_Update_Process();

		$this->admin_notice_manager = new Mooberry_Dreams_Admin_Notice_Manager('mbdb_admin_notice_manager');
		$this->import_books_csv_process = new MBDB_Import_Books_CSV_Process( $this->admin_notice_manager );

	}


	protected function set_pages() {
		$this->pages = array(
			'mbdb_options'            => array(
				'page_title' => __( 'Mooberry Book Manager General Settings', 'mooberry-book-manager' ),
				'menu_title' => __( 'General Settings', 'mooberry-book-manager' )
			),
			'mbdb_book_page_options'  => array(
				'page_title' => __( 'Mooberry Book Manager Book Page Settings', 'mooberry-book-manager' ),
				'menu_title' => __( 'Book Page', 'mooberry-book-manager' )
			),
			'mbdb_grid_options'       => array(
				'page_title' => __( 'Mooberry Book Manager Book Grid Settings', 'mooberry-book-manager' ),
				'menu_title' => __( 'Book Grid', 'mooberry-book-manager' )
			),
			/*'mbdb_publishers_options' => array(
				'page_title' => __( 'Mooberry Book Manager Publishers', 'mooberry-book-manager' ),
				'menu_title' => __( 'Publishers', 'mooberry-book-manager' )
			),*/
			'mbdb_imprints_options' => array(
				'page_title' => __( 'Mooberry Book Manager Imprints', 'mooberry-book-manager' ),
				'menu_title' => __( 'Imprints', 'mooberry-book-manager' )
			),
			'mbdb_retailers_options'  => array(
				'page_title' => __( 'Mooberry Book Manager Retailers', 'mooberry-book-manager' ),
				'menu_title' => __( 'Retailers', 'mooberry-book-manager' )
			),
			'mbdb_formats_options'    => array(
				'page_title' => __( 'Mooberry Book Manager E-book Formats', 'mooberry-book-manager' ),
				'menu_title' => _x( 'E-book Formats', 'noun', 'mooberry-book-manager' )
			),
			'mbdb_editions_options'   => array(
				'page_title' => __( 'Mooberry Book Manager Edition Formats', 'mooberry-book-manager' ),
				'menu_title' => __( 'Edition Formats', 'mooberry-book-manager' )
			),
			'mbdb_import_export'      => array(
				'page_title' => __( 'Mooberry Book Manager Import/Export Books', 'mooberry-book-manager' ),
				'menu_title' => __( 'Import / Export',
					'mooberry-book-maanager' )
			),
		);

		// show migrate page if version is 3.x
		$current_version = get_option( MBDB_PLUGIN_VERSION_KEY );
		if ( version_compare( $current_version, '2.4.4', '>' ) ) { //} && version_compare($current_version, '4.0', '<')) {
			$this->pages['mbdb_migrate'] = array(
				'page_title' => __( 'Mooberry Book Manager v3.0 Data Migration', 'mooberry-book-manager' ),
				'menu_title' => __( 'Migrate Data', 'mooberry-book-manager' )
			);
		}

		// show migrate book grids page if version is 3.4.2
		$import_grids = get_option( 'mbdb_migrate_grids' );
		//if ($import_grids === true) {
		$this->pages['mbdb_migrate_grids'] = array(
			'page_title' => __( 'Mooberry Book Manager Book Grid Migration', 'mooberry-book-manager' ),
			'menu_title' => __( 'Migrate Book Grids', 'mooberry-book-manager' )
		);
		//}

		$this->pages = apply_filters( 'mbdb_settings_pages', $this->pages );


	}


	public function do_migrate_pages() {


		// metabox
		if ( $this->page == 'mbdb_migrate' ) {
			$this->migrate_data();

			return;
		}

		if ( $this->page == 'mbdb_migrate_grids' ) {
			$this->migrate_grids();

			return;
		}
	}

	/**
	 * Add the options metabox to the array of metaboxes
	 * Choose which metabox based on $tab
	 *
	 * @since  3.0
	 */
	function set_up_metabox( $mbdb_settings_metabox ) {


		// load the metabox based on what tab is set to
		switch ( $this->page ) {
			case 'mbdb_options':
				$mbdb_settings_metabox = $this->mbdb_general_settings( $mbdb_settings_metabox );
				break;
			case 'mbdb_book_page_options':
				$mbdb_settings_metaxbox = $this->mbdb_book_page_settings( $mbdb_settings_metabox );
				break;
			case 'mbdb_grid_options':
				$mbdb_settings_metaxbox = $this->mbdb_grid_settings( $mbdb_settings_metabox );
				break;
			/*case 'mbdb_publishers_options':
				$mbdb_settings_metabox = $this->mbdb_publishers( $mbdb_settings_metabox );
				break;*/
			case 'mbdb_imprints_options':
				$mbdb_settings_metabox = $this->mbdb_imprints( $mbdb_settings_metabox );
				break;
			case 'mbdb_retailers_options' :
				$mbdb_settings_metabox = $this->mbdb_retailers( $mbdb_settings_metabox );
				break;
			case 'mbdb_formats_options' :
				$mbdb_settings_metabox = $this->mbdb_formats( $mbdb_settings_metabox );
				break;
			case 'mbdb_social_media_options' :
				$mbdb_settings_metabox = $this->mbdb_social_media( $mbdb_settings_metabox );
				break;
			//case 'mbdb_import_export' :
			//	$mbdb_settings_metabox = $this->mbdb_import_export( $mbdb_settings_metabox );
			//	break;
			// case 'output':
			// mbdb_print_book_list();
			// break;
			case 'mbdb_editions_options' :
				$mbdb_settings_metabox = $this->mbdb_editions( $mbdb_settings_metabox );
				//mbdb_meta_fields($fields);
				break;

		}

		return apply_filters( 'mbdb_settings_core_metabox', $mbdb_settings_metabox, $this->page, $this->tab );

	}

	function migrate_data() {

		$import_books = get_option( 'mbdb_import_books' );
		if ( $import_books ) {
			echo '<h3>' . __( 'Note: This page for users of previous versions of Mooberry Book Manager only.', 'mooberry-book-manager' ) . '</h3>';
			echo '<h4>' . __( 'Data already migrated. Mooberry Book Manager 3.0 is ready to use!', 'mooberry-book-manager' ) . '</h4>';
			echo __( 'You may choose to re-migrate your data from version 2 if you\'ve noticed issues with your books\' information.  ', 'mooberry-book-manager' );
			echo '<p><b>' . __( 'Changes you\'ve made since migrating may be lost.', 'mooberry-book-manager' ) . '</b></p>';
			echo '<p><a href="#"  id="mbdb_3_1_remigrate" class="button">' . __( 'Re-Migrate Data Now', 'mooberry-book-manager' ) . '</a></p> ';
			echo '<div id="results"></div>';

			return;
		}
		echo '<h4>' . __( 'Migrating Data...', 'mooberry-book-manager' ) . '</h4>';
		$success = MBDB()->books->import();
		if ( $success === true ) {
			echo '<h4>' . __( 'Success! Mooberry Book Manager version 3.0 is ready to use!', 'mooberry-book-manager' ) . '</h4>';
			update_option( 'mbdb_import_books', true );
		} else {
			global $wpdb;
			echo '<h4>' . sprintf( __( 'An error occured while migrating data to version 3.0. Please contact Mooberry Dreams at %s with the following error message.', 'mooberry-book-manager' ), '<A HREF="mailto:bookmanager@mooberrydreams.com">bookmanager@mooberrydreams.com</A>' ) . '</h4><p><h4><b>' . __( 'Error:', 'mooberry-book-manager' ) . '</b></h3> <h3>' . $wpdb->last_error . '</h4></p>';
		}
	}

	function migrate_grids() {
		$import_grids = get_option( 'mbdb_migrate_grids' );
		if ( ! $import_grids ) {
			echo '<h4>' . __( 'Note: Book Grids have already been migrated. Mooberry Book Manager 3.4.3 is ready to use!', 'mooberry-book-manger' ) . '</h4>';

			return;
		}

		echo '<h4>' . __( 'Migrating Book Grids...please wait...', 'mooberry-book-manger' ) . '</h4>';
		echo '<img id="mbdb_migrate_books_loading" src="' . MBDB_PLUGIN_URL . 'includes/assets/ajax-loader.gif"/>';
		flush();
		mbdb_upgrade_to_3_4();
		echo '<h4>' . __( 'Success! Mooberry Book Manager version 3.4.3 is ready to use!', 'mooberry-book-manager' ) . '</h4>  <a href="edit.php?post_type=mbdb_book_grid">' . __( 'Click here to see your book grids.', 'mooberry-book-manager' ) . '</a>';
		delete_option( 'mbdb_migrate_grids' );
	}

	function mbdb_book_page_settings( $mbdb_settings_metabox ) {
		$this->title = __( 'MBM Book Page Settings', 'mooberry-book-manager' );
		$mbdb_settings_metabox->add_field( array(
				'id'   => 'mbdb_book_book_page_settings_title',
				'name' => __( 'BOOK PAGE SETTINGS', 'mooberry-book-manager' ),
				'type' => 'title',
			)
		);
		$mbdb_settings_metabox->add_field( array(
				'id'      => 'mbdb_default_template',
				'name'    => __( 'Page Template', 'mooberry-book-manager' ),
				'type'    => 'select',
				'default' => 'default',
				'options' => MBDB()->helper_functions->get_template_list(),
			)
		);
		$mbdb_settings_metabox->add_field( array(
				'id'      => 'mbdb_show_back_to_grid_link',
				'name'    => __( 'Show Back to Grid Link on Books When Coming From a Book Grid?', 'mooberry-book-manager' ),
				'type'    => 'select',
				'default' => 'no',
				'options' => array('no'=>__('No', 'mooberry-book-manager'), 'yes'=> __('Yes', 'mooberry-book-manager'))
			)
		);
		/*
		// split up the descriptions to keep the html out of the
		// translatable text
		$description1 = __('If you need to restore the image that came with Mooberry Book Manager, download the ', 'mooberry-book-manager');
		$description2 = __('Mooberry Book Manager Image Fixer plugin', 'mooberry-book-manager');
		$description = '<span style="font-style:italic">' .$description1 . ' <a target="_new" href="' . admin_url('plugin-install.php?tab=search&s=mooberry+book+manager+image+fixer') . '">' . $description2 . '</a></span>.';
	*/
		$mbdb_settings_metabox->add_field( array(
				'id'         => 'goodreads',
				'name'       => __( 'Add to Goodreads Image', 'mooberry-book-manager' ),
				'type'       => 'file',
				'preview_size'	=>	'thumbnail',
				//		'after_row'=> $description,
				'options'    => array(
					'url'                  => false,
					'add_upload_file_text' => __( 'Choose or Upload File', 'mooberry-book-manager' ),
				),
			)
		);

		$mbdb_settings_metabox->add_field( array(
				'id'         => 'reedsy',
				'name'       => __( 'Add to Reedsy Image', 'mooberry-book-manager' ),
				'type'       => 'file',

				//		'after_row'=> $description,
				'options'    => array(
					'url'                  => false,
					'add_upload_file_text' => __( 'Choose or Upload File', 'mooberry-book-manager' ),
				),
			)
		);

		$mbdb_settings_metabox->add_field( array(
				'id'         => 'google_books',
				'name'       => __( 'Add to Google Books Image', 'mooberry-book-manager' ),
				'type'       => 'file',
				'preview_size'	=>	'thumbnail',
				//		'after_row'=> $description,
				'options'    => array(
					'url'                  => false,
					'add_upload_file_text' => __( 'Choose or Upload File', 'mooberry-book-manager' ),
				),
			)
		);



		$mbdb_settings_metabox->add_field( array(
				'id'         => 'mbdb_reset_meta_boxes',
				'name'       => __( 'Reset Book Edit Page', 'mooberry-book-manager' ),
				'type'       => 'text',
				'after_row'  => "<span style='font-style:italic'>" . __( "If you've reordered the boxes on the Book Edit page, this will revert them to their default positions.", "mooberry-book-manager" ) . "</span>",
				'before'     => '<a href="#" class="button" id="reset_meta_boxes">' . __( 'Reset', 'mooberry-book-manager' ) . '</a><img id="reset_progress" src="' . MBDB_PLUGIN_URL . 'includes/assets/ajax-loader.gif" style="display:none;padding-left:10px;"/><img id="reset_complete" src="' . MBDB_PLUGIN_URL . 'includes/assets/check.png" style="display:none;padding-left:10px;"/>',
				'attributes' => array(
					'type' => 'hidden'
				),
			)
		);

		$mbdb_settings_metabox->add_field( array(
				'id'   => 'mbdb_book_default_settings_title',
				'name' => __( 'DEFAULT SETTINGS', 'mooberry-book-manager' ),
				'type' => 'title',
			)
		);
		$mbdb_settings_metabox->add_field( array(
				'id'      => 'mbdb_default_unit',
				'name'    => __( 'Default Unit of Measurement', 'mooberry-book-manager' ),
				'type'    => 'select',
				'default' => 'in',
				'options' => MBDB()->options->units,
			)
		);
		$mbdb_settings_metabox->add_field( array(
				'id'      => 'mbdb_default_currency',
				'name'    => __( 'Default Currency', 'mooberry-book-manager' ),
				'type'    => 'select',
				'default' => 'USD',
				'options' => MBDB()->options->currencies,
			)
		);
		$mbdb_settings_metabox->add_field( array(
				'id'      => 'mbdb_default_language',
				'name'    => __( 'Default Language', 'mooberry-book-manager' ),
				'type'    => 'select',
				'default' => 'EN',
				'options' => MBDB()->options->languages,
			)
		);

		$mbdb_settings_metabox->add_field( array(
			'name'    => esc_html__( 'Show Currency for Books in Default Language?', 'mooberry-directory' ),
			'id'      => 'mbdb_show_currency',
			'type'    => 'radio',
			'default' => 'no',
			'options' => array(
				'no'  => 'No',
				'yes' => 'Yes',
			)
		) );


		$mbdb_settings_metabox->add_field( array(
				'id'   => 'mbdb_book_seo_settings_title',
				'name' => __( 'SEO SETTINGS', 'mooberry-book-manager' ),
				'type' => 'title',
			)
		);
		$mbdb_settings_metabox->add_field( array(
			'id'      => 'mbdb_book_seo_enabled',
			'name'    => __( 'Allow MBM to add META tags for SEO?' ),
			'type'    => 'select',
			'options' => array( 'yes' => 'Yes', 'no' => 'No' )

		) );
		if ( MBDB_WPSEO_INSTALLED ) {
			$mbdb_settings_metabox->add_field( array(
					'id'      => 'override_wpseo',
					'name'    => __( 'Override Wordpress SEO (Yoast) settings for the following fields:', 'mooberry-book-manager' ),
					'type'    => 'multicheck',
					'options' => MBDB()->helper_functions->override_wpseo_options(),
				)
			);
		}


		return apply_filters( 'mbdb_settings_book_page_settings', $mbdb_settings_metabox );

	}

	/**
	 *  Sets up the metabox for the grid settings
	 *
	 *
	 *
	 * @param object $mbdb_settings_metabox
	 *
	 * @return metabox object with grid settings fields
	 *
	 * @access public
	 * @since  3.0
	 */
	function mbdb_grid_settings( $mbdb_settings_metabox ) {
		$this->title = __( 'MBM Book Grid Settings', 'mooberry-book-manager' );
		$mbdb_settings_metabox->add_field( array(
				'id'   => 'mbdb_book_grid_default_settings_title',
				'name' => __( 'BOOK GRID DEFAULT SETTINGS', 'mooberry-book-manager' ),
				'type' => 'title',
				'desc' => __( 'This setting will be used on all Taxonomy Grids. It\'s also used on any grids that do not override the default setting.', 'mooberry-book-manager' ),
			)
		);

		$mbdb_settings_metabox->add_field( array(
				'id'         => 'mbdb_default_cover_height',
				'name'       => __( 'Default Cover Height (px)', 'mooberry-book-manager' ),
				'type'       => 'text_small',
				'default'    => MBDB_GRID_COVER_HEIGHT_DEFAULT,
				'attributes' => array(
					'type'    => 'number',
					'pattern' => '\d*',
					'min'     => MBDB_GRID_COVER_HEIGHT_MIN,
				),
			)
		);

		$mbdb_settings_metabox->add_field( array(
				'id'   => 'mbdb_book_grid_template_settings_title',
				'name' => __( 'TAXONOMY GRID SETTINGS', 'mooberry-book-manager' ),
				'type' => 'title',
				'desc' => '<p>' . __( 'This page will be used to display the Taxonomy Book Grids. A page has already been set up with the necessary code for this purpose, but if you have accidently deleted it and need to create a new one, click the Create button.', 'mooberry-book-manager' ) . '</p><p>' . __( 'To change the template used for the taxonomy grids, edit this page and set the <b>Template</b> in the <b>Page Attributes</b> section.', 'mooberry-book-manager' ),
			)
		);
		/*	$mbdb_settings_metabox->add_field(array(
						'id'	=>	'mbdb_tax_grid_template',
						'name'	=> __('Page Template', 'mooberry-book-manager'),
						'type'	=> 'select',
						'default'	=>	'default',
						'options'	=> MBDB()->helper_functions->get_template_list(),
					)
				);
			*/
		// page for tax grids
		$page_objs = get_pages();
		$pages     = array( '' => '' );
		foreach ( $page_objs as $page ) {
			$pages[ $page->ID ] = $page->post_title;
		}
		$mbdb_settings_metabox->add_field( array(
				'id'      => 'mbdb_tax_grid_page',
				'name'    => __( 'Page', 'mooberry-book-manager' ),
				'type'    => 'select',
				'default' => 'default',
				'options' => $pages,
				'after'   => '<p><a class="button" id="mbdb_create_tax_grid_page_button">' . __( 'Create a new page', 'mooberry-book-manager' ) . '</a><img src="' . MBDB_PLUGIN_URL . 'includes/assets/ajax-loader.gif" style="display:none;" id="mbdb_create_tax_grid_page_progress"/><div id="mbdb_create_tax_grid_page_results"></div> </p>'
			)
		);

		// break up the description into multiple sections to keep the HTML
		// out of the translatable text
		$description1 = __( 'These will be used to build website URL for the Taxonomy Book Grids.  Text entered in these fields will be converted to "friendly URLs" by making them lower-case, removing the spaces, etc.', 'mooberry-book-manager' );
		$description2 = '<b>' . __( 'NOTE:', 'mooberry-book-manager' ) . '</b> ' . __( 'Wordpress reserved terms are not allowed here.', 'mooberry-book-manager' );
		$description4 = __( 'Reserved Terms', 'mooberry-book-manager' );
		$description5 = __( 'See a list of reserved terms.', 'mooberry-book-manager' );

		$description = $description1 .
		               '<br><br>' .
		               $description2 .
		               ' <a href="" onClick="window.open(\'' . plugins_url( 'views/reserved_terms.php', __FILE__ ) . '\', \'' . $description4 .
		               '\',  \'width=460, height=300, left=550, top=250, scrollbars=yes\'); return false;">' .
		               $description5 .
		               '</a>';

		$mbdb_settings_metabox->add_field( array(
				'id'   => 'mbdb_book_grid_slug_settings_title',
				'name' => __( 'TAXONOMY BOOK GRID URL SETTINGS', 'mooberry-book-manager' ),
				'type' => 'title',
				'desc' => $description,
			)
		);

		// get all taxonomies on a book
		//$taxonomies = mbdb_tax_grid_objects();
		$taxonomies = MBDB()->book_CPT->taxonomies;
		//$taxonomies = get_object_taxonomies('mbdb_book', 'objects' );


		// add a text field for each taxonomy
		foreach ( $taxonomies as $name => $taxonomy ) {
			$id            = 'mbdb_book_grid_' . $name . '_slug';
			$singular_name = $taxonomy->singular_name;
			$mbdb_settings_metabox->add_field( array(
					'id'              => $id,
					'name'            => $singular_name,
					'default'         => $this->get_tax_grid_slug_default( $singular_name ),
					'sanitization_cb' => array( $this, 'sanitize_slug' ),
					'type'            => 'text',
				)
			);
		}

		return apply_filters( 'mbdb_settings_grid_settings', $mbdb_settings_metabox );

	}

	public function get_tax_grid_slug_default( $name ) {
		if ( array_key_exists( $name, MBDB()->options->tax_grid_slugs ) ) {
			return MBDB()->options->tax_grid_slugs[ $name ];
		} else {
			$reserved_terms = mbdb_wp_reserved_terms();
			$slug           = sanitize_title( $name );
			if ( in_array( $slug, $reserved_terms ) ) {
				$slug = 'book-' . $slug;
			}

			return $slug;
		}
	}

	/**
	 *  Sets up the metabox for the Publishers settings
	 *
	 *
	 *
	 * @param object $mbdb_settings_metabox
	 *
	 * @return metabox object with Publishers fields
	 *
	 * @access public
	 * @since  3.0
	 */
	function mbdb_publishers( $mbdb_settings_metabox ) {
		$this->title = __( 'MBM Publishers Settings', 'mooberry-book-manager' );
		$mbdb_settings_metabox->add_field( array(
				'id'      => 'publishers',
				'type'    => 'group',
				'desc'    => __( 'Add your publishers.', 'mooberry-book-manager' ),
				'options' => array(
					'group_title'   => __( 'Publisher', 'mooberry-book-manager' ) . ' {#}',
					// since version 1.1.4, {#} gets replaced by row number
					'add_button'    => __( 'Add New Publisher', 'mooberry-book-manager' ),
					'remove_button' => __( 'Remove Publisher', 'mooberry-book-manager' ),
					'sortable'      => false,
					// beta
				),
			)
		);

		$mbdb_settings_metabox->add_group_field( 'publishers', array(
				'name'       => __( 'Publisher', 'mooberry-book-manager' ),
				'id'         => 'name',
				'type'       => 'text_medium',
				'attributes' => array(
					'required' => 'required',
				),
			)
		);

		$mbdb_settings_metabox->add_group_field( 'publishers', array(
				'name'       => __( 'Publisher Website', 'mooberry-book-manager' ),
				'id'         => 'website',
				'type'       => 'text_url',
				'desc'       => 'http://www.someWebsite.com/',
				'attributes' => array(
					'pattern' => MBDB()->helper_functions->url_validation_pattern(),
					'style'   => 'width:300px',
				),
			)
		);

		$mbdb_settings_metabox->add_group_field( 'publishers', array(
				'id'              => 'uniqueID',
				'type'            => 'text',
				'show_names'      => false,
				'sanitization_cb' => array( $this, 'uniqueID_generator' ),
				'attributes'      => array(
					'type' => 'hidden',
				),
			)
		);

		return apply_filters( 'mbdb_settings_publishers_settings', $mbdb_settings_metabox );
	}

		/**
	 *  Sets up the metabox for the Imprints settings
	 *
	 *
	 *
	 * @param object $mbdb_settings_metabox
	 *
	 * @return metabox object with Imprints fields
	 *
	 * @access public
	 * @since  3.0
	 */
	function mbdb_imprints( $mbdb_settings_metabox ) {
		$this->title = __( 'MBM Imprints Settings', 'mooberry-book-manager' );
		$mbdb_settings_metabox->add_field( array(
				'id'      => 'imprints',
				'type'    => 'group',
				'desc'    => __( 'Add your imprints.', 'mooberry-book-manager' ),
				'options' => array(
					'group_title'   => __( 'Imprint', 'mooberry-book-manager' ) . ' {#}',
					// since version 1.1.4, {#} gets replaced by row number
					'add_button'    => __( 'Add New Imprint', 'mooberry-book-manager' ),
					'remove_button' => __( 'Remove Imprint', 'mooberry-book-manager' ),
					'sortable'      => false,
					// beta
				),
			)
		);

		$mbdb_settings_metabox->add_group_field( 'imprints', array(
				'name'       => __( 'Imprint', 'mooberry-book-manager' ),
				'id'         => 'name',
				'type'       => 'text_medium',
				'attributes' => array(
					'required' => 'required',
				),
			)
		);

		$mbdb_settings_metabox->add_group_field( 'imprints', array(
				'name'       => __( 'Imprint Website', 'mooberry-book-manager' ),
				'id'         => 'website',
				'type'       => 'text_url',
				'desc'       => 'http://www.someWebsite.com/',
				'attributes' => array(
					'pattern' => MBDB()->helper_functions->url_validation_pattern(),
					'style'   => 'width:300px',
				),
			)
		);

		$mbdb_settings_metabox->add_group_field( 'imprints', array(
				'id'              => 'uniqueID',
				'type'            => 'text',
				'show_names'      => false,
				'sanitization_cb' => array( $this, 'uniqueID_generator' ),
				'attributes'      => array(
					'type' => 'hidden',
				),
			)
		);

		return apply_filters( 'mbdb_settings_imprints_settings', $mbdb_settings_metabox );
	}
	/**
	 *  Sets up the metabox for the Editions settings
	 *
	 *
	 *
	 * @param object $mbdb_settings_metabox
	 *
	 * @return metabox object with Editions fields
	 *
	 * @access public
	 * @since  3.0
	 */
	function mbdb_editions( $mbdb_settings_metabox ) {
		$this->title = __( 'MBM Editions Settings', 'mooberry-book-manager' );
		$mbdb_settings_metabox->add_field( array(
				'id'      => 'editions',
				'type'    => 'group',
				'desc'    => __( 'Add any additional formats your books are available in.', 'mooberry-book-manager' ),
				'options' => array(
					'group_title'   => __( 'Format', 'mooberry-book-manager' ) . ' {#}',
					// since version 1.1.4, {#} gets replaced by row number
					'add_button'    => __( 'Add New Format', 'mooberry-book-manager' ),
					'remove_button' => __( 'Remove Format', 'mooberry-book-manager' ),
					'sortable'      => false,
					// beta
				)
			)
		);

		$mbdb_settings_metabox->add_group_field( 'editions', array(
				'name'       => __( 'Format Name', 'mooberry-book-manager' ),
				'id'         => 'name',
				'type'       => 'text_medium',
				'attributes' => array(
					'required' => 'required',
				),
			)
		);

		$mbdb_settings_metabox->add_group_field( 'editions', array(
				'id'              => 'uniqueID',
				'type'            => 'text',
				'show_names'      => false,
				'sanitization_cb' => array( $this, 'uniqueID_generator' ),
				'attributes'      => array(
					'type' => 'hidden',
				),
			)
		);

		return apply_filters( 'mbdb_settings_editions_settings', $mbdb_settings_metabox );
	}

	/**
	 *  Sets up the metabox for the General settings
	 *
	 *
	 *
	 * @param object $mbdb_settings_metabox
	 *
	 * @return metabox object with General fields
	 *
	 * @access public
	 * @since  3.0
	 */
	function mbdb_general_settings( $mbdb_settings_metabox ) {
		$this->title = __( 'MBM General Settings', 'mooberry-book-manager' );
		$mbdb_settings_metabox->add_field( array(
				'id'   => 'mbdb_book_image_settings_title',
				'name' => __( 'BOOK COVER SETTINGS', 'mooberry-book-manager' ),
				'type' => 'title',
			)
		);

		$mbdb_settings_metabox->add_field( array(
			'id'      => 'use_featured_image',
			'name'    => __( 'Use Book Cover as Featured Image', 'mooberry-book-manager' ),
			'type'    => 'select',
			'desc'    => __( 'Using the book cover as featured image can allow you to add books to sliders and other tools.  However, be aware that some themes automatically display the featured image at the top of the page. Custom CSS code may be necessary to hide that.', 'mooberry-book-manager' ),
			'options' => array(
				'no'  => 'No',
				'yes' => 'Yes'
			)
		) );

		$mbdb_settings_metabox->add_field( array(
			'id'	=>	'show_coming_soon_ribbon',
			'name'	=>	__('Show COMING SOON Ribbon on Corner of Book Cover?', 'mooberry-book-manager'),
			'type'              => 'multicheck',
				'select_all_button' => false,

				'options'           => MBDB()->helper_functions->ribbon_options(),
		));

		$mbdb_settings_metabox->add_field( array(
			'id'	=>	'coming_soon_ribbon_color',
			'name'	=>	__('Color of COMING SOON Ribbon', 'mooberry-book-manager'),
			'type'    => 'colorpicker',
			'default'	=>	'#ffff00'
		));

		$mbdb_settings_metabox->add_field( array(
			'id'	=>	'coming_soon_ribbon_color_text',
			'name'	=>	__('Text Color of COMING SOON Ribbon', 'mooberry-book-manager'),
			'type'    => 'colorpicker',
			'default'	=>	'#000000'
		));

		$mbdb_settings_metabox->add_field( array(
			'id'	=>	'show_new_ribbon',
			'name'	=>	__('Show NEW Ribbon on Corner of Book Cover?', 'mooberry-book-manager'),
			'type'              => 'multicheck',
				'select_all_button' => false,

				'options'           => MBDB()->helper_functions->ribbon_options(),

		));

		$mbdb_settings_metabox->add_field( array(
			'id'	=>	'new_ribbon_color',
			'name'	=>	__('Color of NEW Ribbon', 'mooberry-book-manager'),
			'type'    => 'colorpicker',
			'default'	=>	'#ff0000'
		));

		$mbdb_settings_metabox->add_field( array(
			'id'	=>	'new_ribbon_color_text',
			'name'	=>	__('Text Color of NEW Ribbon', 'mooberry-book-manager'),
			'type'    => 'colorpicker',
			'default'	=>	'#ffffff'
		));

		$mbdb_settings_metabox->add_field( array(
			'id'	=>	'new_ribbon_days',
			'name'	=>	__('Number of Days to Display NEW Ribbon', 'mooberry-book-manager'),
			'type'    => 'text_small',
			'default'	=> 7,
			'attributes' => array(
					'type'    => 'number',
					'pattern' => '\d*',
				'min'	=> 1,)

		));

		$mbdb_settings_metabox->add_field( array(
				'id'                => 'show_placeholder_cover',
				'name'              => __( 'Show Placeholder Cover On', 'mooberry-book-manager' ),
				'type'              => 'multicheck',
				'select_all_button' => false,
				'desc'              => __( 'The placeholder cover is used for books that do not have a cover selected.', 'mooberry-book-manager' ),
				'options'           => MBDB()->helper_functions->placeholder_cover_options(),
			)
		);
		/*
		// split up the descriptions to keep the html out of the
		// translatable text
		$description1 = __('If you need to restore the image that came with Mooberry Book Manager, download the ', 'mooberry-book-manager');
		$description2 = __('Mooberry Book Manager Image Fixer plugin', 'mooberry-book-manager');
		$description = '<span style="font-style:italic">' .$description1 . ' <a target="_new" href="' . admin_url('plugin-install.php?tab=search&s=mooberry+book+manager+image+fixer') . '">' . $description2 . '</a></span>.';
	*/
		$mbdb_settings_metabox->add_field( array(
				'id'         => 'coming-soon',
				'name'       => __( 'Placeholder Cover Image', 'mooberry-book-manager' ),
				'type'       => 'file',
				'attributes' => array(
					'style' => 'width:300px',
				),
				//		'after_row'	=>	$description,
				'options'    => array(
					'url'                  => false,
					'add_upload_file_text' => __( 'Choose or Upload File', 'mooberry-book-manager' ),
				),
			)
		);


		$mbdb_settings_metabox->add_field( array(
				'id'   => 'mbdb_popup_settings',
				'name' => __( 'POP UP CARD ON HOVER SETTINGS', 'mooberry-book-manager' ),
				'type' => 'title',
				'desc' => __('A card with information about a book will pop up when the mouse moves over the book cover', 'mooberry-book-manager'),

			)
		);

		 $fields = MBDB()->helper_functions->get_popup_card_fields();

		 $current_fields = MBDB()->options->popup_card_fields;

		 $fields_off = '';
		 $fields_on = '';
		 foreach ( $fields as $key => $title ) {
			 if ( !in_array($key, $current_fields)) {
				 $fields_off .= '<li class="ui-state-default" data-field="' . $key . '">' . $title . '</li>';
			 }
		 }

		 foreach ( $current_fields as $field ) {
			 if ( isset($fields[$field])) {
				 $fields_on .= '<li class="ui-state-default" data-field="' . $field . '">' . $fields[ $field ] . '</li>';
			 }
		 }

		$mbdb_settings_metabox->add_field( array(
			'id'	=>	'use_popup_card',
			'name'	=>	__('Use Pop-up Card?', 'mooberry-book-manager'),
			'type'	=>	'select',
			'options' => array(
				'no'  => 'No',
				'yes' => 'Yes'
			),
			'after'	=> '<div id="mbdb_popup_card_fields" style="display:none;"><p>' . sprintf(__('Drag and Drop up to %d fields into the Selected Fields list to display that data on the popup card.', 'mooberry-book-manager'),  3 ) . '</p><div id="mbdb_popup_card_field_list_all"> ' . __('Field List: ', 'mooberry-book-manager') . '<ul id="_mbdb_popup_card_field_list_all" class="mbdb_popup_card_connected_field_list">' . $fields_off . '</ul></div><div id="mbdb_popup_card_field_list">' . __('Selected Fields: ', 'mooberry-book-manager') . '<ul id="_mbdb_popup_card_field_list" class="mbdb_popup_card_connected_field_list">' . $fields_on . '</ul></div></div>',
			));

		 $mbdb_settings_metabox->add_field( array(
			 'id'=>'popup_card_background_color',
			 'name'	=>	__('Background Color of Pop-up Card', 'mooberry-book-manager'),
			 'type'	=>	'colorpicker',
			 'default'	=> '#ffffff'
		 ));

		 $mbdb_settings_metabox->add_field( array(
			 'id'=>'popup_card_text_color',
			 'name'	=>	__('Text Color of Pop-up Card', 'mooberry-book-manager'),
			 'type'	=>	'colorpicker',
			 'default'	=> '#000000'
		 ));

		 $mbdb_settings_metabox->add_field( array(
			 'id'=>'popup_card_width',
			 'name'	=>	__('Width of Pop-up Card (pixels)', 'mooberry-book-manager'),
			 'type'	=>	'text_small',
			 'default'	=> '400',
			 	'attributes' => array(
					'type'    => 'number',
					'pattern' => '\d*',
				)
		 ));


		return apply_filters( 'mbdb_settings_general_settings', $mbdb_settings_metabox );
	}

	/**
	 *  Sets up the metabox for the Retailers settings
	 *
	 *
	 *
	 * @param object $mbdb_settings_metabox
	 *
	 * @return metabox object with Retailers fields
	 *
	 * @access public
	 * @since  3.0
	 */
	function mbdb_retailers( $mbdb_settings_metabox ) {
		/*
		// split up the descriptions to keep the html out of the
		// translatable text
		$description1 = __('Add any additional retailers that sell your books. If you need to restore images that came with Mooberry Book Manager, download the ', 'mooberry-book-manager');
		$description2 = __('Mooberry Book Manager Image Fixer plugin', 'mooberry-book-manager');
		$description = $description1 . ' <a target="_new" href="' . admin_url('plugin-install.php?tab=search&s=mooberry+book+manager+image+fixer') . '">' . $description2 . '</a>.';
		*/
		$this->title = __( 'MBM Retailers Settings', 'mooberry-book-manager' );

		$mbdb_settings_metabox->add_field( array(
			'id'	=>	'retailer_buttons_title',
			'type'	=>	'title',
		'name' => __('Retailer Buttons', 'mooberry-book-manager')));

		$mbdb_settings_metabox->add_field( array(
			'id'=>'retailer_buttons',
			'type'	=> 'radio',
			'options' => array( 'matching' => __('Use Matching Buttons for All Retailers', 'mooberry-book-manager'),
				'individual' => __('Use Individual Buttons for Each Retailer', 'mooberry-book-manager'),)
		));

		$style = 'style="margin:0;';
			$background = isset($_POST['retailer_buttons_color']) ? sanitize_text_field($_POST['retailer_buttons_color']) : MBDB()->options->retailer_buttons_background_color;
			if ( $background != '' ) {
				$style .= 'background-color: ' . $background . ';';
			}
			$text = isset($_POST['retailer_buttons_color_text']) ? sanitize_text_field($_POST['retailer_buttons_color_text']) :MBDB()->options->retailer_buttons_text_color;
			if ( $text !='') {
				$style .= 'color: ' . $text . ';';
			}


		$style .= '"';

		$mbdb_settings_metabox->add_field( array(
			'id'	=>	'retailer_buttons_color_title',
			'type'	=>	'title',
		'name' => __('Retailer Buttons Colors', 'mooberry-book-manager'),
			'after' => '<div style="display:block;margin:2em 0em;"><a class="mbdb_retailer_button " id="mbdb_test_retailer_button" ' . $style . '>Sample Button</a></div>'));


		$mbdb_settings_metabox->add_field( array(
			'id'=>'retailer_buttons_color',
			'type'	=> 'colorpicker',
			'name'=> __ ('Background Color'),

		));


		$mbdb_settings_metabox->add_field( array(
			'id'=>'retailer_buttons_color_text',
			'type'	=> 'colorpicker',
			'name'=> __ ('Text Color'),

		));


		$mbdb_settings_metabox->add_field( array(
			'id'	=>	'retailers_title',
			'type'	=>	'title',
		'name' => __('Retailers', 'mooberry-book-manager')));


		$mbdb_settings_metabox->add_field( array(
				'id'      => 'retailers',
				'type'    => 'group',
				//		'desc'			=>	$description,
				'options' => array(
					'group_title'   => __( 'Retailer', 'mooberry-book-manager' ) . ' {#}',
					// since version 1.1.4, {#} gets replaced by row number
					'add_button'    => __( 'Add Retailer', 'mooberry-book-manager' ),
					'remove_button' => __( 'Remove Retailer', 'mooberry-book-manager' ),
					'sortable'      => false,
					// beta
				)
			)
		);
		$mbdb_settings_metabox->add_group_field( 'retailers', array(
				'name'       => __( 'Retailer Name', 'mooberry-book-manager' ),
				'id'         => 'name',
				'type'       => 'text_medium',
				'attributes' => array(
					'required' => 'required',
				),
				'description' => __('If you are using buttons instead of images, this will be the text of the button', 'mooberry-book-manager'),
			)
		);

			$mbdb_settings_metabox->add_group_field( 'retailers', array(
			'id'=>'retailer_button_image',
			'name'	=>	__('Use a Button or an Image for this Retailer?', 'mooberry-book-manager'),
			'type'	=> 'radio',
			'options' => array( 'button' => __('Button', 'mooberry-book-manager'),
				'image' => __('Image', 'mooberry-book-manager'),),

		));

			$mbdb_settings_metabox->add_group_field( 'retailers', array(
				'id'=>'retailer_test_button',
			'type'=>'title',
			'render_row_cb' => array( $this, 'render_retailer_test_button'),
			));

			$mbdb_settings_metabox->add_group_field( 'retailers', array(
			'id'=>'retailer_button_color',
			'type'	=> 'colorpicker',
			'name'=> __ ('Button Background Color'),

		));


		$mbdb_settings_metabox->add_group_field( 'retailers', array(
			'id'=>'retailer_button_color_text',
			'type'	=> 'colorpicker',
			'name'=> __ ('Button Text Color'),

		));



		$mbdb_settings_metabox->add_group_field( 'retailers', array(
				'name'       => __( 'Retailer Logo Image', 'mooberry-book-manager' ),
				'id'         => 'image',
				'type'       => 'file',
				'attributes' => array(
					'size' => 45
				),
				'options'    => array(
					'add_upload_file_text' => __( 'Choose or Upload File', 'mooberry-book-manager' ),
				),
			)
		);
		$mbdb_settings_metabox->add_group_field( 'retailers', array(
				'id'              => 'uniqueID',
				'type'            => 'text',
				'show_names'      => false,
				'sanitization_cb' => array( $this, 'uniqueID_generator' ),
				'attributes'      => array(
					'type' => 'hidden',
				),
			)
		);


		$mbdb_settings_metabox = MBDB()->helper_functions->affiliate_fields( 'retailers', $mbdb_settings_metabox );


		return apply_filters( 'mbdb_settings_retailer_fields', $mbdb_settings_metabox );
	}

	function render_retailer_test_button( $field_args, $field) {
		$index = $field->group->index;
		$id = $field->group->value[ $index ]['uniqueID'];

		$style = 'style="margin:0;';
			$background = isset($_POST['retailers'][$index])  ? sanitize_text_field($_POST['retailers'][$index]['retailer_button_color']) : MBDB()->options->retailers[$id]->button_background_color;
			if ( $background != '' ) {
				$style .= 'background-color: ' . $background . ';';
			}
			$text = isset($_POST['retailers'][$index]) ? sanitize_text_field($_POST['retailers'][$index]['retailer_button_color_text']) : MBDB()->options->retailers[$id]->button_text_color;
			if ( $text !='') {
				$style .= 'color: ' . $text . ';';
			}

			$style .= '"';


		$name = isset($_POST['retailers'][$index]) ? sanitize_text_field($_POST['retailers'][$index]['name']) : MBDB()->options->retailers[$id]->name;

		echo '<div class="cmb-row mbdb_retailer_button_preview">
					<div class="cmb-th">
						<label>Preview:</label>
					</div>
					<div class="cmb-td">
						<a class="mbdb_retailer_button" id="retailers_' . $index . '_retailer_test_button" ' . $style . '> ' . $name . '</a>
					</div>
			</div>';
	}

	/**
	 *  Sets up the metabox for the Formats settings
	 *
	 *
	 *
	 * @param object $mbdb_settings_metabox
	 *
	 * @return metabox object with Formats fields
	 *
	 * @access public
	 * @since  3.0
	 */
	function mbdb_formats( $mbdb_settings_metabox ) {
		/*
		// split up the descriptiosn to keep the HTML out of the
		// translatable text
		$description1 = __('If you have free books for download, add any additional formats your books are available in. If you need to restore images that came with Mooberry Book Manager, download the', 'mooberry-book-manager');
		$description2 = __('Mooberry Book Manager Image Fixer plugin', 'mooberry-book-manager');
		$description = $description1 . ' <a target="_new" href="' . admin_url('plugin-install.php?tab=search&s=mooberry+book+manager+image+fixer') . '">' . $description2 . '</a>.';
  */
		$this->title = __( 'MBM Book Formats Settings', 'mooberry-book-manager' );
		$mbdb_settings_metabox->add_field( array(
				'id'      => 'formats',
				'type'    => 'group',
				//		'desc'			=> $description,
				'options' => array(
					'group_title'   => __( 'Format Name', 'mooberry-book-manager' ) . ' {#}',
					// since version 1.1.4, {#} gets replaced by row number
					'add_button'    => __( 'Add Format', 'mooberry-book-manager' ),
					'remove_button' => __( 'Remove Format', 'mooberry-book-manager' ),
					'sortable'      => false,
					// beta
				)
			)
		);
		$mbdb_settings_metabox->add_group_field( 'formats', array(
				'name'       => _x( 'Format', 'noun: the format of a book', 'mooberry-book-manager' ),
				'id'         => 'name',
				'type'       => 'text',
				'attributes' => array(
					'required' => 'required',
				),
			)
		);
		$mbdb_settings_metabox->add_group_field( 'formats', array(
				'name'    => _x( 'Format Image', 'noun: the image that represents the format of a book', 'mooberry-book-manager' ),
				'id'      => 'image',
				'type'    => 'file',
				'options' => array(
					'add_upload_file_text' => __( 'Choose or Upload File', 'mooberry-book-manager' ),
				),
			)
		);
		$mbdb_settings_metabox->add_group_field( 'formats', array(
				'id'              => 'uniqueID',
				'type'            => 'text',
				'show_names'      => false,
				'sanitization_cb' => array( $this, 'uniqueID_generator' ),
				'attributes'      => array(
					'type' => 'hidden',
				),
			)
		);

		return apply_filters( 'mbdb_settings_format_fields', $mbdb_settings_metabox );
	}


	/**
	 *  Sets up the metabox for the Social Media settings
	 *
	 *  This is only shown by the add-ons that need it (MA and MK)
	 *  because the tab is not added except by those add-ons
	 *
	 * @param object $mbdb_settings_metabox
	 *
	 * @return metabox object with Social Media fields
	 *
	 * @access public
	 * @since  3.0
	 */
	public function mbdb_social_media( $metabox ) {
		$this->title = __( 'MBM Social Media Settings', 'mooberry-book-manager' );
		$metabox->add_field( array(
				'id'      => 'social_media',
				'type'    => 'group',
				'desc'    => __( 'Add Social Media Sites With Whom You Have Accounts.', 'mooberry-book-manager' ),
				'options' => array(
					'group_title'   => __( 'Social Media Site', 'mooberry-book-manager' ) . ' {#}',
					// {#} gets replaced by row number
					'add_button'    => __( 'Add New Social Media Site', 'mooberry-book-manager' ),
					'remove_button' => __( 'Remove Social Media Site', 'mooberry-book-manager' ),
					'sortable'      => false,
				),
			)
		);

		$metabox->add_group_field( 'social_media', array(
				'name'       => __( 'Social Media Site Name', 'mooberry-book-manager' ),
				'id'         => 'name',
				'type'       => 'text_medium',
				'attributes' => array(
					'required' => 'required',
				),
			)
		);

		$metabox->add_group_field( 'social_media', array(
				'name'       => __( 'Social Media Logo Image', 'mooberry-book-manager' ),
				'id'         => 'image',
				'type'       => 'file',
				'attributes' => array(
					'size' => 45
				),
				'options'    => array(
					'add_upload_file_text' => __( 'Choose or Upload File', 'mooberry-book-manager' ),
				),
			)
		);
		$metabox->add_group_field( 'social_media', array(
				'id'              => 'uniqueID',
				'type'            => 'text',
				'show_names'      => false,
				'sanitization_cb' => array( $this, 'uniqueID_generator' ),
				'attributes'      => array(
					'type' => 'hidden',
				),
			)
		);

		return apply_filters( 'mbdb_settings_social_media_fields', $metabox );

	}


	// this is called with an add_filter('mbdb_settings_pages') by the
	// add ons that need it.
	public function mbdb_add_social_media_page( $pages ) {

		$pages['mbdb_social_media_options'] = array(
			'page_title' => __( 'Mooberry Book Manager Social Media Options', 'mooberry-book-manager' ),
			'menu_title' => __( 'Social Media', 'mooberry-book-manager' )
		);

		return $pages;
	}

	public function cancel_import() {
		check_ajax_referer( 'mbdb_admin_options_cancel_import_nonce', 'security' );

		$this->import_process->cancel_process();
		$this->import_books_csv_process->cancel_process();
		$this->import_novelist_books_process->cancel_process();
		//echo '<h3>' . __('Import canceled.', 'mooberry-book-manager') . '</h3>';
		// $key = 'mbdb_import_books_cancel';
		// $message = __('Book import canceled!', 'mooberry-book-manager');
		// $message .= '&nbsp;&nbsp;<a href="#" class="button mbdb_admin_notice_dismiss" data-admin-notice="' . $key . '">' . __('Dismiss this notice', 'mooberry-book-manager') . '</a>';
		// $type = 'updated';

		// MBDB()->helper_functions->set_admin_notice( $message, $type, $key);
		MBDB()->helper_functions->remove_admin_notice( 'mbdb_import_books_process' );
		MBDB()->helper_functions->remove_admin_notice( 'mbdb_import_books_complete' );

		echo 'Import has been canceled!';
		wp_die();
	}

	protected function import_json() {

			if ( isset( $_POST['mbdb_import_file_nonce'] ) && wp_verify_nonce( $_POST['mbdb_import_file_nonce'], plugin_basename( __FILE__ ) ) ) {
				if ( ! empty( $_FILES ) && isset( $_FILES['mbdb_import_file'] ) ) {
					$file = wp_upload_bits( $_FILES['mbdb_import_file']['name'], null, @file_get_contents( $_FILES['mbdb_import_file']['tmp_name'] ) );
					if ( false === $file['error'] ) {

						//error_log('calling import');
						$this->import( $file );
					}
				}
			} ?>
			<h3><?php _e('Import Books', 'mooberry-book-manager'); ?></h3>
            <p><?php _e( 'Books will be imported in the background. You may leave this page while they are importing. A notice will be displayed while books are importing.', 'mooberry-book-manager' ); ?> </p> <?php

			if ( ! $this->import_process->is_queue_empty() ) {
				echo '<h3>' . __( 'Please wait for the current batch of imports to finish before importing more.', 'mooberry-book-manager' ) . '</h3>';
				echo '<a id="mbdb_cancel_import" class="button" >' . __( 'Cancel Import', 'mooberry-book-manager' ) . '</a><img src="' . MBDB_PLUGIN_URL . 'includes/assets/ajax-loader.gif" style="display:none;" id="mbdb_cancel_import_progress"/><div id="mbdb_cancel_results"></div>';

				return;
			}
			?>
            <h3><?php _e( 'Choose an export file from Mooberry Book Manager.', 'mooberry-book-manager' ); ?></h3>

            <form enctype="multipart/form-data" method="post">
                <input type="file" id="mbdb_import_file" name="mbdb_import_file"/>
				<?php wp_nonce_field( plugin_basename( __FILE__ ), 'mbdb_import_file_nonce' ); ?>
                <input type="submit" id="mbdb_import_button" value="Import"/>
            </form>

			<?php
	}

	protected function export_json() {
		if ( ! $this->import_process->is_queue_empty()|| !$this->import_novelist_books_process->is_queue_empty() || !$this->import_books_csv_process->is_queue_empty()  ) {
				echo '<h3>' . __( 'Please wait for the current batch of imports to finish before exporting more.', 'mooberry-book-manager' ) . '</h3>';

				return;
			}

			?>
           <h3><?php _e('Export Books', 'mooberry-book-manager'); ?></h3> <p><?php _e( 'This will create a text file with all of the books entered into Mooberry Book Manager. Books that are in Draft Mode or in the Trash will not be exported.  Options for filtering which books to export will be coming in a future update.', 'mooberry-book-manager' ); ?></p>
			<?php do_action( 'mbdb_export_add_fields' ); ?>
            <p><a class="button"
                  id="mbdb_<?php echo $this->tab; ?>"><?php echo $this->tabs[ $this->page ][ $this->tab ]; ?></a><img
                        src="<?php echo MBDB_PLUGIN_URL; ?>includes/assets/ajax-loader.gif"
                        style="display:none;"
                        id="mbdb_<?php echo $this->tab; ?>_progress"/></p>
            <p>
            <div id="mbdb_results"/></p>
			<?php

	}

	protected function import_novelist_form() {
		if ( ! $this->import_process->is_queue_empty() || !$this->import_novelist_books_process->is_queue_empty() || !$this->import_books_csv_process->is_queue_empty() ) {
				echo '<h3>' . __( 'Please wait for the current batch of imports to finish before importing more.', 'mooberry-book-manager' ) . '</h3>';

				return;
			}
			?>
			<h3><?php _e('Import Books from Novelist', 'mooberry-book-manager') ?></h3>
            <p><?php _e( 'This will take any books you have entered with Novelist and import them into Mooberry Book Manager.  Books that are in the Trash will not be imported.  Nothing will be deleted from Novelist so you still have full access to that data while the Novelist plugin in activated.', 'mooberry-book-manager' ); ?></p>
            <ul style="padding-left:40px; list-style:initial">
                <li><?php _e( 'The Novelist plugin must be activated in order to import series and genres.', 'mooberry-book-manager' ) ?></li>
                <li><?php _e( 'ISBN13 and page length will be imported as a Paperback format', 'mooberry-book-manager' ); ?></li>
                <li><?php _e( 'ASIN will be imported as a Kindle format', 'mooberry-book-manager' ) ?></li>
                <li><?php _e('If ASIN is included, it will be used to add Kindle Live Preview. The excerpt, if included, will also
                    be imported. If you would rather use the excerpt text than the Kindle Live Preview, you can change
                    that setting on the book page.', 'mooberry-book-manager'); ?>
                </li>
                <li><?php _e( 'Contributors will not be imported because there isn\'t a single corresponding field. Mooberry Book Manager has separate fields for editors, cover artists, and illustrators where contributors can be added to.', 'mooberry-book-manager' ); ?> </li>
            </ul>

           <?php _e('PLEASE NOTE: At this time, only data entered with the base, free level of Novelist will be imported. Data entered with extensions will not be imported.' ,'mooberry-book-manager'); ?>

            <p><a class="button"
                  id="mbdb_<?php echo $this->tab; ?>"><?php echo $this->tabs[ $this->page ][ $this->tab ]; ?></a><img
                        src="<?php echo MBDB_PLUGIN_URL; ?>includes/assets/ajax-loader.gif"
                        style="display:none;"
                        id="mbdb_<?php echo $this->tab; ?>_progress"/></p>
            <p>
            <div id="mbdb_results"/></p>
			<?php
	}

	protected function import_csv() {
		if ( ! $this->import_process->is_queue_empty() || !$this->import_novelist_books_process->is_queue_empty() || !$this->import_books_csv_process->is_queue_empty() ) {
				echo '<h3>' . __( 'Please wait for the current batch of imports to finish before importing more.', 'mooberry-book-manager' ) . '</h3>';
echo '<a id="mbdb_cancel_import" class="button" >' . __( 'Cancel Import', 'mooberry-book-manager' ) . '</a><img src="' . MBDB_PLUGIN_URL . 'includes/assets/ajax-loader.gif" style="display:none;" id="mbdb_cancel_import_progress"/><div id="mbdb_cancel_results"></div>';

				return;
			}

		if (  isset( $_POST[  'mbdb_import_file_nonce' ] )  && wp_verify_nonce( $_POST[ 'mbdb_import_file_nonce'], plugin_basename( __FILE__ ) ) ) {
			if ( ! empty( $_FILES ) && isset( $_FILES['mbdb_import_file'] ) ) {
				$file = wp_upload_bits( $_FILES['mbdb_import_file']['name'], null, @file_get_contents( $_FILES['mbdb_import_file']['tmp_name'] ) );
				if ( false === $file['error'] ) {

					// do the import
					$importer = new MBDB_Book_CSV_Importer( $file, $this->import_books_csv_process, $this->admin_notice_manager  );
					$importer->import();
			}
			}
		}   ?>
            <h1>Importing Books via CSV</h1>
                <h3>Choose an import CSV file.</h3>
<p><?php _e(sprintf('The CSV file must be formatted in a specific manner. Please see %s for details. Download a file template to use by clicking the button below.', '<a href="https://mooberry-book-manager.helpscoutdocs.com/article/119-importing-from-a-csv-file" target="_new">https://mooberry-book-manager.helpscoutdocs.com/article/119-importing-from-a-csv-file</a>', 'mooberry-book-manager')); ?></p>
            <p><?php _e( 'Books will be imported in the background. You may leave this page while they are importing.', 'mooberry-book-manager' ); ?> </p>
		 <p><a class="button"
                  id="mbdb_export_csv_columns"><?php _e('Download file template') ?></a><img
                        src="<?php echo MBDB_PLUGIN_URL; ?>includes/assets/ajax-loader.gif"
                        style="display:none;"
                        id="mbdb_<?php echo $this->tab; ?>_progress"/></p>
            <p>
				<?php

				if ( ! $this->import_process->is_queue_empty() ) {
					echo '<h3>' . __( 'Please wait for the current batch of imports to finish before importing more.', 'mooberry-book-manager' ) . '</h3>';
					echo '<a id="mbdb_cancel_import" class="button" >' . __( 'Cancel Import', 'mooberry-book-manager' ) . '</a><img src="' . MBDB_PLUGIN_URL . 'includes/assets/ajax-loader.gif" style="display:none;" id="mbdb_cancel_import_progress"/><div id="mbdb_cancel_results"></div>';

					return;
				}
				?>
            <form id="mbdb_import_listings_form" enctype="multipart/form-data" method="post">
			<input type="file" id="mbdb_import_file" name="mbdb_import_file" />
			<?php wp_nonce_field( plugin_basename( __FILE__ ), 'mbdb_import_file_nonce' ); ?>
			<input type="submit" id="mbdb_import_button" value="Import" />
			</form>

		<?php
	}

	protected function export_csv() {
	if ( ! $this->import_process->is_queue_empty() || !$this->import_novelist_books_process->is_queue_empty() || !$this->import_books_csv_process->is_queue_empty() ) {
				echo '<h3>' . __( 'Please wait for the current batch of imports to finish before exporting more.', 'mooberry-book-manager' ) . '</h3>';

				return;
			}

			?>
           <h3><?php _e('Export Books to CSV file', 'mooberry-book-manager'); ?></h3>
		<p><?php _e( 'This will create a CSV file with all of the books entered into Mooberry Book Manager. Books that are in Draft Mode or in the Trash will not be exported.', 'mooberry-book-manager' ); ?></p>
			<?php do_action( 'mbdb_export_add_fields' ); ?>
            <p><a class="button"
                  id="mbdb_<?php echo $this->tab; ?>"><?php echo $this->tabs[ $this->page ][ $this->tab ]; ?></a><img
                        src="<?php echo MBDB_PLUGIN_URL; ?>includes/assets/ajax-loader.gif"
                        style="display:none;"
                        id="mbdb_<?php echo $this->tab; ?>_progress"/></p>
            <p>
            <div id="mbdb_results"/></p>
			<?php
	}

	public function import_export() {

		if ( $this->page != 'mbdb_import_export' ) {
			return;
		}
		if ( wp_doing_ajax() ) {
			return;
		}
		$this->title        = __( 'MBM Book Import/Export Settings', 'mooberry-book-manager' );
		$this->show_metabox = false;

		switch ( $this->tab ) {
			case 'import':
				$this->import_json();
				break;
			case 'import_novelist':
				$this->import_novelist_form();
				break;
			case 'export':
				$this->export_json();
				break;
			case 'export_csv':
				$this->export_csv();
				break;
			case 'import_csv':
			case '':
				$this->tab = 'import_csv';
				$this->import_csv();
				break;
		}

	}

	function export_csv_file_row($book, $rows) {


		$columns = array(
			'title'      => 'title',
			'summary'    => 'summary',
			'subtitle'   => 'subtitle',
			'goodreads'  => 'goodreads',
			'reedsy'     => 'reedsy',
			'google'     => 'google_books',
			'order'      => 'series_order',
			'additional' => 'additional_info',
			'date'       => 'release_date',
		);


		$row = array();

		foreach ( $columns as $column => $property ) {
			$row[] = $book->$property != null ? $book->$property : '';
		}


		$taxonomies = array(
			'genres'        => array( 'property' => 'genres', 'taxonomy' => 'mbdb_genre' ),
			'tags'          => array( 'property' => 'tags', 'taxonomy' => 'mbdb_tag' ),
			'series'        => array( 'property' => 'series', 'taxonomy' => 'mbdb_series' ),
			'editors'       => array( 'property' => 'editors', 'taxonomy' => 'mbdb_editor' ),
			'illustrators'  => array( 'property' => 'illustrators', 'taxonomy' => 'mbdb_illustrator' ),
			'cover_artists' => array( 'property' => 'cover_artists', 'taxonomy' => 'mbdb_cover_artist' ),
		);

		foreach ( $taxonomies as $column => $info ) {
			$terms = $book->{$info['property']};
			$slugs = array();
			foreach ( $terms as $term ) {
				$slugs[] = $term->slug;
			}
			$row[] = join( ',', $slugs );
		}



		// publisher
		$row[] = $book->publisher != null ? $book->publisher->name : '';


		// imprint
		$row[] = $book->imprint != null ? $book->imprint->name : '';

		// excerpt
		$row[] = $book->excerpt_type;
		$row[] = $book->excerpt_type == 'text' ?  $book->excerpt : $book->kindle_preview;


		// buy links
		$retailers       = MBDB()->options->retailers;
		if ( is_array( $retailers ) ) {
			foreach ( $retailers as $retailer ) {
				$link = '';
				foreach ( $book->buy_links as $buy_link ) {
					if ( $buy_link->retailer->name == $retailer->name ) {
						$link = $buy_link->link;
						break;
					}
				}
				$row[] = $link;
			}
		}


		// download links
		$download_formats     = MBDB()->options->download_formats;
		if ( is_array( $download_formats ) ) {
			foreach ( $download_formats as $download_format ) {
				$link = '';
				foreach ( $book->download_links as $download_link ) {
					if ( $download_link->download_format->name == $download_format->name) {
						$link = $download_link->link;
						break;
					}
				}
				$row[] = $link;
			}
		}

		// formats
		$formats        = MBDB()->options->edition_formats;
		$edition_fields = array(
					'isbn'     => 'isbn',
					'doi'      => 'doi',
					'sku'     => 'sku',
					'language' => 'language',
					'pages'    => 'length',
					'height'   => 'height',
					'width'    => 'width',
					'unit'     => 'unit',
					'price'    => 'retail_price',
					'currency' => 'currency',
					'title'    => 'edition_title',
				);
		if ( is_array( $formats ) ) {
			foreach ( $formats as $format ) {
				$book_edition = null;
				foreach ( $book->editions as $edition ) {
					if ( $edition->format->name == $format->name ) {
						$book_edition = $edition;
						break;
					}
				}
				foreach ( $edition_fields as $edition_field => $property ) {
					$row[] = isset( $book_edition ) ? $book_edition->$property : '';
				}
			}
		}


		// cover
		$row[] = $book->cover;


		// reviews
		$review_columns = array( 'name'=> 'reviewer_name',
			'link'=> 'url',
			'website'=>'website_name',
			'review'=>'review',);
		for($x =1; $x<6; $x++) {
			foreach ( $review_columns as $column => $property ) {
				$row[] = isset( $book->reviews[ $x - 1 ] ) ? $book->reviews[ $x - 1 ]->$property : '';

			}
		}




		$rows[] = apply_filters('mbdb_export_books_csv_row', $row, $book);

		return $rows;

	}


 	function export_csv_file() {
		check_ajax_referer( 'mbdb_export_nonce', 'export_nonce' );

		//$book_list = new MBDB_Book_List( 'all', 'title',  'ASC',  null,  null,  null,  null,  false,  false, true  );

		if ( array_key_exists( 'data', $_POST ) ) {
			$data = array_column( $_POST['data'], 'value', 'name' );
		} else {
			$data = array();
		}
		$columns_only =  isset($_POST['columns_only']) && $_POST['columns_only'] == 'true';


		$columns = array(
			'title'  ,
			'summary' ,
			'subtitle',
			'goodreads',
			'reedsy'   ,
			'google'   ,
			'order'    ,
			'additional',
			'date'     ,
			'genres'     ,
			'tags'          ,
			'series'        ,
			'editors'       ,
			'illustrators'  ,
			'cover_artists',
			'publisher',
			'imprint',
			'excerpt_type',
			'excerpt',

		);
		$retailers       = MBDB()->options->retailers;
		if ( is_array( $retailers ) ) {
			foreach ( $retailers as $retailer ) {
				$columns[] = 'retailer_' . $retailer->name . '_link';
			}
		}

		$download_formats     = MBDB()->options->download_formats;
		if ( is_array( $download_formats ) ) {
			foreach ( $download_formats as $download_format ) {
				$columns[] = 'download_' . $download_format->name . '_link';
			}
		}

		$formats        = MBDB()->options->edition_formats;
		$edition_fields = array(
					'isbn'     ,
					'doi'      ,
					'sku'     ,
					'language' ,
					'pages'    ,
					'height'   ,
					'width'    ,
					'unit'     ,
					'price'    ,
					'currency' ,
					'title'    ,
				);
		if ( is_array( $formats ) ) {
			foreach ( $formats as $format ) {
				foreach ( $edition_fields as $edition_field ) {
					$columns[] = 'format_' . $format->name . '_' . $edition_field;
				}
			}
		}

		$columns[]= 'cover';

		$review_columns = array( 'name',
			'link',
			'website',
			'review');
		for($x =1; $x<6; $x++) {
			foreach ( $review_columns as $column ) {
				$columns[] = 'review' . $x . '_' . $column;

			}
		}

		$file = array();
		$file[] = apply_filters('mbdb_export_books_csv_columns', $columns);

		if ( !$columns_only ) {
			$books = get_posts( apply_filters( 'mbdb_export_books_query', array(
					'posts_per_page' => - 1,
					'post_type'      => 'mbdb_book',
					'post_status'    => 'publish',
				), $data )
			);

			foreach ( $books as $book ) {
				//$book_obj = new Mooberry_Book_Manager_Book( $book->ID );
				$book_obj = MBDB()->book_factory->create_book( $book->ID );
				$book_obj = apply_filters( 'mbdb_export_data_book_object', $book_obj, $book );
				$file     = $this->export_csv_file_row( $book_obj, $file );
			}
		}

		$file = apply_filters('mbdb_export_data_all_books_csv', $file, $books );

		$fp = fopen(MBDB_PLUGIN_DIR . '/includes/admin/export.csv', 'w');
   		//add BOM to fix UTF-8 in Excel
    	fputs($fp, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
		foreach ($file as $fields) {
			fputcsv($fp, $fields);
		}

		fclose($fp);
		echo MBDB_PLUGIN_URL . '/includes/export_csv.php';
		wp_die();


 	}

	function export() {
		check_ajax_referer( 'mbdb_export_nonce', 'export_nonce' );

		//$book_list = new MBDB_Book_List( 'all', 'title',  'ASC',  null,  null,  null,  null,  false,  false, true  );

		if ( array_key_exists( 'data', $_POST ) ) {
			$data = array_column( $_POST['data'], 'value', 'name' );
		} else {
			$data = array();
		}
		$books     = get_posts( apply_filters( 'mbdb_export_books_query', array(
				'posts_per_page' => - 1,
				'post_type'      => 'mbdb_book',
				'post_status'    => 'publish',
			), $data )
		);
		$book_list = array();
		foreach ( $books as $book ) {
			//$book_obj = new Mooberry_Book_Manager_Book( $book->ID );
			$book_obj    = MBDB()->book_factory->create_book( $book->ID );
			$book_obj = apply_filters('mbdb_export_data_book_object', $book_obj, $book );
			if ( $book_obj ) {
				$book_list[] = apply_filters( 'mbdb_export_data_book_json', $book_obj->to_json(), $book_obj, $book );
			}
		}

		$book_list = apply_filters('mbdb_export_data_all_books', $book_list, $books );

		wp_reset_postdata();
		file_put_contents( MBDB_PLUGIN_DIR . '/includes/admin/export.txt', json_encode( $book_list ) );

		//print_r();
		//$output .= JSON_encode($results);
		//f
		////error_log(MBDB_PLUGIN_URL . 'export.html');
		//wp_redirect(plugin_dir_path( __FILE__ ) . 'export.php');
		//exit;
		//echo  MBDB_PLUGIN_URL . 'export.txt';//JSON_encode($books);
		echo MBDB_PLUGIN_URL . '/includes/export.php';
		//echo print_r(json_decode($book_list->to_json()));
		wp_die();

	}

	function update_apple_books_links() {
		check_ajax_referer( 'update_apple_books_link_nonce', 'update_apple_books_links_nonce' );

		$this->update_apple_books_links_process->update_links();

		wp_die();
	}

	function import_novelist() {
		check_ajax_referer( 'import_novelist_nonce', 'import_novelist' );

		$this->import_novelist_books_process->set_up_data();
		$novelist_books = get_posts( array(
			'post_type'      => 'book',
			'post_status'    => array( 'publish', 'draft' ),
			'posts_per_page' => - 1
		) );

		// get all book data
		foreach ( $novelist_books as $novelist_book ) {
			$this->import_novelist_books_process->push_to_queue( $novelist_book );
			$this->import_novelist_books_process->save();
		}
		$this->import_novelist_books_process->dispatch();

		echo admin_url( '/admin.php?page=mbdb_import_export&tab=import_novelist' );

		wp_die();
	}

	function import( $file ) {

		//$filename = plugin_dir_path( __FILE__ ) . $_POST['filename'];
		//$filename = MBDB_PLUGIN_DIR . 'NE MBMExport.txt';

		//$import = file_get_contents ( $filename );
		$import = file_get_contents( $file['file'] );

		$books = array();
		$data  = JSON_decode( $import );
		if ( $data == null || ! is_array( $data ) ) {
			echo '<h3>' . __( 'Invalid file format', 'mooberry-book-manager' ) . '</h3>';

			return;
		}
		echo '<p>' . sprintf( __( 'Importing %d books.', 'mooberry-book-manager' ), count( $data ) ) . '</p>';


		//return;
		$counter = 1;
		foreach ( $data as $json_string ) {

			$book = JSON_decode( $json_string );
			if ( $book == null ) {
				echo '<h3>' . __( 'Invalid file format', 'mooberry-book-manager' ) . '</h3>';

				return;
			}

			$this->import_process->push_to_queue( $book );
			if ( property_exists( $book, 'title' ) ) {
				$title = $book->title;
			} else {
				$title = 'Book';
			}
			echo '<p>' . sprintf( __( '%s added to queue!', 'mooberry-book-manager' ), $title ) . '</p>';

			//if ( $counter % 25 == 0 ) {

			$this->import_process->save();
			//}
			$counter ++;

			//$books[] = $new_book;
		}
		echo '<p>Starting import!</p>';
		$this->import_process->dispatch();
	}

	public function import_export_notices() {
		if ( is_admin() ) {
			if ( strstr( $_SERVER['REQUEST_URI'], 'export.php' ) !== false ) {
				$page = __( 'export', 'mooberry-book-manager' );
			} elseif ( strstr( $_SERVER['REQUEST_URI'], 'import.php' ) !== false ) {
				$page = __( 'import', 'mooberry-book-manager' );
			} else {
				return;
			}
		}
		$m = sprintf( __( 'If you would like to %s books entered with Mooberry Book Manager, please use the Import/Export page in the Mooberry Book Manager settings menu.', 'mooberry-book-manager' ), $page );
		echo apply_filters( 'mbdb_' . $page . '_notice', '<div id="message" class="updated"><p>' . $m . '</p></div>' );

	}

	protected function set_tabs() {
		$this->tabs = apply_filters('mbdb_import_export_tabs', array(
			'mbdb_import_export' => array(
				'import_csv'          => __( 'Import Books from CSV', 'mooberry-book-manager' ),
				'export_csv'		=>	__('Export Books to CSV', 'mooberry-book-manager' ),
				'import'          => __( 'Import Books', 'mooberry-book-manager' ),
				'export'          => __( 'Export Books', 'mooberry-book-manager' ),
				'import_novelist' => __( 'Import from Novelist', 'mooberry-book-manager' )
			) )
		);
	}


	/**
	 *
	 *  Verifies the Grid URL slug is not a WP reserved term
	 *
	 *
	 * @param  [string] $meta_value value the user entered
	 * @param  [array] $args        contains field id
	 * @param  [obj] $object        contains original value before user input
	 *
	 * @return string sanitized value. Either the inputted value if it checks out
	 *                            or the original value if not
	 *
	 * @access public
	 * @since  3.0
	 */
	public function sanitize_slug( $meta_value, $args, $object ) {

		// make sure none of the fields are blank
		if ( ! isset( $meta_value ) || trim( $meta_value ) == '' ) {
			// default to the field id as a last resort
			$meta_value = $args['id'];

			// pull the singular name from the field id
			$field_id = $args['id'];
			$results  = preg_match( '/mbdb_book_grid_(mbdb_.*)_slug/', $field_id, $matches );
			if ( $results ) {
				$taxonomy = get_taxonomy( $matches[1] );
				if ( $taxonomy ) {
					$meta_value = sanitize_title( $taxonomy->labels->singular_name );
				}
			}

		}
		$reserved_terms = MBDB()->helper_functions->wp_reserved_terms();
		if ( in_array( $meta_value, $reserved_terms ) ) {
			//show a message
			$msg = '"' . $meta_value . '" ' . __( 'is a reserved term and not allowed. This field was not saved.', 'mooberry-book-manager' );
			add_settings_error( $this->key . '-error', '', $msg, 'error' );
			settings_errors( $this->key . '-error' );

			// return the original value
			return sanitize_title( $object->value );
		}

		// entered value is OK. Sanitize it and return it
		return sanitize_title( $meta_value );
	}


	/**
	 *
	 *  If any of the tag slugs were changed , the rewrite rules
	 *  need to be flushed.
	 *  This function runs if ANY of the fields were updated.
	 *
	 *
	 * @param  [string] $old_value
	 * @param  [string] $new_value
	 *
	 * @access public
	 * @since  3.0
	 */
	public function options_updated( $old_value, $new_value ) {

		$flush = false;

		// if tax grid page changes, flush rewrite rules
		$key = 'mbdb_tax_grid_page';
		if ( ( ! array_key_exists( $key, $old_value ) ) || ( $old_value[ $key ] != $new_value[ $key ] ) ) {
			$flush = true;
		} else {
			// if any of the tax slugs change, flush the rewrite rules
			//$taxonomies = MBDB()->book_CPT->taxonomies;
			$taxonomies = get_object_taxonomies( 'mbdb_book', 'objects' );
			foreach ( $taxonomies as $name => $taxonomy ) {
				$key = 'mbdb_book_grid_' . $name . '_slug';
				if ( ( ! array_key_exists( $key, $old_value ) ) || ( $old_value[ $key ] != $new_value[ $key ] ) ) {
					/*	// multi-site compatible
						global $wp_rewrite;
						$wp_rewrite->init(); //important...
						$wp_rewrite->flush_rules(); */
					$flush = true;
					break;
				}
			}
		}
		if ( $flush ) {
			flush_rewrite_rules();
		}
	}


	// ajax function to reset meta boxes
	public function reset_meta_boxes() {
		check_ajax_referer( 'mbdb_admin_options_ajax_nonce', 'security' );
		$user_id = get_current_user_id();
		delete_user_meta( $user_id, 'meta-box-order_mbdb_book' );
		wp_die();
	}


	public function create_tax_grid_page_ajax() {
		check_ajax_referer( 'mbdb_admin_options_create_tax_grid_page_nonce', 'security' );
		echo MBDB()->helper_functions->create_tax_grid_page();
		wp_die();
	}

	public function update_featured_images( $object_id, $updated, $cmb ) {
		if ( in_array( 'use_featured_image', $updated ) ) {
			if ( $cmb->data_to_save['use_featured_image'] == 'yes' ) {
				MBDB()->helper_functions->set_all_attach_ids();
			} else {
				MBDB()->helper_functions->remove_all_attach_ids();
			}

		}

	}

	public function save_popup_card_fields() {
		check_ajax_referer( 'mbdb_admin_options_ajax_nonce', 'security' );

		if (isset($_POST['fields']) && $_POST['fields'] != '') {
			$fields = JSON_decode( str_replace( "\\", "", $_POST['fields'] ) );
			MBDB()->options->set_popup_card_fields( $fields );
		}

		wp_die();

	}
} // end class






