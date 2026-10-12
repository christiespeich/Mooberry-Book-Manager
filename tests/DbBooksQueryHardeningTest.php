<?php
use PHPUnit\Framework\TestCase;

/**
 * Hardening of the remaining string-built SQL in MBDB_DB_Books:
 *   - LIMIT / OFFSET in get_ordered_selection()
 *   - the publisher name used as a label in the SELECT list
 *   - the book filter and hardcoded table names in get_newest_books()
 */
class Mbm_Test_Helper_With_Publishers extends Mooberry_Book_Manager_Helper_Functions {
	public $publishers = array();
	public function get_publishers() {
		return $this->publishers;
	}
}

class DbBooksQueryHardeningTest extends TestCase {

	private $db;
	private $original_helper;
	private $helper;

	protected function setUp(): void {
		mbm_test_reset();
		$this->db              = new Mbm_Test_DB_Books();
		$this->original_helper = MBDB()->helper_functions;
		$this->helper          = new Mbm_Test_Helper_With_Publishers();
		MBDB()->helper_functions = $this->helper;
	}

	protected function tearDown(): void {
		MBDB()->helper_functions = $this->original_helper;
		$GLOBALS['wpdb']->prefix = 'wp_';
		$GLOBALS['wpdb']->posts  = 'wp_posts';
	}

	private function data_sql() {
		return preg_replace( '/\s+/', ' ', $this->db->last_sql() );
	}

	private function limit_clause() {
		return preg_match( '/ LIMIT (.*?)\s*$/i', $this->data_sql(), $m ) ? trim( $m[1] ) : null;
	}

	// ======================================================================
	// LIMIT / OFFSET
	// ======================================================================

	// ---- (1) Happy path ----------------------------------------------------

	public function testLimitOnly() {
		$this->db->get_ordered_selection( 'all', null, 'titleA', null, null, false, 10 );
		$this->assertSame( '10', $this->limit_clause() );
	}

	public function testLimitWithOffset() {
		$this->db->get_ordered_selection( 'all', null, 'titleA', null, null, false, 10, 20 );
		$this->assertSame( '20, 10', $this->limit_clause() );
	}

	public function testNumericStringsAreAcceptedAsTheyComeFromShortcodeAttributes() {
		$this->db->get_ordered_selection( 'all', null, 'titleA', null, null, false, '10', '20' );
		$this->assertSame( '20, 10', $this->limit_clause() );
	}

	public function testNoLimitMeansNoLimitClause() {
		$this->db->get_ordered_selection( 'all', null, 'titleA' );
		$this->assertNull( $this->limit_clause() );
	}

	// ---- (2) The vulnerability ---------------------------------------------

	public function maliciousOffsetProvider() {
		return array(
			'union'   => array( '1 UNION SELECT user_pass FROM wp_users-- ' ),
			'comment' => array( '1 -- ' ),
			'sleep'   => array( '1, (SELECT SLEEP(5)) ' ),
			'stacked' => array( '1; DROP TABLE wp_posts' ),
		);
	}

	/** @dataProvider maliciousOffsetProvider */
	public function testMaliciousOffsetNeverReachesTheSql( $payload ) {
		$this->db->get_ordered_selection( 'all', null, 'titleA', null, null, false, 10, $payload );
		foreach ( $this->db->queries as $sql ) {
			$this->assertStringNotContainsStringIgnoringCase( 'UNION', $sql );
			$this->assertStringNotContainsStringIgnoringCase( 'SLEEP', $sql );
			$this->assertStringNotContainsStringIgnoringCase( 'DROP', $sql );
			$this->assertStringNotContainsString( '--', $sql );
			$this->assertStringNotContainsString( ';', $sql );
		}
		$this->assertMatchesRegularExpression( '/^(\d+, )?10$/', $this->limit_clause() );
	}

	/** @dataProvider maliciousOffsetProvider */
	public function testMaliciousLimitNeverReachesTheSql( $payload ) {
		$this->db->get_ordered_selection( 'all', null, 'titleA', null, null, false, $payload, 5 );
		foreach ( $this->db->queries as $sql ) {
			$this->assertStringNotContainsStringIgnoringCase( 'UNION', $sql );
			$this->assertStringNotContainsStringIgnoringCase( 'SLEEP', $sql );
			$this->assertStringNotContainsStringIgnoringCase( 'DROP', $sql );
			$this->assertStringNotContainsString( '--', $sql );
			$this->assertStringNotContainsString( ';', $sql );
		}
	}

