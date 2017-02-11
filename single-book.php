<?php

add_shortcode( 'book_title', 'mbdb_shortcode_title'  );
add_shortcode( 'book_cover', 'mbdb_shortcode_cover'  );
add_shortcode( 'book_subtitle', 'mbdb_shortcode_subtitle'  );
add_shortcode( 'book_summary', 'mbdb_shortcode_summary'  );
add_shortcode( 'book_publisher', 'mbdb_shortcode_publisher'  );
add_shortcode( 'book_published', 'mbdb_shortcode_published'  );
add_shortcode( 'book_goodreads', 'mbdb_shortcode_goodreads'  );
add_shortcode( 'book_length', 'mbdb_shortcode_length' );
add_shortcode( 'book_excerpt', 'mbdb_shortcode_excerpt'  );
add_shortcode( 'book_additional_info', 'mbdb_shortcode_additional_info' );
add_shortcode( 'book_genre', 'mbdb_shortcode_genre'  );
add_shortcode( 'book_reviews', 'mbdb_shortcode_reviews'  );
add_shortcode( 'book_buylinks', 'mbdb_shortcode_buylinks'  );
add_shortcode( 'book_downloadlinks', 'mbdb_shortcode_downloadlinks'  );
add_shortcode( 'book_serieslist', 'mbdb_shortcode_serieslist');
add_shortcode( 'book_series', 'mbdb_shortcode_series');
add_shortcode( 'book_tags', 'mbdb_shortcode_tags');
add_shortcode( 'book_illustrator', 'mbdb_shortcode_illustrator');
add_shortcode( 'book_editor', 'mbdb_shortcode_editor');
add_shortcode( 'book_cover_artist', 'mbdb_shortcode_cover_artist');
add_shortcode( 'book_links', 'mbdb_shortcode_links');
add_shortcode( 'book_editions', 'mbdb_shortcode_editions');
add_shortcode( 'mbdb_book', 'mbdb_shortcode_book');
add_shortcode( 'book_kindle_preview', 'mbdb_kindle_preview');
		
/******************************************************************
 *  
 *						 GENERIC FUNCTIONS
 * 
 ******************************************************************/
		
/**
 *  Get book ID based on slug or current post
 *  
 *  
 *  
 *  @since 1.0
 *  @since 3.0 use MBDB obj
 *  
 *  @param [string] $slug slug/post_name of book (optional)
 *  
 *  @return ID of book. 0 if book isn't found
 *  
 *  
 */
function mbdb_get_book_ID( $slug = '' ) {
	global $post;
	if ( $slug == '' ) {
		if ($post) {
			return $post->ID;
		} 
	} else {
		$book = MBDB()->books->get_by_slug($slug); 

		if ( $book ) {
			return $book->book_id;
		} 
	}
	return 0;
}

/**
 *  Retrieves book data based on meta_data field ID
 * 
 *  Gets book by either ID or slug 
 *  
 *  @since 2.0
 *  @since 3.0 use MBDB obj
 *  
 *  @param string $meta_data meta_data field ID of data to return
 *  @param string $book slug of book to retrieve data for (optional)
 *  
 *  @return mixed data or false if book couldn't be found or if data was blank
 *  				( make sure to test with === false when calling this function )
 *  
 *  @access public
 *  
 */
function mbdb_get_book_data( $meta_data, $book = '') {
	$bookID = mbdb_get_book_ID( $book );
	if ( $bookID != 0 ) {
		$book_data = MBDB()->books->get_data( $meta_data, $bookID );
		if ( $book_data != '' && $book_data != null ) {
			return $book_data;
		}
	}
	return false;
}

/**
 * Return the output for a blank element  
 *  
 *  Returns the html for when an element doesn't have any data
 *  including the proper css classes
 *  
 *  @since 2.0
 *  @param string $classname    identifies the datafield in the class name
 *  @param string $blank_output what should be displayed if the data is blank
 *  
 *  @return string 	html output
 */
function mbdb_blank_output( $classname, $blank_output) {
	return apply_filters('mbdb_shortcode_' . $classname, '<span class="mbm-book-' . $classname . '"><span class="mbm-book-' . $classname . '-blank">' . esc_html($blank_output) . '</span></span>');
}



/******************************
 *  
 *			SUMMARY
 *
 *****************************/

/**
 *  Output the summary including the proper css classes
 *  
 *  Includes the label & after text
 *  Adds auto <p> tags for newlines
 *  
 *  @since 1.0
 *  @param string $book_data data to output
 *  @param array $attr       args passed in to shortcode
 *  
 *  @return html output
 *  
 *  @access public
 */
function mbdb_output_summary($book_data, $attr) {
	
	$output = '<div class="mbm-book-summary"><span class="mbm-book-summary-label">' . esc_html($attr['label']) . '</span><span class="mbm-book-summary-text">';
	$output .= mbdb_get_wysiwyg_output( $book_data);
	//. do_shortcode(wpautop(wp_kses_post($book_data))) . 
	
	$output .= '</span><span class="mbm-book-summary-after">' . esc_html($attr['after']) . '</span></div>';
	
	return apply_filters('mbdb_shortcode_summary', $output);
}

/**
 *  
 *  Get the book's summary
 *  
 *  
 *  @since 2.0
 *  @since 3.0 use custom table column name instead of post_meta
 *  
 *  @return mixed data or false if book couldn't be found or if data was blank
 *  				( make sure to test with === false when calling this function )
 *  
 *  @access public
 *  
 */
function mbdb_get_summary_data($book = '') {
	 return mbdb_get_book_data('summary', $book);
}

/**
 *  
 *  Shortcode function for book's summary
 *  
 *  
 *  @since 1.0
 *  
 *  @param [array]	parameters
 *  @param [string]	content
 *  
 *  @return HTML output
 *  
 *  @access public
 *  
 */
function mbdb_shortcode_summary($attr, $content) {
	$attr = shortcode_atts(array('label' => '',
									'after' => '',
									'blank' => '',
									'book' => ''), $attr);
									
	$book_data = mbdb_get_summary_data( $attr['book']);
	if ($book_data === false) {
		return mbdb_blank_output('summary', $attr['blank']);
	} else {
		return mbdb_output_summary($book_data, $attr);
	}
																	
}


/*******************************
	PUBLICATION DATE
*****************************/
	
/**
 *  
 *  Get the book's published date
 *  
 *  
 *  @since 2.0
 *  @since 3.0 use custom table column name instead of post_meta
 *  
 *  @return mixed data or false if book couldn't be found or if data was blank
 *  				( make sure to test with === false when calling this function )
 *  
 *  @access public
 *  
 */	
function mbdb_get_published_data($book = '' ) {
	return mbdb_get_book_data('release_date', $book);
}

/**
 *  
 *  Shortcode function for book's published date
 *  
 *  
 *  @since 1.0
 *  
 *  @param [array]	parameters
 *  @param [string]	content
 *  
 *  @return HTML output
 *  
 *  @access public
 *  
 */
function mbdb_shortcode_published($attr, $content) {
	$attr = shortcode_atts(array('format' => 'short',
									'label' => '',
									'after' => '',
									'blank' => '',
									'book' => ''), $attr);	
	$mbdb_published = mbdb_get_published_data($attr['book']);
	if ($mbdb_published === false) {
		return mbdb_blank_output('published', $attr['blank']);
	} else {
		return mbdb_output_published($mbdb_published, $attr);
	}
	
}
/**
 *  output the book's published date
 *  
 *  
 *  
 *  @since 2.0
 *  @since 3.0 use WP's default date format if one is not specified
 *  
 *  @param [string] $mbdb_published date to output
 *  @param [array] $attr           Parameters
 *  
 *  @return Return_Description
 *  
 *  @access public
 */

function mbdb_output_published($mbdb_published, $attr) {
	switch ($attr['format']) {
		case 'short':
			/* translators: short date format. see http://php.net/date */
			$format = _x('m/d/Y', 'short date format. see http://php.net/date', 'mooberry-book-manager');
			break;
		case 'long':
			/* translators: long date format. see http://php.net/date */
			$format = _x('F j, Y', 'long date format. see http://php.net/date', 'mooberry-book-manager');
			break;
		case 'default':
			$format = get_option('date_format');
			break;
	}
		return apply_filters('mbdb_shortcode_published',  '<span class="mbm-book-published"><span class="mbm-book-published-label">' . esc_html($attr['label']) . '</span><span class="mbm-book-published-text">' .  date_i18n($format, strtotime($mbdb_published)) . '</span><span class="mbm-book-published-after">' .  esc_html($attr['after']) . '</span></span>');
}


