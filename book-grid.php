<?php
 
add_action( 'template_redirect', 'mbdb_redirect_tax_grid' );
function mbdb_redirect_tax_grid() {	
	if (get_post_type() == 'mbdb_tax_grid' && is_main_query() && !is_admin()) {
		if(preg_match('/\/mbdb_tax_grid\//', $_SERVER['REQUEST_URI'])) {
			wp_redirect( site_url(), 301);
			exit;
		}
	}
}

// set the title in the book grid to the appropriate tag, genre, or series
// if query vars have been passed to handle the special case of showing
// just one tag, genre, or series
add_filter('the_title', 'mbdb_tax_grid_title');
function mbdb_tax_grid_title( $content, $id = null ) {
	global $wp_query;
	if ( is_main_query() && in_the_loop() && get_post_type() == 'mbdb_tax_grid' ) {
		if ( isset( $wp_query->query_vars['the-term'] ) ) {
			$mbdb_term = trim( urldecode( $wp_query->query_vars['the-term'] ), '/');
			if ( isset( $wp_query->query_vars['the-taxonomy'] ) ) {
				$mbdb_taxonomy = trim( urldecode( $wp_query->query_vars['the-taxonomy'] ), '/');
				$term = get_term_by('slug', $mbdb_term, $mbdb_taxonomy);			
				$taxonomy = get_taxonomy($mbdb_taxonomy);
					if ($mbdb_taxonomy != 'post_tag') {
					$content = apply_filters('mbdb_book_grid_' . $mbdb_taxonomy . '_title', $term->name . ' ' . $taxonomy->labels->singular_name, $term, $taxonomy);
				} else {
					$content = apply_filters('mbdb_book_grid_tag_title', 'Books tagged with ' . $term->name, $term, $taxonomy);
				}
			}
		}
	}
	return $content;
} 

add_filter( 'template_include', 'mbdb_grid_template', 99 );
function mbdb_grid_template( $template ) {
	if (get_post_type()=='mbdb_tax_grid') {
		$new_template = locate_template( array('single.php'));
		if ($new_template != '') {
			return $new_template;
		}
	}
	return $template;
}



add_filter( 'cmb2_meta_boxes', 'mbdb_book_grid_meta_boxes' );
function mbdb_book_grid_meta_boxes( array $meta_boxes ) {
		$meta_boxes['mbdb_book_grid'] = array(
			'id'			=> 'mbdb_book_grid',
			'title'			=> 'Book Grid Settings',
			'object_types'	=> array( 'page' ),
			'context'		=> 'normal',
			'priority'		=> 'default',
			'show_names'	=> true,
			'fields'		=> array(
				array(
					'name'	=> 'Display Books on This Page?',
					'id'	=> '_mbdb_book_grid_display',
					'type'	=> 'select',
					'default'	=> 'no',
					'options'	=> array(
						'yes'	=> 'Yes',
						'no'	=> 'No',
					),
				),
				array(
					'name' 	=> 'Books to Display',
					'id' 	=> '_mbdb_book_grid_books',
					'type'	=> 'select',
					'options'	=> array(
						'all'		=> 'All',
						'published'	=> 'All Published',
						'unpublished'	=> 'All Coming Soon',
						'genre'			=> 'Select Genres',
						'series'	=> 'Select Series',
						'custom'	=> 'Select Books',
					)
				),
				array(
					'name' 	=> 'Select Books',
					'id'	=> '_mbdb_book_grid_custom_select',
					'type'	=> 'multicheck',
					'options' => mbdb_get_book_array(),
				),
				array(
					'name'	=> 'Select Genres',
					'id'	=> '_mbdb_book_grid_genre',
					'taxonomy' => 'mbdb_genre', //Enter Taxonomy Slug
					'type' 	=> 'taxonomy_multicheck',   
				),
					array(
					'name'	=> 'Select Series',
					'id'	=> '_mbdb_book_grid_series',
					'taxonomy' => 'mbdb_series', //Enter Taxonomy Slug
					'type' 	=> 'taxonomy_multicheck',   
				),
				array(
					'name'	=> 'Order By',
					'id'	=> '_mbdb_book_grid_order',
					'type'	=> 'select',
					'options'	=> array(
						'pubdateA'	=> 'Publication Date (oldest first)',
						'pubdateD'	=> 'Publication Date (newest first)',
						'titleA'	=> 'Title (A-Z)',
						'titleD'	=> 'Title (Z-A)',
						'series'	=> 'By Series',
						'genre'		=> 'By Genre',
					),
				),
				array(
					'name'	=> 'Order Within the Genre by',
					'id'	=> '_mbdb_book_grid_genre_order',
					'type'	=> 'select',
					'options'	=> array(
						'pubdateA'	=> 'Publication Date (oldest first)',
						'pubdateD'	=> 'Publication Date (newest first)',
						'titleA'	=> 'Title (A-Z)',
						'titleD'	=> 'Title (Z-A)',
						'series'	=> 'Series',
					),
				),
				array(
					'name'	=> 'Book Cover Height',
					'id'	=> '_mbdb_book_grid_cover_height',
					'type'	=> 'text_small',
					'default'	=> 200,
					'attributes' => array(
							'type' => 'number',
							'pattern' => '\d*',
							'min' => 50,
					),
				),
				array(
					'name'	=> 'Number of Books Across',
					'id'	=> '_mbdb_book_grid_books_across',
					'type'	=> 'text_small',
					'default' => 3,
					'attributes' => array(
							'type' => 'number',
							'pattern' => '\d*',
							'min' => 1,
					),
				),
		
			),
		);
		return apply_filters('mbdb_book_grid_meta_boxes', $meta_boxes);
	}

