<?php
use PHPUnit\Framework\TestCase;

/**
 * The shared DB base classes (MBDB_DB_CPT / MOOBD_Database):
 *   get( $id )                - used to interpolate $id raw
 *   get_all()/get_multiple()  - used prepare() with %s for ORDER BY and for an
 *                               imploded id list, producing quoted identifiers
 *   get_by()                  - esc_sql() on a column name
 */
class DbBaseClassQueriesTest extends TestCase {

	private $db;
	private $wpdb;

	protected function setUp(): void {
		mbm_test_reset();
		$this->wpdb = $GLOBALS['wpdb'];
		$this->db   = new Mbm_Test_DB_Books();
	}

	private function sql() {
		return preg_replace( '/\s+/', ' ', $this->db->last_sql() );
	}

	// ======================================================================
	// get( $id )   (MBDB_DB_CPT)
	// MBDB_DB_Books::get() wraps it and adds editions, reviews etc., so call
	// the CPT base implementation directly.
	// ======================================================================

	private function cpt_get( $id ) {
		$db = new class() extends Mbm_Test_DB_Books {
			public function call_cpt_get( $id ) {
				return MBDB_DB_CPT::get( $id );
			}
		};
		return $db->call_cpt_get( $id );
	}

	// ---- (1) Happy path ----------------------------------------------------

	public function testGetByIntegerId() {
		$this->wpdb->row = (object) array( 'book_id' => 42 );
		$row             = $this->cpt_get( 42 );
		$this->assertSame( 42, $row->book_id );
		$this->assertMatchesRegularExpression( '/WHERE p\.id = 42\s*$/', preg_replace( '/\s+/', ' ', $this->wpdb->last_query() ) );
	}

	public function testGetByNumericStringId() {
		$this->wpdb->row = (object) array( 'book_id' => 42 );
		$this->assertNotNull( $this->cpt_get( '42' ) );
		$this->assertMatchesRegularExpression( '/WHERE p\.id = 42\s*$/', preg_replace( '/\s+/', ' ', $this->wpdb->last_query() ) );
	}

	// ---- (2) The vulnerability ---------------------------------------------

	public function maliciousIdProvider() {
		return array(
			'or true'  => array( '1 OR 1=1' ),
			'union'    => array( '0 UNION SELECT user_pass FROM wp_users' ),
			'comment'  => array( '1-- ' ),
			'sleep'    => array( '1 AND SLEEP(5)' ),
			'stacked'  => array( '1; DROP TABLE wp_posts' ),
			'quote'    => array( "1' OR '1'='1" ),
		);
	}

	/** @dataProvider maliciousIdProvider */
	public function testGetRejectsNonNumericIdsWithoutQueryingTheDatabase( $payload ) {
		$this->wpdb->row = (object) array( 'book_id' => 1 );
		$this->assertNull( $this->cpt_get( $payload ) );
		$this->assertSame( array(), $this->wpdb->queries, 'a bad id must not reach the database at all' );
	}

	// ---- (3) Edge cases ----------------------------------------------------

	public function badIdProvider() {
		return array(
			'null'     => array( null ),
			'empty'    => array( '' ),
			'zero'     => array( 0 ),
			'negative' => array( -3 ),
			'float'    => array( 1.5 ),
			'array'    => array( array( 1, 2 ) ),
			'text'     => array( 'abc' ),
			'bool'     => array( true ),
		);
	}

	/** @dataProvider badIdProvider */
	public function testGetWithUnusableIdReturnsNullAndDoesNotError( $id ) {
		$this->assertNull( $this->cpt_get( $id ) );
		$this->assertSame( array(), $this->wpdb->queries );
	}

	// ---- (4) Zero results --------------------------------------------------

	public function testGetWithNoMatchingRowReturnsNull() {
		$this->wpdb->row = null;
		$this->assertNull( $this->cpt_get( 99999 ) );
		$this->assertCount( 1, $this->wpdb->queries );
	}