/*******************************
	GOODREADS
*****************************/

/**
 *  Output Add to Goodreads button
 *  If can't find the Goodreads image, just use the text in the params
 *  
 *  
 *  @since 2.0
 *  @since 3.0 added alt text
 *  
 *  @param [string] $mbdb_goodreads goodreads url
 *  @param [array] $attr           Parameters
 *  
 *  @return HTML output
 *  
 *  @access public
 */	
function mbdb_output_goodreads($mbdb_goodreads, $attr) {
	$mbdb_options = get_option('mbdb_options');
	
	if (empty($mbdb_options['goodreads'])) { 
		return apply_filters('mbdb_shortcode_goodreads', '<div class="mbm-book-goodreads"><span class="mbm-book-goodreads-label">' . esc_html($attr['label']) . '</span><A class="mbm-book-goodreads-link" HREF="' . esc_url($mbdb_goodreads) . '" target="_new"><span class="mbm-book-goodreads-text">' . $attr['text'] . '</span></A><span class="mbm-book-goodreads-after">' . esc_html($attr['after']) . '</span></div>'); 
	} else {
		/*
		if (array_key_exists('goodreads-id', $mbdb_options)) {
			$imageID = $mbdb_options['goodreads-id'];
		} else {
			$imageID = 0;
		}
		$alt = mbdb_get_alt_text( $imageID, __('Add to Goodreads', 'mooberry-book-manager') ); */
		$alt = __('Add to Goodreads', 'mooberry-book-manager');
		$url = esc_url($mbdb_options['goodreads']);
		if (is_ssl()) {
			$url = preg_replace('/^http:/', 'https:', $url);
		}
		return apply_filters('mbdb_shortcode_goodreads', '<div class="mbm-book-goodreads"><span class="mbm-book-goodreads-label">' . esc_html($attr['label']) . '</span><A class="mbm-book-goodreads-link" HREF="' . esc_url($mbdb_goodreads) . '" target="_new"><img class="mbm-book-goodreads-image" src="' . $url . '"' . $alt . '/></A><span class="mbm-book-goodreads-after">' . esc_html($attr['after']) . '</span></div>');
	}
}
/**
 *  
 *  Get the book's goodreads link
 *  
 *  
 *  @since 2.0
 *  @since 3.0 use custom table column name instead of post_meta
 *    
 *  @return mixed data or false if book couldn't be found or if data was blank
 *  				( make sure to test with === false when calling this function )
 *  
 *  @access public
 *  
 */
function mbdb_get_goodreads_data( $book = '') {
	return  mbdb_get_book_data('goodreads', $book);
}

/**
 *  
 *  Shortcode function for book's goodreads link
 *  
 *  
 *  @since 1.0
 *  
 *  @param [array]	parameters
 *  @param [string]	content
 *  
 *  @return HTML output
 *  
 *  @access public
 *  
 */
function mbdb_shortcode_goodreads($attr, $content) {
	$attr = shortcode_atts(array('text' => __('View on Goodreads', 'mooberry-book-manager'),
								'label' => '',
								'after' => '',
								'blank' => '',
								'book' => ''), $attr);
	$mbdb_goodreads = mbdb_get_goodreads_data($attr['book']);
	if ($mbdb_goodreads === false) {
		return mbdb_blank_output('goodreads', $attr['blank']);
	} else {
		return mbdb_output_goodreads($mbdb_goodreads, $attr);
	}
	
}

/******************************************
	EXCERPT
**************************************/
/**
 *  Output the book's excerpt
 *  Truncate it around $attr['length'] characters after a paragraph
 *  behind a "show more" link
 *  
 *  
 *  @since 2.0
 *  
 *  @param [string] $mbdb_excerpt excerpt to output
 *  @param [array] $attr         Parameters
 *  
 *  @return html output
 *  
 *  @access public
 */
function mbdb_output_excerpt($mbdb_excerpt, $attr) {
	 $mbdb_excerpt = wpautop($mbdb_excerpt);
	 $excerpt1 = '';
	 $excerpt2 = '';
	 if ($attr['length'] == 0) {
		$excerpt1 = $mbdb_excerpt;
		$excerpt2 = '';
	 } else {
		if (preg_match('/^(.{1,' . $attr['length'] . '}<\/p>)(.*)/s', $mbdb_excerpt, $match))	{
			$excerpt1 = $match[1];
			$excerpt2 = $match[2];
		} else {
			// if no paragraphs in 1000 characters then take the first paragraph
			if (preg_match('/^(.*<\/p>)(.*?)/sU', $mbdb_excerpt, $match)) {
				$excerpt1 = $match[1];
				$excerpt2 = $match[2];
			}
		}
	 }
	$html_output = '<div class="mbm-book-excerpt">
		<span class="mbm-book-excerpt-label">' . esc_html($attr['label']) . '</span>
		<span class="mbm-book-excerpt-text">';
	$html_output .= mbdb_get_wysiwyg_output($excerpt1);
	
	if (trim($excerpt2) != '' ) {
		$html_output .= '<a name="more" class="mbm-book-excerpt-read-more">' . __('READ MORE', 'mooberry-book-manager') . '</a>
	<span class="mbm-book-excerpt-text-hidden">';
	$html_output .= mbdb_get_wysiwyg_output($excerpt2);
	$html_output .= '<a class="mbm-book-excerpt-collapse" name="collapse">' . __('COLLAPSE', 'mooberry-book-manager') . '</a></span>';
	}
	
	$html_output .=' </span><span class="mbm-book-excerpt-after">' . esc_html($attr['after']) . '</span></div>';
	return apply_filters('mbdb_shortcode_excerpt', $html_output);
		
}

/**
 *  
 *  Get the book's excerpt
 *  
 *  
 *  @since 2.0
 *  @since 3.0 use custom table column name instead of post_meta
 *  
 *  @return mixed data or false if book couldn't be found or if data was blank
 *  				( make sure to test with === false when calling this function )
 *  
 *  @access public
 *  
 */
function mbdb_get_excerpt_data($book = '') {
	// get exerpt or kindle preview text
	// return false if not entered
	$excerpt_type = mbdb_get_book_data('excerpt_type', $book);

	if ( $excerpt_type == 'kindle' ) {
		//$asin = mbdb_get_book_data('kindle_asin', $book);
		$preview_code = mbdb_get_book_data('kindle_preview', $book);
		
		if ( strpos($preview_code, 'iframe' ) !== false || strpos($preview_code, 'href') !== false  ) {
			
			// iframe or link, convert into a ASIN
			preg_match_all('/asin=([a-zA-Z0-9]*)&/', $preview_code, $matches);
			
			if ( count( $matches ) > 1 ) {
				$asin = $matches[1][0];
				$bookID = mbdb_get_book_ID( $book );
				$update_book['_mbdb_kindle_preview'] = $asin;
				MBDB()->books->save( array('_mbdb_kindle_preview' => $asin), $bookID );
			} else {
				$asin = '';
			}
		} else {
			$asin = $preview_code;
		}
		
		return $asin;
	} else {
		return mbdb_get_book_data('excerpt', $book);
	}
}

/**
 *  
 *  Shortcode function for book's excerpt
 *  
 *  
 *  @since 1.0
 *  
 *  @param [array]	parameters
 *  @param [string]	content
 *  
 *  @return HTML output
 *  
 *  @access public
 *  
 */
function mbdb_shortcode_excerpt($attr, $content) {
	$attr = shortcode_atts(array('label' => '',
									'after' => '',
									'blank' => '',
									'length' => '0',
									'book' => ''), $attr);
									
	$mbdb_excerpt = mbdb_get_excerpt_data( $attr['book']);
	if ($mbdb_excerpt === false) {
		return mbdb_blank_output('excerpt', $attr['blank']);
	} else {
		$excerpt_type = mbdb_get_book_data('excerpt_type', $attr['book']);
		if ( $excerpt_type == 'kindle' ) {
			return do_shortcode('[book_kindle_preview asin="' . $mbdb_excerpt . '"]');
		} else {
			return mbdb_output_excerpt($mbdb_excerpt, $attr);
		}
	}
}

/*******************************************
	ADDITIONAL INFO
*******************************************/
/**
 *  Output book's additional info
 *  
 *  
 *  
 *  @since 2.0
 *  @since 3.3.3 Added do_shortcode
 *  
 *  @param [string] $mbdb_additional_info string to output
 *  @param [array] $attr                 Parameters
 *  
 *  @return HTML output
 *  
 *  @access public
 */
