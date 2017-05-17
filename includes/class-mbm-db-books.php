<?php

class MBDB_DB_Books extends MBDB_DB_CPT {
	
	public function __construct() {
		
		$this->primary_key = 'book_id';
		$this->post_type = 'mbdb_book';
		$this->version = MBDB_PLUGIN_VERSION; //'3.0';
		
		parent::__construct( 'mbdb_books' );
		
		$this->taxonomies = array( 
			'mbdb_genre',
			'mbdb_series',
			'mbdb_tag',
			'mbdb_editor',
			'mbdb_cover_artist',
			'mbdb_illustrator',
		);
		
	
	}
	

	
	protected function get_columns() {
		return array(
			'book_id' => '%d',
			'subtitle' => '%s',
			'summary' => '%s',
			'excerpt' => '%s',
			'excerpt_type'	=>	'%s',
			'kindle_preview'	=>	'%s',
			'additional_info' => '%s',
			'cover_id' => '%d',
			'cover' => '%s',
			'release_date' => '%s',
			'publisher_id' => '%s',
			'goodreads' => '%s',
			'series_order' => '%f',
			//'blog_id'	=> '%d',
		);
	}
	
	public function map_postmeta_to_columns() {
		return array(
			'_mbdb_summary' => 'summary',
			'_mbdb_excerpt' => 'excerpt',
			'_mbdb_excerpt_type'	=>	'excerpt_type',
			'_mbdb_kindle_preview'	=>	'kindle_preview',
			'_mbdb_additional_info' => 'additional_info',
			'_mbdb_subtitle' => 'subtitle',
			'_mbdb_cover' => 'cover',
			'_mbdb_cover_id' => 'cover_id',
			'_mbdb_published' => 'release_date',
			'_mbdb_publisherID' => 'publisher_id',
			'_mbdb_goodreads' => 'goodreads',
			'_mbdb_series_order' => 'series_order',
		);
	}
	
	public function postmeta_fields() {
		return array(
			'_mbdb_reviews',
			'_mbdb_editions',
			'_mbdb_buy_links',
			'_mbdb_download_links',
		);
	}
	
	protected function columns_with_html() {
		return array(
			'summary',
			'excerpt',
			'additional_info',
			'kindle_preview',
		);
	}
	

	
	// gets all data for a book
	// only this class knows how everything is stored in the database
	// so when another class needs a book, we need to get all the info 
	// and return it as an object
	// book_id can be id or slug
	public function get( $book_id, $cache_results = false ) {
		//print_r('pulling from database: ' . $book_id);
		// if book_id isn't an integer, get book by slug
		
		if ( !is_numeric($book_id)  ) {
			$book = $this->get_by_slug( $book_id, $cache_results );
			$book_id = $book->ID;
		} else {
			$book = parent::get( $book_id, $cache_results );
		}
		
		if ($book != null ) {
			$book->editions = $this->get_editions( $book_id );
			$book->reviews = $this->get_reviews( $book_id );
			$book->buy_links = $this->get_buy_links( $book_id );
			$book->download_links = $this->get_download_links( $book_id );
			$book->genres = $this->get_genres( $book_id );
			$book->series = $this->get_series( $book_id );
			$book->tags = $this->get_tags( $book_id );
			$book->editors = $this->get_editors( $book_id );
			$book->cover_artists = $this->get_cover_artists( $book_id );
			$book->illustrators = $this->get_illustrators( $book_id );
		}
		return $book;
	}
	
	public function get_editions ( $book_id ) {
		// why not just call the database directly? in case the underlying
		// database structure changes. Let get_data deal with figuring out 
		// where to get _mbdb_editions from
		$editions = $this->get_data( '_mbdb_editions', $book_id );
		
		if ( !is_array($editions) ) {
			return array();
		}
		
		// WHY do this??  So that in case the underlying database structure of
		// _mbdb_editions changes, the returning array still has the same keys
		// this way only this function has to change and the other classes
		// that use this function don't (loose coupling)
		$data = array();
		$defaults = array(
							'_mbdb_format'	=>	'',
							'_mbdb_isbn'		=>	'',
							'_mbdb_language'	=>	'',
							'_mbdb_length'		=>	'',
							'_mbdb_height'		=>	'',
							'_mbdb_width'		=>	'',
							'_mbdb_unit'		=>	'',
							'_mbdb_retail_price'	=>	'',
							'_mbdb_currency'		=>	'',
							'_mbdb_edition_title'	=>	'',
						);
		foreach ( $editions as $edition ) {
			$edition = wp_parse_args( $edition, $defaults );
			
			$data[] = array(
						'format_id' 	=>  $edition['_mbdb_format'],
						'isbn' 		=>  $edition['_mbdb_isbn'],
						'language' 	=>  $edition['_mbdb_language'],
						'length' 	=>  $edition['_mbdb_length'],
						'height' 	=>  $edition['_mbdb_height'],
						'width'		=>  $edition['_mbdb_width'],
						'unit' 		=>  $edition['_mbdb_unit'],
						'retail_price' =>  $edition['_mbdb_retail_price'],
						'currency' 	=>  $edition['_mbdb_currency'],
						'edition_title' =>  $edition['_mbdb_edition_title'],
					);
		}			
		return $data;
	}
	
