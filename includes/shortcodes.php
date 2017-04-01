<?php



add_shortcode( 'book_title',  'mbdb_shortcode_title' );
add_shortcode( 'book_cover',  'mbdb_shortcode_cover'   );
add_shortcode( 'book_subtitle',  'mbdb_shortcode_subtitle'  );
add_shortcode( 'book_summary',  'mbdb_shortcode_summary' );
add_shortcode( 'book_publisher',  'mbdb_shortcode_publisher' );
add_shortcode( 'book_published',  'mbdb_shortcode_published' );
add_shortcode( 'book_goodreads',  'mbdb_shortcode_goodreads' );
add_shortcode( 'book_excerpt',  'mbdb_shortcode_excerpt'  );
add_shortcode( 'book_additional_info',  'mbdb_shortcode_additional_info' );
add_shortcode( 'book_genre',  'mbdb_shortcode_genre'  );
add_shortcode( 'book_reviews',  'mbdb_shortcode_reviews' );
add_shortcode( 'book_buylinks',  'mbdb_shortcode_buylinks'  );
add_shortcode( 'book_downloadlinks',  'mbdb_shortcode_downloadlinks' );
add_shortcode( 'book_serieslist',  'mbdb_shortcode_serieslist'  );
add_shortcode( 'book_series',  'mbdb_shortcode_series' );
add_shortcode( 'book_tags',  'mbdb_shortcode_tags'  );
add_shortcode( 'book_illustrator',  'mbdb_shortcode_illustrator'  );
add_shortcode( 'book_editor',  'mbdb_shortcode_editor'  );
add_shortcode( 'book_cover_artist',  'mbdb_shortcode_cover_artist'  );
add_shortcode( 'book_links',  'mbdb_shortcode_links'  );
add_shortcode( 'book_editions',  'mbdb_shortcode_editions'  );
add_shortcode( 'mbdb_book',  'mbdb_shortcode_book'  );
add_shortcode( 'book_kindle_preview',  'mbdb_shortcode_kindle_preview');


