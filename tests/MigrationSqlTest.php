<?php
use PHPUnit\Framework\TestCase;

// Stand-ins for what the background-process library and the admin-notice
// helpers do; only the SQL the migration builds is under test.
if ( ! class_exists( 'WP_Background_Process' ) ) {
	class WP_Background_Process {
		public function __construct() {}
		protected function complete() {}
	}
}
if ( ! function_exists( 'mbdb_remove_admin_notice' ) ) {
	function mbdb_remove_admin_notice() {}
}
class Mbm_Test_Helper_Quiet extends Mooberry_Book_Manager_Helper_Functions {
	public function set_admin_notice( $message, $type, $key, $dismissable = false, $users = array() ) {}
}

require_once dirname( __DIR__ ) . '/includes/admin/class-publisher-update-fix-process.php';
require_once dirname( __DIR__ ) . '/includes/updates.php';

/**
 * One-off migration code that built SQL by concatenation:
 *   - MBDB_Publisher_Update_Fix_Process::set_publisher() / complete()
 *   - the "imported book grid already exists" lookup in the 3.4 upgrade
 */
class MigrationSqlTest extends TestCase {

	private $wpdb;
	private $original_helper;

	protected function setUp(): void {
		mbm_test_reset();
		$this->wpdb              = $GLOBALS['wpdb'];
		$this->original_helper   = MBDB()->helper_functions;
		MBDB()->helper_functions = new Mbm_Test_Helper_Quiet();
	}

	protected function tearDown(): void {
		MBDB()->helper_functions = $this->original_helper;
	}

	private function sql_all() {
		return preg_replace( '/\s+/', ' ', implode( ' | ', $this->wpdb->queries ) );
	}

	private function call( $method, ...$args ) {
		$process = new MBDB_Publisher_Update_Fix_Process();
		$m       = new ReflectionMethod( $process, $method );
		$m->setAccessible( true );
		return $m->invoke( $process, ...$args );
	}

	// ======================================================================
	// Publisher fix: set_publisher()
	// ======================================================================

	// ---- (1) Happy path ----------------------------------------------------

	public function testSetPublisherUpdatesTheBook() {
		// no entry in new_publishers, so a publisher post is created
		$GLOBALS['mbm_test']['next_post_id'] = 77;
		$this->call( 'set_publisher', 'Acme', (object) array( 'id' => 12 ) );
		$this->assertMatchesRegularExpression(
			"/update wp_mbdb_books set publisher_id ?= ?['\"]77['\"] where book_id ?= ?12\$/i",
			$this->sql_all()
		);
	}

	// ---- (2) The vulnerability ---------------------------------------------

	public function testBookIdCannotCarryInjectedSql() {
		$GLOBALS['mbm_test']['next_post_id'] = 77;
		$this->call( 'set_publisher', 'Acme', (object) array( 'id' => '12 OR 1=1' ) );
		$this->assertStringNotContainsString( 'OR 1=1', $this->sql_all() );
	}

	public function testNonNumericPublisherIdNeverReachesSql() {
		// wp_insert_post() can return a WP_Error object or 0 on failure
		$GLOBALS['mbm_test']['next_post_id'] = '77", book_id = book_id -- ';
		$this->call( 'set_publisher', 'Acme', (object) array( 'id' => 12 ) );
		$this->assertSame( array(), $this->wpdb->queries );
	}

	// ---- (3) Edge cases ----------------------------------------------------

	public function testFailedPublisherInsertDoesNotRunAnUpdate() {
		$GLOBALS['mbm_test']['next_post_id'] = 0;
		$this->call( 'set_publisher', 'Acme', (object) array( 'id' => 12 ) );
		$this->assertSame( array(), $this->wpdb->queries );
	}

	// ======================================================================
	// Publisher fix: complete()  (delete duplicate publishers)
	// ======================================================================

	// ---- (1) Happy path ----------------------------------------------------

	public function testCompleteDeletesAllButTheOldestPublisherOfEachName() {
		$this->wpdb->col = array( '3', '5', '9' );
		$this->call( 'complete' );
		$this->assertMatchesRegularExpression( '/ID not in \( ?3, ?5, ?9 ?\)/i', $this->sql_all() );
	}

	// ---- (2) The vulnerability ---------------------------------------------

	public function testCompleteSanitisesTheIdsItGotBack() {
		$this->wpdb->col = array( '3', '5) OR (1=1', '9' );
		$this->call( 'complete' );
		$this->assertStringNotContainsString( 'OR (1=1', $this->sql_all() );
		$this->assertMatchesRegularExpression( '/ID not in \( ?3, ?9 ?\)/i', $this->sql_all() );
	}

	// ---- (3) Edge cases / (4) Zero results ---------------------------------

	public function testCompleteWithNoPublishersDoesNotRunADeleteWithAnEmptyList() {
		$this->wpdb->col = array();
		$this->call( 'complete' );
		$this->assertStringNotContainsStringIgnoringCase( 'DELETE', $this->sql_all() );
		$this->assertStringNotContainsString( 'in ( )', $this->sql_all() );
	}

	public function testCompleteWithOnlyUnusableIdsDoesNotDeleteAnything() {
		$this->wpdb->col = array( 'abc', '0' );
		$this->call( 'complete' );
		$this->assertStringNotContainsStringIgnoringCase( 'DELETE', $this->sql_all() );
	}

	// ======================================================================
	// "Imported Book Grid" lookup (3.4 upgrade)
	// ======================================================================

	public function testImportedGridLookupFindsExistingGrids() {
		$this->wpdb->results = array( (object) array( 'ID' => 4 ) );
		$found               = mbdb_get_imported_book_grids( 'imported-book-grid-home' );
		$this->assertCount( 1, $found );
		$this->assertMatchesRegularExpression(
			"/post_type ?= ?'mbdb_book_grid' and post_status ?= ?'publish' and post_name ?= ?'imported-book-grid-home'\$/i",
			preg_replace( '/\s+/', ' ', $this->wpdb->last_query() )
		);
	}

	public function testImportedGridLookupEscapesTheTitle() {
		mbdb_get_imported_book_grids( "x' OR '1'='1" );
		$this->assertStringNotContainsString( "x' OR '1'='1", $this->wpdb->last_query() );
	}

	public function testImportedGridLookupWithNoMatchReturnsEmptyArray() {
		$this->wpdb->results = array();
		$this->assertSame( array(), mbdb_get_imported_book_grids( 'nothing-here' ) );
	}
}
