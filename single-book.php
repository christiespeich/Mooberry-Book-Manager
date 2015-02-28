<?php

add_shortcode( 'book_title', 'mbdb_shortcode_title'  );
add_shortcode( 'book_cover', 'mbdb_shortcode_cover'  );
add_shortcode( 'book_subtitle', 'mbdb_shortcode_subtitle'  );
add_shortcode( 'book_author', 'mbdb_shortcode_author'  );
add_shortcode( 'book_summary', 'mbdb_shortcode_summary'  );
add_shortcode( 'book_publisher', 'mbdb_shortcode_publisher'  );
add_shortcode( 'book_published', 'mbdb_shortcode_published'  );
add_shortcode( 'book_goodreads', 'mbdb_shortcode_goodreads'  );
add_shortcode( 'book_length', 'mbdb_shortcode_length' );
add_shortcode( 'book_excerpt', 'mbdb_shortcode_excerpt'  );
add_shortcode( 'book_genre', 'mbdb_shortcode_genre'  );
add_shortcode( 'book_reviews', 'mbdb_shortcode_reviews'  );
add_shortcode( 'book_buylinks', 'mbdb_shortcode_buylinks'  );
add_shortcode( 'book_downloadlinks', 'mbdb_shortcode_downloadlinks'  );
add_shortcode( 'book_serieslist', 'mbdb_shortcode_serieslist');
add_shortcode( 'book_series', 'mbdb_shortcode_series');
add_shortcode( 'book_tags', 'mbdb_shortcode_tags');
		
		
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

function mbdb_shortcode_subtitle($attr, $content) {
	$attr = shortcode_atts(array('book' => '',
								'blank' => ''), $attr);
	$bookID = mbdb_get_book_ID($attr['book']);
	if ($bookID != 0) {
		$mbdb_subtitle = get_post_meta($bookID, '_mbdb_subtitle', true);
		if (!empty($mbdb_subtitle)) {
			return apply_filters('mbdb_shortcode_subtitle', '<span class="mbm-book-subtitle"><span class="mbm-book-subtitle-text">' . esc_html($mbdb_subtitle) . '</span></span>');
		}
	}
	return apply_filters('mbdb_shortcode_subtitle', '<span class="mbm-book-subtitle"><span class="mbm-book-subtitle-blank">' . esc_html($attr['blank']) . '</span></span>');
}

function mbdb_shortcode_author($attr, $content) {
	$attr = shortcode_atts(array('book' => '',
								'blank' => ''), $attr);
	$bookID = mbdb_get_book_ID($attr['book']);
	if ($bookID != 0) {
		$mbdb_author = get_post_meta($bookID, '_mbdb_author', true);
		if (!empty($mbdb_author)) {
			return apply_filters('mbdb_shortcode_author', '<span class="mbm-book-author"><span class="mbm-book-author-text">' . esc_html(mbdb_get_author_names($mbdb_author)) . '</span></span>');	
		}
	}
	return apply_filters('mbdb_shortcode_author', '<span class="mbm-book-author"><span class="mbm-book-author-blank">' . esc_html($attr['blank']) . '</span></span>');
}



function mbdb_shortcode_summary($attr, $content) {
		$attr = shortcode_atts(array('label' => '',
									'after' => '',
									'blank' => '',
									'book' => ''), $attr);
		$bookID = mbdb_get_book_ID($attr['book']);
		if ($bookID == 0 ) {
			return apply_filters('mbdb_shortcode_summary', '<span class="mbm-book-summary"><span class="mbm-book-summary-blank">' . esc_html($attr['blank']) . '</span></span>');
		} else {
			$mbdb_summary =  get_post_meta($bookID, '_mbdb_summary', true);
			if (empty($mbdb_summary)) {
				return apply_filters('mbdb_shortcode_summary', '<span class="mbm-book-summary"><span class="mbm-book-summary-blank">' . esc_html($attr['blank']) . '</span></span>');
			} 
			return apply_filters('mbdb_shortcode_summary', '<div class="mbm-book-summary"><span class="mbm-book-summary-label">' . esc_html($attr['label']) . '</span><span class="mbm-book-summary-text">' . wpautop($mbdb_summary) . '</span><span class="mbm-book-summary-after">' . esc_html($attr['after']) . '</span></div>');
		}
	}