function mbdb_bookgrid_content() {
	global $post;
	$content ='';
	
	$display_grid = get_post_meta( $post->ID, '_mbdb_book_grid_display', true );
	if ( $display_grid != 'yes' ) {
		return apply_filters('mbdb_book_grid_display_grid_no', $content);
	}
	
	$mbdb_book_grid_books = 		get_post_meta( $post->ID, '_mbdb_book_grid_books', true );
	$mbdb_book_grid_order =			get_post_meta ($post->ID, '_mbdb_book_grid_order', true );
	$mbdb_book_grid_cover_height =  get_post_meta( $post->ID, '_mbdb_book_grid_cover_height', true );
	$mbdb_book_grid_books_across =  get_post_meta( $post->ID, '_mbdb_book_grid_books_across', true );
	$mbdb_book_grid_genre = 		get_post_meta( $post->ID, '_mbdb_book_grid_genre', true );
	$mbdb_book_grid_series = 		get_post_meta( $post->ID, '_mbdb_book_grid_series', true );
	$mbdb_book_grid_custom_select = get_post_meta( $post->ID, '_mbdb_book_grid_custom_select', true );
	$mbdb_book_grid_genre_order = 	get_post_meta( $post->ID, '_mbdb_book_grid_genre_order', true );
	
	// set defaults just in case
	if ( (int) $mbdb_book_grid_books_across < 1 ) {
		$mbdb_book_grid_books_across = 1;
	}
	if ( (int) $mbdb_book_grid_cover_height < 50 ) {
		$mbdb_book_grid_cover_height = 50;
	}
	// first get the list of books
	// and in the right order
	// loop through the books to display
	// use _mbdb_book_grid_books_across to know when to start a new row
	// use separate tables for:
	//		* Coming Soon vs Published books
	//		* Series
	//		* Genres
	
	// grab the main sort order
	do_action('mbdb_book_grid_before_set_sort', $mbdb_book_grid_order, $mbdb_book_grid_genre_order );
	switch ( $mbdb_book_grid_order ) {
		case 'pubdateA':
			$sort_field = '_mbdb_published';
			$sort_order = 'ASC';
			break;
		case 'pubdateD':
			$sort_field = '_mbdb_published';
			$sort_order = 'DESC';
			break;
		case 'titleA':
			$sort_field = 'title';
			$sort_order = 'ASC';
			break;
		case 'titleD':
			$sort_field = 'title';
			$sort_order = 'DESC';
			break;
		case 'series':
			$sort_field = '_mbdb_series_order';
			$sort_order = 'ASC';
			break;
		case 'genre':
			switch ( $mbdb_book_grid_genre_order ) {
				case 'titleA':
					$sort_field = 'title';
					$sort_order = 'ASC';
					break;
				case 'titleD':
					$sort_field = 'title';
					$sort_order = 'DESC';						
					break;
				case 'pubdateA':
					$sort_field = '_mbdb_published';
					$sort_order = 'ASC';
					break;
				case 'pubdateD':
					$sort_field = '_mbdb_published';
					$sort_order = 'DESC';						
					break;
				case 'series':
					$sort_field = '_mbdb_series_order';
					$sort_order = 'ASC';
				default:
			}
			break;
		default:
	}
	do_action('mbdb_book_grid_after_set_sort', $mbdb_book_grid_order, $mbdb_book_grid_genre_order );
	
	// make sure the variables are valid, just in case
	
	// if not choosing books by genre, there shouldn't be any genres chosen
	if ( $mbdb_book_grid_books != 'genre' ) {
		$mbdb_book_grid_genre = null;
	}
	// if not choosing books by series, there shouldn't be any series chosen
	if ( $mbdb_book_grid_books != 'series' ) {
		$mbdb_book_grid_series = null;
	}
	// if not choosing books, there shouldn't be any books chosen
	if ( $mbdb_book_grid_books != 'custom' ) {
		$mbdb_book_grid_custom_select = null;
	}
	// if not grouping by genre, there shouldn't be a genre order
	if ( $mbdb_book_grid_order != 'genre' ) {
		$mbdb_book_grid_genre_order = null;
	}
	// if getting standalones, there shouldn't be any series chosen
	if ( $mbdb_book_grid_books == 'standalone' ) {
		$mbdb_book_grid_series = '0';
	}
	
	if ( $mbdb_book_grid_order == 'genre' ) {
		// don't get uncategorized if we've selected by genre
		if ( $mbdb_book_grid_books != 'genre' ) {
			do_action('mbdb_book_grid_before_get_uncategorized_books', $mbdb_book_grid_genre_order, $mbdb_book_grid_books, $mbdb_book_grid_custom_select, $sort_field, $sort_order, '0', 'Uncategorized', $mbdb_book_grid_series, $mbdb_book_grid_genre);
	
			mbdb_get_genre_books( $mbdb_books, $mbdb_book_grid_genre_order, $mbdb_book_grid_books, $mbdb_book_grid_custom_select, $sort_field, $sort_order, '0', 'Uncategorized', $mbdb_book_grid_series, $mbdb_book_grid_genre );
			do_action('mbdb_book_grid_after_get_uncategorized_books', $mbdb_books, $mbdb_book_grid_genre_order, $mbdb_book_grid_books, $mbdb_book_grid_custom_select, $sort_field, $sort_order, '0', 'Uncategorized', $mbdb_book_grid_series, $mbdb_book_grid_genre);
		}
		
		// Get all terms from the 'genre' Taxonomy 
		if ( $mbdb_book_grid_genre != null ) {
			$genre_list = implode( ',', $mbdb_book_grid_genre );
		} else {
			$genre_list = null;
		}
		$genres = get_terms( 'mbdb_genre', 'orderby=slug&hide_empty=1&include=' . $genre_list );
	
		foreach($genres as $term) {
			do_action('mbdb_book_grid_before_get_books_by_genre',  $mbdb_book_grid_genre_order, $mbdb_book_grid_books, $mbdb_book_grid_custom_select, $sort_field, $sort_order, $term->term_id, $term->name, $mbdb_book_grid_series, $mbdb_book_grid_genre );
	
	mbdb_get_genre_books( $mbdb_books, $mbdb_book_grid_genre_order, $mbdb_book_grid_books, $mbdb_book_grid_custom_select, $sort_field, $sort_order, $term->term_id, $term->name, $mbdb_book_grid_series, $mbdb_book_grid_genre );
			do_action('mbdb_book_grid_after_get_books_by_genre', $mbdb_books, $mbdb_book_grid_genre_order, $mbdb_book_grid_books, $mbdb_book_grid_custom_select, $sort_field, $sort_order, $term->term_id, $term->name, $mbdb_book_grid_series, $mbdb_book_grid_genre );
		}
	} else {
		do_action('mbdb_book_grid_before_get_books_not_by_genre',  $mbdb_book_grid_order, $mbdb_book_grid_books, $mbdb_book_grid_custom_select, $sort_field, $sort_order, null, null, $mbdb_book_grid_series, $mbdb_book_grid_genre);
		
		mbdb_get_genre_books( $mbdb_books, $mbdb_book_grid_order, $mbdb_book_grid_books, $mbdb_book_grid_custom_select, $sort_field, $sort_order, null, null, $mbdb_book_grid_series, $mbdb_book_grid_genre );
		do_action('mbdb_book_grid_after_get_books_not_by_genre', $mbdb_books, $mbdb_book_grid_order, $mbdb_book_grid_books, $mbdb_book_grid_custom_select, $sort_field, $sort_order, null, null, $mbdb_book_grid_series, $mbdb_book_grid_genre);
	}
	do_action('mbdb_book_grid_before_display_grid', $mbdb_books, $mbdb_book_grid_cover_height, $mbdb_book_grid_books_across, 0);
	$mbdb_books = apply_filters('mbdb_book_grid_books', $mbdb_books);
	
	$content = mbdb_display_grid(  $mbdb_books, $mbdb_book_grid_cover_height, $mbdb_book_grid_books_across, 0 );	
	do_action('mbdb_book_grid_after_display_grid', $mbdb_books, $mbdb_book_grid_cover_height, $mbdb_book_grid_books_across, 0);

	
	return apply_filters('mbdb_book_grid_content', $content);
}
				

