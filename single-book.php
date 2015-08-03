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
add_shortcode( 'book_links', 'mbdb_shortcode_links');
add_shortcode( 'book_editions', 'mbdb_shortcode_editions');
		
		
/*********************************************
	UTIL FUNCTIONS
*********************************************/

function mbdb_get_book_ID( $slug ) {
	global $post;
	if ( $slug == '' ) {
		return $post->ID;
	} else {
		$book = mbdb_get_single_book( $slug );
		if ($book) {
			return $book[0]->ID;
		} else {
			return 0;
		}
	}
}

function mbdb_get_book_data( $meta_data, $book = '') {
	$bookID = mbdb_get_book_ID($book);
	if ($bookID != 0) {
		$book_data = get_post_meta($bookID, $meta_data, true);
		if (empty($book_data) || $book_data == '' ) {
			return false;
		} else {
			return $book_data;
		}
	} else {
		return false;
	}
}






function mbdb_blank_output( $classname, $blank_output) {
	return apply_filters('mbdb_shortcode_' . $classname, '<span class="mbm-book-' . $classname . '"><span class="mbm-book-' . $classname . '-blank">' . esc_html($blank_output) . '</span></span>');
}

function mbdb_output_data( $classname, $data) {
	return apply_filters('mbdb_shortcode_' . $classname, '<span class="mbm-book-' . $classname . '"><span class="mbm-book-' . $classname . '-text">' . esc_html($data) . '</span></span>');
}

function mbdb_simple_shortcode( $meta_data, $attr, $classname) {
	$book_data = mbdb_get_book_data($meta_data, $attr['book']);
	if ($book_data === false) {
		return mbdb_blank_output($classname, $attr['blank']);
	} else {
		return mbdb_output_data($classname, $book_data);
	}
		
}

/******************************
	SUMMARY
	***************************/


function mbdb_output_summary($book_data, $attr) {
	return apply_filters('mbdb_shortcode_summary', '<div class="mbm-book-summary"><span class="mbm-book-summary-label">' . esc_html($attr['label']) . '</span><span class="mbm-book-summary-text">' . wpautop($book_data) . '</span><span class="mbm-book-summary-after">' . esc_html($attr['after']) . '</span></div>');
}


function mbdb_get_summary_data($book = '') {
	 return mbdb_get_book_data('_mbdb_summary', $book);
}

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
	
	
function mbdb_get_published_data($book = '' ) {
	return mbdb_get_book_data('_mbdb_published', $book);
}

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


function mbdb_output_published($mbdb_published, $attr) {
	if ($attr['format'] =='short') {
			/* translators: short date format. see http://php.net/date */
			$format = __('m/d/Y');
		} else {
			/* translators: long date format. see http://php.net/date */
			$format = __('F j, Y');
		}
		return apply_filters('mbdb_shortcode_published',  '<span class="mbm-book-published"><span class="mbm-book-published-label">' . esc_html($attr['label']) . '</span><span class="mbm-book-published-text">' . date($format, strtotime($mbdb_published)) . '</span><span class="mbm-book-published-after">' .  esc_html($attr['after']) . '</span></span>');
}


/*******************************
	GOODREADS
	*****************************/
	
function mbdb_output_goodreads($mbdb_goodreads, $attr) {
	$mbdb_options = get_option('mbdb_options');
	
	if (empty($mbdb_options['goodreads'])) { 
		return apply_filters('mbdb_shortcode_goodreads', '<div class="mbm-book-goodreads"><span class="mbm-book-goodreads-label">' . esc_html($attr['label']) . '</span><A class="mbm-book-goodreads-link" HREF="' . esc_url($mbdb_goodreads) . '" target="_new"><span class="mbm-book-goodreads-text">' . $attr['text'] . '</span></A><span class="mbm-book-goodreads-after">' . esc_html($attr['after']) . '</span></div>'); 
	} else {
		return apply_filters('mbdb_shortcode_goodreads', '<div class="mbm-book-goodreads"><span class="mbm-book-goodreads-label">' . esc_html($attr['label']) . '</span><A class="mbm-book-goodreads-link" HREF="' . esc_url($mbdb_goodreads) . '" target="_new"><img class="mbm-book-goodreads-image" src="' . esc_url($mbdb_options['goodreads']) . '"/></A><span class="mbm-book-goodreads-after">' . esc_html($attr['after']) . '</span></div>');
	}
}

