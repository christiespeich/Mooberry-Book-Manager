<?php
use PHPUnit\Framework\TestCase;

class HelperSanitizeIdListTest extends TestCase {

	public function testKeepsPositiveIntegersAndNumericStringsInOrder() {
		$this->assertSame( array( 5, 12, 3 ), Mooberry_Book_Manager_Helper_Functions::sanitize_id_list( array( 5, '12', 3 ) ) );
	}

	public function testDropsEverythingThatIsNotAPositiveWholeNumber() {
		$input = array( 0, '0', -1, '-1', 1.5, '1.5', '1e3', ' 4', '4 ', '5abc', '1,2', '', null, true, array( 1 ), '0x1' );
		$this->assertSame( array(), Mooberry_Book_Manager_Helper_Functions::sanitize_id_list( $input ) );
	}

	public function testNonArrayInputGivesEmptyArray() {
		$this->assertSame( array(), Mooberry_Book_Manager_Helper_Functions::sanitize_id_list( '' ) );
		$this->assertSame( array(), Mooberry_Book_Manager_Helper_Functions::sanitize_id_list( null ) );
		$this->assertSame( array(), Mooberry_Book_Manager_Helper_Functions::sanitize_id_list( '1,2,3' ) );
	}

	public function testEmptyArrayGivesEmptyArray() {
		$this->assertSame( array(), Mooberry_Book_Manager_Helper_Functions::sanitize_id_list( array() ) );
	}

	public function testReindexesTheResult() {
		$this->assertSame( array( 2, 3 ), Mooberry_Book_Manager_Helper_Functions::sanitize_id_list( array( 'a' => 'x', 'b' => 2, 'c' => 3 ) ) );
	}
}