	// ======================================================================
	// get_all()   (MOOBD_Database)
	// ======================================================================

	// ---- (1) Happy path ----------------------------------------------------

	public function testGetAllOrdersByAValidColumn() {
		$this->db->get_all( 'release_date', 'DESC' );
		$this->assertMatchesRegularExpression( '/ORDER BY `?release_date`? DESC;?$/', $this->sql() );
	}

	public function testGetAllDefaultsToPrimaryKeyAscending() {
		$this->db->get_all();
		$this->assertMatchesRegularExpression( '/ORDER BY `?book_id`? ASC;?$/', $this->sql() );
	}

	public function testGetAllReturnsTheRows() {
		$this->db->data_rows = array( (object) array( 'book_id' => 1 ), (object) array( 'book_id' => 2 ) );
		$this->assertCount( 2, $this->db->get_all( 'book_id', 'ASC' ) );
	}

	// The old code produced ORDER BY 'book_id' 'ASC', which MySQL rejects.
	public function testGetAllNeverQuotesTheOrderByTerms() {
		$this->db->get_all( 'release_date', 'DESC' );
		$this->assertDoesNotMatchRegularExpression( "/ORDER BY\s+'/", $this->sql() );
		$this->assertDoesNotMatchRegularExpression( "/DESC'|ASC'/", $this->sql() );
	}

	// ---- (2) The vulnerability ---------------------------------------------

	public function maliciousOrderbyProvider() {
		return array(
			'subselect' => array( '(SELECT SLEEP(5))' ),
			'union'     => array( 'book_id; DROP TABLE wp_posts' ),
			'comment'   => array( 'book_id-- ' ),
			'case'      => array( 'CASE WHEN 1=1 THEN book_id ELSE cover END' ),
		);
	}

	/** @dataProvider maliciousOrderbyProvider */
	public function testGetAllIgnoresMaliciousOrderby( $payload ) {
		$this->db->get_all( $payload, 'ASC' );
		$this->assertStringNotContainsStringIgnoringCase( 'SLEEP', $this->sql() );
		$this->assertStringNotContainsStringIgnoringCase( 'DROP', $this->sql() );
		$this->assertStringNotContainsString( '--', $this->sql() );
		$this->assertStringNotContainsStringIgnoringCase( 'CASE WHEN', $this->sql() );
		$this->assertMatchesRegularExpression( '/ORDER BY `?book_id`? ASC;?$/', $this->sql() );
	}

	public function testGetAllIgnoresMaliciousOrder() {
		$this->db->get_all( 'book_id', 'ASC; DROP TABLE wp_posts' );
		$this->assertStringNotContainsStringIgnoringCase( 'DROP', $this->sql() );
		$this->assertMatchesRegularExpression( '/ORDER BY `?book_id`? ASC;?$/', $this->sql() );
	}

	// ---- (3) Edge cases ----------------------------------------------------

	public function testGetAllUnknownColumnFallsBackToPrimaryKey() {
		$this->db->get_all( 'not_a_column', 'DESC' );
		$this->assertMatchesRegularExpression( '/ORDER BY `?book_id`? DESC;?$/', $this->sql() );
	}

	public function testGetAllInvalidDirectionFallsBackToAscending() {
		$this->db->get_all( 'book_id', 'sideways' );
		$this->assertMatchesRegularExpression( '/ORDER BY `?book_id`? ASC;?$/', $this->sql() );
	}

	// ---- (4) Zero results --------------------------------------------------

	public function testGetAllWithNoRowsReturnsEmptyArray() {
		$this->db->data_rows = array();
		$this->assertSame( array(), $this->db->get_all() );
	}

	// ======================================================================
	// get_multiple()   (MOOBD_Database, protected -> call through a subclass)
	// ======================================================================

