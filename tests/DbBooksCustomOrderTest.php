<?php
use PHPUnit\Framework\TestCase;

/**
 * MBDB_DB_Books::get_ordered_selection() builds the ORDER BY clause for book
 * grids. The "custom" sort used to implode() unvalidated values into
 * ORDER BY FIELD( b.book_id, ... ).
 */
class DbBooksCustomOrderTest extends TestCase {

	private $db;

	protected function setUp(): void {
		mbm_test_reset();
		$this->db = new Mbm_Test_DB_Books();
	}

	private function order_by_clause( $sql ) {
		$this->assertMatchesRegularExpression( '/ORDER BY(.*)$/is', $sql );
		preg_match( '/ORDER BY(.*)$/is', $sql, $m );
		return trim( preg_replace( '/\s+/', ' ', $m[1] ) );
	}

	// ---- (1) Happy path ----------------------------------------------------

	public function testTitleAscendingSort() {
		$this->db->get_ordered_selection( 'all', null, 'titleA' );
		$this->assertSame( 'post_title ASC', $this->order_by_clause( $this->db->last_sql() ) );
	}

	public function testReleaseDateDescendingSort() {
		$this->db->get_ordered_selection( 'all', null, 'pubdateD' );
		$this->assertMatchesRegularExpression( '/release_date\s+DESC\s*$/', $this->order_by_clause( $this->db->last_sql() ) );
	}

	public function testCustomOrderUsesFieldInTheGivenOrder() {
		$this->db->get_ordered_selection( 'all', null, array( 'custom', array( 3, 1, 2 ) ) );
		$this->assertMatchesRegularExpression(
			'/ORDER BY\s+FIELD\(\s*b\.book_id,\s*3\s*,\s*1\s*,\s*2\s*\)/',
			$this->db->last_sql()
		);
	}

	public function testCustomOrderAcceptsNumericStringsAsStoredInPostMeta() {
		$this->db->get_ordered_selection( 'all', null, array( 'custom', array( '12', '7' ) ) );
		$this->assertMatchesRegularExpression( '/FIELD\(\s*b\.book_id,\s*12\s*,\s*7\s*\)/', $this->db->last_sql() );
	}

	public function testCustomOrderWithSingleId() {
		$this->db->get_ordered_selection( 'all', null, array( 'custom', array( 5 ) ) );
		$this->assertMatchesRegularExpression( '/FIELD\(\s*b\.book_id,\s*5\s*\)/', $this->db->last_sql() );
	}

	// ---- (2) The vulnerability ---------------------------------------------

	public function maliciousIdProvider() {
		return array(
			'union select'    => array( '1) UNION SELECT user_pass FROM wp_users-- ' ),
			'stacked query'   => array( '1); DROP TABLE wp_posts;-- ' ),
			'time based'      => array( '1) AND SLEEP(5)-- ' ),
			'subselect'       => array( '(SELECT 1)' ),
			'comma smuggling' => array( '1,2' ),
			'hex literal'     => array( '0x31' ),
			'leading digit'   => array( '5abc' ),
		);
	}

	/**
	 * @dataProvider maliciousIdProvider
	 */
	public function testMaliciousIdInCustomOrderNeverReachesTheSql( $payload ) {
		$this->db->get_ordered_selection( 'all', null, array( 'custom', array( 1, $payload, 2 ) ) );
		$sql = $this->db->last_sql();

		$this->assertStringNotContainsString( 'UNION', $sql );
		$this->assertStringNotContainsString( 'DROP', $sql );
		$this->assertStringNotContainsString( 'SLEEP', $sql );
		$this->assertStringNotContainsString( 'wp_users', $sql );
		$this->assertStringNotContainsString( 'SELECT 1', $sql );
		$this->assertStringNotContainsString( '0x31', $sql );

		// whatever is inside FIELD( b.book_id, ... ) must be plain integers
		$this->assertSame( 1, preg_match( '/FIELD\(\s*b\.book_id,([^)]*)\)/', $sql, $m ), 'FIELD() clause missing' );
		$this->assertMatchesRegularExpression( '/^[\d,\s]+$/', $m[1] );
	}