function mbdb_get_genre_books( &$mbdb_books, $book_order, $mbdb_book_grid_books, $mbdb_book_grid_custom_select, $sort_field, $sort_order, $genres, $genre_name, $mbdb_book_grid_series, $mbdb_book_grid_genre ) {
	if ($book_order=='series') {
			// Get Standalones
			if ( isset( $genres ) ) {
				// don't get Stand Alones when series are selected
				if ( $mbdb_book_grid_books != 'series' ) {
					do_action('mbdb_book_grid_before_get_standalones_by_genre', $mbdb_book_grid_books, $mbdb_book_grid_custom_select, $sort_field, $sort_order, $genres, '0');
					
					$books = mbdb_get_books_list( $mbdb_book_grid_books, $mbdb_book_grid_custom_select, $sort_field, $sort_order, $genres, '0' );
					do_action('mbdb_book_grid_after_get_standalones_by_genre', $mbdb_book_grid_books, $mbdb_book_grid_custom_select, $sort_field, $sort_order, $genres, '0');
					if ( $books ) {
						$mbdb_books[$genre_name]['Stand Alones']  = $books;
					}
				}
			} else {
				// don't get Stand Alones when series are selected
				if ($mbdb_book_grid_books != 'series') {
					do_action('mbdb_book_grid_before_get_standalones', $mbdb_book_grid_books, $mbdb_book_grid_custom_select, $sort_field, $sort_order, $mbdb_book_grid_genre, '0');
					$books = mbdb_get_books_list( $mbdb_book_grid_books, $mbdb_book_grid_custom_select, $sort_field, $sort_order, $mbdb_book_grid_genre, '0' );
					do_action('mbdb_book_grid_after_get_standalones', $mbdb_book_grid_books, $mbdb_book_grid_custom_select, $sort_field, $sort_order, $mbdb_book_grid_genre, '0');
					if ( $books ) {
						$mbdb_books['Stand Alones'] = $books;
					}
					
				}			
			}
			// Get series
			$series = get_terms('mbdb_series', 'orderby=slug&hide_empty=1');
			foreach ( $series as $series_term ) {	
				if ( $mbdb_book_grid_series == null || ( $mbdb_book_grid_series != null && in_array( $series_term->term_id, $mbdb_book_grid_series ) ) ) {
					if ( isset( $genres ) ) {
						do_action('mbdb_book_grid_before_get_series_by_genre', $mbdb_book_grid_books, $mbdb_book_grid_custom_select, $sort_field, $sort_order, $genres, $series_term->term_id);
						
						$books = mbdb_get_books_list( $mbdb_book_grid_books, $mbdb_book_grid_custom_select, $sort_field, $sort_order, $genres, $series_term->term_id );
						do_action('mbdb_book_grid_after_get_series_by_genre', $mbdb_book_grid_books, $mbdb_book_grid_custom_select, $sort_field, $sort_order, $genres, $series_term->term_id);
						if ( $books ) {
							$mbdb_books[$genre_name][$series_term->name . ' Series'] = $books;
						}
						
					} else {	
						do_action('mbdb_book_grid_before_get_series', $mbdb_book_grid_books, $mbdb_book_grid_custom_select, $sort_field, $sort_order, $mbdb_book_grid_genre, $series_term->term_id);
						
						
						$books = mbdb_get_books_list( $mbdb_book_grid_books, $mbdb_book_grid_custom_select, $sort_field, $sort_order, $mbdb_book_grid_genre, $series_term->term_id );
						do_action('mbdb_book_grid_after_get_series', $mbdb_book_grid_books, $mbdb_book_grid_custom_select, $sort_field, $sort_order, $mbdb_book_grid_genre, $series_term->term_id);
						if ( $books ) {
							$mbdb_books[$series_term->name . ' Series'] = $books;
						}
						
					}
					
				}
			}
	} else {
		if ( isset( $genres ) ) {
			do_action('mbdb_book_grid_before_get_books_by_genre', $mbdb_book_grid_books, $mbdb_book_grid_custom_select, $sort_field, $sort_order, $genres, $mbdb_book_grid_series );
			
			$books = mbdb_get_books_list( $mbdb_book_grid_books, $mbdb_book_grid_custom_select, $sort_field, $sort_order, $genres, $mbdb_book_grid_series );
			do_action('mbdb_book_grid_after_get_books_by_genre', $mbdb_book_grid_books, $mbdb_book_grid_custom_select, $sort_field, $sort_order, $genres, $mbdb_book_grid_series );
			if ( $books ) {
				$mbdb_books[$genre_name] = $books;
			}
			
			
		} else {
			do_action('mbdb_book_grid_before_get_books', $mbdb_book_grid_books, $mbdb_book_grid_custom_select, $sort_field, $sort_order, $mbdb_book_grid_genre, $mbdb_book_grid_series );
			
			$books = mbdb_get_books_list( $mbdb_book_grid_books, $mbdb_book_grid_custom_select, $sort_field, $sort_order, $mbdb_book_grid_genre, $mbdb_book_grid_series );
			do_action('mbdb_book_grid_after_get_books', $books, $mbdb_book_grid_books, $mbdb_book_grid_custom_select, $sort_field, $sort_order, $mbdb_book_grid_genre, $mbdb_book_grid_series );
			if ( $books ) {
				$mbdb_books[] = $books;
			}
		}
	}

}				