	public function get_buy_links( $book_id ) {
		$data = array();
		
		// see get_editions for why we do this
		$buy_links = $this->get_data( '_mbdb_buylinks', $book_id );
		
		if ( !is_array($buy_links) ) {
			return array();
		}
		$defaults = array( '_mbdb_retailerID' => 0,
							'_mbdb_buylink'	=>	'',
						);
		foreach( $buy_links as $buy_link ) {
			
			$buy_link = wp_parse_args( $buy_link, $defaults);
			
			$data[] = array('retailerID' => $buy_link['_mbdb_retailerID'],
							'link' => $buy_link['_mbdb_buylink'],
					);
		}
			
		return $data;		
	}
	
	public function get_download_links( $book_id ) {
		// see get_editions for why we do this
		$download_links = $this->get_data( '_mbdb_downloadlinks', $book_id );
		
		if ( !is_array($download_links) ) {
			return array();
		}
		$data = array();
		$defaults = array( '_mbdb_formatID' => 0,
							'_mbdb_downloadlink'	=>	'',
						);
		foreach( $download_links as $download_link ) {
			$download_link = wp_parse_args( $download_link, $defaults);
			$data[] = array('formatID' => $download_link['_mbdb_formatID'],
							'link' => $download_link['_mbdb_downloadlink'],
					);
		}
		return $data;		
	}
	
	public function get_reviews( $book_id ) {
		// see get_editions for why we do this
		$reviews = $this->get_data( '_mbdb_reviews', $book_id );
		if ( !is_array($reviews) ) {
			return array();
		}
		$data = array();
		$defaults = array(	'mbdb_reviewer_name'	=>	'',
							'mbdb_review_url'	=>	'',
							'mbdb_review_website'	=>	'',
							'mbdb_review'		=>	'',
					);
		foreach( $reviews as $review ) {
			$review = wp_parse_args( $review, $defaults );
			$data[] = array( 'reviewer_name'  => $review[ 'mbdb_reviewer_name' ],
						'url'  => $review['mbdb_review_url' ],
						'website_name' 	=> $review[ 'mbdb_review_website' ],
						'review'  => $review[ 'mbdb_review' ],
					);
		}
		return $data;
	}
	
	public function get_genres( $book_id ) {
		return $this->get_data( 'mbdb_genre', $book_id );
	}
	
	public function get_series( $book_id) {
		return $this->get_data( 'mbdb_series', $book_id );
	}
	
	public function get_tags( $book_id ) {
		return $this->get_data( 'mbdb_tag', $book_id );
	}
	
	public function get_editors( $book_id ) {
		return $this->get_data( 'mbdb_editor', $book_id );
	}
	
	public function get_cover_artists( $book_id ) {
		return $this->get_data( 'mbdb_cover_artist', $book_id );
	}
	