	// ---- (3) Edge cases ----------------------------------------------------

	public function testOffsetWithoutLimitIsIgnored() {
		$this->db->get_ordered_selection( 'all', null, 'titleA', null, null, false, null, 20 );
		$this->assertNull( $this->limit_clause() );
	}

	public function testZeroOffsetIsOmitted() {
		$this->db->get_ordered_selection( 'all', null, 'titleA', null, null, false, 10, 0 );
		$this->assertSame( '10', $this->limit_clause() );
	}

	public function testNegativeOffsetBecomesPositiveInteger() {
		$this->db->get_ordered_selection( 'all', null, 'titleA', null, null, false, 10, -5 );
		$this->assertMatchesRegularExpression( '/^(\d+, )?10$/', $this->limit_clause() );
	}

	public function testNonNumericOffsetIsDropped() {
		$this->db->get_ordered_selection( 'all', null, 'titleA', null, null, false, 10, 'abc' );
		$this->assertSame( '10', $this->limit_clause() );
	}

	public function testArrayOffsetDoesNotError() {
		$this->db->get_ordered_selection( 'all', null, 'titleA', null, null, false, 10, array( 1, 2 ) );
		$this->assertMatchesRegularExpression( '/^(\d+, )?10$/', $this->limit_clause() );
	}

	public function testNonNumericLimitIsDropped() {
		$this->db->get_ordered_selection( 'all', null, 'titleA', null, null, false, 'abc' );
		$this->assertNull( $this->limit_clause() );
	}

	// ---- (4) Zero results --------------------------------------------------

	public function testLimitWithNoRowsReturnsEmptyArray() {
		$this->db->data_rows = array();
		$result              = $this->db->get_ordered_selection( 'all', null, 'titleA', null, null, false, 10, 20 );
		$this->assertSame( array(), $result );
	}

	// ======================================================================
	// Publisher name in the SELECT list
	// ======================================================================

	private function publisher( $id, $name ) {
		$this->helper->publishers[ $id ] = (object) array( 'name' => $name );
	}

	private function group_by_publisher( $publisher_id ) {
		return $this->db->get_ordered_selection( 'all', null, 'titleA', null, array( 'publisher' => $publisher_id ) );
	}

	// ---- (1) Happy path ----------------------------------------------------

	public function testPublisherNameIsSelectedAsALabel() {
		$this->publisher( 15, 'Acme Books' );
		$this->group_by_publisher( 15 );
		$this->assertStringContainsString( '"Acme Books" as name2', $this->data_sql() );
		$this->assertMatchesRegularExpression( '/b\.publisher_id ?= ?"15"/', $this->data_sql() );
	}

	public function testPublisherNameWithApostropheStillWorks() {
		$this->publisher( 15, "O'Brien & Sons" );
		$this->group_by_publisher( 15 );
		// the apostrophe is escaped but the name is otherwise intact
		$this->assertStringContainsString( 'O\\\'Brien & Sons" as name2', $this->data_sql() );
	}

	public function testNoPublisherGroupSelectsBooksWithoutAPublisher() {
		$this->group_by_publisher( -1 );
		$this->assertStringContainsString( '"" AS name2', $this->data_sql() );
		$this->assertStringContainsString( 'b.publisher_id IS NULL', $this->data_sql() );
	}

	// ---- (2) The vulnerability ---------------------------------------------

	public function testPublisherNameCannotBreakOutOfTheQuotedLabel() {
		$this->publisher( 15, '" , (SELECT user_pass FROM wp_users LIMIT 1) AS x, "' );
		$this->group_by_publisher( 15 );
		// every double quote that came from the name must be escaped, so the
		// label is still exactly one string literal
		$this->assertStringContainsString( '\\"', $this->data_sql() );
		$this->assertDoesNotMatchRegularExpression( '/[^\\\\]" , \(SELECT/', $this->data_sql() );
	}

	public function testPublisherNameWithBackslashCannotEscapeTheClosingQuote() {
		$this->publisher( 15, 'Evil\\' );
		$this->group_by_publisher( 15 );
		$this->assertStringContainsString( 'Evil\\\\" as name2', $this->data_sql() );
	}