function mbdb_output_additional_info($mbdb_additional_info, $attr) {
	 
	 $html_output = '<div class="mbm-book-additional-info">';
	 $html_output .= mbdb_get_wysiwyg_output( $mbdb_additional_info ); //do_shortcode(wpautop(wp_kses_post($mbdb_additional_info)));
	 $html_output .= '</div>';
	 return apply_filters('mbdb_shortcode_additional_info', $html_output);
}
/**
 *  
 *  Get the book's additional info
 *  
 *  
 *  @since 2.0
 *  @since 3.0 use custom table column name instead of post_meta
 *  
 *  @return mixed data or false if book couldn't be found or if data was blank
 *  				( make sure to test with === false when calling this function )
 *  
 *  @access public
 *  
 */
function mbdb_get_additional_info_data($book = '') {
	return mbdb_get_book_data('additional_info', $book);
}

/**
 *  
 *  Shortcode function for book's additioanl info
 *  
 *  
 *  @since 
 *  
 *  @param [array]	parameters
 *  @param [string]	content
 *  
 *  @return HTML output
 *  
 *  @access public
 *  
 */
function mbdb_shortcode_additional_info($attr, $content) {
	$attr = shortcode_atts(array(	'blank' => '',
									'book' => ''), $attr);
									
	$mbdb_additional_info = mbdb_get_additional_info_data( $attr['book']);
	if ($mbdb_additional_info === false) {
		return mbdb_blank_output('additional_info', $attr['blank']);
	} else {
		return mbdb_output_additional_info($mbdb_additional_info, $attr);
	}
}



/********************************************
	TAXONOMIES
******************************************/
/**
 *  output one of the book's taxonomies in either a bulleted list
 *  or a comma-delimited list
 *  
 *  
 *  
 *  @since 
 *  
 *  @param [string] $classname  name for css class that identifies the taxonomy
 *  @param [array] $mbdb_terms list of terms
 *  @param [string] $permalink  permalink for tax grid
 *  @param [string] $taxonomy   name of taxonomy
 *  @param [array] $attr       Parameters
 *  
 *  @return html output
 *  
 *  @access public
 */
function mbdb_output_taxonomy($classname, $mbdb_terms, $permalink, $taxonomy, $attr) {
	if ($attr['delim'] == 'comma') {
		$delim = ', ';
		$after = '';
		$before = '';
		$begin = '';
		$end = '';
	} else {
		$delim = '</li><li class="' . $classname . '-listitem">';
		$before = '<li class="' . $classname . '-listitem">';
		$after = '</li>';
		$begin = '<ul class="' . $classname . '-list">';
		$end = '</ul>';
	}
	
	$list = '';
	$list .= $before;
	
	foreach ($mbdb_terms as $term) {
		
		$list .= '<a class="' . $classname . '-link" href="';
		
		// check if using permalinks
		if ( get_option('permalink_structure') !='' ) {
			$list .= home_url($permalink . '/' . $term->slug);
		} else {
			$list .= home_url('?the-taxonomy=' . $taxonomy . '&the-term=' . $term->slug . '&post_type=mbdb_tax_grid');
		}
		
		$list .= '"><span class="' . $classname . '-text">' . $term->name . '</span></a>';
		
		if ( in_array( $term->taxonomy, mbdb_taxonomies_with_websites() ) ) {
			$website = get_term_meta( $term->term_id, $term->taxonomy . '_website', true);
			if ($website != '' ) {
				$list .= ' (<a class="' . $classname . '-website" href="' . $website . '" target="_new">' . __('Website', 'mooberry-book-manager') . '</a>)';
			}
		}
		
		$list .= $delim;
	}
	
	// there's an extra $delim added to the string
	if ($attr['delim']=='list') {
		// trim off the last </li> by cutting the entire $delim off and then adding in the </li> back in
		$list = substr($list, 0, strripos($list, $delim)) . '</li>';
	} 	else {
		// trim off the last space and comma
		$list = substr($list, 0, -2);
	}
	return apply_filters('mbdb_shortcode_' . $permalink . '_taxonomy',  '<div class="' . $classname . '" style="display:inline;">' . $begin . $list  . $end . '</div>');

}

/**
 *  Get terms for a book's taxonomy
 *  
 *  @since 2.0
 *  
 *  @param [string] $taxonomy taxonomy to retrieve
 *  @param [string] $book slug (optional)
 *  
 *  @return mixed 	list of terms or false if book couldn't be found 
 *  				or if data was blank
 *  
 *  @access public
 */
function mbdb_get_taxonomy_data($taxonomy, $book = '' ) {
	$bookID = mbdb_get_book_ID($book);
	if ($bookID != 0) {
		$mbdb_terms = get_the_terms( $bookID, $taxonomy);
	
		if (!$mbdb_terms) { 
			return false;
		} else {
			return $mbdb_terms;
		}
	} else {
		return false;
	}
}

/**
 *  
 *  Shortcode function for book's series
 *  
 *  
 *  @since 1.0
 *  
 *  @param [array]	parameters
 *  @param [string]	content
 *  
 *  @return HTML output
 *  
 *  @access public
 *  
 */		
function mbdb_shortcode_series( $attr, $content) {
	return mbdb_shortcode_taxonomy($attr, 'mbdb_series', 'series');
}
/**
 *  
 *  Shortcode function for book's tags
 *  
 *  
 *  @since 1.0
 *  
 *  @param [array]	parameters
 *  @param [string]	content
 *  
 *  @return HTML output
 *  
 *  @access public
 *  
 */
function mbdb_shortcode_tags( $attr, $content) {
	return mbdb_shortcode_taxonomy($attr, 'mbdb_tag', 'book-tag');
}

/**
 *  
 *  Shortcode function for book's genres
 *  
 *  
 *  @since 1.0
 *  
 *  @param [array]	parameters
 *  @param [string]	content
 *  
 *  @return HTML output
 *  
 *  @access public
 *  
 */
function mbdb_shortcode_genre($attr, $content) {
	return mbdb_shortcode_taxonomy($attr, 'mbdb_genre', 'genre');
}

/**
 *  
 *  Shortcode function for book's editors
 *  
 *  
 *  @since 1.0
 *  
 *  @param [array]	parameters
 *  @param [string]	content
 *  
 *  @return HTML output
 *  
 *  @access public
 *  
 */
function mbdb_shortcode_editor($attr, $content) {
	return mbdb_shortcode_taxonomy($attr, 'mbdb_editor', 'editor');
}

/**
 *  
 *  Shortcode function for book's illustrators
 *  
 *  
 *  @since 1.0
 *  
 *  @param [array]	parameters
 *  @param [string]	content
 *  
 *  @return HTML output
 *  
 *  @access public
 *  
 */
function mbdb_shortcode_illustrator($attr, $content) {
	return mbdb_shortcode_taxonomy($attr, 'mbdb_illustrator', 'illustrator');
}

/**
 *  
 *  Shortcode function for book's cover artists
 *  
 *  
 *  @since 1.0
 *  
 *  @param [array]	parameters
 *  @param [string]	content
 *  
 *  @return HTML output
 *  
 *  @access public
 *  
 */
function mbdb_shortcode_cover_artist($attr, $content) {
	return mbdb_shortcode_taxonomy($attr, 'mbdb_cover_artist', 'cover-artist');
}

/**
 *  
 *  Get data for a book's taxonomy and output it
 *  
 *  
 *  @since 1.0
 *  @since 3.0 get permalink from options
 *  
 *  @param [array]	parameters
 *  @param [string]	taxonomy to get
 *  @param [string]	default permalink and css class
 *  
 *  @return HTML output
 *  
 *  @access public
 *  
 */
function mbdb_shortcode_taxonomy($attr, $taxonomy, $default_permalink) {

	$attr = shortcode_atts(array('delim' => 'comma',
								'blank' => '',
								'book' => ''), $attr);
	
	// v3.0 get permalink from options
	$permalink =  mbdb_get_tax_grid_slug( $taxonomy ); /* $mbdb_options['mbdb_book_grid_' . $taxonomy . '_slug'];
	if ($permalink == '') {
		$permalink = $default_permalink;
	}
	*/
	$mbdb_terms = mbdb_get_taxonomy_data( $taxonomy, $attr['book']);
	if ($mbdb_terms === false) {
		return mbdb_blank_output($permalink . '_taxonomy', $attr['blank']);
	} else {
		return mbdb_output_taxonomy('mbm-book-' . $default_permalink, $mbdb_terms, $permalink, $taxonomy, $attr);
	}
}