	public function get_illustrators( $book_id ) {
		return $this->get_data( 'mbdb_illustrator', $book_id );
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
	/*
	
	public function get_books_by_taxonomy( $bookIDs, $taxonomy, $taxIDs, $orderby = null, $order = null ) {
		return $this->get_by_taxonomy(  $bookIDs, $taxonomy, $taxIDs, $orderby = null, $order = null );
		
	}
	*/
	public function get_published_books( $orderby = null, $order = null, $book_filter = null, $selection_filter = null ) {
		// get all books where published date is before tomorrow
		return $this->get_ordered_selection( 'published', null, array( $orderby, $order ), $book_filter, $selection_filter );		
	}
	
	public function get_unpublished_books( $orderby = null, $order = null, $book_filter = null, $selection_filter = null) {
		return  $this->get_ordered_selection( 'unpublished', null, array( $orderby, $order ), $book_filter, $selection_filter);
	}
	
	
	public function save( $data, $id, $auto_increment = false, $type = '' ) {
		// clear title list cache
		wp_cache_delete( 'title_list', 'mbdb_lists');
		// clear all book grid cache
		$args = array(
					'posts_per_page' => -1,
					'post_type' => 'mbdb_book_grid',
					'post_status'=>	'publish',
				);
		$grids = get_posts( $args );
		foreach ( $grids as $grid ) {
			//error_log('delete cached grid ' . $grid->ID );
			
				wp_cache_delete ( $grid->ID, 'mbdb_book_grid' );
		}
		wp_reset_postdata();
		
		
		return parent::save( $data, $id, $auto_increment , $type  );
	}
	
	public function save_all( $book, $id = null ) {
		$errors = array();
		
		// saves all fields of data
		// used for importing
		// if id is null, insert new item into posts table to get book id
		if ( $id == null ) {
			//error_log('inserting post');
			$id = wp_insert_post(  apply_filters( 'mbdb_book_save_all_insert_post', array(
				'post_title' => $book->title,
				'post_content' => '[mbdb_book]',
				'post_status' => 'publish',
				'post_type' => $this->post_type,
					)
				)
			);
			
			
			if ( $id == 0 ) {
				$errors[] = 'Unable to add book ' . $book->title . '.';
				return $errors;
			}	
		}
		$book->id = $id;
		
		$locked = wp_set_post_lock( $id );
		//error_log('locked = ' . print_r($locked, true));
		//error_log(print_r(get_current_user_id(), true));
		// update the post meta
		$data = array();
		foreach ( $book->reviews as $review ) {
			$data[]	=	array(
								'mbdb_reviewer_name' => $review->reviewer_name,
								'mbdb_review_url'	=> $review->url,
								'mbdb_review_website'	=> $review->website_name,
								'mbdb_review'	=> $review->review,
							);
		}
		//error_log('saving reviews');
		update_post_meta( $id, '_mbdb_reviews', $data);

		
	 	$data = array();
		foreach ( $book->editions as $edition ) {
			$data[] = array(
						'_mbdb_format'	=>	$edition->format_id,
						'_mbdb_isbn'	=>	$edition->isbn,
						'_mbdb_language'	=>	$edition->language,
						'_mbdb_length'		=>	$edition->length,
						'_mbdb_height'		=>	$edition->height,
						'_mbdb_width'		=>	$edition->width,
						'_mbdb_unit'		=>	$edition->unit,
						'_mbdb_retail_price'	=>	$edition->retail_price,
						'_mbdb_currency'	=>	$edition->currency,
						'_mbdb_edition_title'	=>	$edition->edition_title,
					);
		}
		//error_log('saving editions');
		update_post_meta( $id, '_mbdb_editions', $data );
		
		$data = array();
		foreach ( $book->buy_links as $buylink ) {
			
			//$buylink = json_decode($link);
			
			$data[] = array(
						'_mbdb_retailerID'	=>	$buylink->retailer->id,
						'_mbdb_buylink'	=>	$buylink->link,
					);
		}
		//error_log('saving buy links');
		update_post_meta( $id, '_mbdb_buylinks', $data );
		
		$data = array();
		foreach ( $book->download_links as $link ) {
			$data[] = array(
						'_mbdb_formatID'	=>	$link->download_format->id,
						'_mbdb_downloadlink'	=>	$link->link,
					);
		}
		//error_log('saving download links');
		update_post_meta( $id, '_mbdb_downloadlinks', $data );
		
		// update taxonomies
		$taxonomies = array ( 'mbdb_genre'	=>	$book->genres,
								'mbdb_tag'	=>	$book->tags,
								'mbdb_series'	=>	$book->series,
								'mbdb_cover_artist'	=>	$book->cover_artists,
								'mbdb_illustrator'	=>	$book->illustrators,
								'mbdb_editor'		=>	$book->editors,
							);
		foreach ( $taxonomies as $taxonomy => $array ) {
			
			if ( count( $array ) > 0 ) {
				$success = $this->update_taxonomy( $taxonomy, $array, $id );
				$errors = array_merge( $success, $errors );
			}
		}
		//error_log('taxonomies saved');
		
		// update books table
		//error_log('about to update table');
		
		$success = $book->save( $id );
		 delete_post_meta( $id, '_edit_lock' );
		if ( $success === false ) {
			$errors[] = 'Error saving to books table for book ' . $book->title . '.';
			return $errors;
		} else {
			return true;
		}
		
	}
	
	private function update_taxonomy( $taxonomy_name, $terms_array, $book_id ) {
		$term_id = 0;
		$errors = array();
		$taxonomy = get_taxonomy( $taxonomy_name );
		foreach ( $terms_array as $single_term ) {
			$term = get_term_by( 'slug', sanitize_title( $single_term->slug ), $taxonomy_name );
			if ( $term !== false ) {
				$term_id = $term->term_id;
			} else {
				$result = wp_insert_term( $single_term->name, $taxonomy_name,
										$args = array(
											'description'	=>	$single_term->description,
											'parent'	=>	$single_term->parent,
											'slug'	=>	$single_term->slug,
										) 
									);
				if ( is_array( $result ) ) {
					$term_id = $result['term_id'];
				} else {
					$errors[] = 'Unable to create ' . $taxonomy->labels->singular_name . ' ' . $single_term->name . ' for book ' . $book->title;
				}
			}
			if ( $term_id != 0 ) {
				$result = wp_add_object_terms( $book_id, $term_id, $taxonomy_name );
			}				
		}
		return $errors;
	}
	
	public function get_newest_books( ) {
		$sql = 'select DISTINCT b.*, p.post_title, p.post_name  from wp_mbdb_books as b 
		join wp_posts as p on b.book_id = p.ID 
		where p.post_status="publish" and
		b.release_date in 
		   (
			select max(release_date) as release_date
			from wp_mbdb_books as b 
			join wp_posts as p on b.book_id = p.ID
			and p.post_status = "publish"
			where release_date <= CURRENT_DATE()
			)';
		
		$books =  $this->run_sql( $sql );
		
		return apply_filters('mbdb_book_get_newest_books', $books );
	}
	
	public function get_ordered_selection( $selection, $selection_ids, $sort, $book_ids = null, $taxonomy = null, $include_drafts = false, $limit = null, $offset = null, $random = false) {
	
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
		//			OR { custom, array of ids }
		
	
		global $wpdb;
		$table = $this->table_name();
		
		$limit_clause = '';
		if ( $limit != null && (int) $limit == $limit ) {
			$limit_clause = ' LIMIT ';
			if ( $offset != null && (int) $offset ) {
					$limit_clause .= $offset . ', ';
			}
			$limit_clause .=  $limit . ' ';
		}
		
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
		
	/* 	// ensure that the sort field is a column in the table
		// and that the direction is either ASC or DESC
		if ( $sort != 'custom' ) {
			$sort = $this->validate_orderby( $sort );
			$order = $this->validate_order( $order );
		} */
		
		
		// SELECTION VARIABLES 
		
		// default to all books
		$book_selection_options = MBDB()->book_grid_CPT->selection_options();
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
			$tax_options = array_keys(MBDB()->book_grid_CPT->group_by_options());
			foreach($taxonomy as $tax => $tax_ids) {
				if ( ! in_array( $tax, $tax_options ) ) {
					unset($taxonomy[$tax]);
				}
			}
			if ( count($taxonomy) == 0 ) {
				$taxonomy = null;
			}
		}
		/*
		$where = '';
		if ( $this->column_exists( 'blog_id' ) ) {
			$where = " AND blog_id = $this->blog_id";
		}
*/
		
		$select = 'SELECT DISTINCT ';
		$join = ' JOIN ' . $wpdb->posts . ' p ON p.id = b.book_id ';
		$where = ' WHERE p.post_status = "publish" AND p.post_type = "' . $this->post_type . '" ';
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
					$join .= ' JOIN ' . $wpdb->term_relationships . ' AS tr ON tr.object_id = b.book_id 
								JOIN ' . $wpdb->term_taxonomy . ' AS tt  ON tt.term_taxonomy_id = tr.term_taxonomy_id ';
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
								$where .= ' AND (b.publisher_id IS NULL or b.publisher_id = 0) ';
							} else {
								if ( is_array($tax_ids) ) {
									$tax_ids = $tax_ids[0];
								} 
								$publishers = MBDB()->options->publishers;
								if ( array_key_exists( $tax_ids, $publishers) ) {
									$name = $publishers[ $tax_ids ]->name;
								} else {
									$name = '';
								}
								$select .= '"' . $name . '" as name' . $tax_level . ', ';
								$where .= ' AND (b.publisher_id ="' . esc_sql($tax_ids) . '") ';
								
							}
							break;
						// anything left is a taxonomy
						default:
							if (in_array($tax, $taxonomies) ) {
								// if -1 then get books that are NOT in any of this taxonomny
								if ($tax_ids == -1) {
									$select .=  ' "" AS name' . $tax_level . ', ';
									$where .= ' and b.book_id not in (select book_id from ' . $table . ' as b 
																		join ' . $wpdb->term_relationships . ' as tr3 on tr3.object_id = b.book_id 
																		join ' . $wpdb->term_taxonomy . ' tt3 on tt3.term_taxonomy_id = tr3.term_taxonomy_id 
																		where tt3.taxonomy = "mbdb_' . $tax . '" ) ';
								} else {	
									if (!is_array($tax_ids)) {
										$tax_ids = array($tax_ids);
									}
									$tax_ids = array_map('absint', $tax_ids);
									$select .= 't' . $tax_level . '.name AS name' . $tax_level . ', ';
									$where .= ' AND (tt' . $tax_level . '.taxonomy = "mbdb_' . $tax . '" AND tt' . $tax_level . '.term_id in (' . implode(',', $tax_ids) . ') ) ';
									$join .= ' JOIN ' . $wpdb->term_relationships . ' AS tr' . $tax_level . ' ON tr' . $tax_level . '.object_id = b.book_id 
												JOIN ' . $wpdb->term_taxonomy . ' AS tt' . $tax_level . '  ON tt' . $tax_level . '.term_taxonomy_id = tr' . $tax_level . '.term_taxonomy_id 
												JOIN ' . $wpdb->terms . ' AS t' . $tax_level . ' ON t' . $tax_level . '.term_id = tt' . $tax_level . '.term_id';
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
			case 'custom':
				$ids = implode(',', $order);
				$orderby .= ' FIELD( b.book_id, ' . $ids . ') ';
				$order = '';
				break;
			default:
				$orderby .= ' post_title ';
				break;
			
		}
		
		
		
