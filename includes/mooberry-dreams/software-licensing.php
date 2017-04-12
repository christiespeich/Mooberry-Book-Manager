<?php 

 if( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
	// load our custom updater if it doesn't already exist 
	include( dirname( __FILE__ ) . '/EDD_SL_Plugin_Updater.php' );
}

class MBDB_License {
	private $file;
	private $license;
	private $item_name;
	private $item_shortname;
	private $version;
	private $author;
	private $api_url = 'http://bookmanager.mooberrydreams.com/';
	private $settings;
	
	

	/**
	 * Class constructor
	 *
	 * @param string  $_file
	 * @param string  $_item_name
	 * @param string  $_version
	 * @param string  $_author
	 * @param string  $_optname
	 * @param string  $_api_url
	 */
	function __construct( $_file, $_item, $_version,  $_api_url = null ) {

		$this->file           = $_file;

		$this->item_name 	  = $_item;
		
		$this->item_shortname = 'mbdb_' . preg_replace( '/[^a-zA-Z0-9_\s]/', '', str_replace( ' ', '_', strtolower( $this->item_name ) ) );
		$this->version        = $_version;
		$license_keys = get_option( 'mbdb_license', array() );
		if (array_key_exists($this->item_shortname . '_license_key', $license_keys)) {
			$this->license        = trim( $license_keys[ $this->item_shortname . '_license_key'] );
		} else {
			$this->license = '';
		}
		$this->author         = 'Mooberry Dreams';
		$this->api_url        = is_null( $_api_url ) ? $this->api_url : $_api_url;

	//	$this->settings = MBDB()->license_settings;
		
		
		// Setup hooks
		$this->hooks();

	}
	
	/**
	 * Setup hooks
	 *
	 * @access  private
	 * @return  void
	 */
	private function hooks() {

		// Register settings
		add_action( 'admin_menu', array($this, 'add_options_page' ) );
		add_action('admin_init', array($this, 'define_settings'));


		
		// Activate license key on settings save
		add_action( 'admin_init', array( $this, 'activate_license' ) );

		// Deactivate license key
		add_action( 'admin_init', array( $this, 'deactivate_license' ) );

		// Check that license is valid once per week
		add_action( 'edd_weekly_scheduled_events', array( $this, 'weekly_license_check' ) );

		// For testing license notices, uncomment this line to force checks on every page load
		//add_action( 'admin_init', array( $this, 'weekly_license_check' ) );

		// Updater
		add_action( 'admin_init', array( $this, 'auto_updater' ), 0 );

		// Display notices to admins
		add_action( 'admin_notices', array( $this, 'notices' ) );

		add_action( 'in_plugin_update_message-' . plugin_basename( $this->file ), array( $this, 'plugin_row_license_missing' ), 10, 2 );

	}
	

/**
	 * Add menu options page
	 * @since 0.1.0
	 */
	public function add_options_page() {
		global $submenu;
		if (!array_key_exists('mbdb_options', $submenu)) {
			return;
		}
		if (!$this->in_array_r('mbdb_license_keys', $submenu['mbdb_options'])) {
			$sub_page_hook = add_submenu_page( 'mbdb_options', 
							__( 'Mooberry Book Manager Extension License Keys', 'mooberry-book-manager' ), 
							__('License Keys', 'mooberry-book-manager'), 
							'manage_mbm', 
							'mbdb_license_keys', 
							array( $this, 'admin_page_display') );
			
			add_action( "admin_print_styles-{$sub_page_hook}", array( 'CMB2_hookup', 'enqueue_cmb_css' ) );
		}
	}
	
	function define_settings(){
		register_setting( 'mbdb_license_keys', 'mbdb_license'  );
		
		add_settings_section(
			'mbdb_license_keys_section',
			'',
			array( $this, 'license_key_section'),
			'mbdb_license_keys'
		);
		
		add_settings_field($this->item_shortname . '_license_key', 
						$this->item_name, 
						array($this, 'display_field'), 
						'mbdb_license_keys' ,
						'mbdb_license_keys_section' 
						);
	}

	function license_key_section() {
	}
	
	public function admin_page_display() {
		?>
		<div class="wrap cmb2-options-page mbdb_license">
			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
			
		
		<!--	  <form action="options.php" method="post">  -->
		<form method="post">
			 
				<?php settings_fields('mbdb_license_keys'); ?>
				<?php do_settings_sections('mbdb_license_keys'); ?>
				 
				
			</form>
		</div>
		<?php
	}
	