/***********************************************
	TITLE
*******************************************/
/**
 *  
 *  Shortcode function for book's title
 *  This function is different than the others because every book
 *  has a title. it's not an optional property. Also title comes
 *  from the post object not the custom table or post meta
 *  
 *  Title woudl only be blank if an invalid book is passed in
 *  
 *  @since 1.0
 *  @since 3.0 use MBDB object
 *  
 *  @param [array]	parameters
 *  @param [string]	content
 *  
 *  @return HTML output
 *  
 *  @access public
 *  
 */
 
function mbdb_get_title_data( $slug ) {
	global $post;
	
	if ($slug == '') {
		$title = $post->post_title;
	} else {
		$book = MBDB()->books->get_by_slug($slug);
		if ($book) {
			$title = $book->post_title;
		} else {
			$title = '';
		}
	}
	return $title;
}


function mbdb_shortcode_title( $attr, $content) {
	$attr = shortcode_atts(array('book' => '',
								'blank' => ''), $attr);
								
	$title = mbdb_get_title_data( $attr['book'] );
	
	if ($title != '') {
		$html = '<span class="mbm-book-title"><span class="mbm-book-title-text">' . esc_html($title) . '</span></span>';
	} else {
		$html = '<span class="mbm-book-title"><span class="mbm-book-title-blank">' . esc_html($attr['blank']) . '</span></span>';
	}

	return apply_filters('mbdb_shortcode_title', $html);
}

/************************************************
	SUBTITLE
*********************************************/

/**
 *  
 *  Get the book's subtitle
 *  
 *  
 *  @since 2.0
 *  @since 3.0 use custom table column name instead of post_meta
 *  
 *  @return mixed data or false if book couldn't be found or if data was blank
 *  				( make sure to test with === false when calling this function )
 *  
 *  @access public
 *  
 */
function mbdb_get_subtitle_data( $book = '') {
	return mbdb_get_book_data('subtitle', $book);
}

/**
 *  Returns the output for subtitle
 *  
 *  
 *  @since 3.0
 *  
 *  @param string $classname identifies the data field in the css class
 *  @param string $data      data to display
 *  
 *  @return string	html output
 */
function mbdb_output_subtitle( $data, $attr ) {
	return apply_filters('mbdb_shortcode_subtitle', '<span class="mbm-book-subtitle"><span class="mbm-book-subtitle-text">' . esc_html($data) . '</span></span>');
}

/**
 *  
 *  Shortcode function for book's subtitle
 *  
 *  
 *  @since 1.0
 *  
 *  @param [array]	parameters
 *  @param [string]	content
 *  
 *  @return HTML output
 *  
 *  @access public
 *  
 */
function mbdb_shortcode_subtitle($attr, $content) {
	$attr = shortcode_atts(array('book' => '',
								'blank' => ''), $attr);
								
	
	$book_data = mbdb_get_subtitle_data( $attr['book'] );
	if ($book_data === false) {
		return mbdb_blank_output('subtitle', $attr['blank']);
	} else {
		return mbdb_output_subtitle( $book_data, $attr);
	}
}



/******************************
	PUBLISHER 
	****************************/
/**
 *  
 *  Get the book's publisher
 *  
 *  
 *  @since 2.0
 *  @since 3.0 use custom table column name instead of post_meta
 *  
 *  @return mixed data or false if book couldn't be found or if data was blank
 *  				( make sure to test with === false when calling this function )
 *  
 *  @access public
 *  
 */
function mbdb_get_publisher_data($book = '') {
	$publisherID =  mbdb_get_book_data('publisher_id', $book);
	if ($publisherID === false) {
		return false;
	}
	$publisher = mbdb_get_publisher_info($publisherID);
	if ($publisher == null) {
		return false;
	} else {
		return $publisher;
	}
}

/**
 *  
 *  Shortcode function for book's summary
 *  
 *  
 *  @since 1.0
 *  
 *  @param [array]	parameters
 *  @param [string]	content
 *  
 *  @return HTML output
 *  
 *  @access public
 *  
 */
function mbdb_shortcode_publisher($attr, $content) {
	$attr = shortcode_atts(array('label' => '',
									'after' => '',
									'blank' => '',
									'book' => ''), $attr);
	$book_data = mbdb_get_publisher_data($attr['book']);
	if ($book_data === false) {
		return mbdb_blank_output('publisher', $attr['blank']);
	} else {
		return mbdb_output_publisher($book_data, $attr);
	}
	
}

/**
 *  
 *  Outputs the book's publisher
 *  
 *  
 *  @since 
 *  
 *  @param [array] $book_data Publisher array
 *  @param [array] $attr      Parameters
 *  
 *  @return Return_Description
 *  
 *  @access public
 */
function mbdb_output_publisher($book_data, $attr) {
	if (array_key_exists('name', $book_data)) {
		$mbdb_publisher = $book_data['name'];
	} else {
		$mbdb_publisher = '';
	}
	if (array_key_exists('website', $book_data)) {
		$mbdb_publisherwebsite = $book_data['website'];
	} else {
		$mbdb_publisherwebsite = '';
	}
	
	if (empty($mbdb_publisherwebsite)) {
		$text = '<span class="mbm-book-publisher-text">' . esc_html($mbdb_publisher) . '</span>';
	} else {
		$text = '<A class="mbm-book-publisher-link" HREF="' . esc_url($mbdb_publisherwebsite) . '" target="_new"><span class="mbm-book-publisher-text">' . esc_html($mbdb_publisher) . '</span></a>';
	}
	
	return apply_filters('mbdb_shortcode_publisher',  '<span class="mbm-book-publisher"><span class="mbm-book-publisher-label">' . esc_html($attr['label']) . '</span>' . $text . '<span class="mbm-book-publisher-after">' . esc_html($attr['after']) . '</span></span>'); 

	
}

/*****************************************
	SERIES LIST
***************************************/
/**
 *  
 *  Output series in a UL list with a heading
 *  (default Part of the ___ Series:)
 *  
 *  
 *  @since 
 *  
 *  @param [array] $mbdb_series series
 *  @param [array] $attr        Parameters
 *  
 *  @return Return_Description
 *  
 *  @access public
 */
function mbdb_output_serieslist($mbdb_series, $attr) {

	$bookID = mbdb_get_book_ID($attr['book']);
	$classname = 'mbm-book-serieslist';
	$series_name = '';

	foreach($mbdb_series as $series) {
		$series_name .=  '<div class="' . $classname . '-seriesblock"><span class="' . $classname . '-before">' . esc_html($attr['before']) . '</span>';
		$series_name .= '<a class="' . $classname . '-link" href="';
		
		if ( get_option('permalink_structure') !='' ) {
			// v3.0 get permalink from options
			$permalink =  mbdb_get_tax_grid_slug( 'mbdb_series' );  /*$mbdb_options['mbdb_book_grid_mbdb_series_slug'];
			if ($permalink == '') {
				$permalink = 'series';
			}
			*/
			
			$series_name .= home_url( $permalink . '/' .  $series->slug);
		} else {
			$series_name .= home_url('?the-taxonomy=mbdb_series&the-term=' . $series->slug . '&post_type=mbdb_tax_grid');
		}
		$series_name .=  '"><span class="' . $classname . '-text">' . $series->name . '</span></a>';
		$series_name .= '<span class="' . $classname . '-after">' . esc_html($attr['after']) . '</span>';
		$series_name .= mbdb_series_list($attr['delim'],  $series->term_id, $bookID);
		$series_name .=  '</div>';
	}
	return apply_filters('mbdb_shortcode_serieslist', '<div class="' . $classname . '">' . $series_name . '</div>');
}

/**
 *  
 *  Shortcode function for book's series list
 *  
 *  
 *  @since 1.0
 *  
 *  @param [array]	parameters
 *  @param [string]	content
 *  
 *  @return HTML output
 *  
 *  @access public
 *  
 */
function mbdb_shortcode_serieslist($attr, $content) {
	$attr = shortcode_atts(array('blank' => '',
									'before' => __('Part of the ', 'mooberry-book-manager'),
									'after' => __(' series:', 'mooberry-book-manager'),
									'delim' => 'list',
									'book' => ''), $attr);
	
	$mbdb_series = mbdb_get_taxonomy_data('mbdb_series', $attr['book']);
	if ($mbdb_series === false) {
		return mbdb_blank_output('serieslist', $attr['blank']);
	} else {
		
		return mbdb_output_serieslist( $mbdb_series, $attr);
	}
}
	