	private function get_multiple( $values, $orderby = null, $order = null ) {
		$db = new class() extends Mbm_Test_DB_Books {
			public function call_get_multiple( $values, $orderby, $order ) {
				return $this->get_multiple( $values, $orderby, $order );
			}
		};
		$this->db = $db;
		return $db->call_get_multiple( $values, $orderby, $order );
	}

	// ---- (1) Happy path ----------------------------------------------------

	public function testGetMultipleListsEachValueSeparately() {
		$this->get_multiple( array( 3, 4, 5 ), 'release_date', 'DESC' );
		// IN ('3','4','5') -- not IN ('3,4,5'), which only ever matched the first value
		$this->assertMatchesRegularExpression( "/book_id IN \(\s*'?3'?\s*,\s*'?4'?\s*,\s*'?5'?\s*\)/", $this->sql() );
		$this->assertMatchesRegularExpression( '/ORDER BY `?release_date`? DESC;?$/', $this->sql() );
	}

	public function testGetMultipleAcceptsASingleValue() {
		$this->get_multiple( 7 );
		$this->assertMatchesRegularExpression( "/book_id IN \(\s*'?7'?\s*\)/", $this->sql() );
	}

	public function testGetMultipleNeverQuotesTheOrderByTerms() {
		$this->get_multiple( array( 1, 2 ), 'release_date', 'DESC' );
		$this->assertDoesNotMatchRegularExpression( "/ORDER BY\s+'/", $this->sql() );
	}

	// ---- (2) The vulnerability ---------------------------------------------

	public function testGetMultipleCannotBeInjectedThroughValues() {
		$this->get_multiple( array( "1') OR ('1'='1", '2' ) );
		// the quote must be escaped so the payload stays inside a string literal
		$this->assertStringNotContainsString( "1') OR ('1'='1", $this->sql() );
	}

	/** @dataProvider maliciousOrderbyProvider */
	public function testGetMultipleIgnoresMaliciousOrderby( $payload ) {
		$this->get_multiple( array( 1 ), $payload, 'ASC' );
		$this->assertStringNotContainsStringIgnoringCase( 'SLEEP', $this->sql() );
		$this->assertStringNotContainsStringIgnoringCase( 'DROP', $this->sql() );
		$this->assertStringNotContainsString( '--', $this->sql() );
	}

	// ---- (3) Edge cases ----------------------------------------------------

	public function testGetMultipleWithEmptyArrayMatchesNothingAndIsValidSql() {
		$this->get_multiple( array() );
		$this->assertDoesNotMatchRegularExpression( '/IN \(\s*\)/', $this->sql() );
	}

	// ---- (4) Zero results --------------------------------------------------

	public function testGetMultipleWithNoRowsReturnsEmptyArray() {
		$this->db->data_rows = array();
		$this->assertSame( array(), $this->get_multiple( array( 1, 2 ) ) );
	}

	// ======================================================================
	// get_by()
	// ======================================================================

	private function get_by( $column, $value ) {
		$db = new class() extends Mbm_Test_DB_Books {
			public function call_get_by( $column, $value ) {
				return $this->get_by( $column, $value );
			}
		};
		return $db->call_get_by( $column, $value );
	}

	public function testGetByValidColumn() {
		$this->wpdb->row = (object) array( 'book_id' => 1 );
		$this->assertNotNull( $this->get_by( 'release_date', '2020-01-01' ) );
		$this->assertMatchesRegularExpression( "/WHERE `?release_date`? = '2020-01-01' LIMIT 1;?$/", preg_replace( '/\s+/', ' ', $this->wpdb->last_query() ) );
	}

	public function testGetByRejectsAColumnThatIsNotInTheTable() {
		$this->assertNull( $this->get_by( '1=1 OR book_id', 'x' ) );
		$this->assertSame( array(), $this->wpdb->queries );
	}

	public function testGetByWithNoMatchReturnsNull() {
		$this->wpdb->row = null;
		$this->assertNull( $this->get_by( 'release_date', '1999-01-01' ) );
	}
}