function mbdb_get_goodreads_data( $book = '') {
	return  mbdb_get_book_data('_mbdb_goodreads', $book);
}

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
function mbdb_output_excerpt($mbdb_excerpt, $attr) {
	 $mbdb_excerpt = wpautop($mbdb_excerpt);
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
		<span class="mbm-book-excerpt-text">' . $excerpt1;
	if (trim($excerpt2) != '' ) {
		$html_output .= '<a name="more" class="mbm-book-excerpt-read-more">' . __('READ MORE', 'mooberry-book-manager') . '</a>
	<span class="mbm-book-excerpt-text-hidden">' . $excerpt2 . '<a class="mbm-book-excerpt-collapse" name="collapse">' . __('COLLAPSE', 'mooberry-book-manager') . '</a></span>';
	}
	$html_output .=' </span><span class="mbm-book-excerpt-after">' . esc_html($attr['after']) . '</span></div>';
	return apply_filters('mbdb_shortcode_excerpt', $html_output);
		
}


function mbdb_get_excerpt_data($book = '') {
	return mbdb_get_book_data('_mbdb_excerpt', $book);
}

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
		return mbdb_output_excerpt($mbdb_excerpt, $attr);
	}
}

/*******************************************
	ADDITIONAL INFO
*******************************************/
function mbdb_output_additional_info($mbdb_additional_info, $attr) {
	 $mbdb_additional_info = wpautop($mbdb_additional_info);
	 $html_output = '<div class="mbm-book-additional-info">';
	 $html_output .= $mbdb_additional_info;
	 $html_output .= '</div>';
	 return apply_filters('mbdb_shortcode_additional_info', $html_output);
}

function mbdb_get_additional_info_data($book = '') {
	return mbdb_get_book_data('_mbdb_additional_info', $book);
}