/**
 *  Outputs list of books in series with links to individual books
 *  except for the current book
 *  
 *  
 *  @since 1.0
 *  
 *  @param [string] $delim  list or comma
 *  @param [string] $series series to print out (term)
 *  @param [int] $bookID current bookid
 *  
 *  @return html output
 *  
 *  @access public
 */
function mbdb_series_list($delim, $series, $bookID) {
	$classname = 'mbm-book-serieslist';
	
	$books = MBDB()->books->get_books_by_taxonomy( null, 'series', $series, 'series_order', 'ASC'); 
	
	
	if ($delim=='list') {
		$list = '<ul class="' . $classname . '-list">';
	} else {
		$list = '';
	}
	foreach ($books as $book) {
		if ($delim=='list') {
			$list .= '<li class="' . $classname . '-listitem">';
		}
		if ($book->book_id != $bookID) {
			$list .= '<A class="' . $classname . '-listitem-link" HREF="' . get_permalink($book->book_id) . '">';
		}
		$list .= '<span class="' . $classname . '-listitem-text">' . esc_html($book->post_title) . '</span>';
		if ($book->book_id != $bookID) {
			$list .='</a>';
		}
		if ($delim=='list') {
			$list .= '</li>';
		} else {
			$list .= ', ';
		}
	}
	if ($delim=='list') {
		$list .= '</ul>';
	} 	else {
		// trim off the last space and comma
		$list = substr($list, 0, -2);
	}
	return $list;
}

/**********************************
	COVER
********************************/
/**
 *  Output book's cover  
 *  
 *  
 *  
 *  @since 2.0
 *  @since 3.0 added alt text
 *  
 *  @param [string] $image_src cover's URL
 *  @param [array] $attr      Parameters
 *  
 *  @return output html
 *  
 *  @access public
 */
function mbdb_output_cover($image_src, $attr) {
		
	$image_html ='';
	
	if (isset($image_src) && $image_src != '') {
		$image_html = '<img ';
		// v  3 -- while working on  customizer
	/*	if (esc_attr($attr['width']) != '') {
			$image_html .= 'style="width:' . esc_attr($attr['width']) . 'px" ';
		} else {
			$image_html .= 'style="width: 100%" ';
		}
		*/
		// get alt text
		$cover_id = mbdb_get_book_data( 'cover_id', $attr['book'] );
		
		$alt = mbdb_get_alt_text( $cover_id,  __('Book Cover:', 'mooberry-book-manager') . ' ' . mbdb_get_title_data( $attr['book'] ) );
		$image_html .= 'src="' . esc_url($image_src) . '" ' . $alt . '/>';
	}
	return apply_filters('mbdb_shortcode_cover',  '<span class="mbm-book-cover">' . $image_html . '</span>');
}

/**
 *  
 *  Shortcode function for book's cover
 *  
 *  
 *  @since 1.0
 *  @since 3.0  check if placeholder cover should be used
 *  
 *  @param [array]	parameters
 *  @param [string]	content
 *  
 *  @return HTML output
 *  
 *  @access public
 *  
 */
function mbdb_shortcode_cover( $attr, $content) {
	$attr = shortcode_atts(array('width' =>  '',
								'align' => 'right',
								'wrap' => 'yes',
								'book' => ''), $attr);
	$image_src = '';
	// 3.4.4 -- uses get_attachemnt_image_src
	$cover_id = mbdb_get_book_data('cover_id', $attr['book']);
	if ($cover_id !== false) {
		$attachment_src = wp_get_attachment_image_src ( $cover_id, 'large' );
		if ( $attachment_src !== false) {
			$image_src = $attachment_src[0];
		}
	} 
	
	$image_src = mbdb_get_cover( $image_src, 'page' );
	
	if ( $image_src == '' ) {
		return mbdb_blank_output( 'cover', '' );
	} else {
		return mbdb_output_cover( $image_src, $attr );
	}
}

/*********************************************
	REVIEWS
	******************************************/
	
function mbdb_output_reviews($mbdb_reviews, $attr) {
	$review_html = '';
	foreach ($mbdb_reviews as $review) {
		// 3.5.4
		$review_text = mbdb_check_field('mbdb_review', $review);
		// if not review, skip to the next one
		if ( !$review_text ) {
			continue;
		}
		$reviewer_name = mbdb_check_field('mbdb_reviewer_name', $review);
		$review_url = mbdb_check_field('mbdb_review_url', $review);
		$review_website = mbdb_check_field('mbdb_review_website', $review);
		$review_html .= '<span class="mbm-book-reviews-block"><span class="mbm-book-reviews-header">';
		if ($reviewer_name) {
			$review_html .=  '<span class="mbm-book-reviews-reviewer-name">' . esc_html($review['mbdb_reviewer_name']) . '</span> ';
		}
		if ($review_url || $review_website) {
			$review_html .= __('on ','mooberry-book-manager');
		}
		if ($review_url) {
			$review_html .= '<A class="mbm-book-reviews-link" HREF="' . esc_url($review['mbdb_review_url']) . '" target="_new"><span class="mbm-book-reviews-website">';
			if (!$review_website) {
				$review_html .= esc_html($review['mbdb_review_url']);	
			} else {
				$review_html .= esc_html($review['mbdb_review_website']);
			}
			$review_html .=	'</span></A>';
		} else {
			if ($review_website) {
				$review_html .= '<span class="mbm-book-reviews-website">' . esc_html($review['mbdb_review_website']) . '</span>';
			}
		}
		if ($reviewer_name) {
			$review_html .= ' ' . __('wrote','mooberry-book-manager');
		}
		$review_html .=	':</span>';
		$review_html .= ' <blockquote class="mbm-book-reviews-text">' . wpautop(wp_kses_post($review['mbdb_review'])) . '</blockquote></span>';
	}
	// if (!mbdb_check_field('mbdb_review', $review)) {
		// return mbdb_apply_filters('mbdb_shortcode_reviews', '<span class="mbm-book-reviews"><span class="mbm-book-reviews-blank">' . esc_html($attr['blank']) . '</span></span>'); 
	// } else {
	
	
		return apply_filters('mbdb_shortcode_reviews', '<div class="mbm-book-reviews"><span class="mbm-book-reviews-label">' . esc_html($attr['label']) . '</span>' . $review_html . '<span class="mbm-book-reviews-after">' . esc_html($attr['after']) . '</span></div>');
	//}
}

/**
 *  
 *  Get the book's summary
 *  
 *  
 *  @since 2.0
 *  @since 3.0 use custom table column name instead of post_meta
 *  
 *  @return mixed data or false if book couldn't be found or if data was blank
 *  				( make sure to test with === false when calling this function )
 *  
 *  @access public
 *  
 */
function mbdb_get_reviews_data($book = '') {
	$reviews = mbdb_get_book_data(	'_mbdb_reviews', $book);
	if ( $reviews !== false ) {
		if ( is_array($reviews) ) {
			foreach ( $reviews as $review ) {
				if ( $review != '' && ( is_array($review) && count($review) > 0 ) ) {
					return $reviews;
				}
			}
			return false;			
		}
	}
	return $reviews;
}

/**
 *  
 *  Shortcode function for book's summary
 *  
 *  
 *  @since 1.0
 *  
 *  @param [array]	parameters
 *  @param [string]	content
 *  
 *  @return HTML output
 *  
 *  @access public
 *  
 */
function mbdb_shortcode_reviews( $attr, $content) {
	 $attr = shortcode_atts(array('label' => '',
									 'after' => '',
									 'blank' => '',
									'book' => ''), $attr);
									
	$mbdb_reviews = mbdb_get_reviews_data($attr['book']);
	if ($mbdb_reviews === false ) {
		return mbdb_blank_output('reviews', $attr['blank']);
	} else {
		return mbdb_output_reviews($mbdb_reviews, $attr);
	}				
}

/*****************************************************
	DOWNLAOD LINKS
	****************************************************/

