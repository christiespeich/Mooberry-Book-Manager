<?php

class MBDB_DB_Books extends MBDB_DB_CPT {
		
	public function __construct() {
		$this->primary_key = 'book_id';
		$this->post_type = 'mbdb_book';
		$this->version = MBDB_PLUGIN_VERSION; //'3.0';
		
		parent::__construct( 'mbdb_books' );
		

	}
	
	protected function get_columns() {
		return array(
			'book_id' => '%d',
			'subtitle' => '%s',
			'summary' => '%s',
			'excerpt' => '%s',
			'additional_info' => '%s',
			'cover_id' => '%d',
			'cover' => '%s',
			'release_date' => '%s',
			'publisher_id' => '%s',
			'goodreads' => '%s',
			'series_order' => '%d',
		);
	}
	
	public function map_postmeta_to_columns() {
		return array(
			'_mbdb_summary' => 'summary',
			'_mbdb_excerpt' => 'excerpt',
			'_mbdb_additional_info' => 'additional_info',
			'_mbdb_subtitle' => 'subtitle',
			'_mbdb_cover' => 'cover',
			'_mbdb_cover_id' => 'cover_id',
			'_mbdb_pubdate' => 'release_date',
			'_mbdb_publisherID' => 'publisher_id',
			'_mbdb_goodreads' => 'goodreads',
			'_mbdb_series_order' => 'series_order',
		);
	}
	
	public function columns_with_html() {
		return array(
			'summary',
			'excerpt',
			'additional_info',
		);
	}
	
