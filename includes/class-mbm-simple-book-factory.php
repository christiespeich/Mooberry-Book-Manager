<?php

class Mooberry_Book_Manager_Simple_Book_Factory {

	public function create_book( $input = 0) {
		$book = null;
		
	//	$book = new Mooberry_Book_Manager( $input );
		
		if ( is_object( $input ) ) {
			$book = new Mooberry_Book_Manager_Book_Basic( $input );
		} else {
			$book = new  Mooberry_Book_Manager_Book( $input );
		}
		return $book;
	}
	
}