function mbdb_get_downloadlinks_data( $book ) {
	$bookID = mbdb_get_book_ID($book);
	if ($bookID != 0) {
		$mbdb_downloadlinks = get_post_meta( $bookID, '_mbdb_downloadlinks', true);
	
	if (($mbdb_downloadlinks=='') || (!array_key_exists(0, $mbdb_downloadlinks))) { 
			return false;
		} else {
			if ( count($mbdb_downloadlinks) == 1 && $mbdb_downloadlinks[0]['_mbdb_formatID'] == 0 ) {
				return false;
			} else {
				return $mbdb_downloadlinks;
			}
		}
	} else {
		return false;
	}
}

// v3.0 added alt text
function mbdb_output_downloadlinks($mbdb_downloadlinks, $attr) {
	
	$classname = 'mbm-book-download-links';
	$mbdb_options = get_option( 'mbdb_options' );
	$download_links_html = '<UL class="' . $classname . '-list" style="list-style-type:none;">';
	if ($attr['align'] =='vertical') {
		$li_style = "margin: 1em 0 1em 0;";
	} else {
		$li_style = "display:inline;margin: 0 3% 0 0;";
	}
	foreach ($mbdb_downloadlinks as $mbdb_downloadlink) {
		// get format info based on formatid = uniqueid
		if (array_key_exists('formats', $mbdb_options)) {
			foreach($mbdb_options['formats'] as $r) {
				if ($r['uniqueID'] == $mbdb_downloadlink['_mbdb_formatID']) {
		
					$download_links_html .= '<li class="' . $classname . '-listitem" style="' . $li_style . '">';
				
				// 3.5.6
					if ( $r['uniqueID'] == '2' ) {
						$download_links_html .= '<span style="float:right;">' . __('Available on', 'mooberry-book-manager') . '<br/> ';
					}
					
					$download_links_html .= '<A class="' . $classname . '-link" HREF="' . esc_url($mbdb_downloadlink['_mbdb_downloadlink']) . '">';
					if (array_key_exists('image', $r) && $r['image']!='') {
						if (array_key_exists('imageID', $r)) {
							$imageID = $r['imageID'];
						} else {
							if (array_key_exists('image_id', $r)) {
								$imageID = $r['image_id'];
							} else {
								$imageID = 0;
							}
						}
				
						$alt = mbdb_get_alt_text( $imageID, __('Download Now:', 'mooberry-book-manager')  . ' ' . $r['name'] );
						
						$download_links_html .= '<img class="' . $classname . '-image" src="' . esc_url($r['image']) . '"' . $alt . '/>';
					} else {
						$download_links_html .= '<span class="' . $classname . '-text">' . esc_html($r['name']) . '</span>';
					}
					$download_links_html .= '</a>';
					if ( $r['uniqueID'] == '2' ) {
						$download_links_html .= '</span>';
					}
					$download_links_html  .= '</li>';
				}			
			}
		}
	}
	$download_links_html .= "</ul>"; 
	
	return apply_filters('mbdb_shortcode_downloadlinks', '<div class="' . $classname . '"><span class="' . $classname . '-label">' .  esc_html($attr['label']) . '</span>' . $download_links_html . '<span class="' . $classname . '-after">' . esc_html($attr['after']) . '</span></div>');
	
}


/**
 *  
 *  Shortcode function for book's summary
 *  
 *  
 *  @since 1.0
 *  
 *  @param [array]	parameters
 *  @param [string]	content
 *  
 *  @return HTML output
 *  
 *  @access public
 *  
 */
function mbdb_shortcode_downloadlinks( $attr, $content) {
	$attr = shortcode_atts(array('align' => 'vertical',
								'label' => '',
								'after' => '',
								'blank' => '',
								'book' => ''), $attr);
								
	$mbdb_downloadlinks	= mbdb_get_downloadlinks_data($attr['book']);
	if ($mbdb_downloadlinks === false) {
		return mbdb_blank_output('download-links', $attr['blank']);
	} else {
		return mbdb_output_downloadlinks($mbdb_downloadlinks, $attr);
	}
}

/************************************************
	BUY LINKS
	*********************************************/
// v3.0 added alt text
function mbdb_output_buylinks( $mbdb_buylinks, $attr) {
								
	$classname = 'mbm-book-buy-links';
	$mbdb_options = get_option( 'mbdb_options' );
	//$buy_links_html = '<UL class="' . $classname . '-list" style="list-style-type:none;">';
	$buy_links_html = '';
	$img_size = '';
	if ($attr['align'] =='vertical') {
		//$li_style = "margin: 2px 0 2px 0;";
		if ($attr['size']) { $attr['width'] = $attr['size']; }
		
	} else {
		//$li_style = "display:inline;margin: 0 1% 0 0;";
		if ($attr['size']) { $attr['height'] = $attr['size']; }
		
	}
	if ($attr['width']) {
			$img_size = "width:" . esc_attr($attr['width']) ;
		}
	if ($attr['height']) {
			$img_size = "height:" . esc_attr($attr['height']);		
		}
		
	foreach ($mbdb_buylinks as $mbdb_buylink) {
		// get format info based on formatid = uniqueid
		if (array_key_exists('retailers', $mbdb_options)) {
			foreach($mbdb_options['retailers'] as $r) {
				if ($r['uniqueID'] == $mbdb_buylink['_mbdb_retailerID']) {
					// 3.5 this filter for backwards compatibility
					$mbdb_buylink = apply_filters('mbdb_buy_links_output', $mbdb_buylink, $r);
					// 3.5 add affiliate codes
					$mbdb_buylink = apply_filters('mbdb_buy_links_pre_affiliate_code', $mbdb_buylink, $r);
					$retailer = apply_filters('mbdb_buy_links_retailer_pre_affiliate_code', $r, $mbdb_buylink);
					// Does the retailer have an affiliate code?
					if (array_key_exists('affiliate_code', $retailer) && $retailer['affiliate_code'] != '') {
						
						// default to after
						if (!array_key_exists('affiliate_position', $retailer) || $retailer['affiliate_position'] == '') {
							$retailer['affiliate_position'] = 'after';
						}
						
						// append or prepend the code
						if ($retailer['affiliate_position'] == 'before') {
							$mbdb_buylink['_mbdb_buylink'] = $retailer['affiliate_code'] . $mbdb_buylink['_mbdb_buylink'];
						} else {
							$mbdb_buylink['_mbdb_buylink'] .= $retailer['affiliate_code'];
						}
					}		
					$mbdb_buylink = apply_filters('mbdb_buy_links_post_affiliate_code', $mbdb_buylink, $r);
					$retailer = apply_filters('mbdb_buy_links_retailer_post_affiliate_code', $r, $mbdb_buylink);
					//$buy_links_html .= '<li class="' . $classname . '-listitem" style="' . $li_style . '">';
					
					// 3.5.6
					if ( $r['uniqueID'] == '13' ) {
						$buy_links_html .= '<span style="float:left;">' . __('Available on', 'mooberry-book-manager') . ' <br/>';
					}
					$buy_links_html .= '<A class="' . $classname . '-link" HREF="' . esc_url($mbdb_buylink['_mbdb_buylink']) . '" TARGET="_new">';
					if (array_key_exists('image', $r) && $r['image']!='') {
						
						if (array_key_exists('imageID', $r)) {
							$imageID = $r['imageID'];
						} else {
							if (array_key_exists('image_id', $r)) {
								$imageID = $r['image_id'];
							} else {
								$imageID = 0;
							}
						}
						$alt = mbdb_get_alt_text( $imageID, __('Buy Now:', 'mooberry-book-manager')  . ' ' . $r['name'] );
						
						$buy_links_html .= '<img class="' . $classname . '-image" style="' . esc_attr($img_size) . '" src="' . esc_url($r['image']) . '" ' . $alt . ' />';
					} else {
						$buy_links_html .= '<span class="' . $classname . '-text">' . esc_html($r['name']) . '</span>';
					}
					$buy_links_html .= '</a>';
					if ( $r['uniqueID'] == '13' ) {
						$buy_links_html .= '</span>';
					}
					//$buy_links_html .= '</li>';
				}			
			}
		}
	}
	//$buy_links_html .= "</ul>"; 
	return apply_filters('mbdb_shortcode_buylinks', '<div class="' . $classname . '"><span class="' . $classname . '-label">' . esc_html($attr['label']) . '</span>' . $buy_links_html . '<span class="' . $classname . '-after">'.  esc_html($attr['after']) . '</span></div>');
}
	
	
/**
 *  
 *  Shortcode function for book's summary
 *  
 *  
 *  @since 1.0
 *  @since 3.0 use custom table column name instead of post_meta
 *  
 *  @param [array]	parameters
 *  @param [string]	content
 *  
 *  @return HTML output
 *  
 *  @access public
 *  
 */	