	private function get_sort_fields( $sort ) {
		switch ($sort) {
			case 'titleA':
				$sort_fields = array ( 'post_title', 'ASC' );
				break;
			case 'titleD':
				$sort_fields =  array ( 'post_title', 'DESC' );
				break;
			case 'pubdateA':
				$sort_fields =  array ( 'release_date', 'ASC' );
				break;
			case 'pubdateD':
				$sort_fields =  array ( 'release_date', 'DESC' );
				break;
			case 'series_order':
				$sort_fields =  array ( 'series_order', 'ASC' );
				break;
			default:
				$sort_fields =  array ( null, null );
		}
		return apply_filters('mbdb_book_sort_fields', $sort_fields, $sort);
	}
	
	
	public function get_ordered_selection( $selection, $selection_ids, $sort, $book_ids = null, $taxonomy = null ) {
	
		// selection_ids = book_ids if selection = "custom"
		// 				 = tax_ids if selection is a taxonomy
		//				 = publisher_ids if selection = "publisher"
		//				 should be null otherwise
		
		// book_ids is an optional, additional filtering of book ids
		
		// taxonmy:  array of taxonomy and id(s) to filter on. Also includes publisher. 
		// 		Examples:
		//		{ series => 24 }
		//		{	genre => { 20, 21 } }
		//		{	publisher => 15 }  // publisher is expected to be a single value. If it's an array, only the 1st is selected
		
		// $sort = titleA, titleD, pubdateA, pubdateD, series_order
		//			OR { field, direction } ie { release_date, DESC }
		
		
		
		// validate inputs
		
		// SORT VARIABLES
		
		//if an array is passed in, separate into field, direction
		if ( is_array($sort) ) {
			list( $sort, $order ) = $sort;
		} else {
			// otherwise, set field, direction based on value
			list( $sort, $order ) = $this->get_sort_fields( $sort );
		}
		
		// if getting books by series, the sort should be by series aascending
		if ($taxonomy == null && $selection == 'series')  {
			$sort = 'series_order';
			$order = 'ASC';
		} else {
			if ($taxonomy != null && array_key_exists('series', $taxonomy)) {
				$sort = 'series_order';
				$order = 'ASC';
			} 
		}
		
		// ensure that the sort field is a column in the table
		// and that the direction is either ASC or DESC
		$sort = $this->validate_orderby( $sort );
		$order = $this->validate_order( $order );
		
		
		// SELECTION VARIABLES 
		
		// default to all books
		$book_selection_options = mbdb_book_grid_selection_options();
		if ( ! array_key_exists( $selection, $book_selection_options ) ) {
			$selection = 'all';
		}
			
		
		$taxonomies = array('genre', 'series', 'tag', 'illustrator', 'editor', 'cover_artist');
		// if custom, genre, series, tag, illustrator, editor, cover artist, or publisher and no selection ids are passed, default to all books
		// otherwise if selection ids is not an array, make it an array
		
		if ( in_array( $selection, array_merge( array('custom', 'publisher'), $taxonomies) ) ) {
			if ($selection_ids == null || $selection_ids == '') {
				$selection =  'all';
			} else {
				if (!is_array($selection_ids)) {
					$selection_ids = array($selection_ids);
				}
			}
		}
		
		
		// TAXONOMY ARRAY
		
		// if taxonomy is supplied, the keys must be one of the options
		if ($taxonomy) {
			$tax_options = array_keys(mbdb_book_grid_group_by_options());
			foreach($taxonomy as $tax => $tax_ids) {
				if ( ! in_array( $tax, $tax_options ) ) {
					unset($taxonomy[$tax]);
				}
			}
			if ( count($taxonomy) == 0 ) {
				$taxonomy = null;
			}
		}
		

		
		$select = 'SELECT DISTINCT ';
		$join = ' JOIN ' . $this->prefix() . 'posts p ON p.id = b.book_id ';
		$where = ' WHERE p.post_status = "publish" ';
		$orderby = ' ORDER BY ';
		
		// if book_ids are sent, filter by them
		if ( $book_ids != null ) {
			if ( ! is_array( $book_ids ) ) {
				$book_ids = array( $book_ids );
			}
			$book_ids = array_map ('absint', $book_ids);
			$where .= ' AND (book_id in (' . implode(', ', $book_ids) . ') ) ';
		}
		
		// set the where clause
		switch ($selection) {
			case 'all':
				// no change
				// this is included only so it doesn't fall into the "default"
				$where .= '';
				break;
			case 'published':
				$where .= ' AND ( release_date <= CURRENT_DATE() ) ';
				break;
			case 'unpublished':
				$where .= ' AND ( release_date > CURRENT_DATE() OR release_date IS NULL ) ';
				break;
			case 'custom':
				$selection_ids = array_map('absint', $selection_ids);
				$where .= ' AND (book_id in (' . implode(', ', $selection_ids) . ') ) ';
				break;
			case 'publisher':
				$selection_ids = array_map('esc_sql', $selection_ids);
				$where .= ' AND ( b.publisher_id in ( "' . implode('", "', $selection_ids) . '" ) ) ';
				break;
			default:
				// anything else is a taxonomy, a type handled by another add-on, or an 
				// invalid input
				// if it's a taxonomy, add where and join
				if (in_array($selection, $taxonomies) ) {
					$selection_ids = array_map('absint', $selection_ids);
					$where .= ' AND ( tt.taxonomy = "mbdb_' . $selection . '" 
									AND tt.term_id in ( ' . implode(', ', $selection_ids) . ' ) 
									AND p.post_type = "mbdb_book" ) ';
					$join .= ' JOIN ' . $this->prefix() . 'term_relationships AS tr ON tr.object_id = b.book_id 
								JOIN ' . $this->prefix() . 'term_taxonomy AS tt  ON tt.term_taxonomy_id = tr.term_taxonomy_id ';
				}
				break;
		}
		