	public function testSortDirectionIsRestrictedToAscOrDesc() {
		$this->db->get_ordered_selection( 'all', null, array( 'release_date', 'ASC; DROP TABLE wp_posts' ) );
		$sql = $this->db->last_sql();
		$this->assertStringNotContainsString( 'DROP', $sql );
		$this->assertMatchesRegularExpression( '/\b(ASC|DESC)\s*$/', $this->order_by_clause( $sql ) );
	}

	// ---- (3) Other edge cases ----------------------------------------------

	public function testNonArrayCustomOrderFallsBackToTitleSort() {
		// BookGrid::$order_custom defaults to '' when no custom order was saved
		$this->db->get_ordered_selection( 'all', null, array( 'custom', '' ) );
		$this->assertSame( 'post_title', $this->order_by_clause( $this->db->last_sql() ) );
	}

	public function testZeroNegativeFloatAndNestedValuesAreDropped() {
		$this->db->get_ordered_selection(
			'all',
			null,
			array( 'custom', array( 4, 0, -5, 2.5, array( 9 ), null, true, '', 6 ) )
		);
		$this->assertMatchesRegularExpression( '/FIELD\(\s*b\.book_id,\s*4\s*,\s*6\s*\)/', $this->db->last_sql() );
	}

	public function testOrderingIsStableWhenIdsAreRepeated() {
		$this->db->get_ordered_selection( 'all', null, array( 'custom', array( 8, 8, 3 ) ) );
		$this->assertMatchesRegularExpression( '/FIELD\(\s*b\.book_id,\s*8\s*,\s*8\s*,\s*3\s*\)/', $this->db->last_sql() );
	}

	public function testDefaultSortWhenSortIsUnknown() {
		$this->db->get_ordered_selection( 'all', null, 'not-a-real-sort' );
		$this->assertSame( 'post_title', $this->order_by_clause( $this->db->last_sql() ) );
	}

	// ---- (4) Zero results --------------------------------------------------

	public function testZeroRowsReturnsAnEmptyArray() {
		$this->db->count_rows = array();   // e.g. nothing came back from the COUNT query
		$this->db->data_rows  = array();
		$result = $this->db->get_ordered_selection( 'all', null, 'titleA' );
		$this->assertSame( array(), $result );
	}

	public function testCountOfZeroReturnsAnEmptyArray() {
		$this->db->count_rows = array( (object) array( 'count' => '0' ) );
		$this->db->data_rows  = array();
		$result = $this->db->get_ordered_selection( 'published', null, 'pubdateD' );
		$this->assertSame( array(), $result );
	}

	public function testCustomOrderWithEmptyIdListProducesValidSql() {
		$this->db->get_ordered_selection( 'all', null, array( 'custom', array() ) );
		$sql = $this->db->last_sql();
		$this->assertStringNotContainsString( 'FIELD( b.book_id, )', preg_replace( '/\s+/', ' ', $sql ) );
		$this->assertDoesNotMatchRegularExpression( '/FIELD\(\s*b\.book_id,\s*\)/', $sql );
		$this->assertSame( 'post_title', $this->order_by_clause( $sql ) );
	}

	public function testCustomOrderWithOnlyInvalidIdsFallsBackToTitleSort() {
		$this->db->get_ordered_selection( 'all', null, array( 'custom', array( 'abc', '1); DROP TABLE x;--', 0 ) ) );
		$sql = $this->db->last_sql();
		$this->assertStringNotContainsString( 'DROP', $sql );
		$this->assertDoesNotMatchRegularExpression( '/FIELD\(/', $sql );
		$this->assertSame( 'post_title', $this->order_by_clause( $sql ) );
	}

	public function testRowsAreReturnedWhenPresent() {
		$this->db->data_rows = array( (object) array( 'book_id' => 3 ), (object) array( 'book_id' => 1 ) );
		$result = $this->db->get_ordered_selection( 'all', null, array( 'custom', array( 3, 1 ) ) );
		$this->assertCount( 2, $result );
	}
}
