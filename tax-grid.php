<?php

// Set up redirects to series/{series-name} based on query vars
// same for genres and tags
// this is so the book grid can be displayed instead of 
// using a template file that is reliant on theme
add_action('generate_rewrite_rules',  'mbdb_rewrite_rules');
function mbdb_rewrite_rules( $rules ) {
	global $wp_rewrite;
	$mbdb_options = get_option('mbdb_options');
	if (!is_array($mbdb_options)) {
		$mbdb_options = array();
	}
	$new_rules = array();
	$taxonomies = mbdb_tax_grid_objects(); //get_object_taxonomies( 'mbdb_book', 'objects' );
	foreach($taxonomies as $name => $taxonomy) {
		$url = mbdb_get_tax_grid_slug( $name, $mbdb_options );
		/*
		$url = '';
		if (array_key_exists('mbdb_book_grid_' . $name . '_slug', $mbdb_options)) {
			$url = $mbdb_options['mbdb_book_grid_' . $name . '_slug'];
		}
		if ( $url == '') {
			$url = $taxonomy->labels->singular_name;
		}
	
		$url = sanitize_title($url);
*/	
		//$new_rules['series/([^/]*)/?$'] =  'mbdb_tax_grid/?x=x&the-taxonomy=mbdb_series&the-term=$matches[1]&post_type=mbdb_tax_grid';
		$new_rules[$url . '/([^/]*)/?$'] = 'mbdb_tax_grid/test/?x=x&the-taxonomy=' . $name . '&the-term=$matches[1]&post_type=mbdb_tax_grid';
		$pretty_name = str_replace('mbdb_', '', $name);
		$new_rules['book/' . $pretty_name . '/(.+)/?$'] = 'index.php?post_type=mbdb_book&' . $name . '=' . $wp_rewrite->preg_index(1) ;
		
		//$new_rules['mbdb_series/([^/]*)/?$'] =  'mbdb_tax_grid/test/?x=x&the-taxonomy=mbdb_series&the-term=$matches[1]&post_type=mbdb_tax_grid';
		$new_rules[$name . '/([^/]*)/?$'] =  'mbdb_tax_grid/test/?x=x&the-taxonomy=' . $name . '&the-term=$matches[1]&post_type=mbdb_tax_grid';
	}
	
	if (count($new_rules)>0) {
		$wp_rewrite->rules = $new_rules + $wp_rewrite->rules;		
	}
}

// Add query vars to be used for the redirection for series, genres, and tags
add_filter('query_vars', 'mbdb_add_query_vars');
function mbdb_add_query_vars($query_vars) {
	$query_vars[] = "the-term"; 
	$query_vars[] = "the-taxonomy";
	return $query_vars;
}

add_shortcode('mbdb_tax_grid', 'mbdb_tax_grid_content');
function mbdb_tax_grid_content($attr, $content) {
$attr = shortcode_atts(array('taxonomy' => '',
								'term' => ''), $attr);
								
global $wp_query;
	
if ( isset($wp_query->query_vars['the-term'] ) ) {
	$term = trim( urldecode( $wp_query->query_vars['the-term'] ), '/' );
} else {
	$term = $attr['term'];
}
if ( isset( $wp_query->query_vars['the-taxonomy'] ) ) {
	$taxonomy = trim( urldecode( $wp_query->query_vars['the-taxonomy'] ), '/' );
} else {
	$taxonomy = $attr['taxonomy'];
}

if ($taxonomy == '' || $term == '') {

	return __('There was an error!', 'mooberry-book-manager');
}

$selection = str_replace('mbdb_', '', $taxonomy);
//$groups[1] = $selection;
$groups[1] = 'none';
$groups[2] = 'none';
//$current_group = array($selection => 0, 'none' => 0);
$current_group = array( 'none' => 0);
// sort by series if viewing series grid
if ( $taxonomy == 'mbdb_series') {
	$sort = mbdb_set_sort($groups, 'series_order');
} else {
	$sort = mbdb_set_sort($groups, 'titleA');
}

// set sort varialbles
//list( $orderby, $order ) = MBDB()->books->get_sort_fields( $sort );

// get id
$term_obj = get_term_by( 'slug', $term, $taxonomy);
if ($term_obj != null) {
	$selected_ids = array((int) $term_obj->term_id);
} else {
	$selected_ids = null;
}

$books = apply_filters('mbdb_tax_grid_get_group', mbdb_get_group(1, $groups, $current_group, $selection, $selected_ids, $sort, null ), $groups, $current_group, $selection, $selected_ids, $sort, $term); //$orderby, $order, null);

/********************* term meta ***************************************/
if (  function_exists( 'get_term_meta' ) ) {
	$content = '<p>' . get_term_meta( $selected_ids[0], $taxonomy . '_book_grid_description', true ) . '</p>';
	$content2 = '<p>' . get_term_meta ($selected_ids[0], $taxonomy . '_book_grid_description_bottom', true ) . '</p>';
} else {
	$content = '';
	$content2 = '';
}
return $content  . mbdb_display_grid($books, 0) . $content2;
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

// this is for themes that use the updated wp_title filters
add_filter( 'wp_title_parts', 'mbdb_tax_grid_document_title', 99, 1);
function mbdb_tax_grid_document_title( $title ) {
	$title[0] =  mbdb_tax_grid_document_title_pre44( $title[0] );
	return $title;
	
}

// this if for themes that don't use the updated wp_title filters
// priority 20 puts it after Yoast SEO
add_filter( 'wp_title', 'mbdb_tax_grid_document_title_pre44', 20, 1);
function mbdb_tax_grid_document_title_pre44( $title) {
	if (get_post_type() == 'mbdb_tax_grid') {
		$title =  mbdb_get_tax_title( $title);
	}
		return $title;
	
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