function mbdb_shortcode_buylinks( $attr, $content) {
	$attr = shortcode_atts(array('width' =>  '',
								'height' => '',
								'size' => '',
								'align' => 'vertical',
								'label' => '',
								'after' => '',
								'blank' => '',
								'book' => ''), $attr);
								
	$mbdb_buylinks = mbdb_get_book_data('_mbdb_buylinks', $attr['book']);
	if ($mbdb_buylinks === false) {
		return mbdb_blank_output('buy-links', $attr['blank']);
	} else {
		return mbdb_output_buylinks($mbdb_buylinks, $attr);
	}
}

/*******************************************************
	LINKS
	**************************************************/

function mbdb_output_links($mbdb_buylinks, $mbdb_downloadlinks, $attr, $attr2) {
	$output_html = '<div class="mbm-book-links">';
	if ($mbdb_buylinks !== false) {
		$attr2['label'] = $attr['buylabel'];
		$output_html .= mbdb_output_buylinks($mbdb_buylinks, $attr2);
	}
	
	if ($mbdb_downloadlinks !== false) {
		$attr2['label'] = $attr['downloadlabel'];
		$output_html .= mbdb_output_downloadlinks($mbdb_downloadlinks, $attr2);
	}
	$output_html .= '</div>'; 
	return apply_filters('mbdb_shortcode_links', $output_html);
}

function mbdb_blank_links_output($attr) {
	$classname = 'mbm-book-buy-links';
	return apply_filters('mbdb_shortcode_links', '<span class="' . $classname . '"><span class="' . $classname . '-label">' . esc_html($attr['blanklabel']) . '</span><span class="' . $classname . '-blank">' . esc_html($attr['blank']) . '</span></span>');
}

/**
 *  
 *  Get the book's summary
 *  
 *  
 *  @since 2.0
 *  @since 3.0 use custom table column name instead of post_meta
 *  
 *  @return mixed data or false if book couldn't be found or if data was blank
 *  				( make sure to test with === false when calling this function )
 *  
 *  @access public
 *  
 */
function mbdb_get_links_data( $book = '') {
	$mbdb_buylinks = mbdb_get_book_data('_mbdb_buylinks', $book);
	$mbdb_downloadlinks = mbdb_get_book_data('_mbdb_downloadlinks', $book);
	return array('buylinks' => $mbdb_buylinks, 
				'downloadlinks' => $mbdb_downloadlinks);
}

function mbdb_is_links_data( $links_data = null, $book = '' ) {
	if ($links_data == null) {
		$links_data = mbdb_get_links_data ($book);
	}
	return !($links_data['buylinks'] === false && $links_data['downloadlinks'] === false);
}

/**
 *  
 *  Shortcode function for book's summary
 *  
 *  
 *  @since 1.0
 *  
 *  @param [array]	parameters
 *  @param [string]	content
 *  
 *  @return HTML output
 *  
 *  @access public
 *  
 */
function mbdb_shortcode_links($attr, $content) {
	

	$attr = shortcode_atts(array('width' =>  '',
								'height' => '',
								'size' => '',
								'align' => 'vertical',
								'downloadlabel' => '',
								'buylabel' => '',
								'after' => '',
								'blank' => '',
								'blanklabel' => '',
								'book' => ''), $attr);
	$attr2 = $attr;
	$attr2['blank'] = '';
	
	$mbdb_links = mbdb_get_links_data($attr['book']);
	
	if (!mbdb_is_links_data($mbdb_links, $attr['book'])) {
	//if ($mbdb_buylinks === false && $mbdb_downloadlinks === false) {
		return mbdb_blank_links_output($attr);
	} else {
		return mbdb_output_links($mbdb_links['buylinks'], $mbdb_links['downloadlinks'], $attr, $attr2);
	}
}

/************************************************************
	EDITIONS
	***********************************************************/

/**
 *  
 *  Get the book's summary
 *  
 *  
 *  @since 2.0
 *  @since 3.0 use custom table column name instead of post_meta
 *  
 *  @return mixed data or false if book couldn't be found or if data was blank
 *  				( make sure to test with === false when calling this function )
 *  
 *  @access public
 *  
 */
 function mbdb_get_editions_data($book = '') {
	$editions = mbdb_get_book_data(	'_mbdb_editions', $book);
	// the only way to know for sure there are no editions is to check 
	// for the existance of the format field
	
	if ($editions !== false) {
		foreach ($editions as $edition) {
			if (array_key_exists('_mbdb_format', $edition) && $edition['_mbdb_format'] != '0') {
				return $editions;
			}
		}
	}
	return false;
}

function mbdb_output_editions($mbdb_editions, $attr) {
	$output_html = '';
	$counter = 0;
	$default_language = mbdb_get_default_language();
	
	foreach ($mbdb_editions as $edition) {
		if ( !array_key_exists('_mbdb_format', $edition) ) {
			continue;
		}
		$is_isbn = mbdb_check_field('_mbdb_isbn', $edition);
		$is_height = mbdb_check_field('_mbdb_height', $edition);
		$is_width = mbdb_check_field('_mbdb_width', $edition);
		$is_pages = mbdb_check_field('_mbdb_length', $edition);
		$is_price = mbdb_check_field('_mbdb_retail_price', $edition);
		$is_language = mbdb_check_field('_mbdb_language', $edition);
		$is_title = mbdb_check_field('_mbdb_edition_title', $edition);

		
		$output_html .= '<span class="mbm-book-editions-format" id="mbm_book_editions_format_'  . $counter . '" name="mbm_book_editions_format[' . $counter . ']">';
		if ($is_isbn || $is_pages || ($is_height && $is_width)) {
			$output_html .= '<a class="mbm-book-editions-toggle" id="mbm_book_editions_toggle_'  . $counter . '" name="mbm_book_editions_toggle[' . $counter . ']"></a>';
		}
		$format_name = mbdb_get_format_name($edition['_mbdb_format']);
		$output_html .= '<span class="mbm-book-editions-format-name">' . $format_name . '</span>';
		
		
		if ($is_language && $edition['_mbdb_language'] != $default_language) {
			$output_html .= ' <span class="mbm-book-editions-language">(' . mbdb_get_language_name($edition['_mbdb_language']) . ')</span>';
		}
		
		if ($is_title) {
			$output_html .= ' - <span class="mbm-book-editions-title">' . $edition['_mbdb_edition_title'] . '</span>';
		}
		if ($is_price && $edition['_mbdb_retail_price'] != '0.00' && $edition['_mbdb_retail_price'] != '0,00') {
			$edition['_mbdb_retail_price'] = str_replace(',', '.', $edition['_mbdb_retail_price']);
			$price = number_format_i18n($edition['_mbdb_retail_price'], 2);
			$symbol = mbdb_get_currency_symbol($edition['_mbdb_currency']);
			$output_html .= ': <span class="mbm-book-editions-srp"><span class="mbm-book-editions-price">';
			/* translators: %1$s is the currency symbol. %2$s is the price. To put currency after price, enter %2$s %1$s */
			$output_html .= sprintf( _x('%1$s %2$s', '%1$s is the currency symbol. %2$s is the price. To put currency after price, enter %2$s %1$s', 'mooberry-book-manager'), $symbol, $price);
			$output_html .= '</span></span>';
		}
		if ($is_isbn || ($is_height && $is_width) || $is_pages) {
			$output_html .= '<div name="mbm_book_editions_subinfo[' . $counter . ']" id="mbm_book_editions_subinfo_' . $counter . '" class="mbm-book-editions-subinfo">';
		
			if ($is_isbn) {
				$output_html .= '<strong>' . __('ISBN:', 'mooberry-book-manager') . '</strong> <span class="mbm-book-editions-isbn">' . $edition['_mbdb_isbn'] . '</span><br/>';
			}
			if ($is_height && $is_width) {
				$output_html .= '<strong>' . __('Size:', 'mooberry-book-manager') . '</strong> <span class="mbm-book-editions-size"><span class="mbm-book-editions-height">' . number_format_i18n($edition['_mbdb_width'], 2) . '</span>x<span class="mbm-book-editions-width">' . number_format_i18n($edition['_mbdb_height'], 2) . '</span> <span class="mbm-book-editions-unit">' . $edition['_mbdb_unit'] . '</span></span><br/>';
			}
			if ($is_pages) {
				$output_html .= '<strong>' . __('Pages:', 'mooberry-book-manager') . '</strong> <span class="mbm-book-editions-length">' . number_format_i18n($edition['_mbdb_length']) . '</span>';
			}
			$output_html .= '</div>';
		}
		$output_html .= '</span>';
		$counter++;
	}
	return apply_filters('mbdb_shortcode_editions', '<div class="mbm-book-editions"><span class="mbm-book-editions-label">' . esc_html($attr['label']) . '</span>' . $output_html . '<span class="mbm-book-editions-after">' . esc_html($attr['after']) . '</span></div>');
	
}