	// v3.3.4 always show activate button unless deactivate button should be there
	// v3.3.4 add default/catch all for other errors
	function display_field() {
		$options = get_option('mbdb_license');
		$id = $this->item_shortname . '_license_key';
		echo "";
		if ( !is_array($options) )  {
			$options = array($options);
		}
		if ( !array_key_exists( $id, $options ) ) {
			$options[$id] = '';
		}
		$details = get_option( $this->item_shortname . '_license_active' );
	
		$show_deactivate = false;
		$show_activate = true;
		$color = 'red';
		if ( !is_object( $details ) ) {
			
			$status = __('inactive', 'mooberry-book-manager');
		} else {
			if (isset($details->error) ) {
			
				switch ($details->error) {
					case 'no_activations_left':
						$status = __('No activations left', 'mooberry-book-manager');
						break;
					case 'item_name_mismatch':
						$status = __('Wrong Key', 'mooberry-book-manager');
						break;
					case 'expired':
						$status = __('expired', 'mooberry-book-manager');
						break;
					default:
						$status = $details->error;
						break;
					}
			} else {		
				$status = $details->license;
				switch ($details->license) {
					case 'site_inactive':
					case 'inactive':
						$status = __('inactive', 'mooberry-book-manager');
						if ( $details->activations_left == 0 && $details->activations_left != 'unlimited' ) {
							$show_activate = false;		
						}
						break;				
					case 'valid':
						$show_deactivate = true;
						$show_activate = false;
						$color = "green";
						$status = __('valid', 'mooberry-book-manager');
						break;
					
				}
					
			}
		}
					
		
?>
			<input id="<?php echo $id; ?>" name="mbdb_license[<?php echo $id; ?>]" size="40" type="text" value="<?php echo $options[$id]; ?>" />
			
			<label id="<?php echo $this->item_shortname; ?>_status" style="text-transform:uppercase;font-weight:bold;color:<?php echo $color; ?>"><?php echo $options[$id] == '' ? '' : $status; ?></label> 
			
			<input type="submit" class="button-secondary"  name="btn_deactivate_<?php echo  $this->item_shortname; ?>" <?php echo ($show_deactivate ? '' : 'style="display:none;"'); ?>  value="<?php  _e('Deactivate', 'mooberry-book-manager'); ?>" />
	
			<input type="submit" class="button-secondary"  name="btn_activate_<?php echo $this->item_shortname; ?>" <?php echo ($show_activate ? '' : 'style="display:none;"');  ?>  value="<?php  _e('Activate', 'mooberry-book-manager');  ?>"/> 
			
	
<?php
	
		
	}
	

	function auto_updater( ) {
		
		$args = array(
			'version'   => $this->version,
			'license'   => $this->license,
			'author'    => $this->author,
			'item_name'	=> $this->item_name,
			'url'           => home_url()
		);

		// Setup the updater
		$edd_updater = new EDD_SL_Plugin_Updater(
			$this->api_url,
			$this->file,
			$args
		);
		
	}
	
	/**
	 * Activate the license key
	 *
	 * @access  public
	 * @return  void
	 */
	public function activate_license() {
		

		// change this to match settings
		if ( ! isset( $_POST['option_page'] ) || $_POST['option_page'] != 'mbdb_license_keys' )  {
			return;
		}


	
		// change 
		if ( ! current_user_can( 'manage_mbm' ) ) {
			return;
		}
		
	

		// are we activating this key?
		if ( !isset( $_POST['btn_activate_' . $this->item_shortname] ) ) {
			return;
		}
		
	

		// if the license key is blank or it's different than the last one,
		// clean out the cached results
		if ( empty( $_POST['mbdb_license'][ $this->item_shortname . '_license_key'] ) ) {
			delete_option( $this->item_shortname . '_license_active' );
			
			return;
		}
		
		$mbdb_license = get_option('mbdb_license');
		if ( $mbdb_license[ $this->item_shortname . '_license_key' ] != $_POST['mbdb_license'][ $this->item_shortname . '_license_key'] ) {
			delete_option( $this->item_shortname . '_license_active' );
			
		}

		foreach ( $_POST as $key => $value ) {
			
			if( false !== strpos( $key, 'btn_deactivate' ) ) {
				// Don't activate a key when deactivating a different key
				return;
			}
		}
		
		

		$details = get_option( $this->item_shortname . '_license_active' );

		if ( is_object( $details ) && 'valid' === $details->license ) {
			return;
		}

		$license = sanitize_text_field( $_POST['mbdb_license'][ $this->item_shortname . '_license_key'] );

		$mbdb_license[ $this->item_shortname . '_license_key' ] = $license;
		update_option('mbdb_license', $mbdb_license);

		
		if( empty( $license ) ) {
			return;
		}
		
		
		// Data to send to the API
		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => $license,
			'item_name'  => urlencode( $this->item_name ),
			'url'        => home_url()
		);
		

