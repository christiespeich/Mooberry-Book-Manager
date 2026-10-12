<?php
use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/includes/class-mbdb-books.php';
require_once dirname( __DIR__ ) . '/includes/class-mbdb-cpt.php';

/**
 * MBDB_Books::get_all() and MBDB_CPT::get_all() exist for backwards
 * compatibility with the original add-ons. Both put $orderby into ORDER BY
 * unchecked; MBDB_CPT also did that with $order.
 */
class LegacyGetAllTest extends TestCase {

	private $wpdb;

	protected function setUp(): void {
		mbm_test_reset();
		$this->wpdb          = $GLOBALS['wpdb'];
		MBDB()->books_db     = new Mbm_Test_DB_Books();
	}

	private function sql() {
		return preg_replace( '/\s+/', ' ', $this->wpdb->last_query() );
	}

	private function cpt() {
		return new class() extends MBDB_CPT {
			public function __construct() {
				$this->db_object = MBDB()->books_db;
			}
			public function post_meta_to_column( $post_meta ) {
				return false;
			}
		};
	}

	// ======================================================================
	// MBDB_Books::get_all()
	// ======================================================================

	// ---- (1) Happy path ----------------------------------------------------

	public function testBooksGetAllOrdersByTheGivenColumn() {
		$books = new MBDB_Books();
		$books->get_all( 'release_date', 'DESC' );
		$this->assertMatchesRegularExpression( '/WHERE p\.post_status = "publish" ORDER BY release_date DESC$/', $this->sql() );
	}

	public function testBooksGetAllAcceptsAliasedColumn() {
		$books = new MBDB_Books();
		$books->get_all( 'p.post_title', 'ASC' );
		$this->assertMatchesRegularExpression( '/ORDER BY p\.post_title ASC$/', $this->sql() );
	}

	public function testBooksGetAllDefaultsToBookIdAscending() {
		$books = new MBDB_Books();
		$books->get_all( null, null );
		$this->assertMatchesRegularExpression( '/ORDER BY book_id ASC$/', $this->sql() );
	}

	public function testBooksGetAllReturnsTheRows() {
		$this->wpdb->results = array( (object) array( 'book_id' => 1 ), (object) array( 'book_id' => 2 ) );
		$books               = new MBDB_Books();
		$this->assertCount( 2, $books->get_all( 'book_id', 'ASC' ) );
	}

	// ---- (2) The vulnerability ---------------------------------------------

	public function maliciousOrderbyProvider() {
		return array(
			'subselect' => array( '(SELECT SLEEP(5))' ),
			'stacked'   => array( 'book_id; DROP TABLE wp_posts' ),
			'comment'   => array( 'book_id-- ' ),
			'case'      => array( 'CASE WHEN 1=1 THEN book_id ELSE cover END' ),
		);
	}

	/** @dataProvider maliciousOrderbyProvider */
	public function testBooksGetAllIgnoresMaliciousOrderby( $payload ) {
		$books = new MBDB_Books();
		$books->get_all( $payload, 'ASC' );
		$this->assertStringNotContainsStringIgnoringCase( 'SLEEP', $this->sql() );
		$this->assertStringNotContainsStringIgnoringCase( 'DROP', $this->sql() );
		$this->assertStringNotContainsString( '--', $this->sql() );
		$this->assertStringNotContainsStringIgnoringCase( 'CASE WHEN', $this->sql() );
		$this->assertMatchesRegularExpression( '/ORDER BY book_id ASC$/', $this->sql() );
	}

	// ---- (3) Edge cases ----------------------------------------------------

	public function testBooksGetAllInvalidDirectionFallsBackToAscending() {
		$books = new MBDB_Books();
		$books->get_all( 'release_date', 'ASC; DROP TABLE wp_posts' );
		$this->assertStringNotContainsStringIgnoringCase( 'DROP', $this->sql() );
		$this->assertMatchesRegularExpression( '/ORDER BY release_date ASC$/', $this->sql() );
	}

	public function testBooksGetAllArrayOrderbyDoesNotError() {
		$books = new MBDB_Books();
		$books->get_all( array( 'release_date' ), 'DESC' );
		$this->assertMatchesRegularExpression( '/ORDER BY book_id DESC$/', $this->sql() );
	}

	// ---- (4) Zero results --------------------------------------------------

	public function testBooksGetAllWithNoRowsReturnsEmptyArray() {
		$this->wpdb->results = array();
		$books               = new MBDB_Books();
		$this->assertSame( array(), $books->get_all( 'book_id', 'ASC' ) );
	}

	// ======================================================================
	// MBDB_CPT::get_all()
	// ======================================================================

	// ---- (1) Happy path ----------------------------------------------------

	public function testCptGetAllOrdersByTheGivenColumnAndDirection() {
		$this->cpt()->get_all( 'p.post_title', 'DESC' );
		$this->assertMatchesRegularExpression( '/WHERE p\.post_status = "publish" ORDER BY p\.post_title DESC$/', $this->sql() );
	}

	public function testCptGetAllDefaultsToPostIdAscending() {
		$this->cpt()->get_all();
		$this->assertMatchesRegularExpression( '/ORDER BY p\.id ASC$/', $this->sql() );
	}

	public function testCptGetAllIncludesUnpublishedWhenAsked() {
		$this->cpt()->get_all( null, null, true );
		$this->assertStringNotContainsString( 'post_status', $this->sql() );
	}

	public function testCptGetAllReturnsTheRows() {
		$this->wpdb->results = array( (object) array( 'ID' => 1 ) );
		$this->assertCount( 1, $this->cpt()->get_all() );
	}

	// ---- (2) The vulnerability ---------------------------------------------

	/** @dataProvider maliciousOrderbyProvider */
	public function testCptGetAllIgnoresMaliciousOrderby( $payload ) {
		$this->cpt()->get_all( $payload, 'ASC' );
		$this->assertStringNotContainsStringIgnoringCase( 'SLEEP', $this->sql() );
		$this->assertStringNotContainsStringIgnoringCase( 'DROP', $this->sql() );
		$this->assertStringNotContainsString( '--', $this->sql() );
		$this->assertStringNotContainsStringIgnoringCase( 'CASE WHEN', $this->sql() );
		$this->assertMatchesRegularExpression( '/ORDER BY p\.id ASC$/', $this->sql() );
	}

	public function testCptGetAllIgnoresMaliciousOrder() {
		$this->cpt()->get_all( 'p.id', 'ASC; DROP TABLE wp_posts' );
		$this->assertStringNotContainsStringIgnoringCase( 'DROP', $this->sql() );
		$this->assertMatchesRegularExpression( '/ORDER BY p\.id ASC$/', $this->sql() );
	}

	// ---- (3) Edge cases ----------------------------------------------------

	public function testCptGetAllLowerCaseDirectionStillWorks() {
		$this->cpt()->get_all( 'p.id', 'desc' );
		$this->assertMatchesRegularExpression( '/ORDER BY p\.id DESC$/', $this->sql() );
	}

	// ---- (4) Zero results --------------------------------------------------

	public function testCptGetAllWithNoRowsReturnsEmptyArray() {
		$this->wpdb->results = array();
		$this->assertSame( array(), $this->cpt()->get_all() );
	}
}
