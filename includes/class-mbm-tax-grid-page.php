<?php

class Mooberry_Book_Manager_Tax_Grid_Page { // extends Mooberry_Book_Manager_Grid_CPT {

	
	protected $taxonomy;
	protected $terms;
	protected $sort;
	
	public function __construct(  ) {
		// initialize
		//parent::__construct();	

	
		
		add_action('generate_rewrite_rules', array( $this, 'rewrite_rules' ) );
		add_filter('query_vars', array( $this, 'add_query_vars' ) );
		add_shortcode('mbdb_tax_grid', array( $this, 'tax_grid_shortcode' ) );
		add_filter('tc_breadcrumb_trail_items', array( $this, 'breadcrumb' ) , 10, 2);
		add_filter( 'wp_title_parts', array( $this, 'document_title' ) , 90, 1);
		add_filter('pre_get_document_title', array( $this, 'pre_document_title' ), 90, 1);
		// this if for themes that don't use the updated wp_title filters
		// priority 20 puts it after Yoast SEO
		add_filter( 'wp_title', array( $this, 'document_title_pre44' ) , 20, 1);
		add_filter('tc_title_text', array( $this, 'title' ) );
		add_filter('the_title', array( $this, 'title' ), 99, 2 );	
		
		add_filter('wpseo_opengraph_title', array( $this, 'override_wp_seo_meta') );
		add_filter('wpseo_opengraph_url', array( $this, 'override_wp_seo_meta') );
		add_filter('wpseo_opengraph_desc', array( $this, 'override_wp_seo_meta') );
		add_filter('wpseo_opengraph_image', array( $this, 'override_wp_seo_meta') );
		add_filter('wpseo_twitter_title', array( $this, 'override_wp_seo_meta') );
		add_filter('wpseo_twitter_card_type', array( $this, 'override_wp_seo_meta') );
		add_filter('wpseo_twitter_description', array( $this, 'override_wp_seo_meta') );
		add_filter('wpseo_twitter_image', array( $this, 'override_wp_seo_meta') );
		add_filter('wpseo_metadesc', array( $this, 'override_wp_seo_meta') );
		
		add_filter('wp_head', array($this, 'meta_tags') );
		
		add_filter( 'wp_page_menu_args', array($this, 'hide_page_from_menu'), 999, 1 );		
	}
	
	function hide_page_from_menu( $args ) {
		$page_id = MBDB()->options->tax_grid_page;
		if ( $page_id != '' )  {
			if ( !empty($args['exclude']) ) {
				$args['exclude'] .= ',';
			} 
			$args['exclude'] .= $page_id; // comma separated IDs
		}
		return $args;
	}

	
	
	// Set up redirects to series/{series-name} based on query vars
	// same for genres and tags
	// this is so the book grid can be displayed instead of 
	// using a template file that is reliant on theme
	public function rewrite_rules( $rules ) {
		global $wp_rewrite;

		$new_rules = array();
		$taxonomies = get_object_taxonomies( 'mbdb_book', 'objects' );
		$page_id = MBDB()->options->tax_grid_page;
		foreach($taxonomies as $name => $taxonomy) {
			
			$singular_name = $taxonomy->labels->singular_name;
		
			$url = MBDB()->options->get_tax_grid_slug( $taxonomy->name );
			
			//$url = MBDB()->helper_functions->get_tax_grid_slug( $taxonomy );
			
			//$new_rules['series/([^/]*)/?$'] =  'mbdb_tax_grid/?x=x&the-taxonomy=mbdb_series&the-term=$matches[1]&post_type=mbdb_tax_grid';
			//$new_rules[$url . '/([^/]*)/?$'] = 'mbdb_tax_grid/test/?x=x&the-taxonomy=' . $name . '&the-term=$matches[1]&post_type=mbdb_tax_grid';
			
			$new_rules[$url . '/([^/]*)/?$'] = 'index.php?page_id=' . $page_id . '&the-taxonomy=' . $name . '&the-term=$matches[1]';
			//$pretty_name = str_replace('mbdb_', '', $name);
			//$new_rules['book/' . $pretty_name . '/(.+)/?$'] = 'index.php?post_type=mbdb_book&' . $name . '=' . $wp_rewrite->preg_index(1) ;
			
			//$new_rules['mbdb_series/([^/]*)/?$'] =  'mbdb_tax_grid/test/?x=x&the-taxonomy=mbdb_series&the-term=$matches[1]&post_type=mbdb_tax_grid';
			//$new_rules[$name . '/([^/]*)/?$'] =  'mbdb_tax_grid/test/?x=x&the-taxonomy=' . $name . '&the-term=$matches[1]&post_type=mbdb_tax_grid';
			$new_rules[$name . '/([^/]*)/?$'] =   'index.php?page_id=' . $page_id . '&the-taxonomy=' . $name . '&the-term=$matches[1]';
		}
		
		if (count($new_rules)>0) {
			$wp_rewrite->rules = $new_rules + $wp_rewrite->rules;		
		}
	}
	
	// Add query vars to be used for the redirection for series, genres, and tags
	public function add_query_vars($query_vars) {
		$query_vars[] = "the-term"; 
		$query_vars[] = "the-taxonomy";
		return $query_vars;
	}
	
