<?php

class Mooberry_Book_Manager_Tax_Grid extends Mooberry_Book_Manager_Book_Grid {
	
	public function __construct( $taxonomy, $term, $sort = null ) {
		parent::__construct();
		$this->books = $taxonomy;
		$this->selection = $term;
		if ( $sort != null ) {
			$this->set_order_by( $sort );
		}
		$this->get_book_list();
		
	}
}