function mbdb_display_grid($mbdb_books, $mbdb_book_grid_cover_height, $mbdb_book_grid_books_across,  $l) {
	// count how many books have been put in the row
	$c = 0;
	// figure out how wide each cell should be
	$width = floor( 100 / $mbdb_book_grid_books_across );
	// indent the grid by 50px per depth level of the array
	do_action('mbdb_book_grid_before_div', $l);
	$content = '<div class="mbm-book-grid-div" style="padding-left:' . (50 * $l) . 'px;">';
	
	// loop through the array
	foreach($mbdb_books as $key => $set) {
	
		// If a label is set and there's at least one book, print the label
		if ( $key && count( $set ) > 0 ) {
			// set the heading level based on the depth level of the array
			do_action('mbdb_book_grid_before_heading',  $l, $key);
			$content .= '<h' . ( 2 + $l ) . ' class="mbm-book-grid-heading' . ( $l + 1 ) . '">' . $key . '</h' . ( 2 + $l ) .'>';
			do_action('mbdb_book_grid_after_heading', $l, $key);
		}
		// because the index of the array could be a genre or series name and not a sequential index use array_keys to get the index
		// if the first element in the array isn't an object that means there's another level in the array
		// and we need to re-call this function recursively to get the next level	
		if ( gettype( $set[array_keys($set)[0]] ) != 'object') {
			$l++;
			do_action('mbdb_book_grid_before_recursion',$set, $mbdb_book_grid_cover_height, $mbdb_book_grid_books_across,  $l);
			$content .= mbdb_display_grid($set, $mbdb_book_grid_cover_height, $mbdb_book_grid_books_across,  $l);
			
			do_action('mbdb_book_grid_after_recursion', $set, $mbdb_book_grid_cover_height, $mbdb_book_grid_books_across,  $l);
			$l--;
		} else {
			// we're at the inner most level now so we can print out the grid
			do_action('mbdb_book_grid_before_table',  $l);
			$content .= '<table class="mbm-book-grid-table">';
			do_action('mbdb_book_grid_before_row',  $l);
			$content .= '<tr class="mbm-book-grid-row">';	
			
			// print out each book
			foreach($set as $book) {
					$mbdb_bookID = $book->ID;
					$mbdb_book_title = apply_filters('mbdb_book_grid_book_title', $book->post_title, $l);
					$image_src = get_post_meta( $mbdb_bookID, '_mbdb_cover', true );
					do_action('mbdb_book_grid_before_cell',  $mbdb_bookID, $mbdb_book_title, $image_src, $mbdb_book_grid_cover_height, $c );
					$content .= '<td class="mbm-book-grid-cell" style="width:' . $width . '%;padding:15px;vertical-align:top;">';
					do_action('mbdb_book_grid_before_link',  $mbdb_bookID, $mbdb_book_title, $image_src, $mbdb_book_grid_cover_height, $c);
					$content .= '<A class="mbm-book-grid-title-link" HREF="' . esc_url(get_permalink($mbdb_bookID)) . '">';
					if (isset($image_src) && $image_src != '') {
						do_action('mbdb_book_grid_before_cover_image', $mbdb_bookID, $mbdb_book_title, $image_src, $mbdb_book_grid_cover_height, $c );
						$content .= '<img class="mbm-book-grid-cover" src="' . esc_url($image_src) . '" style="height:' . esc_attr($mbdb_book_grid_cover_height) . 'px" /><BR> ';
						do_action('mbdb_book_grid_after_cover_image', $content, $mbdb_bookID, $mbdb_book_title, $image_src, $mbdb_book_grid_cover_height, $c );
					} else {
						do_action('mbdb_book_grid_no_cover_image',$mbdb_bookID, $mbdb_book_title, $mbdb_book_grid_cover_height, $c);
					}
					do_action('mbdb_book_grid_before_book_title',  $mbdb_bookID, $mbdb_book_title);
					$content .= '<H3 class="mbm-book-grid-title">' . esc_html($mbdb_book_title) . '</h3>';	
					do_action('mbdb_book_grid_after_book_title',   $mbdb_bookID, $mbdb_book_title);
					$content .= '</a>';
					do_action('mbdb_book_grid_after_link', $mbdb_bookID, $mbdb_book_title, $image_src, $mbdb_book_grid_cover_height, $c);
					$content .= '</td> ';
					do_action('mbdb_book_grid_after_cell',  $mbdb_bookID, $mbdb_book_title, $image_src, $mbdb_book_grid_cover_height, $c );
					$c++;
					// close the row and start a new one if we've reached the number the user set
					if ($c == $mbdb_book_grid_books_across) {
						$content .= '</tr>';
						do_action('mbdb_book_grid_after_row', $l);
						do_action('mbdb_book_grid_before_row',$l);
						$content .= '<tr>';
						$c=0;
					}
			}
			$content .= '</tr>';
			do_action('mbdb_book_grid_after_row',  $l);
			$content .= '</table>';
			do_action('mbdb_book_grid_after_table',  $l);
			$c=0;
		}
	}
	$content .= '</div>';
	do_action('mbdb_book_grid_after_div', $l);
	return apply_filters('mbdb_book_grid_table_content', $content, $l);
}


?>