		$select = apply_filters('mbdb_book_get_ordered_selection_select', $select, $selection, $selection_ids, $sort, $order, $book_ids, $taxonomy, $include_drafts, $limit, $offset, $random);
		$join = apply_filters('mbdb_book_get_ordered_selection_join', $join, $selection, $selection_ids, $sort, $order, $book_ids, $taxonomy, $include_drafts, $limit, $offset, $random);
		$where = apply_filters('mbdb_book_get_ordered_selection_where', $where, $selection_ids, $selection, $book_ids, $sort, $order, $taxonomy, $include_drafts, $limit, $offset, $random);
		$orderby = apply_filters('mbdb_book_get_ordered_selection_orderby', $orderby, $sort, $order, $selection, $selection_ids, $book_ids, $taxonomy, $include_drafts, $limit, $offset, $random);
		
		//$sql = "$select b.book_id, p.post_title, b.cover, b.release_date, b.cover_id FROM  $table  as b  $join $where $orderby $order ";
		
		
		$sql = "SELECT COUNT(DISTINCT b.book_id) as count FROM $table as b $join $where";
		
		$row = $data = $this->run_sql( $sql );
		$count = $row[0]->count;
		
		$sql = "$select $count as total, b.*, p.post_title, p.post_name FROM  $table  as b  $join $where $orderby $order $limit_clause";
		
		
		//error_log('before query');