function mbdb_shortcode_additional_info($attr, $content) {
	$attr = shortcode_atts(array(
									'blank' => '',
								
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
	function mbdb_output_taxonomy($classname, $mbdb_terms, $permalink, $attr) {
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
		if ( get_option('permalink_structure') !='' ) {
			$list .= site_url($permalink . '/' . $term->slug);
		} else {
			$list .= site_url('?the-taxonomy=' . $taxonomy . '&the-term=' . $term->slug . '&post_type=mbdb_tax_grid');
		}
		$list .= '"><span class="' . $classname . '-text">' . $term->name . '</span></a>';
		$list .= $delim;
	}
	
	if ($attr['delim']=='list') {
		// there's an extra $delim added to the string
		// trim off the last </li> by cutting the entire $delim off and then adding in the </li> back in
		$list = substr($list, 0, strripos($list, $delim)) . '</li>';
	} 	else {
		// trim off the last space and comma
		$list = substr($list, 0, -2);
	}
	return apply_filters('mbdb_shortcode_' . $permalink . '_taxonomy',  '<div class="' . $classname . '" style="display:inline-block">' . $begin . $list  . $end . '</div>');

}

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

		
function mbdb_shortcode_series( $attr, $content) {
	return mbdb_shortcode_taxonomy($attr, 'mbdb_series', 'series');
}

function mbdb_shortcode_tags( $attr, $content) {
	return mbdb_shortcode_taxonomy($attr, 'mbdb_tag', 'book-tag');
}
function mbdb_shortcode_genre($attr, $content) {
	return mbdb_shortcode_taxonomy($attr, 'mbdb_genre', 'genre');
}

function mbdb_shortcode_taxonomy($attr, $taxonomy, $permalink) {

	$attr = shortcode_atts(array('delim' => 'comma',
								'blank' => '',
								'book' => ''), $attr);
	
	$mbdb_terms = mbdb_get_taxonomy_data( $taxonomy, $attr['book']);
	if ($mbdb_terms === false) {
		return mbdb_blank_output($permalink . '_taxonomy', $attr['blank']);
	} else {
		return mbdb_output_taxonomy('mbm-book-' . $permalink, $mbdb_terms, $permalink, $attr);
	}
}


/***********************************************
	TITLE
	*******************************************/
function mbdb_shortcode_title( $attr, $content) {
	global $post;
	$attr = shortcode_atts(array('book' => '',
								'blank' => ''), $attr);
	if ($attr['book'] == '') {
		$html = '<span class="mbm-book-title"><span class="mbm-book-title-text">' . esc_html($post->post_title) . '</span></span>';
	} else {
		$book = mbdb_get_single_book($attr['book']);
		if ($book) {
			$html = '<span class="mbm-book-title"><span class="mbm-book-title-text">' . esc_html($book[0]->post_title) . '</span></span>';
		} else {
			$html = '<span class="mbm-book-title"><span class="mbm-book-title-blank">' . esc_html($attr['blank']) . '</span></span>';
		}
	}
	return apply_filters('mbdb_shortcode_title', $html);
}

/************************************************
	SUBTITLE
	*********************************************/

function mbdb_get_subtitle_data( $book = '') {
	return mbdb_get_book_data('_mbdb_subtitle', $book);
}

function mbdb_shortcode_subtitle($attr, $content) {
	$attr = shortcode_atts(array('book' => '',
								'blank' => ''), $attr);
								
	return mbdb_simple_shortcode( '_mbdb_subtitle', $attr, 'subtitle');
}



/******************************
	PUBLISHER 
	****************************/

function mbdb_get_publisher_data($book = '') {
	$publisherID =  mbdb_get_book_data('_mbdb_publisherID', $book);
	$publisher = mbdb_get_publisher_info($publisherID);
	if ($publisher == null) {
		return false;
	} else {
		return $publisher;
	}
}

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


function mbdb_output_publisher($book_data, $attr) {
		$mbdb_publisher = $book_data['name'];
		$mbdb_publisherwebsite = $book_data['website'];
		
		if (empty($mbdb_publisherwebsite)) {
			return apply_filters('mbdb_shortcode_publisher',  '<span class="mbm-book-publisher"><span class="mbm-book-publisher-label">' . esc_html($attr['label']) . '</span><span class="mbm-book-publisher-text">' . esc_html($mbdb_publisher) . '</span><span class="mbm-book-publisher-after">' . esc_html($attr['after']) . '</span></span>'); 
		} else {
			return apply_filters('mbdb_shortcode_publisher',  '<span class="mbm-book-publisher"><span class="mbm-book-publisher-label">' . esc_html($attr['label']) . '</span><A class="mbm-book-publisher-link" HREF="' . esc_url($mbdb_publisherwebsite) . '"><span class="mbm-book-publisher-text">' . esc_html($mbdb_publisher) . '</span></a><span class="mbm-book-publisher-after">' . esc_html($attr['after']) . '</span></span>');
		}
	
}

/************************************************
	LENGHT
	********************************************/
function mbdb_get_length_data($book = '') {
	return mbdb_get_book_data('_mbdb_length', $book);
}

function mbdb_shortcode_length($attr, $content) {
	$attr = shortcode_atts(array('label' => '',
									'after' => '',
									'blank' => '',
									'book' => ''), $attr);
									
	return mbdb_simple_shortcode( '_mbdb_length', $attr, 'length'); 
	
}

/*****************************************
	SERIES 
	***************************************/
function mbdb_get_series_data($book = '') {
	$bookID = mbdb_get_book_ID($book);
	if ($bookID != 0) {
		$mbdb_series = get_the_terms( $bookID, 'mbdb_series');	
		if (!$mbdb_series) { 
			return false;
		} else {
			return $mbdb_series;
		}
	} else {
		return false;
	}
}

function mbdb_output_serieslist($mbdb_series, $attr) {
	$bookID = mbdb_get_book_ID($attr['book']);
	$classname = 'mbm-book-serieslist';
	$series_name = '';
	foreach($mbdb_series as $series) {
		$series_name .=  '<div class="' . $classname . '-seriesblock"><span class="' . $classname . '-before">' . esc_html($attr['before']) . '</span>';
		$series_name .= '<a class="' . $classname . '-link" href="';
		if ( get_option('permalink_structure') !='' ) {
			$series_name .= site_url('series/' .  $series->slug);
		} else {
			$series_name .= site_url('?the-taxonomy=mbdb_series&the-term=' . $series->slug . '&post_type=mbdb_tax_grid');
		}
		$series_name .=  '"><span class="' . $classname . '-text">' . $series->name . '</span></a>';
		$series_name .= '<span class="' . $classname . '-after">' . esc_html($attr['after']) . '</span>';
		$series_name .= mbdb_series_list($attr['delim'],  $series->slug, $bookID);
		$series_name .=  '</div>';
	}
	return apply_filters('mbdb_shortcode_serieslist', '<div class="' . $classname . '">' . $series_name . '</div>');
}

function mbdb_shortcode_serieslist($attr, $content) {
	$attr = shortcode_atts(array('blank' => '',
									'before' => __('Part of the ', 'mooberry-book-manager'),
									'after' => __(' series:', 'mooberry-book-manager'),
									'delim' => __('list', 'mooberry-book-manager'),
									'book' => ''), $attr);
	
	$mbdb_series = mbdb_get_series_data($attr['book']);
	if ($mbdb_series === false) {
		return mbdb_blank_output('serieslist', $attr['blank']);
	} else {
		
		return mbdb_output_serieslist( $mbdb_series, $attr);
	}
}
	


function mbdb_series_list($delim, $series, $bookID) {
	$classname = 'mbm-book-serieslist';
	$books = mbdb_get_books_in_taxonomy($series, 'mbdb_series'); 
	if ($delim=='list') {
		$list = '<ul class="' . $classname . '-list">';
	} else {
		$list = '';
	}
	foreach ($books as $book) {
		if ($delim=='list') {
			$list .= '<li class="' . $classname . '-listitem">';
		}
		if ($book->ID != $bookID) {
			$list .= '<A class="' . $classname . '-listitem-link" HREF="' . get_permalink($book->ID) . '">';
		}
		$list .= '<span class="' . $classname . '-listitem-text">' . esc_html($book->post_title) . '</span>';
		if ($book->ID != $bookID) {
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

function mbdb_output_cover($image_src, $attr) {
	
								
	$image_html ='';
	
	if (isset($image_src) && $image_src != '') {
		$image_html = '<img ';
		if (esc_attr($attr['width']) != '') {
			$image_html .= 'style="width:' . esc_attr($attr['width']) . 'px" ';
		} else {
			$image_html .= 'style="width: 100%" ';
		}
		$image_html .= 'src="' . esc_url($image_src) . '" >';
		// if ($attr['wrap']=='yes') {
			// $image_html .= 'class="align' . esc_attr($attr['align']) . '">';
		// } else {
			// $image_html .= 'style="float:' . esc_attr($attr['align']) . '"><div style="clear:' . esc_attr($attr['align']) . '"> &nbsp;</div>';
		// }
	}
	return apply_filters('mbdb_shortcode_cover',  '<span class="mbm-book-cover">' . $image_html . '</span>');
}


function mbdb_shortcode_cover( $attr, $content) {
	$attr = shortcode_atts(array('width' =>  '',
								'align' => 'right',
								'wrap' => 'yes',
								'book' => ''), $attr);
	$image_src = '';							
	$image_src = mbdb_get_book_data('_mbdb_cover', $attr['book']);
	if ($image_src === false) {
		return mbdb_blank_output('cover', '');
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
			$review_html .= '<A class="mbm-book-reviews-link" HREF="' . esc_url($review['mbdb_review_url']) . '"><span class="mbm-book-reviews-website">';
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
		$review_html .= ' <blockquote class="mbm-book-reviews-text">' . wpautop($review['mbdb_review']) . '</blockquote></span>';
	}
	// if (!mbdb_check_field('mbdb_review', $review)) {
		// return mbdb_apply_filters('mbdb_shortcode_reviews', '<span class="mbm-book-reviews"><span class="mbm-book-reviews-blank">' . esc_html($attr['blank']) . '</span></span>'); 
	// } else {
	
	
		return apply_filters('mbdb_shortcode_reviews', '<div class="mbm-book-reviews"><span class="mbm-book-reviews-label">' . esc_html($attr['label']) . '</span>' . $review_html . '<span class="mbm-book-reviews-after">' . esc_html($attr['after']) . '</span></div>');
	//}
}

function mbdb_get_reviews_data($book = '') {
	return mbdb_get_book_data(	'_mbdb_reviews', $book);
}

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
			return $mbdb_downloadlinks;
		}
	} else {
		return false;
	}
}


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
					$download_links_html .= '<li class="' . $classname . '-listitem" style="' . $li_style . '"><A class="' . $classname . '-link" HREF="' . esc_url($mbdb_downloadlink['_mbdb_downloadlink']) . '">';
					if ($r['image']!='') {
						$download_links_html .= '<img class="' . $classname . '-image" src="' . esc_url($r['image']) . '"/>';
					} else {
						$download_links_html .= '<span class="' . $classname . '-text">' . esc_html($r['name']) . '</span>';
					}
					$download_links_html .= '</a></li>';
				}			
			}
		}
	}
	$download_links_html .= "</ul>"; 
	
	return apply_filters('mbdb_shortcode_downloadlinks', '<div class="' . $classname . '"><span class="' . $classname . '-label">' .  esc_html($attr['label']) . '</span>' . $download_links_html . '<span class="' . $classname . '-after">' . esc_html($attr['after']) . '</span></div>');
	
}



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
					//$buy_links_html .= '<li class="' . $classname . '-listitem" style="' . $li_style . '">';
					$buy_links_html .= '<A class="' . $classname . '-link" HREF="' . esc_url($mbdb_buylink['_mbdb_buylink']) . '" TARGET="_new">';
					if ($r['image']!='') {
						$buy_links_html .= '<img class="' . $classname . '-image" style="' . esc_attr($img_size) . '" src="' . esc_url($r['image']) . '"/>';
					} else {
						$buy_links_html .= '<span class="' . $classname . '-text">' . esc_html($r['name']) . '</span>';
					}
					$buy_links_html .= '</a>';
					//$buy_links_html .= '</li>';
				}			
			}
		}
	}
	//$buy_links_html .= "</ul>"; 
	return apply_filters('mbdb_shortcode_buylinks', '<div class="' . $classname . '"><span class="' . $classname . '-label">' . esc_html($attr['label']) . '</span>' . $buy_links_html . '<span class="' . $classname . '-after">'.  esc_html($attr['after']) . '</span></div>');
}
	
	
	
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
function mbdb_get_editions_data($book = '') {
	$editions = mbdb_get_book_data(	'_mbdb_editions', $book);
	// the only way to know for sure there are no editions is to check 
	// for the existance of the format field
	if ($editions !== false) {
		foreach ($editions as $edition) {
			if (array_key_exists('_mbdb_format', $edition)) {
				return $editions;
			}
		}
	}
	return false;
	
}