		// Call the API
		$response = wp_remote_post(
			$this->api_url,
			array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params
			)
		);
		
		// Make sure there are no errors
		if ( is_wp_error( $response ) ) {
		
			return;
		}

		// Tell WordPress to look for updates
		set_site_transient( 'update_plugins', null );

		// Decode license data
		
		
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );
		

		update_option( $this->item_shortname . '_license_active', $license_data );
		

	}
	
	/**
	 * Deactivate the license key
	 *
	 * @access  public
	 * @return  void
	 */
	public function deactivate_license() {
		if ( ! isset( $_POST['option_page'] ) || $_POST['option_page'] != 'mbdb_license_keys'  )
			return;

		if ( ! isset( $_POST['mbdb_license'][ $this->item_shortname . '_license_key'] ) )
			return;

		

		if( ! current_user_can( 'manage_mbm' ) ) {
			return;
		}

		// Run on deactivate button press
		if ( isset( $_POST[ 'btn_deactivate_' . $this->item_shortname ] ) ) {

			// Data to send to the API
			$api_params = array(
				'edd_action' => 'deactivate_license',
				'license'    => $this->license,
				'item_name'  => urlencode( $this->item_name ),
				'url'        => home_url()
			);

			// Call the API
			$response = wp_remote_post(
				$this->api_url,
				array(
					'timeout'   => 15,
					'sslverify' => false,
					'body'      => $api_params
				)
			);
			// Make sure there are no errors
			if ( is_wp_error( $response ) ) {
				return;
			}
			
			
			// Decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			delete_option( $this->item_shortname . '_license_active' );
			
			$mbdb_license = get_option('mbdb_license');
			
			$mbdb_license[ $this->item_shortname . '_license_key' ] = '';
			update_option( 'mbdb_license', $mbdb_license );

		}
	}

	
	/**
	 * Check if license key is valid once per week
	 *
	 * @access  public
	 * @since   2.5
	 * @return  void
	 */
	public function weekly_license_check() {

		if( !empty( $_POST['option_page'] ) && $_POST['option_page'] == 'mbdb_license_key'  ) {
			return; // Don't fire when saving settings
		}

		if( empty( $this->license ) ) {
			return;
		}

		// data to send in our API request
		$api_params = array(
			'edd_action'=> 'check_license',
			'license' 	=> $this->license,
			'item_name' => urlencode( $this->item_name ),
			'url'       => home_url()
		);

		// Call the API
		$response = wp_remote_post(
			$this->api_url,
			array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params
			)
		);

		// make sure the response came back okay
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$license_data = json_decode( ltrim( rtrim(wp_remote_retrieve_body( $response ), ')'), '(') );
		
		
		update_option( $this->item_shortname . '_license_active', $license_data );

	}
	
	/**
	 * Admin notices for errors
	 *
	 * @access  public
	 * @return  void
	 */
	public function notices() {

		static $showed_invalid_message;

		if( empty( $this->license ) ) {
			return;
		}

		if( ! current_user_can( 'manage_mbm' ) ) {
			return;
		}

		$messages = array();

		$license = get_option( $this->item_shortname . '_license_active' );

		if( is_object( $license ) && 'valid' !== $license->license && empty( $showed_invalid_message ) ) {

			if( array_key_exists('page', $_GET) && substr($_GET['page'], 0, 4) == 'mbdb') {

				$messages[] = sprintf(
					__( 'You have invalid or expired license keys for Mooberry Book Manager. Please go to the <a href="%s" title="Go to Licenses page">Licenses page</a> to correct this issue.', 'mooberry-book-manager' ),
					admin_url( 'admin.php?page=mbdb_license_keys' )
				);

				$showed_invalid_message = true;

			}

		}

		if( ! empty( $messages ) ) {

			foreach( $messages as $message ) {

				echo '<div class="error">';
					echo '<p>' . $message . '</p>';
				echo '</div>';

			}

		}

	}
	
/**
	 * Displays message inline on plugin row that the license key is missing
	 *
	 * @access  public
	 * @since   2.5
	 * @return  void
	 */
	public function plugin_row_license_missing( $plugin_data, $version_info ) {

		static $showed_imissing_key_message;

		$license = get_option( $this->item_shortname . '_license_active' );

		if( ( ! is_object( $license ) || 'valid' !== $license->license ) && empty( $showed_imissing_key_message[ $this->item_shortname ] ) ) {

			echo '&nbsp;<strong><a href="' . esc_url( admin_url( 'admin.php?page=mbdb_license_keys' ) ) . '">' . __( 'Enter valid license key for automatic updates.', 'mooberry-book-manager' ) . '</a></strong>';
			$showed_imissing_key_message[ $this->item_shortname ] = true;
		}

	}
	
// multi-dimensional array searching
private function in_array_r($needle, $haystack, $strict = false) {
	 if ( !is_array($haystack) ) {
		 return false;
	 }
		foreach ($haystack as $item) {
			if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && $this->in_array_r($needle, $item, $strict))) {
				return true;
			}
		}

		return false;
	}

}