/************************************************************
	*  
	*  DISPLAY
	*  SHORTCODES
	*  
	************************************************************/

	private function mbdb_set_book( $slug = '' ) {
		
		if ( $slug == '' ) {
			global $post;
			return MBDB()->book_factory->create_book( $post->ID );
			
			if ( !isset($this->data_object) || $this->data_object->id != $post->ID ) {
				$this->set_data_object( $post->ID );
			}
		} else {
			
			if ( !isset($this->data_object) || $this->data_object->slug != $slug ) {
				$this->set_data_object( $slug );
			}
		}
	}
	
	
	private function mbdb_output_blank_data($classname, $blank_output) {
		return apply_filters('mbdb_shortcode_' . $classname, '<span class="mbm-book-' . $classname . '"><span class="mbm-book-' . $classname . '-blank">' . esc_html($blank_output) . '</span></span>');
	}
	 
	public function mbdb_shortcode_title( $attr, $content ) {
		$attr = mbdb_shortcode_atts(array('book' => '',
								'blank' => ''), $attr);
								
		if ( $attr[ 'book' ] == '' ) {
			global $post;
			return $post->post_title;
		} else {
			set_book( $attr[ 'book' ] );
			return $this->data_object->title;
		}		
	}

	public function mbdb_shortcode_cover( $attr, $content ) {
		
		$attr = mbdb_shortcode_atts(array('width' =>  '',
								'align' => 'right',
								'wrap' => 'yes',
								'book' => ''), $attr);
	
		mbdb_set_book( $attr[ 'book' ] );	
		
		$url = $this->data_object->get_cover_url( 'large', 'page' );
		$alt = MBDB()->helper_functions->get_alt_attr( $this->data_object->cover_id, __('Book Cover:', 'mooberry-book-manager') . ' ' . $this->data_object->title ); 
		
		if (isset($url) && $url != '') {
			$image_html = '<img src="' . esc_url($url) . '" ' . $alt . ' itemprop="image" />';
			return apply_filters('mbdb_shortcode_cover',  '<span class="mbm-book-cover">' . $image_html . '</span>');
		} else {
			return mbdb_output_blank_data( 'cover', '');
		}
	}

	public function mbdb_shortcode_subtitle( $attr, $content ) {
		$attr = mbdb_shortcode_atts(array('book' => '',
								'blank' => ''), $attr);
		
		mbdb_set_book( $attr[ 'book' ] );
		
		if ( $this->data_object->subtitle == '' ) {
			return mbdb_output_blank_data( 'subtitle', $attr[ 'blank' ] );
		} 
		return apply_filters('mbdb_shortcode_subtitle', '<span class="mbm-book-subtitle"><span class="mbm-book-subtitle-text">' . esc_html( $this->data_object->subtitle ) . '</span></span>');
			
	}

	public function mbdb_shortcode_summary( $attr, $content ) {
		$attr = mbdb_shortcode_atts(array('label' => '',
									'after' => '',
									'blank' => '',
									'book' => ''), $attr);
									
		mbdb_set_book( $attr[ 'book' ] );
		
		if ( $this->data_object->summary == '' ) {
			return mbdb_output_blank_data( 'summary', $attr[ 'blank' ] );
		} 
		//error_log('summary');
		$output = '<div class="mbm-book-summary"><span class="mbm-book-summary-label">' . esc_html($attr['label']) . '</span><span class="mbm-book-summary-text">';
		$output .= $this->get_wysiwyg_output( $this->data_object->summary );
	
		$output .= '</span><span class="mbm-book-summary-after">' . esc_html($attr['after']) . '</span></div>';

		return apply_filters('mbdb_shortcode_summary', $output);
	
	}

	public function mbdb_shortcode_publisher( $attr, $content ) {
		$attr = mbdb_shortcode_atts(array('label' => '',
									'after' => '',
									'blank' => '',
									'book' => ''), $attr);
									
		mbdb_set_book( $attr[ 'book' ] );
		
		if ( !$this->data_object->has_publisher() ) {	
			return mbdb_output_blank_data( 'publisher', $attr[ 'blank' ] );
		}
		
		$mbdb_publisher = $this->data_object->publisher->name;
		$mbdb_publisherwebsite = $this->data_object->publisher->website;
	
		if ( $mbdb_publisherwebsite == '' ) {
			$text = '<span class="mbm-book-publisher-text">' . esc_html($mbdb_publisher) . '</span>';
		} else {
			$text = '<A class="mbm-book-publisher-link" HREF="' . esc_url($mbdb_publisherwebsite) . '" target="_new"><span class="mbm-book-publisher-text">' . esc_html($mbdb_publisher) . '</span></a>';
		}
		
		return apply_filters('mbdb_shortcode_publisher',  '<span class="mbm-book-publisher"><span class="mbm-book-publisher-label">' . esc_html($attr['label']) . '</span>' . $text . '<span class="mbm-book-publisher-after">' . esc_html($attr['after']) . '</span></span>'); 
	}

	public function mbdb_shortcode_published( $attr, $content ) {
		$attr = mbdb_shortcode_atts(array('format' => 'short',
									'published_label' => __('Published:', 'mooberry-book-manager'),
									'unpublished_label'	=> __('Available on:', 'mooberry-book-manager'),
									'after' => '',
									'blank' => '',
									'book' => ''), $attr);	
		mbdb_set_book( $attr[ 'book' ] );
		
		if ( $this->data_object->release_date == '' ) {
			return mbdb_output_blank_data( 'published', $attr[ 'blank' ] );
		}
		//error_log('published');
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
		if ( $this->data_object->release_date <= strtotime('now') ) {
			$label = $attr['published_label'];
		} else {
			$label = $attr['unpublished_label'];
		}
		return apply_filters('mbdb_shortcode_published',  '<span class="mbm-book-published"><span class="mbm-book-details-published-label"><span class="mbm-book-published-label">' . esc_html($label) . '</span></span> <span class="mbm-book-published-text" itemprop="datePublished" content="' . esc_attr($this->data_object->release_date) . '">' .  date_i18n( $format, strtotime( $this->data_object->release_date ) ) . '</span><span class="mbm-book-published-after">' .  esc_html($attr['after']) . '</span></span>');	
		
	}

	public function mbdb_shortcode_goodreads( $attr, $content ) {
		$attr = mbdb_shortcode_atts(array('text' => __('View on Goodreads', 'mooberry-book-manager'),
								'label' => '',
								'after' => '',
								'blank' => '',
								'book' => ''), $attr);
						//error_log('goodreads');		
		mbdb_set_book( $attr[ 'book' ] );
		$goodreads_link = $this->data_object->goodreads;
		if ( $goodreads_link == '' ) {
			return mbdb_output_blank_data( 'goodreads', $attr[ 'blank' ] );
		}
		$goodreads_image = MBDB()->options->goodreads_image;
		
		if ( $goodreads_image == '' ) { 
			return apply_filters('mbdb_shortcode_goodreads', '<div class="mbm-book-goodreads"><span class="mbm-book-goodreads-label">' . esc_html( $attr['label'] ) . '</span><A class="mbm-book-goodreads-link" HREF="' . esc_url($goodreads_link) . '" target="_new"><span class="mbm-book-goodreads-text">' . $attr['text'] . '</span></A><span class="mbm-book-goodreads-after">' . esc_html($attr['after']) . '</span></div>'); 
		} else {
			$alt = __('Add to Goodreads', 'mooberry-book-manager');
			$url = esc_url($goodreads_image);
			if (is_ssl()) {
				$url = preg_replace('/^http:/', 'https:', $url);
			}
			return apply_filters('mbdb_shortcode_goodreads', '<div class="mbm-book-goodreads"><span class="mbm-book-goodreads-label">' . esc_html($attr['label']) . '</span><A class="mbm-book-goodreads-link" HREF="' . esc_url($goodreads_link) . '" target="_new"><img class="mbm-book-goodreads-image" src="' . $url . '"' . $alt . '/></A><span class="mbm-book-goodreads-after">' . esc_html($attr['after']) . '</span></div>');
		}
	}


	public function mbdb_shortcode_excerpt( $attr, $content ) {
		$attr = mbdb_shortcode_atts(array('label' => '',
									'after' => '',
									'blank' => '',
									'length' => '0',
									'book' => ''), $attr);
		
		mbdb_set_book( $attr[ 'book' ] );
		
		
		
		if ( $this->data_object->has_kindle_preview() ) {
			//return $this->data_object->kindle_preview;
		
			return apply_filters('mbdb_shortcode_excerpt_kindle_preview', do_shortcode('[book_kindle_preview asin="' . $this->data_object->kindle_preview . '"]' ) );
		}
		
		
		$excerpt = $this->data_object->excerpt;
		
		if ( $excerpt == '' ) {
			return mbdb_output_blank_data( 'excerpt', $attr[ 'blank' ] );
		}
		$excerpt = wpautop( $excerpt );
		$excerpt1 = '';
		$excerpt2 = '';
		if ($attr['length'] == 0) {
			$excerpt1 = $excerpt;
			$excerpt2 = '';
		} else {
			if (preg_match('/^(.{1,' . $attr['length'] . '}<\/p>)(.*)/s', $excerpt, $match))	{
				$excerpt1 = $match[1];
				$excerpt2 = $match[2];
			} else {
				// if we're here there's probably no paragraph tags for whatever reason
				// so take teh first 1000 characters ending on a sentence
				if (preg_match('/^(.{1,1000}[.?!"”])(.*)/s', $excerpt, $match)) {
					$excerpt1 = $match[1];
					$excerpt2 = $match[2];
				} else {
					// just grab the first 1000 characters no matter where that ends up
					if ( strlen( $excerpt ) > 1000 )  {
						$excerpt1 = substr( $excerpt, 0, 999 );
						$excerpt2 = substr( $excerpt, 1000);
					} else {
						$excerpt1 = $excerpt;
						$excerpt2 = '';
					}
				}
			}
		}
		$html_output = '<div class="mbm-book-excerpt">
		<span class="mbm-book-excerpt-label">' . esc_html($attr['label']) . '</span>
		<span class="mbm-book-excerpt-text">';
		$html_output .= $this->get_wysiwyg_output($excerpt1);

		if (trim($excerpt2) != '' ) {
			$html_output .= '<a name="more" class="mbm-book-excerpt-read-more">' . __('READ MORE', 'mooberry-book-manager') . '</a>
			<span class="mbm-book-excerpt-text-hidden">';
			$html_output .= $this->get_wysiwyg_output($excerpt2);
			$html_output .= '<a class="mbm-book-excerpt-collapse" name="collapse">' . __('COLLAPSE', 'mooberry-book-manager') . '</a></span>';
		}

		$html_output .=' </span><span class="mbm-book-excerpt-after">' . esc_html($attr['after']) . '</span></div>';
		return apply_filters('mbdb_shortcode_excerpt', $html_output);

	}
	
	public function mbdb_shortcode_kindle_preview( $attr, $content ) {
		$attr = mbdb_shortcode_atts(array(
								'asin'	=>	'',
								'affiliate'	=>	'',
								), $attr);
							
		return '<div class="mbm-book-excerpt"><span class="mbm-book-excerpt-label">Excerpt:</span><iframe type="text/html" width="336" height="550" frameborder="0" allowfullscreen style="max-width:100%" src="https://read.amazon.com/kp/card?asin=' . esc_attr($attr['asin']) . '&preview=inline&linkCode=kpe&tag=' . esc_attr($attr['affiliate']) . '" ></iframe></div>';
	
	}

	public function mbdb_shortcode_additional_info( $attr, $content ) {
		$attr = mbdb_shortcode_atts(array(	'blank' => '',
									'book' => ''), $attr);
									
		mbdb_set_book( $attr[ 'book' ] );
		
		$additional_info = $this->data_object->additional_info;
		if ( $additional_info == '' ) {
			return mbdb_output_blank_data( 'additional_info', $attr[ 'blank' ] );
		}
		$html_output = '<div class="mbm-book-additional-info">';
		$html_output .= $this->get_wysiwyg_output( $additional_info ); 
		$html_output .= '</div>';
		return apply_filters('mbdb_shortcode_additional_info', $html_output);
									
	}
	
	// public needed for other screens to access
	public function output_taxonomy( $classname, $mbdb_terms, $permalink, $taxonomy, $attr) {
		//error_log('taxonomy ' . $taxonomy);
		if ( $attr['delim'] == 'comma' ) {
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
			$itemprop = '';
			if ( $taxonomy == 'mbdb_genre' ) {
				$itemprop = ' itemprop="genre" ';
			}
			$list .= '"><span class="' . $classname . '-text" ' . $itemprop . '>' . $term->name . '</span></a>';
			if ( function_exists( 'get_term_meta' ) ) {
				//if ( in_array( $term->taxonomy, mbdb_taxonomies_with_websites() ) ) {
					$website = get_term_meta( $term->term_id, 'mbdb_website', true);
					if ($website != '' ) {
						$list .= ' (<a class="' . $classname . '-website" href="' . $website . '" target="_new">' . __('Website', 'mooberry-book-manager') . '</a>)';
					}
				//}
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


	private function mbdb_shortcode_taxonomy ( $attr, $taxonomy, $default_permalink, $property ) {
		$attr = mbdb_shortcode_atts(array('delim' => 'comma',
								'blank' => '',
								'book' => ''), $attr);
		
		$permalink = MBDB()->options->get_tax_grid_slug( $taxonomy );
		
		mbdb_set_book( $attr[ 'book' ] );
		
		if ( property_exists( $this->data_object, $property ) && method_exists( $this->data_object, 'has_' . $property ) ) {
			if ( call_user_func( array( $this->data_object, 'has_' . $property ) ) ) {
				return $this->output_taxonomy( 'mbm-book-' . $default_permalink, $this->data_object->$property, $permalink, $taxonomy, $attr );
			} 
		}
		return mbdb_output_blank_data( $permalink . '_taxonomy', $attr[ 'blank' ] );
	}
	
	public function mbdb_shortcode_genre( $attr, $content ) {
		return $this->mbdb_shortcode_taxonomy( $attr, 'mbdb_genre', 'genre', 'genres');
	}

	public function mbdb_shortcode_reviews( $attr, $content ) {
		$attr = mbdb_shortcode_atts(array('label' => '',
									 'after' => '',
									 'blank' => '',
									'book' => ''), $attr);
									
		mbdb_set_book( $attr['book'] );
		$book = $this->data_object;
		if ( !$book->has_reviews() ) {
			return mbdb_output_blank_data( 'reviews', $attr[ 'blank' ] );
		}
		$review_html = '';
		foreach ($book->reviews as $review) {
			$reviewer_name = $review->reviewer_name;
			$review_url = $review->url;
			$review_website = $review->website_name;
			$review_html .= '<span class="mbm-book-reviews-block"><span class="mbm-book-reviews-header">';
			if ($reviewer_name != '') {
				$review_html .=  '<span class="mbm-book-reviews-reviewer-name">' . esc_html($reviewer_name) . '</span> ';
			}
			if ($review_url != ''|| $review_website != '') {
				$review_html .= __('on ','mooberry-book-manager');
			}
			if ($review_url != '') {
				$review_html .= '<A class="mbm-book-reviews-link" HREF="' . esc_url($review_url) . '" target="_new"><span class="mbm-book-reviews-website">';
				if ($review_website == '' ) {
					$review_html .= esc_html($review_url);	
				} else {
					$review_html .= esc_html($review_website);
				}
				$review_html .=	'</span></A>';
			} else {
				if ($review_website != '') {
					$review_html .= '<span class="mbm-book-reviews-website">' . esc_html($review_website) . '</span>';
				}
			}
			if ($reviewer_name != '') {
				$review_html .= ' ' . __('wrote','mooberry-book-manager');
			}
			$review_html .=	':</span>';
			$review_html .= ' <blockquote class="mbm-book-reviews-text">' . wpautop(wp_kses_post($review->review)) . '</blockquote></span>';
		}
		
		return apply_filters('mbdb_shortcode_reviews', '<div class="mbm-book-reviews"><span class="mbm-book-reviews-label">' . esc_html($attr['label']) . '</span>' . $review_html . '<span class="mbm-book-reviews-after">' . esc_html($attr['after']) . '</span></div>');
		
	}
	
	public function output_buylinks( $buylinks, $attr ) {
		$classname = 'mbm-book-buy-links';
//error_log('output_buylinks');
		$buy_links_html = '';
		$img_size = '';
		if ($attr['align'] =='vertical') {
			if ($attr['size']) { $attr['width'] = $attr['size']; }
		} else {
			if ($attr['size']) { $attr['height'] = $attr['size']; }
		}
		if ($attr['width']) {
				$img_size = "width:" . esc_attr($attr['width']) ;
			}
		if ($attr['height']) {
				$img_size = "height:" . esc_attr($attr['height']);		
			}
			
		foreach ($buylinks as $mbdb_buylink) {
			//error_log('next link');
			$retailer = $mbdb_buylink->retailer;
		
						// 3.5 this filter for backwards compatibility
						$mbdb_buylink = apply_filters('mbdb_buy_links_output', $mbdb_buylink, $mbdb_buylink->retailer );
						// 3.5 add affiliate codes
						$mbdb_buylink = apply_filters('mbdb_buy_links_pre_affiliate_code', $mbdb_buylink, $mbdb_buylink->retailer);
						// backwards compatibility with multi-author?!?!
						// will have to convert to arrays
						//error_log('make array');
						$retailer_array = MBDB()->helper_functions->object_to_array( $mbdb_buylink->retailer );
						$mbdb_buylink_array = array();
						
						$mbdb_buylink_array[ '_mbdb_retailerID'] = $retailer->id;
						
						
						
						
						
						// this filter strictly for backwards compatibility with MA ??
						$retailer_array = apply_filters('mbdb_buy_links_retailer_pre_affiliate_code', $retailer_array, $mbdb_buylink_array);
						// convert array back to an object
						//error_log('back to object');
						$retailer = MBDB()->helper_functions->array_to_object( $retailer_array, $retailer);
						// Does the retailer have an affiliate code?
						if ( $retailer->has_affiliate_code() ) { 
						//array_key_exists('affiliate_code', $retailer) && $retailer['affiliate_code'] != '') {
							//error_log('has affilitate code');
							
							// append or prepend the code
							if ($retailer->affiliate_position == 'before') {
								$mbdb_buylink->link = $retailer->affiliate_code . $mbdb_buylink->link;
							} else {
								$mbdb_buylink->link .= $retailer->affiliate_code;
							}
						}		
						$mbdb_buylink = apply_filters('mbdb_buy_links_post_affiliate_code', $mbdb_buylink, $retailer);
						//$buy_links_html .= '<li class="' . $classname . '-listitem" style="' . $li_style . '">';
						
						// 3.5.6
						
						if ( $retailer->id == '13' ) {
							$buy_links_html .= '<span style="float:left;">' . __('Available on', 'mooberry-book-manager') . ' <br/>';
						}
						
						$buy_links_html .= '<A class="' . $classname . '-link" HREF="' . esc_url($mbdb_buylink->link) . '" TARGET="_new">';
						//if (array_key_exists('image', $retailer) && $r['image']!='') {
						if ( $retailer->has_logo_image() ) {
							
							//if (array_key_exists('imageID', $r)) {
								$imageID = $retailer->logo;
							//} else {
							//	if (array_key_exists('image_id', $r)) {
							//		$imageID = $r['image_id'];
							//	} else {
							//		$imageID = 0;
							//	}
							//}
							$alt = __('Buy Now:', 'mooberry-book-manager')  . ' ' . $retailer->name ;
							
							$buy_links_html .= '<img class="' . $classname . '-image" style="' . esc_attr($img_size) . '" src="' . esc_url($retailer->logo) . '" alt="' . $alt . '" />';
						} else {
							$buy_links_html .= '<span class="' . $classname . '-text">' . esc_html($retailer->name) . '</span>';
						}
						$buy_links_html .= '</a>';
						//$buy_links_html .= '</li>';
						//error_log('finish buy link');
					}			
				
		//$buy_links_html .= "</ul>"; 
		return apply_filters('mbdb_shortcode_buylinks', '<div class="' . $classname . '"><span class="' . $classname . '-label">' . esc_html($attr['label']) . '</span>' . $buy_links_html . '<span class="' . $classname . '-after">'.  esc_html($attr['after']) . '</span></div>');
	}

	public function mbdb_shortcode_buylinks( $attr, $content ) {
		$attr = mbdb_shortcode_atts(array('width' =>  '',
								'height' => '',
								'size' => '',
								'align' => 'vertical',
								'label' => '',
								'after' => '',
								'blank' => '',
								'book' => ''), $attr);
								
		mbdb_set_book( $attr[ 'book' ] );
		$book = $this->data_object;
		if ( !$book->has_buy_links() ) {
			return mbdb_output_blank_data( 'buy-links', $attr[ 'blank' ] );
		}
		
		return $this->output_buylinks( $book->buy_links, $attr);							
		
	}

	public function output_downloadlinks( $downloadlinks, $attr ) {
		$classname = 'mbm-book-download-links';
		
		$download_links_html = '<UL class="' . $classname . '-list" style="list-style-type:none;">';
		if ($attr['align'] =='vertical') {
			$li_style = "margin: 1em 0 1em 0;";
		} else {
			$li_style = "display:inline;margin: 0 3% 0 0;";
		}
		
		foreach ($downloadlinks as $mbdb_downloadlink) {
			
			$format = $mbdb_downloadlink->download_format;

			$download_links_html .= '<li class="' . $classname . '-listitem" style="' . $li_style . '">';
			
			
			
			// 3.5.6
				if ( $mbdb_downloadlink->uniqueID == '2' ) {
					$download_links_html .= '<span style="float:right;">' . __('Available on', 'mooberry-book-manager') . '<br/> ';
				}
				
			$download_links_html .= '<A class="' . $classname . '-link" HREF="' . esc_url($mbdb_downloadlink->link) . '">';
			
		
			
			if ( $format->has_logo_image() ) {
				$alt = __('Download Now:', 'mooberry-book-manager')  . ' ' . $format->name ;
				
				$download_links_html .= '<img class="' . $classname . '-image" src="' . esc_url($format->logo) . '"' . $alt . '/>';
			} else {
				$download_links_html .= '<p class="' . $classname . '-text">' . esc_html($format->name) . '</p>';
			}
			$download_links_html .= '</a></li>';
			
		}			
		
		$download_links_html .= "</ul>"; 
	
		return apply_filters('mbdb_shortcode_downloadlinks', '<div class="' . $classname . '"><span class="' . $classname . '-label">' .  esc_html($attr['label']) . '</span>' . $download_links_html . '<span class="' . $classname . '-after">' . esc_html($attr['after']) . '</span></div>');
	}
	
	public function mbdb_shortcode_downloadlinks( $attr, $content ) {
		$attr = mbdb_shortcode_atts(array('align' => 'vertical',
								'label' => '',
								'after' => '',
								'blank' => '',
								'book' => ''), $attr);
								
		mbdb_set_book( $attr['book'] );
		$book = $this->data_object;
		
		if ( !$book->has_download_links() ) {
			return mbdb_output_blank_data( 'download-links', $attr[ 'blank' ] );
		}
		return $this->output_downloadlinks( $book->download_links, $attr);
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
	private function series_list($delim, $series, $bookID) {
		$classname = 'mbm-book-serieslist';
		
		
		//$books = MBDB()->books->get_books_by_taxonomy( null, 'series', $series, 'series_order', 'ASC'); 
		//error_log('before book list');
		$books = new MBDB_Book_List( MBDB_Book_List_Enum::all, 'series_order', 'ASC', null, array('series' => $series) );
		//error_log('after book list');
		//$books = $list->get_books_by_taxonomy( null, 'mbdb_series', $series, 'series_order', 'ASC'); 
		
		if ($delim=='list') {
			$list = '<ul class="' . $classname . '-list">';
		} else {
			$list = '';
		}
		foreach ($books as $book) {
			if ($delim=='list') {
				$list .= '<li class="' . $classname . '-listitem">';
			}
			if ($book->id != $bookID) {
				$list .= '<A class="' . $classname . '-listitem-link" HREF="' . get_permalink($book->id) . '">';
			}
			$list .= '<span class="' . $classname . '-listitem-text">' . esc_html($book->title) . '</span>';
			if ($book->id != $bookID) {
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


	public function mbdb_shortcode_serieslist( $attr, $content ) {
		$attr = mbdb_shortcode_atts(array('blank' => '',
									'before' => __('Part of the ', 'mooberry-book-manager'),
									'after' => __(' series:', 'mooberry-book-manager'),
									'delim' => 'list',
									'book' => ''), $attr);
		//error_log('start series list');
		mbdb_set_book( $attr[ 'book' ] );
		
		if ( !$this->data_object->has_series() ) {
			return mbdb_output_blank_data( 'serieslist', $attr[ 'blank' ] );
		}
		
		$bookID = $this->data_object->id;
		$classname = 'mbm-book-serieslist';
		$series_name = '';

		foreach( $this->data_object->series as $series ) {
			//error_log('next series');
			$series_name .=  '<div class="' . $classname . '-seriesblock"><span class="' . $classname . '-before">' . esc_html($attr['before']) . '</span>';
			$series_name .= '<a class="' . $classname . '-link" href="';
			
			if ( get_option('permalink_structure') !='' ) {
				// v3.0 get permalink from options
				$permalink =  MBDB()->options->tax_grid_slugs[ 'mbdb_series' ]; //mbdb_get_tax_grid_slug( 'mbdb_series' );  
				/* $mbdb_options['mbdb_book_grid_mbdb_series_slug'];
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
			$series_name .= $this->series_list($attr['delim'],  $series->term_id, $bookID);
			$series_name .=  '</div>';
		}
		//error_log('end series list');
		return apply_filters('mbdb_shortcode_serieslist', '<div class="' . $classname . '">' . $series_name . '</div>');
	}

	public function mbdb_shortcode_series( $attr, $content ) {
		return $this->mbdb_shortcode_taxonomy( $attr, 'mbdb_series', 'series', 'series');
	}

	public function mbdb_shortcode_tags( $attr, $content ) {
		return $this->mbdb_shortcode_taxonomy( $attr, 'mbdb_tag', 'book-tag', 'tags');
	}

	public function mbdb_shortcode_illustrator( $attr, $content ) {
		return $this->mbdb_shortcode_taxonomy( $attr, 'mbdb_illustrator', 'illustrator', 'illustrators');
	}

	public function mbdb_shortcode_editor( $attr, $content ) {
		return $this->mbdb_shortcode_taxonomy( $attr, 'mbdb_editor', 'editor', 'editors');
	}

	public function mbdb_shortcode_cover_artist( $attr, $content ) {
		return $this->mbdb_shortcode_taxonomy( $attr, 'mbdb_cover_artist', 'cover-artist', 'cover_artists');
	}

	public function mbdb_shortcode_links( $attr, $content ) {
		$attr = mbdb_shortcode_atts(array('width' =>  '',
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
		
		mbdb_set_book( $attr['book'] );
		$book = $this->data_object;
		if ( !$book->has_buy_links() && !$book->has_download_links() ) {
			$classname = 'mbm-book-buy-links';
			return apply_filters('mbdb_shortcode_links', '<span class="' . $classname . '"><span class="' . $classname . '-label">' . esc_html($attr['blanklabel']) . '</span><span class="' . $classname . '-blank">' . esc_html($attr['blank']) . '</span></span>');
		}
		$output_html = '<div class="mbm-book-links">';
		if ( $book->has_buy_links() ) {
			$attr2['label'] = $attr['buylabel'];
			$output_html .= $this->output_buylinks($book->buy_links, $attr2);
		}
		
		if ( $book->has_download_links() ) {
			$attr2['label'] = $attr['downloadlabel'];
			$output_html .= $this->output_downloadlinks($book->download_links, $attr2);
		}
		$output_html .= '</div>'; 
		return apply_filters('mbdb_shortcode_links', $output_html);
	}

	public function mbdb_shortcode_editions( $attr, $content ) {
		$attr = mbdb_shortcode_atts(array(
								'label'	=>	'',
								'after'	=>	'',
								'blank'	=>	'',
								'book'	=> ''), $attr);
		mbdb_set_book( $attr[ 'book' ] );
		$book = $this->data_object;
		
		if ( !$book->has_editions() ) {
			return mbdb_output_blank_data( 'editions', $attr[ 'blank' ] );
		}
		//error_log('start editions');
		$output_html = '';
		$counter = 0;
		$default_language = MBDB()->options->default_language;
		$languages = MBDB()->options->languages;
		$currency_symbols = MBDB()->options->currency_symbols;
		foreach ($book->editions as $edition) {
			
			$is_isbn = ( $edition->isbn != '' );
			$is_height = ( $edition->height != '' );
			$is_width = ( $edition->width != '' );
			$is_pages = ( $edition->length != '' );
			$is_price = ( $edition->retail_price != '' );
			$is_language = ( $edition->language != '' );
			$is_title = ( $edition->edition_title != '' );

			$output_html .= '<span class="mbm-book-editions-format" id="mbm_book_editions_format_'  . $counter . '" name="mbm_book_editions_format[' . $counter . ']">';
			if ($is_isbn || $is_pages || ($is_height && $is_width)) {
				$output_html .= '<a class="mbm-book-editions-toggle" id="mbm_book_editions_toggle_'  . $counter . '" name="mbm_book_editions_toggle[' . $counter . ']"></a>';
			}
			$format_name = $edition->format->name;
			$output_html .= '<span class="mbm-book-editions-format-name">' . $format_name . '</span>';
			
			
			if ($is_language && $edition->language != $default_language) {
				if ( array_key_exists( $edition->language, $languages ) ) {
					$language_name = $languages[ $edition->language ];
				} else {
					$language_name = $edition->language;
				}
				$output_html .= ' <span class="mbm-book-editions-language">(' . $language_name . ')</span>';
			}
			
			if ($is_title) {
				$output_html .= ' - <span class="mbm-book-editions-title">' . $edition->edition_title . '</span>';
			}
			if ($is_price && $edition->retail_price != '0.00' && $edition->retail_price != '0,00') {
				$edition->retail_price = str_replace(',', '.', $edition->retail_price);
				$price = number_format_i18n($edition->retail_price, 2);
				// TODO get currency symbol
				
				if ( array_key_exists( $edition->currency, $currency_symbols ) ) {
					$symbol = $currency_symbols[ $edition->currency ];
				} else {
					$symbol = $edition->currency;
				}
				$output_html .= ': <span class="mbm-book-editions-srp"><span class="mbm-book-editions-price">';
				/* translators: %1$s is the currency symbol. %2$s is the price. To put currency after price, enter %2$s %1$s */
				$output_html .= sprintf( _x('%1$s %2$s', '%1$s is the currency symbol. %2$s is the price. To put currency after price, enter %2$s %1$s', 'mooberry-book-manager'), $symbol, $price);
				$output_html .= '</span></span>';
			}
			if ($is_isbn || ($is_height && $is_width) || $is_pages) {
				$output_html .= '<div name="mbm_book_editions_subinfo[' . $counter . ']" id="mbm_book_editions_subinfo_' . $counter . '" class="mbm-book-editions-subinfo">';
			
				if ($is_isbn) {
					$output_html .= '<strong>' . __('ISBN:', 'mooberry-book-manager') . '</strong> <span class="mbm-book-editions-isbn">' . $edition->isbn . '</span><br/>';
				}
				if ($is_height && $is_width) {
					$output_html .= '<strong>' . __('Size:', 'mooberry-book-manager') . '</strong> <span class="mbm-book-editions-size"><span class="mbm-book-editions-height">' . number_format_i18n($edition->width, 2) . '</span>x<span class="mbm-book-editions-width">' . number_format_i18n($edition->height, 2) . '</span> <span class="mbm-book-editions-unit">' . $edition->unit . '</span></span><br/>';
				}
				if ($is_pages) {
					$output_html .= '<strong>' . __('Pages:', 'mooberry-book-manager') . '</strong> <span class="mbm-book-editions-length">' . number_format_i18n($edition->length) . '</span>';
				}
				$output_html .= '</div>';
			}
			$output_html .= '</span>';
			$counter++;
		}
		//error_log('end editions');
		return apply_filters('mbdb_shortcode_editions', '<div class="mbm-book-editions"><span class="mbm-book-editions-label">' . esc_html($attr['label']) . '</span>' . $output_html . '<span class="mbm-book-editions-after">' . esc_html($attr['after']) . '</span></div>');
			
	}

	public function mbdb_shortcode_book( $attr, $content ) {
		
		global $post;
		
		$attr = mbdb_shortcode_atts(array(
								'book' => $post->ID), $attr);
		
		mbdb_set_book( $attr['book'] );
		
		$book_page_layout = '<div id="mbm-book-page" itemscope itemtype="http://schema.org/Book"><meta itemprop="name" content="' . esc_attr($post->post_title) . '" >';
		//error_log('subtitile');
		if ( $this->data_object->subtitle != '' ) {
			$book_page_layout .= '<h3>[book_subtitle blank=""]</h3>';
		}
		// v 3.0 for customizer
		//$book_page_layout .= '<div id="mbm-left-column">';
		//error_log('book cover');
			$book_page_layout .= '[book_cover]';
	//error_log('start links');
			$book_page_layout .= '<div id="mbm-book-links1">';
			//$is_links_data = mbdb_get_links_data();
			if ( $this->data_object->has_buy_links() ) {
				$book_page_layout .= '[book_buylinks  align="horizontal"]';
			}
	
			if ( $this->data_object->has_download_links() ) {
				$book_page_layout .= '[book_downloadlinks align="horizontal" label="' . __('Download Now:', 'mooberry-book-manager') . '"]';
			}
			
			$book_page_layout .= '</div>';	
	//error_log('end links');
			if ( !$this->data_object->is_standalone() ) {
				$book_page_layout .= '[book_serieslist before="' . __('Part of the','mooberry-book-manager') . ' " after=" ' . __('series:','mooberry-book-manager') . ' " delim="list"]';
			}
 /*	TO DO */		
 //error_log('editions');
		if ( $this->data_object->has_editions() ) {
				$book_page_layout .= '[book_editions blank="" label="' . __('Editions:', 'mooberry-book-manager') . '"]';
			}
		//error_log('goodreads');
			if ( $this->data_object->gooreads != '' ) {
				$book_page_layout .= '[book_goodreads  ]';
			}
			//error_log('summary');
		// v 3.0 for customizer
		//$book_page_layout .= '</div><div id="mbm-middle-column">';
		if ( $this->data_object->summary != '' ) {
			$book_page_layout .= '[book_summary blank=""]';
		}
		
	
		// v3.0 convert to simple true/false so that the if statement with ||
		// below actually works. Otherwise, valid data could be "0" and still
		// evaulate to false in the if statement
		// also simplifies the following if statments
		$is_published = $this->data_object->is_published();
		$is_publisher = $this->data_object->has_publisher();
		$is_genre = $this->data_object->has_genres();
		$is_tag = $this->data_object->has_tags();
		$is_editor = $this->data_object->has_editors();
		$is_illustrator = $this->data_object->has_illustrators();
		$is_cover_artist = $this->data_object->has_cover_artists();
		
		$display_details = apply_filters('mbdb_display_book_details', $is_published || $is_publisher || $is_genre || $is_tag || $is_editor || $is_illustrator || $is_cover_artist);
		//error_log('start details');
		if ($display_details ) {
			$book_page_layout .= '<div class="mbm-book-details-outer">';
							$book_page_layout .= '<div class="mbm-book-details">';
			if ($is_published ) {
				//$book_page_layout .= '<span class="mbm-book-details-published-label">' . __('Published:', 'mooberry-book-manager') . '</span> <span class="mbm-book-details-published-data">[book_published format="default" blank=""]</span><br/>';
				$book_page_layout .= '<span class="mbm-book-details-published-data">[book_published format="default" blank=""]</span><br/>';
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
		
		
		if ( $this->data_object->has_excerpt() ) {
			$book_page_layout .= '[book_excerpt label="' . __('Excerpt:', 'mooberry-book-manager') . '" length="1000"  blank="" ]';
		} 
	
		// v 3.0 for customizer
		//$book_page_layout .= '</div><div id="mbm-right-column"></div>';
	//error_log('reviews');
		if ( $this->data_object->has_reviews() ) {
			$book_page_layout .= '<span>[book_reviews  blank="" label="' . __('Reviews:', 'mooberry-book-manager') . '"]</span><br/>';
		}
		//error_log('additonal info');
		if ( $this->data_object->additional_info != '' ) {
			$book_page_layout .= '[book_additional_info]';
		}
		//error_log('links 2');
		// only show 2nd set of links if exceprt is more than 1500 characters long
		$has_links = $this->data_object->has_buy_links() || $this->data_object->has_download_links();
		if ( strlen($this->data_object->excerpt) > 1500 && $has_links ) {
			$book_page_layout .= '<div id="mbm-book-links2">';
			if ( $this->data_object->has_buy_links() ) {
				$book_page_layout .= '[book_buylinks  align="horizontal"]';
			}
	
			if ( $this->data_object->has_download_links() ) {
				$book_page_layout .= '[book_downloadlinks align="horizontal" label="' . __('Download Now:', 'mooberry-book-manager') . '"]';
			}
			$book_page_layout .= '</div>';
			//$book_page_layout .= '<div id="mbm-book-links2">[book_links buylabel="" downloadlabel="' . __('Download Now:', 'mooberry-book-manager') . '" align="horizontal"  blank="" blanklabel=""]</div>';
		}
		//error_log('end links 2');
		$book_page_layout .= '</div> <!-- mbm-book-page -->';
		

		
		$content .= stripslashes($book_page_layout); 
		$content = preg_replace('/\\n/', '<br/>', $content);
		
		$content = apply_filters('mbdb_book_content', $content);
		$content = do_shortcode($content);

		//return do_shortcode($content);
		return $content;
		
	}
	