function mbdb_output_editions($mbdb_editions, $attr) {
	$output_html = '';
	$counter = 0;
	foreach ($mbdb_editions as $edition) {
		$is_isbn = mbdb_check_field('_mbdb_isbn', $edition);
		$is_height = mbdb_check_field('_mbdb_height', $edition);
		$is_width = mbdb_check_field('_mbdb_width', $edition);
		$is_pages = mbdb_check_field('_mbdb_length', $edition);
		$is_price = mbdb_check_field('_mbdb_retail_price', $edition);
		$is_language = mbdb_check_field('_mbdb_language', $edition);
		$is_title = mbdb_check_field('_mbdb_edition_title', $edition);
		$default_language = mbdb_get_default_language();
		
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
		if ($is_price && $edition['_mbdb_retail_price'] != '0.00') {
			$output_html .= ': <span class="mbm-book-editions-srp"><span class="mbm-book-editions-currency">' . mbdb_get_currency_symbol($edition['_mbdb_currency']) . '</span><span class="mbm-book-editions-price">' . $edition['_mbdb_retail_price'] . '</span></span>';
		}
		if ($is_isbn || ($is_height && $is_width) || $is_pages) {
			$output_html .= '<div name="mbm_book_editions_subinfo[' . $counter . ']" id="mbm_book_editions_subinfo_' . $counter . '" class="mbm-book-editions-subinfo">';
		
			if ($is_isbn) {
				$output_html .= '<strong>' . __('ISBN', 'mooberry-book-manager') . ':</strong> <span class="mbm-book-editions-isbn">' . $edition['_mbdb_isbn'] . '</span><br>';
			}
			if ($is_height && $is_width) {
				$output_html .= '<strong>' . __('Size', 'mooberry-book-manager') . ':</strong> <span class="mbm-book-editions-size"><span class="mbm-book-editions-height">' . $edition['_mbdb_width'] . '</span>x<span class="mbm-book-editions-width">' . $edition['_mbdb_height'] . '</span> <span class="mbm-book-editions-unit">' . $edition['_mbdb_unit'] . '</span></span><br>';
			}
			if ($is_pages) {
				$output_html .= '<strong>' . __('Pages', 'mooberry-book-manager') . ':</strong> <span class="mbm-book-editions-length">' . $edition['_mbdb_length'] . '</span>';
			}
			$output_html .= '</div>';
		}
		$output_html .= '</span>';
		$counter++;
	}
	return apply_filters('mbdb_shortcode_editions', '<div class="mbm-book-editions"><span class="mbm-book-editions-label">' . esc_html($attr['label']) . '</span>' . $output_html . '<span class="mbm-book-editions-after">' . esc_html($attr['after']) . '</span></div>');
	
}

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