/**
 *  
 *  Shortcode function for book's editions
 *  
 *  
 *  @since 2.0
 *  
 *  @param [array]	parameters
 *  @param [string]	content
 *  
 *  @return HTML output
 *  
 *  @access public
 *  
 */
function mbdb_shortcode_editions( $attr, $content) {
	$attr = shortcode_atts(array(
								'label'	=>	'',
								'after'	=>	'',
								'book'	=> ''), $attr);
	$mbdb_editions = mbdb_get_editions_data($attr['book']);
	if ($mbdb_editions === false) {
		return mbdb_blank_output('editions', $attr['blank']);
	} else {
		return mbdb_output_editions($mbdb_editions, $attr);
	}
}





function mbdb_kindle_preview( $attr, $content ) {
	$attr = shortcode_atts(array(
								'asin'	=>	'',
								'affiliate'	=>	'',
								), $attr);
							
	return '<div class="mbm-book-excerpt"><span class="mbm-book-excerpt-label">Excerpt:</span><iframe type="text/html" width="336" height="550" frameborder="0" allowfullscreen style="max-width:100%" src="https://read.amazon.com/kp/card?asin=' . esc_attr($attr['asin']) . '&preview=inline&linkCode=kpe&tag=' . esc_attr($attr['affiliate']) . '" ></iframe></div>';
	
}



/***************************************************************
						PAGE CONTENT
*******************************************************************/
	
	function mbdb_shortcode_book($attr, $content) {
		$book_page_layout = '<div id="mbm-book-page">';
			if (mbdb_get_subtitle_data() !== false) {
				$book_page_layout .= '<h3>[book_subtitle blank=""]</h3>';
			}
			// v 3.0 for customizer
			//$book_page_layout .= '<div id="mbm-left-column">';
				$book_page_layout .= '[book_cover]';
			
				$book_page_layout .= '<div id="mbm-book-links1">';
				$is_links_data = mbdb_get_links_data();
				if ($is_links_data['buylinks'] !== false) {
					$book_page_layout .= '[book_buylinks  align="horizontal"]';
				}
				if ($is_links_data['downloadlinks'] !== false) {
					$book_page_layout .= '[book_downloadlinks align="horizontal" label="' . __('Download Now:', 'mooberry-book-manager') . '"]';
				}
				
				$book_page_layout .= '</div>';
				
				if (mbdb_get_taxonomy_data('mbdb_series') !== false ) {
					$book_page_layout .= '[book_serieslist before="' . __('Part of the','mooberry-book-manager') . ' " after=" ' . __('series:','mooberry-book-manager') . ' " delim="list"]';
				}
				
			if (mbdb_get_editions_data() !== false) {
					$book_page_layout .= '[book_editions blank="" label="' . __('Editions:', 'mooberry-book-manager') . '"]';
				}
			
				if (mbdb_get_goodreads_data() !== false) {
					$book_page_layout .= '[book_goodreads  ]';
				}
			// v 3.0 for customizer
			//$book_page_layout .= '</div><div id="mbm-middle-column">';
			if (mbdb_get_summary_data() !== false) {
				$book_page_layout .= '[book_summary blank=""]';
			}
			
		
			// v3.0 convert to simple true/false so that the if statement with ||
			// below actually works. Otherwise, valid data could be "0" and still
			// evaulate to false in the if statement
			// also simplifies the following if statments
			$is_published = (mbdb_get_published_data() === false ? false : true);
			$is_publisher = (mbdb_get_publisher_data() === false ? false : true);
			$is_genre = (mbdb_get_taxonomy_data('mbdb_genre') === false ? false : true);
			$is_tag = (mbdb_get_taxonomy_data('mbdb_tag') === false ? false : true);
			$is_editor = (mbdb_get_taxonomy_data('mbdb_editor') === false ? false : true);
			$is_illustrator = (mbdb_get_taxonomy_data('mbdb_illustrator') === false ? false : true);
			$is_cover_artist = (mbdb_get_taxonomy_data('mbdb_cover_artist') === false ? false : true);
			$display_details = apply_filters('mbdb_display_book_details', $is_published || $is_publisher || $is_genre || $is_tag || $is_editor || $is_illustrator || $is_cover_artist);
			if ($display_details ) {
				$book_page_layout .= '<div class="mbm-book-details-outer">';
								$book_page_layout .= '<div class="mbm-book-details">';
				if ($is_published ) {
					$book_page_layout .= '<span class="mbm-book-details-published-label">' . __('Published:', 'mooberry-book-manager') . '</span> <span class="mbm-book-details-published-data">[book_published format="default" blank=""]</span><br/>';
				}
				
				if ($is_publisher  ) {
					$book_page_layout .= '<span class="mbm-book-details-publisher-label">' . __('Publisher:','mooberry-book-manager') . '</span> <span class="mbm-book-details-publisher-data">[book_publisher  blank=""]</span><br/>';
				}
				
				if ($is_editor  ) {
					$book_page_layout .= '<span class="mbm-book-details-editors-label">' . __('Editors:', 'mooberry-book-manager') . '</span> <span class="mbm-book-details-editors-data">[book_editor delim="comma" blank=""]</span><br/>';
				}
				
				if ($is_illustrator ) {
					$book_page_layout .= '<span class="mbm-book-details-illustrators-label">' . __('Illustrators:', 'mooberry-book-manager') . '</span> <span class="mbm-book-details-illustrators-data">[book_illustrator delim="comma" blank=""]</span><br/>';
				}
				
				if ($is_cover_artist ) {
					$book_page_layout .= '<span class="mbm-book-details-cover-artists-label">' . __('Cover Artists:', 'mooberry-book-manager') . '</span> <span class="mbm-book-details-cover-artists-data">[book_cover_artist delim="comma" blank=""]</span><br/>';
				}
							
				if ($is_genre ) {
					$book_page_layout .= '<span class="mbm-book-details-genres-label">' . __('Genres:','mooberry-book-manager') . '</span> <span class="mbm-book-details-genres-data">[book_genre delim="comma" blank=""]</span><br/>';
				}
				
				if ($is_tag ) {
					$book_page_layout .= '<span class="mbm-book-details-tags-label">' . __('Tags:','mooberry-book-manager') . '</span> <span class="mbm-book-details-tags-data">[book_tags  delim="comma" blank=""]</span><br/>';
				}
				
				$book_page_layout = apply_filters('mbdb_extra_book_details', $book_page_layout);
				
				$book_page_layout .= '</div></div> <!-- mbm-book-details -->';
			}
			
			
				$is_excerpt = mbdb_get_excerpt_data();
			if ( $is_excerpt !== false) {
				$book_page_layout .= '[book_excerpt label="' . __('Excerpt:', 'mooberry-book-manager') . '" length="1000"  blank="" ]';
			} 
		
			// v 3.0 for customizer
			//$book_page_layout .= '</div><div id="mbm-right-column"></div>';
		
			if (mbdb_get_reviews_data() !== false ) {
				$book_page_layout .= '<span>[book_reviews  blank="" label="' . __('Reviews:', 'mooberry-book-manager') . '"]</span><br/>';
			}
			if (mbdb_get_additional_info_data() !== false ) {
				$book_page_layout .= '[book_additional_info]';
			}
			
			// only show 2nd set of links if exceprt is more than 1500 characters long
			if ($is_excerpt !== false && strlen($is_excerpt) > 1500 && mbdb_is_links_data() !== false) {
				$book_page_layout .= '<div id="mbm-book-links2">[book_links buylabel="" downloadlabel="' . __('Download Now:', 'mooberry-book-manager') . '" align="horizontal"  blank="" blanklabel=""]</div>';
			}
			
			$book_page_layout .= '</div> <!-- mbm-book-page -->';
			

			
			$content .= stripslashes($book_page_layout); 
			$content = preg_replace('/\\n/', '<br/>', $content);
			
			$content = apply_filters('mbdb_book_content', $content);
			return do_shortcode($content);
		
}
	