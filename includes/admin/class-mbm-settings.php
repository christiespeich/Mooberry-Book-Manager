<?php
/**
 * Admin Settings Page
 * This class is taken directly from the CMB2 example
 * and customized for MBDB
 *
 * @since 3.0
 */
abstract class Mooberry_Book_Manager_Settings {
	
	
	protected $page;
	
	/**
	 *  Keeps track of what the current screen tab is
	 *  @var string
	 *  @since 3.0
	 */
	protected $tab;
	protected $tabs;
	
	/**
 	 * Option key, and option page slug
 	 * @var string
	 * @since 3.0
 	 */
	protected $key;

	/**
 	 * Options page metabox id
 	 * @var string
	 * @since 3.0
 	 */
	protected $metabox_id;
	protected $show_metabox;

	/**
	 * Options Page title
	 * @var string
	 * @since 3.0
	 */
	protected $title;

	/**
	 * Options Page hook
	 * @var string
	 * @since 3.0
	 */
	protected $options_page;
	
	/**
	 * Subpages
	 * @var array
	 * @since 3.0
	 */
	protected $pages;
	
	protected abstract function set_pages();
	
	/**
	 * Constructor
	 *
	 * @since 3.0
	 */
	public function __construct() {
		$this->show_metabox = true;
		$this->title = __( 'Mooberry Book Manager Settings', 'mooberry-book-manager' );
		$this->tab = '';
		$this->page = '';
		$this->options_page = '';
		$this->key = '';
		$this->metabox_id = '';
		$this->pages = array();
		$this->tabs = array();
		
		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'admin_menu', array( $this, 'add_options_page' ) );
		add_action( 'cmb2_admin_init', array( $this, 'add_options_page_metabox' ) );
		
		
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
		
		$parent_slug = 'mbdb_options';
		$capability = 'manage_mbm';
		$callback = array( $this, 'admin_page_display' );
		
		$this->set_pages();
		$this->set_tabs();
									
		foreach($this->pages as $subpage_slug => $subpage) {												
			$sub_page_hook = add_submenu_page( $parent_slug, $subpage['page_title'], $subpage['menu_title'], $capability, $subpage_slug, $callback );
			
			add_action( "admin_print_styles-{$sub_page_hook}", array( 'CMB2_hookup', 'enqueue_cmb_css' ) );
		}
		
		// Include CMB CSS in the head to avoid FOUC
		add_action( "admin_print_styles-{$this->options_page}", array( 'CMB2_hookup', 'enqueue_cmb_css' ) );
	}
	
	protected function set_tabs() {
		$this->tabs = array();
	}

	protected function display_tabs() {
		do_action('mbdb_settings_before_tabs', $this->page, $this->tab, $this->tabs );
		echo '<div id="icon-options-general" class="icon32"></div>';
		echo '<h2 class="nav-tab-wrapper">';
		foreach( $this->tabs as $page => $tabs ) {
			if ( $page == $this->page ) {
				foreach( $tabs as $tab => $name ){
					$class = ( $tab == $this->tab ) ? ' nav-tab-active' : '';
					$page = $this->page;
					echo "<a class='nav-tab $class' href='?page=$page&tab=$tab'>$name</a>";
				}
			}
		}
		echo '</h2>';
		do_action('mbdb_settings_after_tabs', $this->page, $this->tab, $this->tabs );
	}

	/**
	 * Admin page markup.
	 * @since  3.0
	 */
	public function admin_page_display() {
		// title
	
		echo '<h2>' . esc_html( $this->title ) . '</h2>';
	
		// instructions
		do_action('mbdb_settings_before_instructions', $this->page, $this->tab);

		do_action('mbdb_settings_after_instructions');
	
		// tabs
		do_action('mbdb_settings_pre_tab_display', $this->page, $this->tab);
		if ( array_key_exists( $this->page, $this->tabs ) && count($this->tabs[ $this->page ] ) > 0  ) {
			$this->display_tabs();
		}
		
		do_action('mbdb_settings_post_tab_display', $this->page, $this->tab);
		
		
		// About Mooberry boxes

		echo '<div id="mbdb_about_mooberry"><div class="mbdb_box">			<h3>' . __('Need help with Mooberry Book Manager?', 'mooberry-book-manager') . '</h3>';
		include('views/admin-about-mooberry.php');
		echo '</div>';
		include('views/admin-about-mooberry-story.php');
		echo '</div>';
		
			
		echo '<div class="wrap cmb2-options-page mbdb_options ' . $this->key . '">';
		
		do_action('mbdb_settings_before_metabox', $this->page, $this->tab, $this->metabox_id);
		
		//echo '<div id="icon-options-general" class="icon32"></div>';
		if ( $this->show_metabox  ) {
			cmb2_metabox_form( $this->metabox_id, $this->key ); 
		} else {
			
		}
				echo '</div>';
	
		do_action('mbdb_settings_after_metabox', $this->page, $this->tab, $this->metabox_id);
		

	
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
			$this->page = $_GET['page'];
		} else {
			if ( count($this->pages) > 0 ){ 
				$pages = array_keys($this->pages);
				$this->page = $pages[0];
			} else {
				$this->page = '';
			}
		}
		
		if ( isset ( $_GET['tab'] ) ) {
			$this->tab = $_GET['tab'];
		} else {
			if ( array_key_exists( $this->page, $this->tabs ) && count( $this->tabs[ $this->page ] ) > 0 ) {
				$tabs = array_keys( $this->tabs[ $this->page ] );
				$this->tab = $tabs[0];
			} else {
				$this->tab = '';
			}
		}
		
		$mbdb_settings_metabox = apply_filters('mbdb_settings_metabox', $mbdb_settings_metabox, $this->page, $this->tab);
	}
	
	
	public final function uniqueID_generator( $value = '' ) {
		return apply_filters('mbdb_settings_uniqid', MBDB()->helper_functions->uniqueID_generator( $value ) );
		
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
		if ( in_array( $field, array( 'key', 'metabox_id', 'title', 'options_page', 'tab', 'page' ), true ) ) {
			return $this->{$field};
		}

		throw new Exception( 'Invalid property: ' . $field );
	}

	
} // end class