	public function tax_grid_shortcode($attr, $content) {
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
		
		// get id
		// term_obj could be null if this is a custom field
		$term_obj = get_term_by( 'slug', $term, $taxonomy);
		if ($term_obj != null) {
			$selected_ids = array((int) $term_obj->term_id);
		} else {
			$selected_ids = null;
		}
		
		
		$this->taxonomy = apply_filters( 'mbdb_tax_grid_selection', $selection, $selected_ids, $term, null );
		$this->terms = apply_filters( 'mbdb_tax_grid_selected_ids', $selected_ids, $selection, $term, null );
		$this->sort = apply_filters( 'mbdb_tax_grid_sort', null, $selection, $selected_ids, $term );
		
		//$this->data_object = new Mooberry_Book_Manager_Tax_Grid( $selection, $selected_ids, $sort );
		//$this->set_tax_grid_object( $selection, $selected_ids, $sort );
	//	$this->set_data_object();
		//$books = $this->data_object->book_list;
		
		$this->data_object = MBDB()->grid_factory->create_grid( array(
										'taxonomy'	=>	$this->taxonomy,
										'terms'		=>	$this->terms,
										'sort'	=>	$this->sort));
										
		$grid_output = $this->data_object->display_grid(  );
		
		// term meta
		if ( $term_obj != null ) {
			if ( function_exists( 'get_term_meta' ) ) {
				$before = get_term_meta( (int) $term_obj->term_id, 'mbdb_tax_grid_description', true );
				if ( $before != '' ) {
					$grid_output = '<p>' . $this->get_wysiwyg_output(($before)) . '</p>' . $grid_output;
				}
				$after = get_term_meta( (int) $term_obj->term_id, 'mbdb_tax_grid_description_bottom', true );
				if ( $after != '' ) {
					$grid_output = $grid_output . '<p>' . $this->get_wysiwyg_output(($after)) . '</p>';
				}
			}
		}			
		return $grid_output;
	}

	// edit the breadcrumb for the Customizr theme if this is a tax_grid (series, tag, genre)
	// tc_breadcrumb_trail_items should be unique enough to the Customizr theme
	// that it doesn't affect anything else?
	public function breadcrumb( $trail, $args) {
		global $post;
		$page_id = MBDB()->options->tax_grid_page;
		if ( $post->ID == $page_id ) {
		//if (  get_post_type() == 'mbdb_tax_grid' ) {
			$lastitem = count($trail) -1;
			$trail[$lastitem] = $this->get_tax_title($trail[$lastitem]);
		}
		return $trail;
	}

	// this is for themes that use the updated wp_title filters
	public function document_title( $title ) {
		$title[0] =  $this->document_title_pre44( $title[0] );
		return $title;
		
	}
	
	public function pre_document_title( $title ) {
		return $this->document_title_pre44( $title );
	}

	
	public function document_title_pre44( $title) {
		
		global $post;
		//print_r($post->ID);
		//if (get_post_type() == 'mbdb_tax_grid') {
		$page_id = MBDB()->options->tax_grid_page;
		
		if ( $post && $post->ID == $page_id ) {
			$title =  $this->get_tax_title( $title);
		}
			return $title;
		
	}

	// set the title in the book grid to the appropriate tag, genre, or series
	// if query vars have been passed to handle the special case of showing
	// just one tag, genre, or series
	//add_filter('tc_the_title', 'mbdb_tax_grid_title');
	public function title( $content, $id = null ) {
		
		
		$page_id = MBDB()->options->tax_grid_page;
		if ( is_page( $page_id) && $id == $page_id ) {
		//if ( is_main_query() && in_the_loop() && $post->ID == $page_id ) { //get_post_type() == 'mbdb_tax_grid' ) {
			$content = apply_filters('mbdb_tax_grid_title', $this->get_tax_title($content));
		}
		return $content;
	} 

	public function get_tax_title( $content ) {
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
	

	public function override_wp_seo_meta( $tag ) {
		$page_id = MBDB()->options->tax_grid_page;
		global $post;
		if ( is_page( $page_id) && $post->ID == $page_id ) {
					return '';
		}
			return $tag;
	}

	public function meta_tags() {
		
		global $post;
		global $wp_query;
		$page_id = MBDB()->options->tax_grid_page;
		if ( is_page( $page_id) && $post->ID == $page_id ) {
			$title = $this->get_tax_title( '' );
			$url = '';
			$mbdb_term = '';
			if ( isset( $wp_query->query_vars['the-taxonomy'] ) ) {
					$mbdb_taxonomy = trim( urldecode( $wp_query->query_vars['the-taxonomy'] ), '/');
					$term = get_term_by('slug', $mbdb_term, $mbdb_taxonomy);			
					$taxonomy = get_taxonomy($mbdb_taxonomy);
					$url = MBDB()->options->get_tax_grid_slug( $taxonomy->name );
			}
			if ( isset( $wp_query->query_vars['the-term'] ) ) {
				$mbdb_term =  urldecode( $wp_query->query_vars['the-term'] );
			}			
			$site_name = get_bloginfo('name');
			
?>
			<meta name="description" content="<?php echo esc_attr($title); ?>" />
			<meta property="og:title" content="<?php echo esc_attr($title . ' | ' . $site_name); ?>" />
			<meta property="og:url" content="<?php echo esc_attr(home_url( $url . '/' . $mbdb_term) ); ?>" />
			<meta property="og:description" content="<?php echo esc_attr($title); ?>" />
			
			<meta name="twitter:card" content="summary">	
			<meta name="twitter:description" content="<?php echo esc_attr($title); ?>" />
			<meta name="twitter:title" content="<?php echo esc_attr($title. ' | ' . $site_name); ?>" />
			<?php			
		}
	}
	
	protected function get_wysiwyg_output( $content ) {
		global $wp_embed;

		$content = $wp_embed->autoembed( $content );
		$content = $wp_embed->run_shortcode( $content );
		$content = wpautop( $content );
		$content = do_shortcode( $content );


		return $content;
	}



	
}

