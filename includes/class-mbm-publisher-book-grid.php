<?php

	if ( class_exists('Mooberry_Book_Manager_Book_Grid') ) {
class Mooberry_Book_Manager_Publisher_Book_Grid extends Mooberry_Book_Manager_Book_Grid {


	public function __construct( $publisher, $group_by, $order_by, $cover_height ) {
		parent::__construct();
		$this->books = 'publisher';
		$this->selection = $publisher;
		$this->set_order_by($order_by );
		$this->group_by = $group_by;
		$this->default_height = false;
		$this->custom_height = $cover_height;

	}
}

}

