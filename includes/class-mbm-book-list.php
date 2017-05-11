<?php
class MBDB_Book_List_Enum {
	const all = 'all';
	const published = 'published';
	const unpublished = 'unpublished';
	//const select = 'custom';
	const random = 'random';
	//const publisher = 'publisher';
	const newest = 'newest';
}

class MBDB_Book_List implements Countable, Iterator  {
	
	
	private $books;
	 private $db_object;
	private $cursor;
	private $full_count;
	
	
	public function __construct( $book_list_type = 'all', $orderby = 'title', $order = 'ASC', $selection_ids = null, $selection_filter = null, $book_filter = null, $limit = null, $random = false, $include_drafts = false, $full_object = false, $book_limit = null, $offset = null ) {
	
		
		$this->books = array();
		$this->db_object = MBDB()->books_db;
		$this->cursor = -1;
		
		if ( is_array($book_list_type ) && array_key_exists( 'books' , $book_list_type ) ) {
				$books = $book_list_type[ 'books' ];
				$this->full_count = count($books);
		} else {
			if ( $book_list_type == 'newest' ) {
				$books = $this->db_object->get_newest_books();
			} else {
				if ( $orderby == 'random' ) {
					$sort = 'random';
				} else {
					$sort = array( $orderby, $order );
				}
				$books = $this->db_object->get_ordered_selection( $book_list_type, $selection_ids, $sort, $book_filter, $selection_filter, $include_drafts, $book_limit, $offset );
				
			}
		}
		//error_log('got books');
		
		if ( $book_list_type == MBDB_Book_List_Enum::random || $random  ) {
			shuffle( $books );
			// print_r($limit);
			// print_r($books);
			// if ( $limit != null && $limit < count($books ) ) {
				// $keys = array_rand( $books, $limit );
				
				// if ( !is_array($keys) ) {
					// $keys = array( $keys );
				// }
				// $random_books = array();
				// foreach ( $keys as $key ) {
					// $random_books[] = $books[$key];
				// }
				// $books = $random_books;
			// }
				
		}
		
		//error_log('splice books');
		$books = array_slice( $books, 0, $limit );
		//$this->limit_books($limit);
			
		//error_log('create array');
		//error_log(print_r($books, true));
		foreach( $books as $book ) {
			//error_log('new book');
			if ( property_exists($book, 'total' ) ) {
				$this->full_count = $book->total;
			}
			if ( $full_object ) {
				//$this->books[] = new Mooberry_Book_Manager_Book( $book->book_id );
				$this->books[] = MBDB()->book_factory->create_book( $book->book_id );
			} else {
				//$this->books[] = new Mooberry_Book_Manager_Book_Basic( $book );
				$this->books[] = MBDB()->book_factory->create_book( $book );
			}
		}
		//error_log('done');
					
	}
	
	private function load_books_array( $books, $randomize, $limit ) { 
	
	}

	public function limit_books( $limit ) {
		$this->books = array_slice( $this->books, 0, $limit );
	}
	
	public function count() {
		return count($this->books);
	}
	
	public function rewind() {
		$this->cursor = 0;
	}
	
	public function current() {
		if ( $this->valid() ) {
			return $this->books[ $this->cursor ];
		} else {
			return null;
		}
	}
	
	public function valid() {
		return ( array_key_exists( $this->cursor, $this->books ) );
	}

	public function key() {
		return $this->cursor;
	}
	
	public function next() {
		$this->cursor++;
	}
	
	public function remove_book( $book_id ) {
		foreach ( $this->books as $key => $book ) {
			if ( $book->book_id == $book_id ) {
				$this->remove_book_by_index( $key );
				break;
			}
		}
		
	}
	
	public function remove_book_by_index ( $key ) {
		unset( $this->books[ $key ] );
	}
	
	public function get_book_dropdown( $dropdownID, $bookID, $include_empty = 'yes', $empty_value = '0', $name = '' ) {
		
		$options = $this->get_title_list();
	
		return MBDB()->helper_functions->make_dropdown($dropdownID, $options, $bookID, $include_empty, $empty_value, $name  );
	}
	
	public function get_title_list( $add_empty = false, $empty_key = '0', $empty_value = '' ) {
		
		
			
			$options = array();
			
			if ( $add_empty ) {
				$options[ $empty_key ] = $empty_value;
			}
			
			foreach ( $this->books as $book ) {
				$options[ $book->id ] = $book->title;
			}
				
		return $options;
	}
	
	public function to_json() {
		$results = array();
        foreach ( $this->books as $book ) {
			$results[] = $book->to_json();
		}
		return json_encode($results);
       
    }
	

	
	/**
	 * Magic __get function to dispatch a call to retrieve a private property
	 *
	 * @since 1.0
	 */
	public function __get( $key ) {

		if( method_exists( $this, 'get_' . $key ) ) {

			return call_user_func( array( $this, 'get_' . $key ) );

		} else {
			
			if ( property_exists( $this, $key ) ) {
				
				$ungettable_properties = array( 'db_object' );
				
				if ( !in_array( $key, $ungettable_properties ) ) {
				
					return $this->$key;

				}
		
			}
		
		}
	
		return new WP_Error( 'mbdb-invalid-property', sprintf( __( 'Can\'t get property %s', 'mooberry-book-manager' ), $key ) );
				
	}
	
	/**
	 * Magic __set function to dispatch a call to retrieve a private property
	 *
	 * @since 1.0
	 */
	 /*
	public function __set( $key, $value ) {

		if( method_exists( $this, 'set_' . $key ) ) {

			return call_user_func( array( $this, 'set_' . $key ), $key, $value );

		} else {

			if ( property_exists( $this, $key ) ) {
				
				$unsettable_properties = array( 'db_object', 'books' );
				
				if ( !in_array( $key, $unsettable_properties ) ) {
				
					$this->$key = $value;

				}
				
			}
		}
	
		return new WP_Error( 'mbdb-invalid-property', sprintf( __( 'Can\'t get property %s', 'mooberry-book-manager' ), $key ) );
		
	}
*/	
	
}