		$books =  $this->run_sql( $sql );
		//error_log('after query');
		return apply_filters('mbdb_book_get_ordered_selection', $books, $selection, $selection_ids, $sort, $order, $taxonomy, $book_ids );
	

	}	
	
	
	
/****************************************************************
 *  			SEARCHING
 *  
 ****************************************************************/
 

public function search_where( $where ) {
	
	global $wpdb;	
	$table = $this->table_name();
	
	if( is_search() ) {
		
		$where = preg_replace(
		   "/\([^(]*post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
		   "(" . $wpdb->posts . ".post_title LIKE $1) OR ( " . $table . ".subtitle LIKE $1 ) OR (
		   " . $table . ".summary LIKE $1) OR (" . $table .".additional_info LIKE $1) ", $where);
		   
		//$where = parent::search_where( $where );
	}
	
	return $where;
}
 
	public static function create_the_table() {
		static::create_table();
	}

	public function create_table() {
		
		// Needed for dbDelta
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		
		global $charset_collate;
		//$table = $this->table_name();
		global $wpdb;
        $table =  $wpdb->prefix . 'mbdb_books';
		$sql_create_table = "CREATE TABLE " . $table . " (
			  book_id bigint(20) unsigned NOT NULL,
			  subtitle varchar(100),
			  summary longtext,
			  excerpt_type varchar(100),
			  excerpt longtext,
			  kindle_preview longtext,
			  additional_info longtext,
			  cover_id bigint(20) unsigned,
			  cover longtext,
			  release_date date,
			  publisher_id char(13),
			  goodreads longtext,
			  series_order decimal(6,2),
			  PRIMARY KEY  (book_id),
			  KEY release_date (release_date)
		 ) $charset_collate; ";
	 
		dbDelta( $sql_create_table );
		
		update_option( $table . '_db_version', MBDB_PLUGIN_VERSION);
		
	}
}
