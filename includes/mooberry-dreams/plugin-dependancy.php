<?php

$required_version = '3.1';
$plugin_name = '';
$plugin_file = '';

//add_action( 'plugins_loaded', 'moobd_check_dependencies' );
function moobd_check_dependencies( $version, $plugin, $file ) {			
	global $required_version, $plugin_name, $plugin_file;
	$required_version = $version;
	$plugin_name = $plugin;
	$plugin_file =  $file;
	if( moobd_missing_dependencies( $required_version ) ) {
		if ( current_user_can( 'activate_plugins' ) ) {
		  add_action( 'admin_init', 'moobd_deactivate_add_on' );
		  add_action( 'admin_notices', 'moobd_admin_notice' );
		}
	}
}
	
function moobd_missing_dependencies( $version ) {
	// requires Book Manager 3.0 
	$mbdb_version = get_option('mbdb_version');
	return (!function_exists( 'mbdb_activate' ) || 
				$mbdb_version == '' ||  
				version_compare($mbdb_version, $version , '<') );	
}

function moobd_admin_notice() {
	global $required_version, $plugin_name;
		echo '<div class="updated"><p><strong>Mooberry Book Manager (version ' . $required_version . 'or above)</strong> is required to use '  . $plugin_name . '. The plug-in has been <strong>deactivated</strong>. Please install/activate Mooberry Book Manager to use ' . $plugin_name . '</p></div>';
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	}

// deactivates the plugin
function moobd_deactivate_add_on() {
	global $plugin_file;
	deactivate_plugins( plugin_basename( $plugin_file ) );
}