// Template
add_filter('single_template', 'mbdb_single_template');
function mbdb_single_template($template) {
	
	if (get_post_type() == 'mbdb_book' && is_main_query() && !is_admin()) {
		$mbdb_options = get_option('mbdb_options');
	
		if (isset($mbdb_options['mbdb_default_template']) && $mbdb_options['mbdb_default_template'] != '' && $mbdb_options['mbdb_default_template'] != 'default') {
			
			// first check if there's one in the child theme
			$child_theme = get_stylesheet_directory();
		
			if (file_exists($child_theme . '/' . $mbdb_options['mbdb_default_template'])) {
				return $child_theme . '/' . $mbdb_options['mbdb_default_template'];
			} else {
				// if not get the parent theme
				$parent_theme = get_template_directory();
	
				if (file_exists($parent_theme . '/' . $mbdb_options['mbdb_default_template'])) {
					return $parent_theme . '/' . $mbdb_options['mbdb_default_template'];
				}
			}
		}
	}
	// if everything fails, just return the default
	
	return $template;

}


/***************************************************************
						PAGE CONTENT
*******************************************************************/
	
function mbdb_book_content($content) {
	// $mbdb_book_page_options = get_option('mbdb_book_page_options');	
	
	// if ($mbdb_book_page_options) {
		// if (array_key_exists('_mbdb_book_page_layout', $mbdb_book_page_options)) {
		$book_page_layout = '<div id="mbm-book-page">';
			if (mbdb_get_subtitle_data() !== false) {
				$book_page_layout .= '<h3>[book_subtitle blank=""]</h3>';
			}
		
		
		
		
		//	$book_page_layout .= '<div id="mbm-book-sidebar">';
			$book_page_layout .= '[book_cover  align="left"]';
		
			$book_page_layout .= '<div id="mbm-book-links1">';
			$is_links_data = mbdb_get_links_data();
			if ($is_links_data['buylinks'] !== false) {
				$book_page_layout .= '[book_buylinks  align="horizontal"]';
			}
			if ($is_links_data['downloadlinks'] !== false) {
				$book_page_layout .= '[book_downloadlinks align="horizontal" label="' . __('Download Now:', 'mooberry-book-manager') . '"]';
			}
			
			$book_page_layout .= '</div>';
			
			if (mbdb_get_series_data() !== false ) {
				$book_page_layout .= '[book_serieslist before="' . __('Part of the','mooberry-book-manager') . ' " after=" ' . __('series','mooberry-book-manager') . ': " delim="list"]';
			}
			
			
			
			//if (mbdb_is_links_data() !== false) {
			//	$book_page_layout .= '[book_links buylabel="" downloadlabel="' . __('Download Now:', 'mooberry-book-manager') . '" align="vertical" size="205" blank="" blanklabel=""]';
			//}
			
		
			

			
			

			
		//	$book_page_layout .= '</div> <!-- mbm-book-sidebar --><div id="mbm-book-main">';
			
			
			
		
		
		
		if (mbdb_get_editions_data() !== false) {
				$book_page_layout .= '[book_editions blank="" label="' . __('Editions', 'mooberry-book-manager') . ':"]';
			}
		
			if (mbdb_get_goodreads_data() !== false) {
				$book_page_layout .= '[book_goodreads  ]';
			}
			
			if (mbdb_get_summary_data() !== false) {
				$book_page_layout .= '[book_summary blank=""]';
			}
			
		
				$is_published = mbdb_get_published_data();
			$is_publisher = mbdb_get_publisher_data();
			$is_genre = mbdb_get_taxonomy_data('mbdb_genre');
			$is_tag = mbdb_get_taxonomy_data('mbdb_tag');
			
			if ($is_published || $is_publisher || $is_genre || $is_tag) {
				$book_page_layout .= '<div class="mbm-book-details-outer">';
								$book_page_layout .= '<div class="mbm-book-details">';
				if ($is_published !== false) {
					$book_page_layout .= '<strong>' . __('Published', 'mooberry-book-manager') . ':</strong> [book_published format="short" blank=""]<br>';
				}
				
				if ($is_publisher !== false ) {
					$book_page_layout .= '<strong>' . __('Publisher','mooberry-book-manager') . ':</strong> [book_publisher  blank=""]<br>';
				}
							
				if ($is_genre !== false) {
					$book_page_layout .= '<strong>' . __('Genres','mooberry-book-manager') . ':</strong> <span>[book_genre delim="comma" blank=""]</span><br>';
				}
				
				if ($is_tag !== false) {
					$book_page_layout .= '<strong>' . __('Tags','mooberry-book-manager') . ':</strong> <span>[book_tags  delim="comma" blank=""]</span><br>';
				}
				$book_page_layout .= '</div></div> <!-- mbm-book-details -->';
			}
			
			
				$is_excerpt = mbdb_get_excerpt_data();
			if ( $is_excerpt !== false) {
				$book_page_layout .= '[book_excerpt label="' . __('Excerpt', 'mooberry-book-manager') . ':" length="1000"  blank="" ]';
		//		$book_page_layout .= '</div> <!-- mbm-book-main --><div id="mbm-book-bottom">';
			} 
		
		
			if (mbdb_get_reviews_data() !== false ) {
				$book_page_layout .= '<span>[book_reviews  blank="" label="' . __('Reviews', 'mooberry-book-manager') . ':"]</span><br>';
			}
			if (mbdb_get_additional_info_data() !== false ) {
				$book_page_layout .= '[book_additional_info]';
			}
			
		if ($is_excerpt && mbdb_is_links_data() !== false) {
				$book_page_layout .= '<div id="mbm-book-links2">[book_links buylabel="" downloadlabel="' . __('Download Now:', 'mooberry-book-manager') . '" align="horizontal"  blank="" blanklabel=""]</div>';
			}
			
			$book_page_layout .= '</div> <!-- mbm-book-page -->';
			
			
			//$book_page_layout = mbdb_get_default_page_layout(  );
			$content .= stripslashes($book_page_layout); //wpautop(stripslashes($mbdb_book_page_options['_mbdb_book_page_layout']));
			$content = preg_replace('/\\n/', '<br>', $content);
			return apply_filters('mbdb_book_content', $content);
		// }
	// }
	//just in case the option isn't in the database
	// $content .= preg_replace('/\\n/', '<br>', mbdb_get_default_page_layout()); //wpautop(mbdb_get_default_page_layout());
			
	//return apply_filters('mbdb_book_content', $content);
	
}
	