function mbdb_shortcode_publisher($attr, $content) {
	$attr = shortcode_atts(array('label' => '',
									'after' => '',
									'blank' => '',
									'book' => ''), $attr);
	
	$bookID = mbdb_get_book_ID($attr['book']);
	if ($bookID ==0) {
		return apply_filters('mbdb_shortcode_publisher', '<span class="mbm-book-publisher"><span class="mbm-book-publisher-blank">' . esc_html($attr['blank']) . '</span></span>');
	}
	$mbdb_publisher= get_post_meta($bookID, '_mbdb_publisher', true);
	if (empty($mbdb_publisher)) {
		return apply_filters('mbdb_shortcode_publisher',  '<span class="mbm-book-publisher"><span class="mbm-book-publisher-blank">' . esc_html($attr['blank']) . '</span></span>');
	}
	$mbdb_publisherwebsite = get_post_meta($bookID, '_mbdb_publisherwebsite', true);
	if (empty($mbdb_publisherwebsite)) {
		return apply_filters('mbdb_shortcode_publisher',  '<span class="mbm-book-publisher"><span class="mbm-book-publisher-label">' . esc_html($attr['label']) . '</span><span class="mbm-book-publisher-text">' . esc_html($mbdb_publisher) . '</span><span class="mbm-book-publisher-after">' . esc_html($attr['after']) . '</span></span>'); 
	} else {
		return apply_filters('mbdb_shortcode_publisher',  '<span class="mbm-book-publisher"><span class="mbm-book-publisher-label">' . esc_html($attr['label']) . '</span><A class="mbm-book-publisher-link" HREF="' . esc_url($mbdb_publisherwebsite) . '"><span class="mbm-book-publisher-text">' . esc_html($mbdb_publisher) . '</span></a><span class="mbm-book-publisher-after">' . esc_html($attr['after']) . '</span></span>');
	}
}

function mbdb_shortcode_published($attr, $content) {
	$attr = shortcode_atts(array('format' => 'short',
									'label' => '',
									'after' => '',
									'blank' => '',
									'book' => ''), $attr);	
	$bookID = mbdb_get_book_ID($attr['book']);
	if ($bookID == 0) { 
		return apply_filters('mbdb_shortcode_published',  '<span class="mbm-book-published"><span class="mbm-book-published-blank">' . esc_html($attr['blank']) . '</span></span>');
	}
	if ($attr['format'] =='short') {
		$format = 'm/d/Y';
	} else {
		$format = 'F j, Y';
	}
	$mbdb_published = get_post_meta( $bookID, '_mbdb_published', true );
	if ( empty( $mbdb_published ) ) { 
		return apply_filters('mbdb_shortcode_published',  '<span class="mbm-book-published"><span class="mbm-book-published-blank">' . esc_html($attr['blank']) . '</span></span>');  
	}
	return apply_filters('mbdb_shortcode_published',  '<span class="mbm-book-published"><span class="mbm-book-published-label">' . esc_html($attr['label']) . '</span><span class="mbm-book-published-text">' . date($format,strtotime($mbdb_published)) . '</span><span class="mbm-book-published-after">' .  esc_html($attr['after']) . '</span></span>');
}

function mbdb_shortcode_goodreads($attr, $content) {
	$attr = shortcode_atts(array('text' => 'View on Goodreads',
								'label' => '',
								'after' => '',
								'blank' => '',
								'book' => ''), $attr);

	
	$bookID = mbdb_get_book_ID($attr['book']);
	// check if image doesn't exist
	if ($bookID == 0 ) { 
		return apply_filters('mbdb_shortcode_goodreads',  '<span class="mbm-book-goodreads"><span class="mbm-book-goodreads-blank">' . esc_html($attr['blank']) . '</span></span>'); 
	}
	$mbdb_options = get_option('mbdb_options');
	$mbdb_goodreads = get_post_meta($bookID, '_mbdb_goodreads', true);
	if (empty($mbdb_goodreads)) { 
		return apply_filters('mbdb_shortcode_goodreads', '<span class="mbm-book-goodreads"><span class="mbm-book-goodreads-blank">' . esc_html($attr['blank']) . '</span></span>'); 
	}
	
	if (empty($mbdb_options['goodreads'])) { 
		return apply_filters('mbdb_shortcode_goodreads', '<span class="mbm-book-goodreads"><span class="mbm-book-goodreads-label">' . esc_html($attr['label']) . '</span><A class="mbm-book-goodreads-link" HREF="' . esc_url($mbdb_goodreads) . '" target="_new"><span class="mbm-book-goodreads-text">' . $attr['text'] . '</span></A><span class="mbm-book-goodreads-after">' . esc_html($attr['after']) . '</span></span>'); 
	} else {
		return apply_filters('mbdb_shortcode_goodreads', '<span class="mbm-book-goodreads"><span class="mbm-book-goodreads-label">' . esc_html($attr['label']) . '</span><A class="mbm-book-goodreads-link" HREF="' . esc_url($mbdb_goodreads) . '" target="_new"><img class="mbm-book-goodreads-image" src="' . esc_url($mbdb_options['goodreads']) . '"/></A><span class="mbm-book-goodreads-after">' . esc_html($attr['after']) . '</span></span>');
	}
}

