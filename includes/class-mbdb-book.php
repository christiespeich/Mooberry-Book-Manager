<?php
/**
 * Book Object
 *
 * @package     MBDB
 * @copyright   Copyright (c) 2015, Mooberry Dreams
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * MBDB_Books Class
 *
 * @since 3.0
 */
class MBDB_Books extends MBDB_CPT {

	//private $db_books;
	
	public function __construct() {
		$this->db_object = new MBDB_DB_Books();
		
	}
	
	public function get_unpublished_books( $bookIDs, $orderby = null, $order = null ) {
		return $this->db_object->get_ordered_selection( 'unpublished', null, array($orderby, $order), $bookIDs );
	}
	
	public function get_published_books( $bookIDs, $orderby = null, $order = null) {
		return $this->db_object->get_ordered_selection( 'published', null, array($orderby, $order), $bookIDs );
	}
	
	public function get_books_by_taxonomy( $bookIDs, $taxonomy, $taxIDs, $orderby = null, $order = null) {
		return $this->db_object->get_ordered_selection( $taxonomy, null, array($orderby, $order), $bookIDs, array( $taxonomy => $taxIDs ) );
	}
	
	public function get_books_by_publisher( $bookIDs, $publisherIDs, $orderby = null, $order = null) {
		return $this->db_object->get_ordered_selection( 'publisher', $publisherIDs, array($orderby, $order), $bookIDs );
	}
	
	public function get_ordered_selection( $selection, $selection_ids, $sort, $book_ids, $taxonomy ) {
		return $this->db_object->get_ordered_selection( $selection, $selection_ids, $sort, $book_ids, $taxonomy );
	}
	
	public function get_most_recent_book( $bookIDs = null ) {
		$published_books = $this->get_published_books( $bookIDs, 'release_date', 'DESC' );
		if ( $published_books ) {
			return $published_books[0];
		} else {
			return null;
		}
	}
	
	public function get_random_book( $bookIDs = null ) {
		$books = $this->db_object->get( $bookIDs, null, null, false, false );

		return mbdb_get_random( $books );
		
	}
	
	public function get_upcoming_book( $bookIDs = null ) {
		$unpublished_books = $this->get_unpublished_books( $bookIDs );
		return mbdb_get_random( $unpublished_books );
	}
	
	public function get_table_name() {
		return $this->db_object->table_name();
	}
	
}