	// ---- (3) Edge cases ----------------------------------------------------

	public function testUnknownPublisherGetsBlankLabel() {
		$this->group_by_publisher( 999 );
		$this->assertStringContainsString( '"" as name2', $this->data_sql() );
	}

	public function testArrayOfPublisherIdsUsesTheFirst() {
		$this->publisher( 15, 'Acme Books' );
		$this->group_by_publisher( array( 15, 16 ) );
		$this->assertStringContainsString( '"Acme Books" as name2', $this->data_sql() );
	}

	public function testPublisherIdIsEscaped() {
		$this->group_by_publisher( '1" OR "1"="1' );
		$this->assertStringNotContainsString( 'OR "1"="1', str_replace( '\\"', '', $this->data_sql() ) );
	}

	// ---- (4) Zero results --------------------------------------------------

	public function testPublisherGroupWithNoBooksReturnsEmptyArray() {
		$this->publisher( 15, 'Acme Books' );
		$this->db->data_rows = array();
		$this->assertSame( array(), $this->group_by_publisher( 15 ) );
	}

	// ======================================================================
	// get_newest_books()
	// ======================================================================

	private function newest_sql() {
		return preg_replace( '/\s+/', ' ', $this->db->last_sql() );
	}

	// ---- (1) Happy path ----------------------------------------------------

	public function testNewestBooksWithoutFilter() {
		$this->db->get_newest_books();
		$this->assertStringNotContainsString( 'b.book_id in', $this->newest_sql() );
		$this->assertStringContainsString( 'p.post_status="publish"', $this->newest_sql() );
	}

	public function testNewestBooksWithFilter() {
		$this->db->get_newest_books( array( 4, 5, 6 ) );
		$this->assertMatchesRegularExpression( '/b\.book_id in \( ?4, ?5, ?6 ?\)/', $this->newest_sql() );
	}

	public function testNewestBooksAcceptsASingleId() {
		$this->db->get_newest_books( 7 );
		$this->assertMatchesRegularExpression( '/b\.book_id in \( ?7 ?\)/', $this->newest_sql() );
	}

	public function testNewestBooksAcceptsNumericStrings() {
		$this->db->get_newest_books( array( '4', '5' ) );
		$this->assertMatchesRegularExpression( '/b\.book_id in \( ?4, ?5 ?\)/', $this->newest_sql() );
	}

	// ---- (2) The vulnerability ---------------------------------------------

	public function testNewestBooksFilterCannotInjectSql() {
		$this->db->get_newest_books( array( '1) UNION SELECT user_pass FROM wp_users-- ', 2 ) );
		$sql = $this->newest_sql();
		$this->assertStringNotContainsStringIgnoringCase( 'UNION', $sql );
		$this->assertStringNotContainsString( '--', $sql );
		$this->assertMatchesRegularExpression( '/b\.book_id in \( ?2 ?\)/', $sql );
	}

	// ---- (3) Edge cases ----------------------------------------------------

	public function testNewestBooksWithOnlyInvalidFilterValuesMatchesNothing() {
		// a filter was supplied but none of it is usable: fail closed rather
		// than quietly returning every book (or emitting "in ()")
		$this->db->get_newest_books( array( 'abc', '1 OR 1=1' ) );
		$sql = $this->newest_sql();
		$this->assertStringNotContainsString( 'in ( )', $sql );
		$this->assertStringNotContainsString( 'in ()', $sql );
		$this->assertMatchesRegularExpression( '/b\.book_id in \( ?0 ?\)/', $sql );
	}

	public function testNewestBooksUsesTheConfiguredTablePrefix() {
		$GLOBALS['wpdb']->prefix = 'xx_';
		$GLOBALS['wpdb']->posts  = 'xx_posts';
		$this->db->get_newest_books();
		$sql = $this->newest_sql();
		$this->assertStringNotContainsString( 'wp_mbdb_books', $sql );
		$this->assertStringNotContainsString( 'wp_posts', $sql );
		$this->assertStringContainsString( 'xx_mbdb_books', $sql );
		$this->assertStringContainsString( 'xx_posts', $sql );
	}

	// ---- (4) Zero results --------------------------------------------------

	public function testNewestBooksWithNoRowsReturnsEmptyArray() {
		$this->db->data_rows = array();
		$this->assertSame( array(), $this->db->get_newest_books() );
	}
}
