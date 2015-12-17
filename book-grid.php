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


// edit the breadcrumb for the Customizr theme if this is a tax_grid (series, tag, genre)
// tc_breadcrumb_trail_items should be unique enough to the Customizr theme
// that it doesn't affect anything else?
add_filter('tc_breadcrumb_trail_items', 'mbdb_tax_grid_breadcrumb', 10, 2);
function mbdb_tax_grid_breadcrumb( $trail, $args) {
	
	if (  get_post_type() == 'mbdb_tax_grid' ) {
		$lastitem = count($trail) -1;
		$trail[$lastitem] = mbdb_get_tax_title($trail[$lastitem]);
	}
	return $trail;
}


// set the title in the book grid to the appropriate tag, genre, or series
// if query vars have been passed to handle the special case of showing
// just one tag, genre, or series
//add_filter('tc_the_title', 'mbdb_tax_grid_title');
add_filter('tc_title_text', 'mbdb_tax_grid_title');
add_filter('the_title', 'mbdb_tax_grid_title');
function mbdb_tax_grid_title( $content, $id = null ) {
	
	if ( is_main_query() && in_the_loop() && get_post_type() == 'mbdb_tax_grid' ) {
		$content = apply_filters('mbdb_tax_grid_title', mbdb_get_tax_title($content));
	}
	return $content;
} 

function mbdb_get_tax_title( $content ) {
	global $wp_query;
	if ( isset( $wp_query->query_vars['the-term'] ) ) {
			$mbdb_term = trim( urldecode( $wp_query->query_vars['the-term'] ), '/');
			if ( isset( $wp_query->query_vars['the-taxonomy'] ) ) {
				$mbdb_taxonomy = trim( urldecode( $wp_query->query_vars['the-taxonomy'] ), '/');
				$term = get_term_by('slug', $mbdb_term, $mbdb_taxonomy);			
				$taxonomy = get_taxonomy($mbdb_taxonomy);
				if (isset($term) && isset($taxonomy) && $term != null && $taxonomy !=null) {
					switch ($mbdb_taxonomy) {
						case 'mbdb_series':
							$content = apply_filters('mbdb_book_grid_' . $mbdb_taxonomy . '_title', sprintf( _x( '%1$s %2$s', '%1$s = name of series, %2$s = "Series"', 'mooberry-book-manager'), $term->name, $taxonomy->labels->singular_name), $term, $taxonomy);
							break;
						case 'mbdb_genre':
							$content = apply_filters('mbdb_book_grid_' . $mbdb_taxonomy . '_title', sprintf( _x( '%1$s %2$s', '%1$s = name of genre, %2$s = "Genre"', 'mooberry-book-manager'), $term->name, $taxonomy->labels->singular_name), $term, $taxonomy);
							break;
						case 'mbdb_tag':
							$content = apply_filters('mbdb_book_grid_tag_title', sprintf(__('Books Tagged With %s', 'mooberry-book-manager'), $term->name), $term, $taxonomy);
							break;
						case 'mbdb_editor':
							$content = apply_filters('mbdb_book_grid_mbdb_editor_title', sprintf(__('Books Edited By %s', 'mooberry-book-manager'), $term->name), $term, $taxonomy);
							break;
						case 'mbdb_illustrator':
							$content = apply_filters('mbdb_book_grid_mbdb_illustrator_title', sprintf(__('Books Illustrated By %s', 'mooberry-book-manager'), $term->name), $term, $taxonomy);
							break;
						case 'mbdb_cover_artist':
							$content = apply_filters('mbdb_book_grid_mbdb_cover_artist_title', sprintf(__('Book Covers By %s', 'mooberry-book-manager'), $term->name), $term, $taxonomy);
							break;
						default:
							$content = '';
					}
				} else {
					$content = __('Not Found', 'mooberry-book-manager');
				}
			}
		}
	return $content;
}

// add_filter( 'template_include', 'mbdb_grid_template', 99 );
// function mbdb_grid_template( $template ) {
	// if (get_post_type()=='mbdb_tax_grid') {
		// $new_template = locate_template( array('single.php'));
		// if ($new_template != '') {
			// return $new_template;
		// }
	// }
	// return $template;
// }



