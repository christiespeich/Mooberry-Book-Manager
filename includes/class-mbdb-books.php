<?php 

// this class is strictly for backwards compatibility with the 4 original plugin extensions
// and should not be used in other instances
class MBDB_Books {
	
	private $db_object;
	
	public function __construct() {
	//	//error_log('new mbdb_books');
		//$this->db_object = new MBDB_DB_Books();
		$this->db_object = MBDB()->books_db;
	}
	
	public function get( $bookID ) {
		return $this->db_object->get( $bookID );
	}
	
	public function get_ordered_selection( $selection, $selection_ids, $sort, $book_ids = null, $current_group = null ) {
		return $this->db_object->get_ordered_selection( $selection, $selection_ids, $sort, $book_ids, $current_group );
	}
	
	public function get_all( $orderby, $order ) {
		// get all function does not exist in V4....so put it here
		
		global $wpdb;
		$primary_key = 'book_id';
		
		//	$orderby = $this->validate_orderby( $orderby );
		if ($orderby == null) {
			$orderby = $primary_key;
		}
		if ( $order == null || ( $order != 'ASC' && $order != 'DESC' ) ) {
			$order = 'ASC';
		}
		$order = esc_sql( $order );
		
		$join = ' JOIN ' . $wpdb->posts . ' p ON p.id = t.' . $primary_key;
		$where = ' WHERE p.post_status = "publish" ';
		$table = $this->db_object->table_name();
		$sql =  "SELECT * 
				FROM $table AS t
				$join
				$where
				ORDER BY  
				$orderby 
				$order";

		//return $this->db_object->run_sql($sql, true );
		return $wpdb->get_results($sql);
	}
	
	public function get_table_name() {
		return $this->db_object->table_name();
	}
	
	public function get_random_book( $filter_bookIDs ) {
		//print_r('get random book');
		return new MBDB_Book_List( MBDB_Book_List_Enum::random, 'title', 'ASC', null, null, $filter_bookIDs);
	}
	
	public function get_most_recent_book( $filter_bookIDs ) {
		//print_r('get most recent book');
		 new MBDB_Book_List( MBDB_Book_List_Enum::published, 'release_date', 'DESC', null, null, $filter_bookIDs);
	}
	
	public function get_upcoming_book( $filter_bookIDs ) {
		//print_r('get_upcoming book');
		new MBDB_Book_List( MBDB_Book_List_Enum::unpublished, 'title', 'ASC', null, null, $filter_bookIDs);
	}
	
	public function create_table() {
		$this->db_object->create_table();
	}
	
	public function empty_table() {
		$this->db_object->empty_table();
	}
	
	public function import() {
		$this->db_object->import();
	}


}