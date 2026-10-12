<?php
use PHPUnit\Framework\TestCase;

/**
 * save_book_list_order() is the wp_ajax_save_book_list_order handler that
 * stores the custom book order in the _mbdb_book_grid_order_custom post meta.
 */
class SaveBookListOrderTest extends TestCase {

	const FIELD = 'mbdb_custom_book_order_book';

	protected function setUp(): void {
		mbm_test_reset();
	}

	private function post( $pairs, $page_id = 42 ) {
		$_POST = array(
			'security' => 'nonce',
			'pageID'   => $page_id,
			'books'    => $pairs,
		);
	}

	private function ids_query( array $ids ) {
		$parts = array();
		foreach ( $ids as $id ) {
			$parts[] = self::FIELD . '[]=' . urlencode( $id );
		}
		return implode( '&', $parts );
	}

	/** Runs the real handler; it always ends in wp_die(). */
	private function run_handler() {
		$cpt = ( new ReflectionClass( 'Mooberry_Book_Manager_Book_Grid_CPT' ) )->newInstanceWithoutConstructor();
		try {
			$cpt->save_book_list_order();
			$this->fail( 'handler did not end with wp_die()' );
		} catch ( Mbm_Test_Die_Exception $e ) {
			// expected
		}
		return $GLOBALS['mbm_test']['meta_updates'];
	}

	// ---- (1) Happy path ----------------------------------------------------

	public function testSavesTheOrderForAUserWhoCanEditTheGrid() {
		$this->post( $this->ids_query( array( 3, 1, 2 ) ) );
		$updates = $this->run_handler();

		$this->assertCount( 1, $updates );
		$this->assertEquals( 42, $updates[0][0] );
		$this->assertSame( '_mbdb_book_grid_order_custom', $updates[0][1] );
		$this->assertEquals( array( 3, 1, 2 ), $updates[0][2] );
	}

	public function testSavedIdsAreIntegers() {
		$this->post( $this->ids_query( array( 3, 1, 2 ) ) );
		$updates = $this->run_handler();
		$this->assertSame( array( 3, 1, 2 ), $updates[0][2] );
	}

	// ---- (2) The vulnerability ---------------------------------------------

	public function testInjectionPayloadIsNotStored() {
		$this->post( $this->ids_query( array( 1, '1) UNION SELECT user_pass FROM wp_users-- ', 2 ) ) );
		$updates = $this->run_handler();

		$this->assertCount( 1, $updates );
		$this->assertSame( array( 1, 2 ), $updates[0][2] );
		$this->assertStringNotContainsString( 'UNION', serialize( $updates[0][2] ) );
	}

	public function testUserWithoutEditRightsCannotSave() {
		$GLOBALS['mbm_test']['can_edit'] = false;
		$this->post( $this->ids_query( array( 3, 1, 2 ) ) );
		$updates = $this->run_handler();

		$this->assertSame( array(), $updates );
	}

	public function testCapabilityIsCheckedAgainstTheTargetPost() {
		$this->post( $this->ids_query( array( 3, 1 ) ), '42' );
		$this->run_handler();

		$this->assertNotEmpty( $GLOBALS['mbm_test']['cap_checks'] );
		$this->assertSame( array( 'edit_post', 42 ), $GLOBALS['mbm_test']['cap_checks'][0] );
	}

	public function testNonNumericPageIdIsRejected() {
		$this->post( $this->ids_query( array( 3, 1 ) ), '42; DROP TABLE wp_posts' );
		$updates = $this->run_handler();

		foreach ( $updates as $u ) {
			$this->assertIsInt( $u[0] );
		}
		$this->assertStringNotContainsString( 'DROP', serialize( $updates ) );
	}

	// ---- (3) Other edge cases ----------------------------------------------

	public function testBlankBooksParameterSavesNothing() {
		$this->post( '' );
		$this->assertSame( array(), $this->run_handler() );
	}

	public function testMissingOrderFieldSavesNothingAndRaisesNoWarning() {
		$this->post( 'something_else[]=5' );
		$this->assertSame( array(), $this->run_handler() );
	}

	public function testZeroNegativeAndNestedEntriesAreDropped() {
		$this->post( self::FIELD . '[]=0&' . self::FIELD . '[]=-4&' . self::FIELD . '[][]=9&' . self::FIELD . '[]=7' );
		$updates = $this->run_handler();
		$this->assertSame( array( 7 ), $updates[0][2] );
	}

	public function testMissingPageIdSavesNothing() {
		$this->post( $this->ids_query( array( 3, 1 ) ) );
		unset( $_POST['pageID'] );
		$this->assertSame( array(), $this->run_handler() );
	}

	// ---- (4) Zero results --------------------------------------------------

	public function testNoValidIdsStoresAnEmptyOrder() {
		$this->post( $this->ids_query( array( 'abc', 0, '1;--' ) ) );
		$updates = $this->run_handler();

		$this->assertCount( 1, $updates );
		$this->assertSame( array(), $updates[0][2] );
	}
}