add_filter( 'cmb2_meta_boxes', 'mbdb_book_grid_meta_boxes' );
function mbdb_book_grid_meta_boxes( array $meta_boxes ) {
		$meta_boxes['mbdb_book_grid'] = array(
			'id'			=> 'mbdb_book_grid',
			'title'			=> __('Book Grid Settings', 'mooberry-book-manager'),
			'object_types'	=> array( 'page' ),
			'context'		=> 'normal',
			'priority'		=> 'default',
			'show_names'	=> true,
			'fields'		=> array(
				array(
					'name'	=> __('Display Books on This Page?', 'mooberry-book-manager'),
					'id'	=> '_mbdb_book_grid_display',
					'type'	=> 'select',
					'default'	=> 'no',
					'options'	=> array(
						'yes'	=> __('Yes', 'mooberry-book-manager'),
						'no'	=> __('No', 'mooberry-book-manager'),
					),
				),
				array(
					'name' 	=> __('Books to Display', 'mooberry-book-manager'),
					'id' 	=> '_mbdb_book_grid_books',
					'type'	=> 'select',
					'options'	=> array(
						'all'		=> __('All', 'mooberry-book-manager'),
						'published'	=> __('All Published', 'mooberry-book-manager'),
						'unpublished'	=> __('All Coming Soon', 'mooberry-book-manager'),
						'genre'			=> __('Select Genres', 'mooberry-book-manager'),
						'series'	=> __('Select Series', 'mooberry-book-manager'),
						'tag'		=> __('Select Tags', 'mooberry-book-manager'),
						'custom'	=> __('Select Books', 'mooberry-book-manager'),
					)
				),
				array(
					'name' 	=> __('Select Books', 'mooberry-book-manager'),
					'id'	=> '_mbdb_book_grid_custom_select',
					'type'	=> 'multicheck',
					'options' => mbdb_get_book_array(),
				),
				array(
					'name'	=> __('Select Genres', 'mooberry-book-manager'),
					'id'	=> '_mbdb_book_grid_genre',
					'taxonomy' => 'mbdb_genre', //Enter Taxonomy Slug
					'type' 	=> 'taxonomy_multicheck',   
				),
					array(
					'name'	=> __('Select Series', 'mooberry-book-manager'),
					'id'	=> '_mbdb_book_grid_series',
					'taxonomy' => 'mbdb_series', //Enter Taxonomy Slug
					'type' 	=> 'taxonomy_multicheck',   
				),
				array(
					'name'	=>	__('Select Tags', 'mooberry-book-manager'),
					'id'	=>	'_mbdb_book_grid_tag',
					'taxonomy'	=> 'mbdb_tag',
					'type'	=> 'taxonomy_multicheck',
				),
				array(
					'name'	=>	__('Group Books By', 'mooberry-book-manager'),
					'id'	=>	'_mbdb_book_grid_group_by',
					'type'	=>	'select',
					'options'	=> array(
						'none'		=>	__('None', 'mooberry-book-manager'),
						'genre'		=>	__('Genre', 'mooberry-book-manager'),
						'series'	=>	__('Series', 'mooberry-book-manager'),
						'tag'		=>	__('Tag', 'mooberry-book-manager'),
					),
				),
				array(
					'name'	=>	__('Group Within Genre By', 'mooberry-book-manager'),
					'id'	=>	'_mbdb_book_grid_genre_group_by',
					'type'	=>	'select',
					'options'	=>	array(
						'none'		=>	__('None', 'mooberry-book-manager'),
						'series'	=>	__('Series', 'mooberry-book-manager'),
						'tag'		=>	__('Tag', 'mooberry-book-manager'),
					),
				),
				array(
					'name'	=>	__('Group Within Tag By', 'mooberry-book-manager'),
					'id'	=>	'_mbdb_book_grid_tag_group_by',
					'type'	=>	'select',
					'options'	=>	array(
						'none'		=>	__('None', 'mooberry-book-manager'),
						'series'	=>	__('Series', 'mooberry-book-manager'),
						'genre'		=>	__('Genre', 'mooberry-book-manager'),
					),
				),
				array(
					'name'	=>	__('Group Within Tag By', 'mooberry-book-manager'),
					'id'	=>	'_mbdb_book_grid_genre_tag_group_by',
					'type'	=>	'select',
					'options'	=>	array(
						'none'		=>	__('None', 'mooberry-book-manager'),
						'series'	=>	__('Series', 'mooberry-book-manager'),
					),
				),
				array(
					'name'	=>	__('Group Within Genre By', 'mooberry-book-manager'),
					'id'	=>	'_mbdb_book_grid_tag_genre_group_by',
					'type'	=>	'select',
					'options'	=>	array(
						'none'		=>	__('None', 'mooberry-book-manager'),
						'series'	=>	__('Series', 'mooberry-book-manager'),
					),
				),
				array(
					'name'	=> __('Order By', 'mooberry-book-manager'),
					'id'	=> '_mbdb_book_grid_order',
					'type'	=> 'select',
					'sanitization_cb' => 'mbdb_check_grid_order',
					'options'	=> array(
						'pubdateA'	=> __('Publication Date (oldest first)', 'mooberry-book-manager'),
						'pubdateD'	=> __('Publication Date (newest first)', 'mooberry-book-manager'),
						'titleA'	=> __('Title (A-Z)', 'mooberry-book-manager'),
						'titleD'	=> __('Title (Z-A)', 'mooberry-book-manager'),
					),
				),
				array(
					'name'	=>	__('Use default cover height?', 'mooberry-book-manager'),
					'id'	=>	'_mbdb_book_grid_cover_height_default',
					'type'	=>	'select',
					'default'	=>	'yes',
					'options'	=>	array(
						'yes'	=> __('Yes','mooberry-book-manager'),
						'no'	=>	__('No','mooberry-book-manager'),
					),
				),
				array(
					'name'	=> __('Book Cover Height (px)', 'mooberry-book-manager'),
					'id'	=> '_mbdb_book_grid_cover_height',
					'type'	=> 'text_small',
					'attributes' => array(
							'type' => 'number',
							'pattern' => '\d*',
							'min' => 50,
					),
				),
		/*
				array(
					'name'	=>	__('Use default number of books across?', 'mooberry-book-manager'),
					'id'	=>	'_mbdb_book_grid_books_across_default',
					'type'	=>	'select',
					'default'	=>	'yes',
					'options'	=>	array(
						'yes'	=> __('Yes','mooberry-book-manager'),
						'no'	=>	__('No','mooberry-book-manager'),
					),
				),
				array(
					'name'	=> __('Number of Books Across', 'mooberry-book-manager'),
					'id'	=> '_mbdb_book_grid_books_across',
					'type'	=> 'text_small',
					'attributes' => array(
							'type' => 'number',
							'pattern' => '\d*',
							'min' => 1,
					),
				),
		*/
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
	
	$mbdb_options = get_option('mbdb_options');
	
	$mbdb_book_grid_books = 		get_post_meta( $post->ID, '_mbdb_book_grid_books', true );
	$mbdb_book_grid_order =			get_post_meta ($post->ID, '_mbdb_book_grid_order', true );
	// $mbdb_book_grid_cover_height_default = get_post_meta( $post->ID, '_mbdb_book_grid_cover_height_default', true);
	// if ($mbdb_book_grid_cover_height_default == 'yes') {
		// if (!isset($mbdb_options['mbdb_default_cover_height'])) {
			// $mbdb_options['mbdb_default_cover_height'] = 200;
		// }
		// $mbdb_book_grid_cover_height = $mbdb_options['mbdb_default_cover_height'];
		//$mbdb_book_grid_cover_height = 0; // no longer used, just given a value because it's used in arguments.
	// } else {
		// $mbdb_book_grid_cover_height =  get_post_meta( $post->ID, '_mbdb_book_grid_cover_height', true );
	// }
	
	$mbdb_book_grid_cover_height = mbdb_get_grid_cover_height( $post->ID);
	
	// $mbdb_book_grid_books_across_default = get_post_meta( $post->ID, '_mbdb_book_grid_books_across_default', true);
	// if ($mbdb_book_grid_books_across_default == 'yes') {
		// if (!isset($mbdb_options['mbdb_default_books_across'])) {
			// $mbdb_options['mbdb_default_books_across'] = 3;
		// }
		// $mbdb_book_grid_books_across = $mbdb_options['mbdb_default_books_across'];
	// } else {
		// $mbdb_book_grid_books_across =  get_post_meta( $post->ID, '_mbdb_book_grid_books_across', true );
	// }
	$mbdb_book_grid_books_across = 1; // no longer used, just given a value because it's used in arguments.
	
	$mbdb_book_grid_genre = 		get_post_meta( $post->ID, '_mbdb_book_grid_genre', true );
	$mbdb_book_grid_series = 		get_post_meta( $post->ID, '_mbdb_book_grid_series', true );
	$mbdb_book_grid_tag	=			get_post_meta( $post->ID, '_mbdb_book_grid_tag', true);
	$mbdb_book_grid_custom_select = get_post_meta( $post->ID, '_mbdb_book_grid_custom_select', true );
	$mbdb_book_grid_genre_group_by = 	get_post_meta( $post->ID, '_mbdb_book_grid_genre_group_by', true );
	$mbdb_book_grid_group_by = 	get_post_meta( $post->ID, '_mbdb_book_grid_group_by', true );
	$mbdb_book_grid_tag_group_by = get_post_meta($post->ID, '_mbdb_book_grid_tag_group_by', true );
	$mbdb_book_grid_genre_tag_group_by = get_post_meta($post->ID, '_mbdb_book_grid_genre_tag_group_by', true );
	$mbdb_book_grid_tag_genre_group_by = get_post_meta($post->ID, '_mbdb_book_grid_tag_genre_group_by', true );
	
	// set mins just in case
	// if ( (int) $mbdb_book_grid_books_across < 1 ) {
		// $mbdb_book_grid_books_across = 1;
	// }
	if ( (int) $mbdb_book_grid_cover_height < 50 ) {
		$mbdb_book_grid_cover_height = 50;
	}
	
	// grab the main sort order
	do_action('mbdb_book_grid_before_set_sort', $mbdb_book_grid_order );
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
		default:
	}

	do_action('mbdb_book_grid_after_set_sort', $mbdb_book_grid_order);

	// make sure the variables are valid, just in case
	
	// if not choosing books by genre, there shouldn't be any genres chosen
	if ( $mbdb_book_grid_books != 'genre' ) {
		$mbdb_book_grid_genre = null;
	}
	// if not choosing books by series, there shouldn't be any series chosen
	if ( $mbdb_book_grid_books != 'series' ) {
		$mbdb_book_grid_series = null;
	}
	// if not choosing books by tag, there shouldn't be any tags chosen
	if ($mbdb_book_grid_books != 'tag') {
		$mbdb_book_grid_tag = null;
	}
	// if not choosing books, there shouldn't be any books chosen
	if ( $mbdb_book_grid_books != 'custom' ) {
		$mbdb_book_grid_custom_select = null;
	}
	// if not grouping by genre, there shouldn't be a genre group by
	if ( $mbdb_book_grid_group_by != 'genre' ) {
		$mbdb_book_grid_genre_group_by = 'none';
		$mbdb_book_grid_genre_tag_group_by = 'none';
	}
	// if not grouping by tag, there shouldn't be a tag group by 
	if ($mbdb_book_grid_group_by != 'tag' ) {
		$mbdb_book_grid_tag_group_by = 'none';
		$mbdb_book_grid_tag_genre_group_by = 'none';
	}
	// if grouping by genre and not by tag, there shouldn't be a genre tag group by 
	if ($mbdb_book_grid_genre_group_by != 'tag') {
		$mbdb_book_grid_genre_tag_group_by = 'none';
	}
	// if grouping by tag and not by genre, there shouldn't be a tag genre group by 
	if ($mbdb_book_grid_tag_group_by != 'genre') {
		$mbdb_book_grid_tag_genre_group_by = 'none';
	}
	// if getting standalones, there shouldn't be any series chosen
	if ( $mbdb_book_grid_books == 'standalone' ) {
		$mbdb_book_grid_series = '0';
	}
	 	// if either group by option is series, the sort field is _mbdb_series_order
		// and grid_sort should be empty
		// because we've already fixed all the other settings, the only one that would be
		// series (if any) is the one the user set
	if ($mbdb_book_grid_group_by == 'series' || $mbdb_book_grid_genre_group_by == 'series' || $mbdb_book_grid_tag_group_by == 'series' || $mbdb_book_grid_tag_genre_group_by == 'series' || $mbdb_book_grid_genre_tag_group_by == 'series') {
		$sort_field = '_mbdb_series_order';
		$sort_order = 'ASC';
		$mbdb_book_grid_order = '';
	}	 
	
	do_action('mbdb_verify_book_grid_options', $mbdb_book_grid_books, $mbdb_book_grid_genre, $mbdb_book_grid_series, $mbdb_book_grid_custom_select, $mbdb_book_grid_group_by, $mbdb_book_grid_genre_group_by, $mbdb_book_grid_tag);
	
	$mbdb_books = array();
	$groupings = array($mbdb_book_grid_group_by);
	if ($mbdb_book_grid_group_by == 'genre') {
		$groupings[] = $mbdb_book_grid_genre_group_by;
		$groupings[] = $mbdb_book_grid_genre_tag_group_by;
	}
	if ($mbdb_book_grid_group_by == 'tag') {
		$groupings[] = $mbdb_book_grid_tag_group_by;
		$groupings[] = $mbdb_book_grid_tag_genre_group_by;
	}
	$groupings[] = 'none';
	$groupings = array_unique($groupings);
	$groupings = apply_filters('mbdb_book_grid_groupings', $groupings);

	$mbdb_books[] = mbdb_book_grid_get_group($groupings, $mbdb_book_grid_books, $mbdb_book_grid_custom_select, $mbdb_book_grid_series, $mbdb_book_grid_genre, $mbdb_book_grid_tag, $sort_field, $sort_order);
	
	do_action('mbdb_book_grid_before_display_grid', $mbdb_books, $mbdb_book_grid_cover_height, $mbdb_book_grid_books_across, 0);
	$mbdb_books = apply_filters('mbdb_book_grid_books', $mbdb_books);
	$content = mbdb_display_grid(  $mbdb_books, $mbdb_book_grid_cover_height, $mbdb_book_grid_books_across, 0 );	
	do_action('mbdb_book_grid_after_display_grid', $mbdb_books, $mbdb_book_grid_cover_height, $mbdb_book_grid_books_across, 0);
	return apply_filters('mbdb_book_grid_content', $content);
}

// v2.1 parameter added: mbdb_book_grid_tag
function mbdb_book_grid_get_group($groupings, $mbdb_book_grid_books, $mbdb_book_grid_custom_select, $mbdb_book_grid_series, $mbdb_book_grid_genre, $mbdb_book_grid_tag, $sort_field, $sort_order) {
	
	$group = array_shift($groupings);
	$books = array();

	// set defaults
	// the one that matches group will be set to '0'
	$series_no_group = $mbdb_book_grid_series;
	$genre_no_group = $mbdb_book_grid_genre;
	$tag_no_group = $mbdb_book_grid_tag;
	$var = $group . '_no_group';
	${$var} = '0';
	
	
	switch ($group) {
		// this case breaks out of the recursion
		case 'none':
			do_action('mbdb_book_grid_before_get_books', $mbdb_book_grid_books, $mbdb_book_grid_custom_select, $sort_field, $sort_order, $mbdb_book_grid_genre, $mbdb_book_grid_series);
			$books =  mbdb_get_books_list( $mbdb_book_grid_books, $mbdb_book_grid_custom_select, $sort_field, $sort_order, $mbdb_book_grid_genre, $mbdb_book_grid_series, $mbdb_book_grid_tag); 
			do_action('mbdb_book_grid_after_get_books', $mbdb_book_grid_books, $mbdb_book_grid_custom_select, $sort_field, $sort_order, $mbdb_book_grid_genre, $mbdb_book_grid_series, $mbdb_book_grid_tag);
			
			return apply_filters('mbdb_book_grid_books_before_group_options', $books, $group, $groupings, $mbdb_book_grid_books, $mbdb_book_grid_custom_select, $mbdb_book_grid_series, $mbdb_book_grid_genre, $mbdb_book_grid_tag, $sort_field, $sort_order);
			break;
		case 'genre':
			$empty = apply_filters('mbdb_book_grid_uncategorized_heading', __('Uncategorized', 'mooberry-book-manager'));
			break;
		case 'series':
			$empty = apply_filters('mbdb_book_grid_standalones_heading', __('Standalones', 'mooberry-book-manager'));
			break;
		case 'tag':
			$empty = apply_filters('mbdb_book_grid_untagged_heading', __('Untagged', 'mooberry-book-manager'));
			break;
	}
	
	$books = array();
	// get standalones/Uncategorized but only if not selected series/genre/tag
	if ($mbdb_book_grid_books != $group) {
		$book_list = mbdb_book_grid_get_group( $groupings, $mbdb_book_grid_books, $mbdb_book_grid_custom_select, $series_no_group, $genre_no_group, $tag_no_group, $sort_field, $sort_order);
		if (count($book_list)>0) {
			$books[$empty] = $book_list;
		} else {
			$books = null;
		}
	}
	
	$books = mbdb_book_grid_get_books_in_taxonomy($books, $group, $groupings, $mbdb_book_grid_books, $mbdb_book_grid_custom_select, $mbdb_book_grid_series, $mbdb_book_grid_genre, $mbdb_book_grid_tag, $sort_field, $sort_order);
			
	return apply_filters('mbdb_book_grid_books_before_group_options', $books, $group, $groupings, $mbdb_book_grid_books, $mbdb_book_grid_custom_select, $mbdb_book_grid_series, $mbdb_book_grid_genre, $mbdb_book_grid_tag, $sort_field, $sort_order);
}

// v2.1 -- added parameter tag
function mbdb_book_grid_get_books_in_taxonomy($books, $group, $groupings, $mbdb_book_grid_books, $mbdb_book_grid_custom_select, $series, $genre, $tag, $sort_field, $sort_order ) {
	
	$terms_query = array('orderby' => 'slug',
						'hide_empty' => true);
	
	// if we're grouping by what we're filtering by, only get terms that we're filtering on
	if ($group == $mbdb_book_grid_books) {
		$terms_query['include'] = ${$group};
	}
	
	$all_terms = get_terms( 'mbdb_' . $group, $terms_query);
	$taxonomy = get_taxonomy('mbdb_' . $group);
	$ids = ${$group};
	// 2.4.1 verify ids is an array
	if (!is_array($ids)) {
		$ids = array($ids);
	}
		foreach ($all_terms as $term) {
			${$group} = array($term->term_id);
			
			// if we've selected only certain type of this group, and the current term isn't one of them,
			// skip to the next one
			if ($group == $mbdb_book_grid_books && array_search($term->term_id, $ids ) === false) {
				continue;
			}
			
			//do_action('mbdb_book_grid_before_get_group', $group, $groupings, $mbdb_book_grid_books, $mbdb_book_grid_custom_select, $series2, $genre2, $sort_field, $sort_order);
			$book_list =  mbdb_book_grid_get_group( $groupings, $mbdb_book_grid_books, $mbdb_book_grid_custom_select, $series, $genre, $tag, $sort_field, $sort_order);
			//do_action('mbdb_book_grid_after_get_group', $group, $groupings, $mbdb_book_grid_books, $mbdb_book_grid_custom_select, $series2, $genre2, $sort_field, $sort_order);
			if (count($book_list)>0) {
				$books[ apply_filters('mbdb_book_grid_heading', $term->name . ' ' . $taxonomy->labels->singular_name)] = $book_list;
			}
		
	}
	return apply_filters('mbdb_book_grid_group_books', $books);
}

function mbdb_display_grid($mbdb_books, $mbdb_book_grid_cover_height, $mbdb_book_grid_books_across,  $l) {
	// grab the coming soon image
	$mbdb_options = get_option('mbdb_options');
	$coming_soon_image = $mbdb_options['coming-soon'];
// indent the grid by 15px per depth level of the array
	do_action('mbdb_book_grid_before_div', $l);
	$content = '<div class="mbm-book-grid-div" style="padding-left:' . (15 * $l) . 'px;">';
	if (count($mbdb_books)>0) {
	//	$content .= '<div style="clear:both">';
		foreach ($mbdb_books as $key => $set) {
			// If a label is set and there's at least one book, print the label
			if ( $key && count( $set ) > 0 ) {
				// set the heading level based on the depth level of the array
				do_action('mbdb_book_grid_before_heading',  $l, $key);
				$content .= '<h' . ( 2 + $l ) . ' class="mbm-book-grid-heading' . ( $l + 1 ) . '">' . esc_html($key) . '</h' . ( 2 + $l ) .'>';
				do_action('mbdb_book_grid_after_heading', $l, $key);
			}
			// because the index of the array could be a genre or series name and not a sequential index use array_keys to get the index
			// if the first element in the array isn't an object that means there's another level in the array
			// and we need to re-call this function recursively to get the next level	
			$the_key = array_keys($set);
			if (count($the_key)>0) {
				if ( gettype( $set[$the_key[0]] ) != 'object') {
					$l++;
					do_action('mbdb_book_grid_before_recursion',$set, $mbdb_book_grid_cover_height, $mbdb_book_grid_books_across,  $l);

					$content .= mbdb_display_grid($set, $mbdb_book_grid_cover_height, $mbdb_book_grid_books_across,  $l);
					
					do_action('mbdb_book_grid_after_recursion', $set, $mbdb_book_grid_cover_height, $mbdb_book_grid_books_across,  $l);
					$l--;
				} else {
					// we're at the inner most level now so we can print out the grid
					do_action('mbdb_book_grid_before_table',  $l);
					// print out each book
					foreach($set as $book) {
						$image = get_post_meta($book->ID, '_mbdb_cover', true);
						
						$content .= '<span class="mbdb_float_grid">';
						if ($image) {
							
							$content .= '<div class="mbdb_grid_image">';
							$content = apply_filters('mbdb_book_grid_before_image', $content, $book->ID, $image);
							$content .= '<a class="mbm-book-grid-title-link" href="' . esc_url(get_permalink($book->ID)) . '"><img  src="' . esc_url($image) . '"></a>';
							$content = apply_filters('mbdb_book_grid_after_image', $content, $book->ID, $image);
							$content .= '</div>';
							
						} else {
							if (isset($coming_soon_image)) {
								$content .= '<div class="mbdb_grid_image">';
								$content = apply_filters('mbdb_book_grid_before_placeholder_image', $content, $book->ID, $coming_soon_image);
								$content .= '<a class="mbm-book-grid-title-link" href="' . esc_url(get_permalink($book->ID)) . '"><img src="' . esc_url($coming_soon_image) . '"></a></div>';
								$content = apply_filters('mbdb_book_grid_after_placeholder_image', $content, $book->ID, $coming_soon_image);
							} else {
								$content .= '<div class="mbdb_grid_no_image">';
								$content = apply_filters('mbdb_book_grid_no_image', $content, $book->ID);
								$content .= '</div>';
							}
						}
					
						
						$content .= '<span class="mbdb_grid_title">';
						$content = apply_filters('mbdb_book_grid_before_link', $content, $book->ID, $book->post_title);
						$content .= '<a class="mbm-book-grid-title-link" href="' . esc_url(get_permalink($book->ID)) . '">' . esc_html($book->post_title) . '</a>';
						$content = apply_filters('mbdb_book_grid_after_link', $content, $book->ID, $book->post_title);
						$content .= '</span></span>';
					}
				}
			} else {
				do_action('mbdb_book_grid_no_books_found');
				$content = apply_filters('mbdb_book_grid_books_not_found', $content . __('Books not found', 'mooberry-book-manager'));
			}
		}
	} else {
		do_action('mbdb_book_grid_no_books_found');
		$content = apply_filters('mbdb_book_grid_books_not_found', $content . __('Books not found', 'mooberry-book-manager'));
	}
	$content .= '</div>'; 
	do_action('mbdb_book_grid_after_div', $l);
	
	
	return apply_filters('mbdb_book_grid_table_content', $content, $l);
}

function mbdb_check_grid_order( $field ) {
	if ($_POST['_mbdb_book_grid_group_by'] != 'none') {
		if ($_POST['_mbdb_book_grid_group_by'] == 'series' || $_POST['_mbdb_book_grid_genre_group_by'] == 'series') {
			$field = 'series';
		}
	}
	return apply_filters('mbdb_book_grid_check_grid_order', $field);
}