function mbdb_shortcode_length($attr, $content) {
	$attr = shortcode_atts(array('label' => '',
									'after' => '',
									'blank' => '',
									'book' => ''), $attr);
	$bookID = mbdb_get_book_ID($attr['book']);
	if ($bookID ==0 ) { 
		return apply_filters('mbdb_shortcode_length', '<span class="mbm-book-length"><span class="mbm-book-length-blank">' . esc_html($attr['blank']) . '</span></span>'); 
	}
	$mbdb_length = get_post_meta($bookID, '_mbdb_length', true);
	if (empty($mbdb_length)) { 
		return apply_filters('mbdb_shortcode_length', '<span class="mbm-book-length"><span class="mbm-book-length-blank">' . esc_html($attr['blank']) . '</span></span>');  
	}
	return apply_filters('mbdb_shortcode_length', '<span class="mbm-book-length"><span class="mbm-book-length-label">' . esc_html($attr['label']) . '</span><span class="mbm-book-length-text">' . esc_html($mbdb_length) . '</span><span class="mbm-book-length-after">' . esc_html($attr['after']) . '</span></span>');
}

function mbdb_shortcode_excerpt($attr, $content) {
	$attr = shortcode_atts(array('label' => '',
									'after' => '',
									'blank' => '',
									'book' => ''), $attr);
	$bookID = mbdb_get_book_ID($attr['book']);
	if ($bookID ==0 ) { 
		return apply_filters('mbdb_shortcode_excerpt', '<span class="mbm-book-excerpt"><class="mbm-book-excerpt-blank">' . esc_html($attr['blank']) . '</span></span>'); 
	}
	$mbdb_excerpt = get_post_meta($bookID, '_mbdb_excerpt', true);
	if (empty($mbdb_excerpt)) { 
		apply_filters('mbdb_shortcode_excerpt', '<span class="mbm-book-excerpt"><class="mbm-book-excerpt-blank">' . esc_html($attr['blank']) . '</span></span>'); 
	}
	apply_filters('mbdb_shortcode_excerpt', '<div class="mbm-book-excerpt"><span class="mbm-book-excerpt-label">' . esc_html($attr['label']) . '</span><span class="mbm-book-excerpt-text">' . wpautop($mbdb_excerpt) . '</span><span class="mbm-book-excerpt-after">' . esc_html($attr['after']) . '</span></div>');
}

function mbdb_shortcode_series( $attr, $content) {
	return mbdb_shortcode_taxonomy($attr, 'mbdb_series', 'series');
}

function mbdb_shortcode_tags( $attr, $content) {
	return mbdb_shortcode_taxonomy($attr, 'post_tag', 'tag');
}
function mbdb_shortcode_genre($attr, $content) {
	return mbdb_shortcode_taxonomy($attr, 'mbdb_genre', 'genre');
}