		// add in taxonomy filtering
		if ($taxonomy != null) {
			$tax_level = 2;
			foreach($taxonomy as $tax => $tax_ids) {
					switch ($tax) {
						case 'none':
							// no additional filtering needed, this is the innermost level
							break 2;
						case 'publisher':
							//if -1 then get books that don't have a publisher
							if ($tax_ids == -1) {
								$select .= '"" AS name' . $tax_level . ', ';
								$where .= ' AND (b.publisher_id IS NULL) ';
							} else {
								if ( is_array($tax_ids) ) {
									$tax_ids = $tax_ids[0];
								} 
								$publisher = mbdb_get_publisher_info( $tax_ids );
								if ($publisher != null && array_key_exists('name', $publisher) ) {
										$select .= '"' . $publisher['name'] . '" as name' . $tax_level . ', ';
										$where .= ' AND (b.publisher_id ="' . esc_sql($tax_ids) . '") ';
								}
								
							}
							break;
						// anything left is a taxonomy
						default:
							if (in_array($tax, $taxonomies) ) {
								// if -1 then get books that are NOT in any of this taxonomny
								if ($tax_ids == -1) {
									$select .=  ' "" AS name' . $tax_level . ', ';
									$where .= ' and b.book_id not in (select book_id from ' . $this->table_name . ' as b 
																		join ' . $this->prefix() . 'term_relationships as tr3 on tr3.object_id = b.book_id 
																		join ' . $this->prefix() . 'term_taxonomy tt3 on tt3.term_taxonomy_id = tr3.term_taxonomy_id 
																		where tt3.taxonomy = "mbdb_' . $tax . '" ) ';
								} else {	
									if (!is_array($tax_ids)) {
										$tax_ids = array($tax_ids);
									}
									$tax_ids = array_map('absint', $tax_ids);
									$select .= 't' . $tax_level . '.name AS name' . $tax_level . ', ';
									$where .= ' AND (tt' . $tax_level . '.taxonomy = "mbdb_' . $tax . '" AND tt' . $tax_level . '.term_id in (' . implode(',', $tax_ids) . ') ) ';
									$join .= ' JOIN ' . $this->prefix() . 'term_relationships AS tr' . $tax_level . ' ON tr' . $tax_level . '.object_id = b.book_id 
												JOIN ' . $this->prefix() . 'term_taxonomy AS tt' . $tax_level . '  ON tt' . $tax_level . '.term_taxonomy_id = tr' . $tax_level . '.term_taxonomy_id 
												JOIN ' . $this->prefix() . 'terms AS t' . $tax_level . ' ON t' . $tax_level . '.term_id = tt' . $tax_level . '.term_id';
								}
							}
						break;
					}
				$tax_level++;
			}
		}
		// set the order
		switch ($sort) {
			case 'release_date':
				// sort null dates last
				$orderby .= ' CASE WHEN release_date IS NULL THEN 1 ELSE 0 END, release_date ';
				break;
			case 'series_order':
				// sort null orders last
				$orderby .= ' CASE WHEN series_order IS NULL THEN 999 ELSE 0 END, series_order ';
				break;
			default:
				$orderby .= ' post_title ';
				break;
			
		}
		
		
		
		$select = apply_filters('mbdb_book_get_ordered_selection_select', $select);
		$join = apply_filters('mbdb_book_get_ordered_selection_join', $join);
		$where = apply_filters('mbdb_book_get_ordered_selection_where', $where, $selection_ids, $selection, $book_ids);
		$orderby = apply_filters('mbdb_book_get_ordered_selection_orderby', $orderby, $sort, $order);
		
	
		$sql = "$select b.book_id, p.post_title, b.cover, b.release_date, b.cover_id FROM  $this->table_name  as b  $join $where $orderby $order ";
		
		$books =  $this->run_sql( $sql );
		return apply_filters('mbdb_book_get_ordered_selection', $books, $selection, $selection_ids, $sort, $order, $taxonomy, $book_ids );
	

	}	
	
	
	
/****************************************************************
 *  			SEARCHING
 *  
 ****************************************************************/
 

public function search_where( $where ) {
	global $wpdb;	
	if( is_search() ) {
		$where = preg_replace(
		   "/\([^(]*post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
		   "(" . $wpdb->posts . ".post_title LIKE $1) OR ( " . $this->table_name . ".subtitle LIKE $1 ) OR (
		   " . $this->table_name . ".excerpt LIKE $1) OR (
		   " . $this->table_name . ".summary LIKE $1) OR (" . $this->table_name .".additional_info LIKE $1) ", $where);
	
	}
	
	return $where;
}



	public function create_table() {
		
		// Needed for dbDelta
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		
		global $charset_collate;
		
		$sql_create_table = "CREATE TABLE " . $this->table_name . " (
			  book_id bigint(20) unsigned NOT NULL,
			  subtitle varchar(100),
			  summary longtext,
			  excerpt longtext,
			  additional_info longtext,
			  cover_id bigint(20) unsigned,
			  cover longtext,
			  release_date date,
			  publisher_id char(13),
			  goodreads longtext,
			  series_order tinyint unsigned,
			  PRIMARY KEY  (book_id),
			  KEY release_date (release_date)
		 ) $charset_collate; ";
	 
		dbDelta( $sql_create_table );
		
		update_option( $this->table_name . '_db_version', $this->version );
		
	}
}