<?php

class Mooberry_Book_Manager_Simple_Grid_Factory {
	
	// input can be an ID in the case of a book grid
	// or an array of info in the case of a tax grid or coming from the preview on the cpt page
	public function create_grid( $input ) {
		$grid = null;
		
		if ( is_array( $input ) ) {
			if ( array_key_exists( 'taxonomy', $input ) && array_key_exists( 'terms', $input ) && array_key_exists( 'sort', $input ) ) {
				$grid = new Mooberry_Book_Manager_Tax_Grid( $input[	'taxonomy'], $input['terms'], $input['sort'] );
			} else {
				// it is from the preview
				$grid = new Mooberry_Book_Manager_Book_Grid( $input );
			}
		} else {
			$grid = new Mooberry_Book_Manager_Book_Grid( $input );
		}
		
		return $grid;
	}
		
}
