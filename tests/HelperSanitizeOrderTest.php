<?php
use PHPUnit\Framework\TestCase;

/**
 * Mooberry_Book_Manager_Helper_Functions::sanitize_orderby() / sanitize_order()
 * are used wherever a caller-supplied column list or direction ends up in an
 * ORDER BY clause (identifiers cannot go through prepare()).
 */
class HelperSanitizeOrderTest extends TestCase {

	// ---- (1) Happy path ----------------------------------------------------

	public function validOrderbyProvider() {
		return array(
			'plain column'          => array( 'release_date', 'release_date' ),
			'table alias'           => array( 't.release_date', 't.release_date' ),
			'posts alias'           => array( 'p.post_title', 'p.post_title' ),
			'underscore and digits' => array( 'col_2', 'col_2' ),
			'with direction'        => array( 'release_date DESC', 'release_date DESC' ),
			'lower case direction'  => array( 'release_date desc', 'release_date DESC' ),
			'column list'           => array( 'release_date DESC, p.post_title', 'release_date DESC, p.post_title' ),
			'surrounding space'     => array( '  book_id  ', 'book_id' ),
		);
	}

	/** @dataProvider validOrderbyProvider */
	public function testValidOrderbyIsKept( $input, $expected ) {
		$this->assertSame( $expected, Mooberry_Book_Manager_Helper_Functions::sanitize_orderby( $input, 'book_id' ) );
	}

	public function testDirectionsAreNormalised() {
		$this->assertSame( 'ASC', Mooberry_Book_Manager_Helper_Functions::sanitize_order( 'ASC' ) );
		$this->assertSame( 'DESC', Mooberry_Book_Manager_Helper_Functions::sanitize_order( 'DESC' ) );
		$this->assertSame( 'DESC', Mooberry_Book_Manager_Helper_Functions::sanitize_order( 'desc' ) );
		$this->assertSame( 'ASC', Mooberry_Book_Manager_Helper_Functions::sanitize_order( 'asc' ) );
	}

	// ---- (2) The vulnerability ---------------------------------------------

	public function maliciousOrderbyProvider() {
		return array(
			'subselect'   => array( '(SELECT SLEEP(5))' ),
			'function'    => array( 'SLEEP(5)' ),
			'stacked'     => array( 'book_id; DROP TABLE wp_posts' ),
			'comment'     => array( 'book_id-- ' ),
			'block'       => array( 'book_id /* x */' ),
			'case'        => array( 'CASE WHEN 1=1 THEN a ELSE b END' ),
			'quotes'      => array( "book_id' OR '1'='1" ),
			'backtick'    => array( '`book_id` , (SELECT 1)' ),
			'second term' => array( 'book_id, (SELECT SLEEP(5))' ),
			'union'       => array( 'book_id UNION SELECT 1' ),
		);
	}

	/** @dataProvider maliciousOrderbyProvider */
	public function testMaliciousOrderbyFallsBackToTheDefault( $payload ) {
		$this->assertSame( 'book_id', Mooberry_Book_Manager_Helper_Functions::sanitize_orderby( $payload, 'book_id' ) );
	}

	public function maliciousOrderProvider() {
		return array(
			'stacked'  => array( 'ASC; DROP TABLE wp_posts' ),
			'subquery' => array( '(SELECT 1)' ),
			'comment'  => array( 'DESC -- ' ),
			'junk'     => array( 'sideways' ),
		);
	}

	/** @dataProvider maliciousOrderProvider */
	public function testMaliciousOrderFallsBackToAsc( $payload ) {
		$this->assertSame( 'ASC', Mooberry_Book_Manager_Helper_Functions::sanitize_order( $payload ) );
	}

	// ---- (3) Edge cases ----------------------------------------------------

	public function nonStringProvider() {
		return array(
			'null'   => array( null ),
			'empty'  => array( '' ),
			'blank'  => array( '   ' ),
			'array'  => array( array( 'book_id' ) ),
			'int'    => array( 5 ),
			'bool'   => array( true ),
			'object' => array( new stdClass() ),
		);
	}

	/** @dataProvider nonStringProvider */
	public function testNonStringOrderbyFallsBackToTheDefault( $input ) {
		$this->assertSame( 'book_id', Mooberry_Book_Manager_Helper_Functions::sanitize_orderby( $input, 'book_id' ) );
	}

	/** @dataProvider nonStringProvider */
	public function testNonStringOrderFallsBackToAsc( $input ) {
		$this->assertSame( 'ASC', Mooberry_Book_Manager_Helper_Functions::sanitize_order( $input ) );
	}

	public function testTrailingCommaOrDoubleDirectionIsRejected() {
		$this->assertSame( 'book_id', Mooberry_Book_Manager_Helper_Functions::sanitize_orderby( 'release_date,', 'book_id' ) );
		$this->assertSame( 'book_id', Mooberry_Book_Manager_Helper_Functions::sanitize_orderby( 'release_date DESC ASC', 'book_id' ) );
	}

	// ---- (4) Zero results: nothing to look up, so nothing to return -------

	public function testTheDefaultItselfIsReturnedUnchangedWhenInputIsEmpty() {
		$this->assertSame( 'p.id', Mooberry_Book_Manager_Helper_Functions::sanitize_orderby( null, 'p.id' ) );
	}
}