function mbdb_shortcode_taxonomy($attr, $taxonomy, $permalink) {

	$attr = shortcode_atts(array('delim' => 'comma',
								'blank' => '',
								'book' => ''), $attr);
	
	$classname = 'mbm-book-' . $permalink;
	
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
	$bookID = mbdb_get_book_ID($attr['book']);
	if ($bookID == 0) { 
		return apply_filters('mbdb_shortcode_' . $permalink . '_taxonomy',  '<span class="' . $classname . '"><span class="' . $classname . '-blank">' . esc_html($attr['blank']) . '</span></span>'); 
	}
	$mbdb_genres = get_the_terms( $bookID, $taxonomy);
	if (!$mbdb_genres) { 
		return apply_filters('mbdb_shortcode_' . $permalink . '_taxonomy',  '<span class="' . $classname . '"><span class="' . $classname . '-blank">' . esc_html($attr['blank']) . '</span></span>'); 
	} 
	$list = '';
	$list .= $before;
	foreach ($mbdb_genres as $genre) {
		$list .= '<a class="' . $classname . '-link" href="';
		if ( get_option('permalink_structure') !='' ) {
			$list .= site_url($permalink . '/' . $genre->slug);
		} else {
			$list .= site_url('?mbdb_tax_grid=Test&the-taxonomy=' . $taxonomy . '&the-term=' . $genre->slug . '&post_type=mbdb_tax_grid');
		}
		$list .= '"><span class="' . $classname . '-text">' . $genre->name . '</span></a>';
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


function mbdb_shortcode_serieslist($attr, $content) {
	$attr = shortcode_atts(array('blank' => '',
									'before' => 'Part of the ',
									'after' => ' series:',
									'delim' => 'list',
									'book' => ''), $attr);
	
	$bookID = mbdb_get_book_ID( $attr['book'] );
	$classname = 'mbm-book-serieslist';
	if ($bookID == 0) { 
		return apply_filters('mbdb_shortcode_serieslist',  '<span class="' . $classname . '"><span class="' . $classname . '-blank">' . esc_html($attr['blank']) . '</span></span>'); 
	}
	$mbdb_series = get_the_terms( $bookID, 'mbdb_series');	
	if (!$mbdb_series) { 
		return apply_filters('mbdb_shortcode_serieslist', '<span class="' . $classname . '"><span class="' . $classname . '-blank">' . esc_html($attr['blank']) . '</span></span>'); 
	}
	$series_name = '';
	foreach($mbdb_series as $series) {
		$series_name .=  '<div class="' . $classname . '-seriesblock"><span class="' . $classname . '-before">' . esc_html($attr['before']) . '</span>';
		$series_name .= '<a class="' . $classname . '-link" href="';
		if ( get_option('permalink_structure') !='' ) {
			$series_name .= site_url('series/' .  $series->slug);
		} else {
			$series_name .= site_url('?mbdb_tax_grid=Test&the-taxonomy=mbdb_series&the-term=' . $series->slug . '&post_type=mbdb_tax_grid');
		}
		$series_name .=  '"><span class="' . $classname . '-text">' . $series->name . '</span></a>';
		$series_name .= '<span class="' . $classname . '-after">' . esc_html($attr['after']) . '</span>';
		$series_name .= mbdb_series_list($attr['delim'],  $series->slug, $bookID);
		$series_name .=  '</div>';
	}
	return apply_filters('mbdb_shortcode_serieslist', '<div class="' . $classname . '">' . $series_name . '</div>');
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

function mbdb_shortcode_cover( $attr, $content) {
	$image_src = '';
	$attr = shortcode_atts(array('width' =>  300,
								'align' => 'right',
								'wrap' => 'yes',
								'book' => ''), $attr);
	$bookID = mbdb_get_book_ID( esc_html($attr['book'] ));
	if ($bookID == 0) { 
		return apply_filters('mbdb_shortcode_cover', '<span class="mbm-book-cover"><span class="mbm-book-cover-blank"></span></span>'); 
	}
	$image_src = get_post_meta( $bookID, '_mbdb_cover', true );
	$image_html ='';
	if (isset($image_src) && $image_src != '') {
		$image_html = '<img style="width:' . esc_attr($attr['width']) . 'px" src="' . esc_url($image_src) . '" ';
		if ($attr['wrap']=='yes') {
			$image_html .= 'class="align' . esc_attr($attr['align']) . '">';
		} else {
			$image_html .= 'style="float:' . esc_attr($attr['align']) . '"><div style="clear:' . esc_attr($attr['align']) . '"> &nbsp;</div>';
		}
	}
	return apply_filters('mbdb_shortcode_cover',  '<span class="mbm-book-cover">' . $image_html . '</span>');
}

function mbdb_shortcode_reviews( $attr, $content) {
	 $review_html = '';
	 $attr = shortcode_atts(array('label' => '',
									 'after' => '',
									 'blank' => '',
									'book' => ''), $attr);
	$bookID = mbdb_get_book_ID( $attr['book'] );
	if ($bookID == 0) 	{ 
		return apply_filters('mbdb_shortcode_reviews', '<span class="mbm-book-reviews"><span class="mbm-book-reviews-blank">' . esc_html($attr['blank']) . '</span></span>'); 
	}
	$mbdb_reviews = get_post_meta( $bookID, '_mbdb_reviews', true);
	if ($mbdb_reviews) {
		foreach ($mbdb_reviews as $review) {
				$reviewer_name = mbdb_check_field('mbdb_reviewer_name', $review);
				$review_url = mbdb_check_field('mbdb_review_url', $review);
				$review_website = mbdb_check_field('mbdb_review_website', $review);
				$review_html .= '<span class="mbm-book-reviews-block"><span class="mbm-book-reviews-header">';
			if ($reviewer_name) {
				$review_html .=  '<span class="mbm-book-reviews-reviewer-name">' . esc_html($review['mbdb_reviewer_name']) . '</span> ';
			}
			if ($review_url || $review_website) {
				$review_html .= 'on ';
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
				$review_html .= ' wrote';
			}
			$review_html .=	':</span>';
			$review_html .= ' <blockquote class="mbm-book-reviews-text">' . wpautop($review['mbdb_review']) . '</blockquote></span>';
		}
		if (!mbdb_check_field('mbdb_review', $review)) {
			return apply_filters('mbdb_shortcode_reviews', '<span class="mbm-book-reviews"><span class="mbm-book-reviews-blank">' . esc_html($attr['blank']) . '</span></span>'); 
		} else {
			return apply_filters('mbdb_shortcode_reviews', '<div class="mbm-book-reviews"><span class="mbm-book-reviews-label">' . esc_html($attr['label']) . '</span>' . $review_html . '<span class="mbm-book-reviews-after">' . esc_html($attr['after']) . '</span></div>');
		}
	} else {
		return apply_filters('mbdb_shortcode_reviews', '<span class="mbm-book-reviews"><span class="mbm-book-reviews-blank">' . esc_html($attr['blank']) . '</span></span>');
	}
}

function mbdb_shortcode_downloadlinks( $attr, $content) {
	$attr = shortcode_atts(array('align' => 'vertical',
								'label' => '',
								'after' => '',
								'blank' => '',
								'book' => ''), $attr);
	$bookID = mbdb_get_book_ID($attr['book']);
	$classname = 'mbm-book-download-links';
	if ($bookID == 0) { 
		return apply_filters('mbdb_shortcode_downloadlinks', '<span class="' . $classname . '"><span class="' . $classname . '-blank"' . esc_html($attr['blank']) . '</span></span>'); 
	}
	$mbdb_options = get_option( 'mbdb_options' );
	$mbdb_downloadlinks = get_post_meta( $bookID, '_mbdb_downloadlinks', true);
	if (($mbdb_downloadlinks=='') || (!array_key_exists(0, $mbdb_downloadlinks))) { 
		return apply_filters('mbdb_shortcode_downloadlinks', '<span class="' . $classname . '"><span class="' . $classname . '-blank">' . esc_html($attr['blank']) . '</span></span>'); 
	}
	$download_links_html = '<UL class="' . $classname . '-list" style="list-style-type:none;">';
	if ($attr['align'] =='vertical') {
		$li_style = "margin: 1em 0 1em 0;";
	} else {
		$li_style = "display:inline;margin: 0 1em 0 0;";
	}
	foreach ($mbdb_downloadlinks as $mbdb_downloadlink) {
		// get format info based on formatid = uniqueid
		foreach($mbdb_options['formats'] as $r) {
			if ($r['uniqueID'] == $mbdb_downloadlink['_mbdb_formatID']) {
				$download_links_html .= '<li class="' . $classname . '-listitem" style="' . $li_style . '"><A class="' . $classname . '-link" HREF="' . esc_url($mbdb_downloadlink['_mbdb_downloadlink']) . '">';
				if ($r['image']!='') {
					$download_links_html .= '<img class="' . $classname . '-image" src="' . esc_url($r['image']) . '"/>';
				} else {
					$download_links_html .= '<span class="' . $classname . '-text">' . esc_html($mbdb_downloadlink['mbdb_name']) . '</span>';
				}
				$download_links_html .= '</a></li>';
			}			
		}
	}
	$download_links_html .= "</ul>"; 
	
	return apply_filters('mbdb_shortcode_downloadlinks', '<div class="' . $classname . '"><span class="' . $classname . '-label">' .  esc_html($attr['label']) . '</span>' . $download_links_html . '<span class="' . $classname . '-after">' . esc_html($attr['after']) . '</span></div>');
	
}

	
function mbdb_shortcode_buylinks( $attr, $content) {
	$attr = shortcode_atts(array('width' =>  '100',
								'height' => '50',
								'size' => '',
								'align' => 'vertical',
								'label' => '',
								'after' => '',
								'blank' => '',
								'book' => ''), $attr);
	$classname = 'mbm-book-buy-links';
	$bookID = mbdb_get_book_ID($attr['book']);
	if ($bookID == 0) { 
		return apply_filters('mbdb_shortcode_buylinks', '<span class="' . $classname . '"><span class="' . $classname . '-blank">' . esc_html($attr['blank']) . '</span></span>'); 
	}
	$mbdb_options = get_option( 'mbdb_options' );
	$mbdb_downloadlinks = get_post_meta( $bookID, '_mbdb_buylinks', true);
	if ($mbdb_downloadlinks=='') { 
		return apply_filters('mbdb_shortcode_buylinks', '<span class="' . $classname . '"><span class="' . $classname . '-blank">' . esc_html($attr['blank']) . '</span></span>'); 
	}
	$download_links_html = '<UL class="' . $classname . '-list" style="list-style-type:none;">';
	if ($attr['align'] =='vertical') {
		$li_style = "margin: 1em 0 1em 0;";
		if ($attr['size']) { $attr['width'] = $attr['size']; }
		$img_size = "width:" . esc_attr($attr['width']) . "px;";
	} else {
		$li_style = "display:inline;margin: 0 1em 0 0;";
		if ($attr['size']) { $attr['height'] = $attr['size']; }
		$img_size = "height:" . esc_attr($attr['height']) . "px;";		
	}
	foreach ($mbdb_downloadlinks as $mbdb_downloadlink) {
		// get format info based on formatid = uniqueid
		foreach($mbdb_options['retailers'] as $r) {
			if ($r['uniqueID'] == $mbdb_downloadlink['_mbdb_retailerID']) {
				$download_links_html .= '<li class="' . $classname . '-listitem" style="' . $li_style . '"><A class="' . $classname . '-link" HREF="' . esc_url($mbdb_downloadlink['_mbdb_buylink']) . '" TARGET="_new">';
				if ($r['image']!='') {
					$download_links_html .= '<img class="' . $classname . '-image" style="' . esc_attr($img_size) . '" src="' . esc_url($r['image']) . '"/>';
				} else {
					$download_links_html .= '<span class="' . $classname . '-text"' . esc_html($mbdb_downloadlink['mbdb_name']) . '</span>';
				}
				$download_links_html .= '</a></li>';
			}			
		}
	}
	$download_links_html .= "</ul>"; 
	return apply_filters('mbdb_shortcode_buylinks', '<div class="' . $classname . '"><span class="' . $classname . '-label">' . esc_html($attr['label']) . '</span>' . $download_links_html . '<span class="' . $classname . '-after">'.  esc_html($attr['after']) . '</span></div>');
}
	
	
function mbdb_book_content($content) {
	$mbdb_book_page_options = get_option('mbdb_book_page_options');	
	
	if ($mbdb_book_page_options) {
		if (array_key_exists('_mbdb_book_page_layout', $mbdb_book_page_options)) {
			$content .= wpautop(stripslashes($mbdb_book_page_options['_mbdb_book_page_layout']));
			return apply_filters('mbdb_book_content', $content);
		}
	}
	// just in case the option isn't in the database
	$content .= wpautop(mbdb_get_default_page_layout());
				
	return apply_filters('mbdb_book_content', $content);
	
}
	?>