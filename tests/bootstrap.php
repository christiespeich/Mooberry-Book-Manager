<?php
/**
 * Standalone bootstrap: no WordPress or database required.
 *
 * It stubs only the WordPress functions the code under test touches and loads
 * the real plugin classes, so the tests exercise the real SQL-building code.
 * Run from the plugin root with:  phpunit
 */

define( 'ABSPATH', __DIR__ . '/' );
define( 'MBDB_PLUGIN_VERSION', 'test' );

$plugin_dir = dirname( __DIR__ ) . '/';

/**
 * Mutable state the stubs read from / record into. Reset in each test's setUp
 * via mbm_test_reset().
 */
function mbm_test_reset() {
	$GLOBALS['mbm_test'] = array(
		'nonce_valid'  => true,
		'can_edit'     => true,
		'cap_checks'   => array(),
		'meta_updates' => array(),
	);
	$_POST = array();
}

class Mbm_Test_Die_Exception extends Exception {}

// ---- WordPress function stubs ---------------------------------------------

function apply_filters( $tag, $value ) {
	return $value;
}
function add_filter() {
	return true;
}
function add_action() {
	return true;
}
function __( $text ) {
	return $text;
}
function esc_sql( $value ) {
	return is_array( $value ) ? array_map( 'esc_sql', $value ) : addslashes( $value );
}
function absint( $value ) {
	return abs( (int) $value );
}
function wp_timezone_string() {
	return 'UTC';
}
function wp_unslash( $value ) {
	return is_array( $value ) ? array_map( 'wp_unslash', $value ) : stripslashes( $value );
}
function wp_verify_nonce( $nonce, $action ) {
	return $GLOBALS['mbm_test']['nonce_valid'];
}
function current_user_can( $cap, ...$args ) {
	$GLOBALS['mbm_test']['cap_checks'][] = array_merge( array( $cap ), $args );
	return $GLOBALS['mbm_test']['can_edit'];
}
function update_post_meta( $post_id, $key, $value ) {
	$GLOBALS['mbm_test']['meta_updates'][] = array( $post_id, $key, $value );
	return true;
}
function wp_die() {
	throw new Mbm_Test_Die_Exception( 'wp_die' );
}

// ---- Plugin-level stubs ----------------------------------------------------

// $wpdb: only the table names are used when building SQL.
$wpdb                      = new stdClass();
$wpdb->prefix              = 'wp_';
$wpdb->posts               = 'wp_posts';
$wpdb->terms               = 'wp_terms';
$wpdb->term_taxonomy       = 'wp_term_taxonomy';
$wpdb->term_relationships  = 'wp_term_relationships';

require_once $plugin_dir . 'includes/class-mbm-helper-functions.php';

class Mbm_Test_Plugin_Stub {
	public $helper_functions;
	public $book_grid_CPT;
	public $book_CPT;
	public function get_widget_options() {
		return array();
	}
}
class Mbm_Test_Grid_CPT_Stub {
	public function selection_options() {
		return array(
			'all'         => 'All',
			'published'   => 'Published',
			'unpublished' => 'Unpublished',
			'custom'      => 'Custom',
			'publisher'   => 'Publisher',
		);
	}
	public function group_by_options() {
		return array( 'none' => 'None', 'series' => 'Series' );
	}
}
class Mbm_Test_Book_CPT_Stub {
	public function get_taxonomies() {
		return array();
	}
}
function MBDB() {
	static $instance = null;
	if ( $instance === null ) {
		$instance                   = new Mbm_Test_Plugin_Stub();
		$instance->helper_functions = new Mooberry_Book_Manager_Helper_Functions();
		$instance->book_grid_CPT    = new Mbm_Test_Grid_CPT_Stub();
		$instance->book_CPT         = new Mbm_Test_Book_CPT_Stub();
	}
	return $instance;
}

// ---- Real plugin classes under test ---------------------------------------

require_once $plugin_dir . 'includes/interface-mbm-data-storage-behavior.php';
require_once $plugin_dir . 'includes/mooberry-dreams/moobd-database.php';
require_once $plugin_dir . 'includes/class-mbm-db-cpt.php';
require_once $plugin_dir . 'includes/class-mbm-db-books.php';
require_once $plugin_dir . 'includes/class-mbm-cpt.php';
require_once $plugin_dir . 'includes/class-mbm-book-grid-cpt.php';

/**
 * Records the SQL the real class builds instead of sending it to a database.
 */
class Mbm_Test_DB_Books extends MBDB_DB_Books {
	public $queries = array();
	/** Rows returned for the COUNT query and for the data query. */
	public $count_rows = null;
	public $data_rows  = array();

	protected function run_sql( $sql, $cache_results = true ) {
		$this->queries[] = $sql;
		if ( strpos( $sql, 'COUNT(DISTINCT' ) !== false ) {
			return $this->count_rows !== null ? $this->count_rows : array( (object) array( 'count' => count( $this->data_rows ) ) );
		}
		return $this->data_rows;
	}
	public function last_sql() {
		return end( $this->queries );
	}
}
