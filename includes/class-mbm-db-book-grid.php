<?php



class MBDB_DB_Book_Grid extends MBDB_CMB_CPT {

	public function __construct() {
		$this->post_type = 'mbdb_book_grid';
	}



	protected function add_post_meta( $book_grid, $postmeta = null ) {

		if ( $postmeta == null ) {
			$postmeta = get_post_meta( $book_grid->ID );
		}
			$book_grid->books = '';
			$book_grid->filter_selection = array();

		if (array_key_exists( '_mbdb_book_grid_books', $postmeta ) ) {

			$book_grid->books = $postmeta[ '_mbdb_book_grid_books' ][ 0 ];

			if ( array_key_exists( '_mbdb_book_grid_' . $book_grid->books, $postmeta ) ) {

				$filter_selection = maybe_unserialize($postmeta['_mbdb_book_grid_' . $book_grid->books][0]);
				$book_grid->filter_selection = $filter_selection;

			} else {
				$book_grid->filter_selection = array();
			}
		} else {
			$book_grid->books = '';
			$book_grid->filter_selection = array();
		}

		$book_grid->display_group_descriptions = array();
		$book_grid->display_group_bottom_descriptions = array();
		$x = 1;
		//while ( property_exists( $book_grid, 'group_by_level_' . $x ) ) {
			while (array_key_exists( '_mbdb_book_grid_group_by_level_' . $x, $postmeta ) ) {
				$property = 'group_by_level_' . $x;
				$book_grid->{$property} = $postmeta[ '_mbdb_book_grid_group_by_level_' . $x ][ 0 ];
				if ( array_key_exists('_mbdb_book_grid_description_group_by_level_' . $x, $postmeta)) {
					$book_grid->display_group_descriptions[$x] = $postmeta[ '_mbdb_book_grid_description_group_by_level_' . $x ][0];
					$book_grid->display_group_bottom_descriptions[$x]  = $postmeta[ '_mbdb_book_grid_bottom_description_group_by_level_' . $x ][0];
				}
			//}
			$x++;
		}
		if (array_key_exists( '_mbdb_book_grid_order', $postmeta ) ) {
			$book_grid->order_by = $postmeta[ '_mbdb_book_grid_order' ][ 0 ];
		} else {
			$book_grid->order_by = '';
		}
		if (array_key_exists( '_mbdb_book_grid_cover_height_default', $postmeta ) ) {
			$book_grid->default_height = $postmeta[ '_mbdb_book_grid_cover_height_default' ][ 0 ];
		} else {
			$book_grid->default_height = '';
		}
		if (array_key_exists( '_mbdb_book_grid_cover_height', $postmeta ) ) {
			$book_grid->custom_height = $postmeta[ '_mbdb_book_grid_cover_height' ][ 0 ];
		} else {
			$book_grid->custom_height = '';
		}
		if (array_key_exists( '_mbdb_book_grid_order_custom', $postmeta ) ) {
			$book_grid->order_custom = unserialize($postmeta[ '_mbdb_book_grid_order_custom' ][ 0 ] );
		} else {
			$book_grid->order_custom = '';
		}

		return $book_grid;
	}

	public function get_by_slug( $slug, $cache_results = true ) {
		$book_grid = parent::get_by_slug( $slug );
		$book_grid = $this->add_post_meta( $book_grid );
		return $book_grid;
	}




}
