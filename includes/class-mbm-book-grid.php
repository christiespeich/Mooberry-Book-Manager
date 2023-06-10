<?php


/**
 * Book Grid Object
 *
 * @package     MBDB
 * @copyright   Copyright (c) 2015, Mooberry Dreams
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5 ?
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Mooberry_Book_Manager_Book_Grid Class
 *
 * @since 4.0
 */
 class Mooberry_Book_Manager_Book_Grid extends Mooberry_Book_Manager_Grid {

	 protected $title;
	 protected $books;
	 protected $selection;
	 protected $book_selection;
	 protected $genre_selection;
	 protected $series_selection;
	 protected $tag_selection;
	 protected $publisher_selection;
	 protected $illustrator_selection;
	 protected $cover_artist_selection;
	 protected $editor_selection;
	 protected $group_by;
	 protected $order_by;
	 protected $sort;
	 protected $order_custom;
	 protected $default_height;
	 protected $custom_height;
	 protected $cover_height;
	 protected $book_list;
	 protected $current_page;
	 protected $books_per_page;
	 protected $wp_size;
	 protected $group_descriptions;
	 protected $group_bottom_descriptions;
	 protected $display_group_descriptions;
	 protected $display_group_bottom_descriptions;

	 // id can be id number or an array of options
	 public function __construct( $id = 0 ) {

		 parent::__construct( $id );

		 $this->postmeta_to_object = array(
			 '_mbdb_book_grid_books'                => 'books',
			 '_mbdb_book_grid_custom'               => 'book_selection',
			 '_mbdb_book_grid_genre'                => 'genre_selection',
			 '_mbdb_book_grid_series'               => 'series_selection',
			 '_mbdb_book_grid_tag'                  => 'tag_selection',
			 '_mbdb_book_grid_publisher'            => 'publisher_selection',
			 '_mbdb_book_grid_editor'               => 'editor_selection',
			 '_mbdb_book_grid_illustrator'          => 'illustrator_selection',
			 '_mbdb_book_grid_cover_artist'         => 'cover_artist_selection',
			 '_mbdb_book_grid_order'                => 'order_by',
			 '_mbdb_book_grid_cover_height_default' => 'default_height',
			 '_mbdb_book_grid_cover_height'         => 'custom_height',
		 );

		 $this->db_object = MBDB()->book_grid_db;

		 $this->id             = 0;
		 $this->permalink      = '';
		 $this->title          = '';
		 $this->books          = '';
		 $this->selection      = array();
		 $this->group_by       = array();
		 $this->order_by       = 'post_title';
		 $this->sort           = 'ASC';
		 $this->order_custom   = '';
		 $this->default_height = true;
		 $this->custom_height  = '';
		 $this->book_list      = null;
		 $this->current_page   = 1;
		 $this->books_per_page = 0;
		 $this->group_descriptions = array();
		 $this->group_bottom_descriptions = array();
		 $this->display_group_descriptions = array();
		 $this->display_group_bottom_descriptions = array();

		 $this->set_cover_height();

		 if ( $id === 0 ) {
			 return;
		 }

		 $book_grid = $this->db_object->get( $id );

		 if ( $book_grid != null ) {
			 $this->id        = isset( $book_grid->id ) ? $book_grid->id : (isset($book_grid->ID ) ? $book_grid->ID : 0);
			 $this->title     = $book_grid->post_title;
			 $this->books     = $book_grid->books;
			 $this->selection = $book_grid->filter_selection;

			 $this->set_group_by( $book_grid );
			 $this->display_group_descriptions = $book_grid->display_group_descriptions;
			 $this->display_group_bottom_descriptions = $book_grid->display_group_bottom_descriptions;

			 $this->order_custom = $book_grid->order_custom;
			 $this->set_order_by( $book_grid->order_by );
			 $this->set_default_height( $book_grid->default_height );
			 $this->custom_height = $book_grid->custom_height;

			 $this->set_cover_height();

			 //	$this->get_book_list();
		 }

	 }

	 // if selection isn't set, default to "all"
	 public function get_books() {
		 if ( $this->books == '' ) {
			 $this->books = 'all';
		 }

		 return $this->books;
	 }

	 // if Books, selection, group_by, or order_by change, null out book list
	 public function set_books( $value ) {
		 $this->books     = $value;
		 $this->book_list = null;
	 }

	 public function set_selection( $value ) {
		 $this->selection = $value;
		 $this->book_list = null;
	 }

	 public function set_group_by( $book_grid ) {
		 $x = 1;
		 while ( property_exists( $book_grid, 'group_by_level_' . $x ) ) {

			 $property = 'group_by_level_' . $x;

			 $this->group_by[ $x ] = $book_grid->{$property};

			 if ( $book_grid->{$property} == 'none' ) {
				 break;
			 }
			 $x ++;
		 }
		 $this->book_list = null;
	 }

	 public function set_order_custom( $value ) {
		 $this->order_custom = $value;
		 $this->book_list    = null;
	 }

	 /**
	  *
	  *  If any of the groups is a series, order by series
	  *  otherwise order by whatever came in
	  *
	  *
	  * @param  [array] $groups list of groups for the grid
	  * @param  [string] $sort   sort setting
	  *
	  * @return sort setting
	  *
	  * @access public
	  * @since  3.0
	  */
	 public function set_order_by( $value ) {

		 if ( in_array( 'series', $this->group_by ) ) {
			 $this->sort     = 'ASC';
			 $this->order_by = 'series_order';
		 } else {
			 if ( $value == '' ) {
				 $this->sort     = 'ASC';
				 $this->order_by = 'post_title';
			 } else {
				 if ( $value == 'custom' ) {
					 $this->order_by = 'custom';
					 $this->sort     = $this->order_custom;
				 } else {
					 if ( substr( $value, - 1, 1 ) == 'D' ) {
						 $this->sort = 'DESC';
					 } else {
						 $this->sort = 'ASC';
					 }

					 $this->order_by = substr( $value, 0, - 1 );
					 if ( $this->order_by == 'pubdate' ) {
						 $this->order_by = 'release_date';
					 }
				 }
			 }
		 }

		 $this->book_list = null;
	 }

	 public function set_default_height( $value ) {
		 $this->default_height = ( $value == 'yes' || $value === true );
	 }

	 public function set_cover_height() {
		 if ( $this->default_height ) {
			 $height = MBDB()->options->book_grid_default_height;
		 } else {
			 $height = $this->custom_height;
		 }
		 if ( $height < 50 ) {
			 $this->cover_height = 50;
		 } else {
			 $this->cover_height = $height;
		 }
	 }

	 public function get_book_list( $check_cache = true ) {
		 if ( $this->book_list == null ) {

			 if ( $this->id != 0 && $check_cache ) {

				 $this->book_list = wp_cache_get( $this->id, 'mbdb_book_grid' );
			 } else {

				 $this->book_list = false;
			 }
			 if ( $this->book_list === false ) {
				 $this->book_list = $this->generate_book_list();
				 if ( $this->id != 0 ) {
					 wp_cache_set( $this->id, $this->book_list, 'mbdb_book_grid' );
				 }
			 }
		 }

		 return $this->book_list;
	 }


	 // FOR BACKWARDS COMPATIBILITY WITH MA ONLY
	 public function set_book_list( $books ) {
		 $this->book_list = $books;
	 }


	 private function validate_group_by_levels() {
		 $levels = count( $this->group_by );

		 // there must be at least one
		 if ( $levels == 0 ) {
			 $this->group_by[1] = 'none';
		 }

		 // default any that aren't valid options to none
		 for ( $x = 1; $x <= $levels; $x ++ ) {
			 if ( ! array_key_exists( $this->group_by[ $x ], MBDB()->book_grid_CPT->group_by_options() ) ) {
				 $this->group_by[ $x ] = 'none';
			 }
		 }

		 // remove duplicates
		 $this->group_by = array_unique( $this->group_by );
		 // reindex but start at index 1
		 $this->group_by = array_combine( range( 1, count( $this->group_by ) ), array_values( $this->group_by ) );
		 // make sure keys are valid
		 //$this->group_by = array_values( $this->group_by );

		 // the last one must be "none"
		 if ( end( $this->group_by ) != 'none' ) {
			 $this->group_by[] = 'none';
		 }
		 reset( $this->group_by );
	 }

	 /****************************************************************************
	  * GET DATA
	  *****************************************************************************/

	 /**
	  *  Get the data and generate output content for the book grid
	  *
	  *
	  *
	  * @return content to be displayed
	  *
	  * @access public
	  * @since  3.4 added book grid id and otpions parameters
	  *
	  * @since  1.0
	  */
	 protected function generate_book_list() {
		 global $post;
		 $content = '';

		 $book_grid_id = $this->id;

		 $current_group = array();

		 $this->validate_group_by_levels();


		 // initialize the current_group to 0
		 // ie, current_group[ 'genre' ] = 0; current_group[ 'series' ] = 0, etc.
		 for ( $x = 1; $x < count( $this->group_by ); $x ++ ) {
			 $current_group[ $this->group_by[ $x ] ] = 0;
		 }

		 if ( in_array( $this->books, array(
			 'series',
			 'genre',
			 'tag',
			 'editor',
			 'illustrator',
			 'cover_artist',
			 'publisher'
		 ) ) ) {
			 // add as first group
			 array_unshift( $this->group_by, $this->books );
		 }


		 // start off the recursion by getting the first group
		 $level = 1;

		 $books = $this->get_group( $level, $current_group );

		 // $books now contains the complete array of books to display in the grid

		 // if the sort is custom, we have to manually sort the books now
		 /* print_r($books);
		 if ($this->order_by == 'custom') {
			 print_r('custom sort!');
				 $sort_order = $this->order_custom;

				 $sorted_books = array();
				 $book_ids = array();
				 foreach ($books as $key => $book) {
					 $book_ids[$book->book_id] = $key;
				 }
				 foreach ($sort_order as $book_id) {
					 $sorted_books[] = $books[$book_ids[$book_id]];

				 }
				 $books = $sorted_books;

	  } */


		 return $books;

	 }

	 /**
	  *  Return one group of books for the grid
	  *  This is called recursively until the group "none" is found
	  *
	  * @param  [int] $level         the nested level of the grid we're currently one
	  * @param  [array] $groups       the groups in grid
	  * @param  [array] $current_group  the id of the current group. Could be if of a
	  *                                 series, genre, publisher, illustrator, etc.
	  * @param  [string] $selection     what selection of books for the grid ('all',
	  *                                 'unpublished', 'series', etc.)
	  * @param  [array] $selected_ids  ids of the selection
	  * @param  [string] $sort          represents sort, ie 'titleA', 'titleD',
	  *                                 'series, etc.
	  * @param  [array] $book_ids      optional list of book_ids to filter by, useful
	  *                                 for add-on plugins to add on to grid (ie MA)
	  *
	  * @return [array] books for this group
	  *
	  * @access public
	  * @since  1.0
	  * @since  3.0 re-factored
	  *
	  */
	 public function get_group( $level, $current_group, $book_ids = array() ) {
		 $groups       = $this->group_by;
		 $selection    = $this->books;
		 $selected_ids = $this->selection;


		 do_action( 'mbdb_book_grid_pre_get_group', $level, $groups, $current_group, $selection, $selected_ids, $this->order_by, $book_ids );

		 $books      = array();
		 $taxonomies = get_object_taxonomies( 'mbdb_book', 'objects' );
		 $tax_names  = array_keys( $taxonomies );


		 switch ( $groups[ $level ] ) {
			 // break the recursion by actually getting the books
			 case 'none':

				 $books = new MBDB_Book_List( $selection, $this->order_by, $this->sort, $selected_ids, $current_group, apply_filters( 'mbdb_book_grid_book_ids_filter', $book_ids, $books, $level, $groups, $current_group, $selection, $selected_ids, $this->order_by, $book_ids, $this ) );

				 break;
			 case 'publisher':
				 $books = $this->get_books_by_publisher( $level, $current_group, $book_ids );
				 break;
			 default:
				 // see if it's a taxonomy
				 // don't just assume it's a taxonomy because it could be
				 // that there's an add-on plugin (ie MA) that's added
				 // a new group
				 if ( in_array( 'mbdb_' . $groups[ $level ], $tax_names ) ) {
					 $books = $this->get_books_by_taxonomy( $level, $current_group, $book_ids );
				 }
		 }

		 do_action( 'mbdb_book_grid_post_get_group', $level, $groups, $current_group, $selection, $selected_ids, $this->order_by, $book_ids, $this );

		 $books = apply_filters( 'mbdb_book_grid_get_group_books', $books, $level, $groups, $current_group, $selection, $selected_ids, $this->order_by, $book_ids, $this );

		 return $books;
	 }

	 /**
	  *  Get books by publisher
	  *
	  * @param  [int] $level         the nested level of the grid we're currently one
	  * @param  [array] $groups       the groups in grid
	  * @param  [array] $current_group  the id of the current group. Could be if of a
	  *                                 series, genre, publisher, illustrator, etc.
	  * @param  [string] $selection     what selection of books for the grid ('all',
	  *                                 'unpublished', 'series', etc.)
	  * @param  [array] $selected_ids  ids of the selection
	  * @param  [string] $sort          represents sort, ie 'titleA', 'titleD',
	  *                                 'series, etc.
	  * @param  [array] $book_ids      optional list of book_ids to filter by, useful
	  *                                 for add-on plugins to add on to grid (ie MA)
	  *
	  * @return array of books
	  *
	  * @access public
	  * @since
	  */
	 protected function get_books_by_publisher( $level, $current_group, $book_ids ) {

		 $groups = $this->group_by;


		 $books = array();

		 // Get ones w/o publishers first
		 $current_group[ $groups[ $level ] ] = - 1;

		 // recursively get the next nested group of books
		 $results = $this->get_group( $level + 1, $current_group, $book_ids );

		 // only return results if are any so that headers of empty groups
		 // aren't displayed
		 if ( count( $results ) > 0 ) {
			 $books[ apply_filters( 'mbdb_book_grid_no_publisher_heading', __( 'No Publisher Specified', 'mooberry-book-manager' ) ) ] = $results;
		 }

		 // TO DO
		 // loop through each publisher
		 // and recursively get the next nested group of books for that publisher
		 //$mbdb_options = get_option('mbdb_options');
		 //$mbdb_options = get_option('mbdb_options'); //mbdb_get_options('mbdb_options');//'mbdb_options');
		 //if (array_key_exists('publishers', $mbdb_options)) {
		 //$publishers = $mbdb_options['publishers'];
		 //$publishers = MBDB()->options->publishers;
		 $publishers = MBDB()->helper_functions->get_publishers();
		 foreach ( $publishers as $publisher ) {
			 $current_group[ $groups [ $level ] ] = $publisher->id;
			 $results                             = $this->get_group( $level + 1, $current_group, $book_ids );

			 // only return results if are any so that headers of empty groups
			 // aren't displayed
			 if ( count( $results ) > 0 ) {
				 $books[ apply_filters( 'mbdb_book_grid_heading', __( 'Published by', 'mooberry-book-manager' ) . ' ' . $publisher->name ) ] = $results;
			 }
		 }

		 //}
		 return $books;
	 }

	 /**
	  *  Get books by taxonomy
	  *
	  * @param  [int] $level         the nested level of the grid we're currently one
	  * @param  [array] $groups       the groups in grid
	  * @param  [array] $current_group  the id of the current group. Could be id of a
	  *                                 series, genre, publisher, illustrator, etc.
	  * @param  [string] $selection     what selection of books for the grid ('all',
	  *                                 'unpublished', 'series', etc.)
	  * @param  [array] $selected_ids  ids of the selection
	  * @param  [string] $sort          represents sort, ie 'titleA', 'titleD',
	  *                                 'series, etc.
	  * @param  [array] $book_ids      optional list of book_ids to filter by, useful
	  *                                 for add-on plugins to add on to grid (ie MA)
	  *
	  * @return array of books
	  *
	  * @access public
	  * @since
	  */
	 protected function get_books_by_taxonomy( $level, $current_group, $book_ids ) {

		 $groups       = $this->group_by;
		 $selection    = $this->books;
		 $selected_ids = $this->selection;

		 $books = array();

		 // Get ones not in the taxonomy first
		 // unless we are filtering by the same taxonomy
		 //if ( $selection != $groups[ $level ] ) {
		 $current_group[ $groups[ $level ] ] = - 1;


		 // recursively get the next nested group of books
		 $results = $this->get_group( $level + 1, $current_group, $book_ids );

		 // only return results if are any so that headers of empty groups
		 // aren't displayed
		 $empty = '';
		 if ( count( $results ) > 0 ) {
			 switch ( $groups[ $level ] ) {
				 case 'genre':
					 $empty = apply_filters( 'mbdb_book_grid_uncategorized_heading', __( 'Uncategorized', 'mooberry-book-manager' ) );
					 break;
				 case 'series':
					 $empty = apply_filters( 'mbdb_book_grid_standalones_heading', __( 'Stand-Alone Books', 'mooberry-book-manager' ) );
					 break;
				 case 'tag':
					 $empty = apply_filters( 'mbdb_book_grid_untagged_heading', __( 'Untagged', 'mooberry-book-manager' ) );
					 break;
				 case 'editor':
					 $empty = apply_filters( 'mbdb_book_grid_uncategorized_heading', __( 'No Editor Specified', 'mooberry-book-manager' ) );
					 break;
				 case 'illustrator':
					 $empty = apply_filters( 'mbdb_book_grid_uncategorized_heading', __( 'No Illustrator Specified', 'mooberry-book-manager' ) );
					 break;
				 case 'cover_artist':
					 $empty = apply_filters( 'mbdb_book_grid_uncategorized_heading', __( 'No Cover Artist Specified', 'mooberry-book-manager' ) );
					 break;
				 default:
					 $taxonomy = get_taxonomy( 'mbdb_' . $groups[ $level ] );
					 $empty    = sprintf( __( 'No %s Specified', 'mbm-custom-fields' ), $taxonomy->labels->singular_name );
			 }
			 $empty           = apply_filters( 'mbdb_book_grid_empty_tax_group_heading', $empty, $groups[ $level ] );
			 $books[ $empty ] = $results;
		 }
		 //}
		 // loop through each term
		 // and recursively get the next nested group of books for that term
		 $terms_query = array(
			 'orderby'    => 'slug',
			 'hide_empty' => true
		 );

		 // if we're grouping by what we're filtering by, only get terms that we're filtering on
		 if ( $groups[ $level ] == $selection ) {
			 $terms_query['include'] = $selected_ids;
		 }

		 $all_terms = get_terms( 'mbdb_' . $groups[ $level ], $terms_query );
		 $taxonomy  = get_taxonomy( 'mbdb_' . $groups[ $level ] );

		 // loop through all the terms
		 foreach ( $all_terms as $term ) {
			 $current_group[ $groups[ $level ] ] = $term->term_id;

			 $results = $this->get_group( $level + 1, $current_group, $book_ids );

			 // only return results if are any so that headers of empty groups
			 // aren't displayed
			 if ( count( $results ) > 0 ) {
				 /*
				 if (in_array($groups[$level], array('genre', 'series', 'tag'))) {
					 $heading = $term->name . ' ' . $taxonomy->labels->singular_name;
				 } else {
					 */
				 //$heading = $taxonomy->labels->singular_name . ': ' . $term->name;
				 $heading = sprintf( _x( '%1$s: %2$s', '%1$s is the taxonomy name (Genre, Tag, etc.) and %2$s is the item ( romance, mystery, etc. )', 'mooberry-book-manager' ), $taxonomy->labels->singular_name, $term->name );
				 //	}
				 $filtered_heading = apply_filters( 'mbdb_book_grid_heading', $heading );
				 $books[   $filtered_heading ] = $results;

				 // get descriptions
				 if (array_key_exists($level, $this->display_group_descriptions) ) {
					 $this->group_descriptions[ $filtered_heading ]        = $this->display_group_descriptions[ $level ] == 'yes' ? get_term_meta( $term->term_id, 'mbdb_tax_grid_description', true ) : '';
					 $this->group_bottom_descriptions[ $filtered_heading ] = $this->display_group_bottom_descriptions[ $level ] == 'yes' ? get_term_meta( $term->term_id, 'mbdb_tax_grid_description_bottom', true ) : '';
				 }
			 }
		 }

		 return $books;
	 }

	 protected function get_wysiwyg_output( $content ) {
		global $wp_embed;

		$content = $wp_embed->autoembed( $content );
		$content = $wp_embed->run_shortcode( $content );
		$content = wpautop( $content );
		$content = do_shortcode( $content );


		return $content;
	}

	 /**
	  * Magic __get function to dispatch a call to retrieve a private property
	  *
	  * @since 1.0
	  */
	 public function __get( $key ) {

		 if ( method_exists( $this, 'get_' . $key ) ) {

			 return call_user_func( array( $this, 'get_' . $key ) );

		 } else {

			 if ( property_exists( $this, $key ) ) {

				 $ungettable_properties = array( 'db_object' );

				 if ( ! in_array( $key, $ungettable_properties ) ) {

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
	 public function __set( $key, $value ) {

		 if ( method_exists( $this, 'set_' . $key ) ) {

			 return call_user_func( array( $this, 'set_' . $key ), $key, $value );

		 } else {

			 if ( property_exists( $this, $key ) ) {

				 $unsettable_properties = array( 'db_object', 'group_by', 'order_by', 'sort', 'book_list' );

				 if ( ! in_array( $key, $unsettable_properties ) ) {

					 $this->$key = $value;

				 }

			 }
		 }

		 return new WP_Error( 'mbdb-invalid-property', sprintf( __( 'Can\'t get property %s', 'mooberry-book-manager' ), $key ) );

	 }


	 public function display_grid() {
		 //	print_r($this);
		 // figure out the best size
		 $heights     = array();
		 $image_sizes = get_intermediate_image_sizes();
		 //print_r($image_sizes);
		 global $_wp_additional_image_sizes;

		 foreach ( $image_sizes as $image_size ) {

			 if ( $image_size == 'thumbnail' || $image_size == 'post-thumbnail' ) {
				 continue;
			 }
			 if ( in_array( $image_size, array( 'medium', 'large' ) ) ) {
				 $heights[ $image_size ] = get_option( $image_size . '_size_h' );
			 } elseif ( isset( $_wp_additional_image_sizes[ $image_size ] ) ) {
				 $heights[ $image_size ] = $_wp_additional_image_sizes[ $image_size ]['height'];
			 }
		 }

		 asort( $heights );

		 $this->wp_size = '';
		 foreach ( $heights as $this->wp_size => $height ) {

			 if ( $height >= $this->cover_height ) {
				 //&& $widths[$this->wp_size] <= $height ) {
				 if ( ( ! array_key_exists( $this->wp_size, $_wp_additional_image_sizes ) ) || ( array_key_exists( $this->wp_size, $_wp_additional_image_sizes ) && ! $_wp_additional_image_sizes[ $this->wp_size ]['crop'] ) ) {
					 break;
				 }
			 }
		 }

		 if ( $this->wp_size == '' ) {
			 $this->wp_size = 'large';
		 }

		 // start going through the levels
		 if ( $this->book_list == null ) {
			 $this->get_book_list();
		 }

		 return $this->display_grid_level( $this->book_list, 0 );

	 }


	 /**
	  *  Loop through the $books array and generate the HTML output for the
	  *  grid, including printing out the headings and indenting at each
	  *  nested level
	  *
	  *  Recursively called for each level
	  *
	  * @param  [array] $mbdb_books nested array of books in grid
	  * @param  [int] $l           current level to display
	  *
	  * @return [string] html
	  *
	  * @access public
	  * @since  3.4 added book_grid_id and cover_height parameters
	  *
	  * @since  1.0
	  * @since  2.0 made responsive
	  * @since  3.0 re-factored
	  */
	 //TO DO: re-write for book object, settings object,
	 private function display_grid_level( $mbdb_books, $l ) {


		 // indent the grid by 15px per depth level of the array
		 do_action( 'mbdb_book_grid_pre_div', $l );

		 $content = '<div id="mbdb_book_grid_' . $this->id . '" class="mbm-book-grid-div" style="padding-left:' . ( 15 * $l ) . 'px;">';

		 if ( $mbdb_books == null || count( $mbdb_books ) == 0 ) {
			 // No books found at this level
			 do_action( 'mbdb_book_grid_no_books_found' );
			 $content .= apply_filters( 'mbdb_book_grid_books_not_found', __( 'Books not found', 'mooberry-book-manager' ) );
		 } else {
			 // if mbdb_books is a book list then there is no groupings or headings
			 if ( gettype( $mbdb_books ) == 'object' && get_class( $mbdb_books ) == 'MBDB_Book_List' ) {
				 $content .= $this->display_book_list( $mbdb_books );
			 } else {
				 // if we're there there are groupings we need to loop thru recursively
				 // loop through each book
				 foreach ( $mbdb_books as $label => $book_list ) {

					 // If a label is set and there's at least one book, print the label
					 if ( $label && count( $book_list ) > 0 ) {
						 // set the heading level based on the depth level of the array
						 do_action( 'mbdb_book_grid_pre_heading', $l, $label );
						 $content = apply_filters( 'mbdb_book_grid_pre_heading_content', $content, $l, $label );
						 // start the headings at H2
						 $heading_level = $l + 2;
						 // Headings can only go to H6
						 if ( $heading_level > 6 ) {
							 $heading_level = 6;
						 }
						 // display the heading
						 $content .= '<h' . $heading_level . ' class="mbm-book-grid-heading' . ( $l + 1 ) . '">' . esc_html( $label ) . '</h' . $heading_level . '>';
						 do_action( 'mbdb_book_grid_post_heading', $l, $label );
						 $content = apply_filters( 'mbdb_book_grid_post_heading_content', $content, $l, $label );
					 }

					 if ( array_key_exists( $label, $this->group_descriptions ) ) {
						  $description = $this->get_wysiwyg_output($this->group_descriptions[ $label ] );
						 $content .= '<div class="mbm-book-grid-description mbm-book-grid-description-' . ($l + 1) . '">' . $description . '</div>';
						 $content = apply_filters( 'mbdb_book_grid_post_description_content', $content, $l, $this->group_descriptions[ $label ] );
					 }

					 if ( gettype( $book_list ) == 'array' ) {
						 // nested list. Do recursion
						 do_action( 'mbdb_book_grid_pre_recursion', $book_list, $l + 1 );
						 $content .= $this->display_grid_level( $book_list, $l + 1 );
						 do_action( 'mbdb_book_grid_post_recursion', $book_list, $l + 1 );
					 } else {
						 // this breaks the recursion
						 if ( gettype( $book_list ) == 'object' && get_class( $book_list ) == 'MBDB_Book_List' ) {
							 $content .= $this->display_book_list( $book_list );

						 }
					 }
					 if ( array_key_exists( $label, $this->group_bottom_descriptions ) ) {
						 $bottom_description = $this->get_wysiwyg_output($this->group_bottom_descriptions[ $label ] );
						 $content .= '<div class="mbm-book-grid-bottom-description mbm-book-grid-bottom-description-' . ($l + 1) . '">' . $bottom_description . '</div>';
						 $content = apply_filters( 'mbdb_book_grid_post_bottom_description_content', $content, $l, $this->group_bottom_descriptions[ $label ] );
					 }
				 }
			 }
		 }
		 $content .= '</div>';
		 do_action( 'mbdb_book_grid_post_div', $l );

		 /*
		 // find all the book grid's postmeta so we can display it in comments for debugging purposes
		 $grid_values = array();
		 foreach ($mbdb_book_grid_meta_data as $key => $data) {
			 if ( substr($key, 0, 5) == '_mbdb' ) {
				 $grid_values[$key] = $data[0];
			 }
		 }
		 $content = '<!-- Grid Parameters:
					 ' . print_r($grid_values, true) . ' -->' . $content;

	 */

		 return apply_filters( 'mbdb_book_grid_content', $content, $l, $this );
	 }


	 private function display_book_list( $mbdb_books ) {
		 //print_r($this->books_per_page);

		 if ( $this->books_per_page == 0 ) {
			 $max_books = count( $mbdb_books->books );
		 } else {
			 $max_books = $this->current_page * $this->books_per_page;
		 }

		 $content = '';
		 for ( $x = ( $this->current_page - 1 ) * $this->books_per_page; $x < $max_books; $x ++ ) {
			 if ( array_key_exists( (int) $x, $mbdb_books->books ) ) {

				 $content .= $this->output_book( $mbdb_books->books[ $x ] );
			 }
		 }

		 return $content;
	 }

	 protected function get_book_link( $book ) {
		 global $post;
		 $page_id = isset( $_POST['page_id'] ) ? intval( $_POST['page_id'] ) : $post->ID;

		 $link = get_permalink( $book->id );
		 if ( MBDB()->options->show_back_to_grid_link == 'yes' ) {
			 if ( stripos( $link, '?' ) === false ) {
				 $link .= '?';
			 } else {
				 $link .= '&';
			 }

			 $link .= 'grid_referrer=' . $page_id; //$post->ID;

			 if ( $page_id == MBDB()->options->tax_grid_page ) {
				 $link .= '&taxonomy=' . $this->books . '&term=' . $this->selection[0];
			 }
		 }

		 return $link;
	 }

	 /**
	  *  Generate the HTML to display a book and its cover image
	  *  coming soon object passed as parameter because it's stored in
	  *  the options and this function is called several times
	  *
	  * @param  [obj] $book              book object
	  * @param  [string] $coming_soon_image coming soon image
	  *
	  * @return [string]  html output
	  *
	  * @access public
	  * @since  3.4 added book grid id and height parameters and cover height into specific HTML tags
	  *
	  * @since  1.0
	  * @since  2.0 made responsive
	  * @since  3.0 re-factored, added alt text
	  */
	 protected function output_book( $book ) {


		 $image_size                  = $this->wp_size;
		 $mbdb_book_grid_cover_height = $this->cover_height;

		 $extra_height = intval( apply_filters( 'mbdb_book_grid_additional_height', 50, $this ) );

		 $content = '<span itemscope itemtype="http://schema.org/Book"  class="mbdb_float_grid" style="height: ' . ( intval( $mbdb_book_grid_cover_height ) + $extra_height ) . 'px; width: ' . $mbdb_book_grid_cover_height . 'px;">';

		 $cover = $book->get_cover_url( $image_size, 'grid' );

		 $alt = MBDB()->helper_functions->get_alt_attr( $book->cover_id, __( 'Book Cover:', 'mooberry-book-manager' ) . ' ' . $book->title );

		$link = $this->get_book_link($book);
		 if ( isset( $cover ) ) {
			 if ( ! $book->has_cover() ) {
				 $filter = '_placeholder';
			 } else {
				 $filter = '';
			 }

			 $content .= '<div class="mbdb_grid_image">';
			 $content = apply_filters( 'mbdb_book_grid_pre' . $filter . '_image', $content, $book->id, $cover, $book, $this->id );
			  $content .= MBDB()->helper_functions->get_popup_card_html($book);
			  $cover_image = '<a id="book_' . $book->id . '" itemprop="mainEntityOfPage" class="mbm-book-grid-title-link" href="' . esc_url( $link ) . '"><img itemprop="image" style="height: ' . $mbdb_book_grid_cover_height . 'px;" src="' . esc_url( $cover ) . '" ' . $alt . ' data-book="' . $book->id . '" /></a>';
			 $content .= MBDB()->helper_functions->maybe_add_ribbon( $cover_image, $book, 'grid', $this->wp_size, $mbdb_book_grid_cover_height );
			 $content = apply_filters( 'mbdb_book_grid_post' . $filter . '_image', $content, $book->id, $cover, $book );
			 $content .= '</div>';
		 } else {
			 $content .= '<div class="mbdb_grid_no_image" style="height: ' . $mbdb_book_grid_cover_height . 'px; width: ' . $mbdb_book_grid_cover_height . ';">';
			 $content = apply_filters( 'mbdb_book_grid_no_image', $content, $book->id, $book );
			 $content .= '</div>';
		 }

		 //'<meta itemprop="name" content="' . esc_attr($book->title) . '">
		 $content .= '<span class="mbdb_grid_title" itemprop="name">';
		 $content = apply_filters( 'mbdb_book_grid_pre_link', $content, $book->id, $book->title, $book );
		 $content .= '<a itemprop="mainEntityOfPage" class="mbm-book-grid-title-link" href="' . esc_url( $link ) . '">' . esc_html( $book->title ) . '</a>';
		 $content = apply_filters( 'mbdb_book_grid_post_link', $content, $book->id, $book->title, $book );
		 $content .= '</span></span>';


		 return  $content;
	 }

 }
