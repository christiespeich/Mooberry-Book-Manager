<?php
/**
 *  This file does the book widget  
 *  
 */
 
 
class mbdb_widget extends WP_Widget {
	
	protected $coverSize;
	protected $displayBookTitle;
	protected $widgetTitle;
	protected $bookID;

	

	// constructor
	// 3.1 -- added customize_selected_refresh for WP 4.5
	function __construct( $title, $widget_ops) {
		/*$widget_ops = array('classname' => 'mbdb_book_widget2', 
							'description' => __('Shows the cover of the book of your choosing with a link to the book page', 'mooberry-book-manager'),
							 'customize_selective_refresh' => true,);
		*/
		parent::__construct($widget_ops['classname'], $title, $widget_ops  );

	}

	// widget form creation
	function form($instance) {	
		$instance = wp_parse_args((array) $instance);
		// Check values
		if( $instance) {
			 do_action('mbdb_widget_pre_get_data', $instance);
			 
			 $this->setWidgetTitle( esc_attr( $instance['mbdb_widget_title'] )  );
			 $this->setDisplayBookTitle( esc_attr($instance['mbdb_widget_show_title']) );
			 $this->setCoverSize( esc_attr($instance['mbdb_widget_cover_size']) );
			 $this->setAdditionalFields( $instance );
			 
			 do_action('mbdb_widget_post_get_data', $instance);
		} else {
			 do_action('mbdb_widget_pre_set_defaults'); 
			 
			 $this->setWidgetTitle( '' );
			 $this->setDisplayBookTitle( 'yes' );
			 $this->setcoverSize( 100);
			 $this->setAdditionalFields ( null );
			 
			 do_action('mbdb_widget_post_set_defaults'); 
		}
		
		/*$options = apply_filters('mbdb_book_widget_options', array('random' => __('Random Book', 'mooberry-book-manager'),
							'newest'	=>	__('Newest Book', 'mooberry-book-manager'),
							'coming-soon'	=>	__('Future Book', 'mooberry-book-manager'),
							'specific'	=>	__('Specific Book', 'mooberry-book-manager')
						)
					);
							
		$widget_type_dropdown = mbdb_dropdown($this->get_field_id('mbdb_widget_type'), $options, $mbdb_widget_type, 'no', 0, $this->get_field_name('mbdb_widget_type'));
		*/
		include( plugin_dir_path(__FILE__)  . '/views/admin-widget.php');
	}

	// widget update
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		do_action('mbdb_widget_pre_update', $new_instance, $old_instance);
		
		$instance['mbdb_widget_title'] = strip_tags($new_instance['mbdb_widget_title']);
		
		//$instance['mbdb_bookID'] = strip_tags($new_instance['mbdb_bookID']);
		
		$instance['mbdb_widget_show_title'] = strip_tags($new_instance['mbdb_widget_show_title']);
		
		//$instance['mbdb_widget_type'] = strip_tags($new_instance['mbdb_widget_type']);
		
		
		
		if ($new_instance['mbdb_widget_cover_size'] < 50) {
			$new_instance['mbdb_widget_cover_size'] = 50;
		}
		
		$instance['mbdb_widget_cover_size'] = strip_tags($new_instance['mbdb_widget_cover_size']);
		
		$instance = $this->updateAdditionalFields( $instance, $new_instance );
		
		do_action('mbdb_widget_post_update', $new_instance, $instance);
		
		return apply_filters('mbdb_widget_update', $instance, $new_instance);
	}

	// widget display
	function widget($args, $instance) {
		extract($args);
		
		//$this->bookID  = $instance['mbdb_bookID'];
		
		//$mbdb_widget_type = apply_filters('mbdb_widget_type', $instance['mbdb_widget_type']);
		
		$this->widgetTitle = apply_filters('mbdb_widget_title', $instance['mbdb_widget_title']);
		$this->displayBookTitle = apply_filters('mbdb_widget_show_title', $instance['mbdb_widget_show_title']);
		$this->coverSize = apply_filters('mbdb_widget_cover_size', $instance['mbdb_widget_cover_size']);
		
		$this->setAdditionalFields( $instance );
		
	
		$book = null;
		do_action('mbdb_widget_pre_get_books', $instance);
		
/*
		switch ($mbdb_widget_type) {
			case 'random':
				// get book ID of a random book
				$book = apply_filters('mbdb_widget_random_book_list', MBDB()->books->get_random_book(), $instance);
				break;
		
			case "newest":
				// get book ID of most recent book
				
				$book = apply_filters('mbdb_widget_newest_book_list', MBDB()->books->get_most_recent_book(), $instance);
				break;
			
			case "coming-soon":
				// get books with future or blank release dates
				$book = apply_filters('mbdb_widget_coming_soon_book_list', MBDB()->books->get_upcoming_book(), $instance); 
				break;
			
			case "specific":
				// make sure seected book is still a valid book
				$book = apply_filters('mbdb_widget_specific_book_list', MBDB()->books->get($mbdb_bookID), $instance); 				
				break;
		}
		*/
		
		$book = $this->selectBook($instance);
		
		$book = apply_filters('mbdb_widget_book', $book, $instance);
		
		do_action('mbdb_widget_post_get_books', $instance, $book);
		
		do_action('mbdb_widget_pre_display');
		//output
		echo $before_widget;
		
		echo $before_title . esc_html($this->widgetTitle) . $after_title;
				
		$this->outputBook( $book, $instance );
		 
		echo $after_widget;
		
		do_action('mbdb_widget_post_display');
	}
	
	
	function getCoverSize( ) {
		return  $this->coverSize;
	}
	
	function setCoverSize( $value ) {
		$this->coverSize = $value;
	}
	
	function getCover( $image_src ) {
		if (!$image_src || $image_src == '') { 
			// v3.0 check for placeholder image setting
			$mbdb_options = get_option('mbdb_options');
			if (array_key_exists('show_placeholder_cover', $mbdb_options)) {
				$show_placeholder_cover = $mbdb_options['show_placeholder_cover']; //mbdb_get_option('show_placeholder_cover');
			} else {
				$show_placeholder_cover = array();
			}
			if (is_array($show_placeholder_cover)) {
				if (in_array('widget', $show_placeholder_cover)) {
					if (array_key_exists('coming-soon', $mbdb_options)) {
						$image_src = $mbdb_options['coming-soon']; //mbdb_get_option('coming-soon');
					} else {
						$image_src = '';
					}
				}
			}
		}
		return $image_src;	
	}
	

	
	function getDisplayBookTitle() {
		return $this->displayBookTitle;
	}
	
	function setDisplayBookTitle( $value ) {
		$this->displayBookTitle = $value;
	}
	
	function getWidgetTitle() {
		return $this->widgetTitle;
	}
	
	function setWidgetTitle( $value ) {
		$this->widgetTitle = $value;
	}
	
	function getBookID() {
		return $this->bookID;
	}
	
	function setBookID( $value ) {
		$this->bookID = $value;
	}
	
	
	function setAdditionalFields( $instance ) {
		
	}

	function updateAdditionalFields( $instance, $new_instance ) {
		return $instance;
	}
	
	function outputAdditionalFields ( $book, $bookTitle, $book_link, $instance ) {
	}
	
	
	function getBookSelection() {
		
	}
	
	function selectBook( $instance ) {
		return null;
	}
	
	function outputBook ( $book, $instance ) {
		//output
		if ($book == null) {
			$this->bookID = 0;
			$bookTitle = '';
		} else {
			$this->bookID = $book->ID;
			$bookTitle = get_the_title( $this->bookID );
		}
		
		$bookTitle = apply_filters('mbdb_widget_book_title', $bookTitle);
		
		do_action('mbdb_widget_pre_book_display');
		
		if ($this->bookID == 0) {
			echo apply_filters('mbdb_widget_no_books_found', '<em>' . __('No books found', 'mooberry-book-manager') . '</em>');
		} else {
			$book_link = get_permalink( $this->bookID );
			
			$this->displayCover( $book, $book_link, $bookTitle );
			
			$this->displayTitle( $book, $bookTitle, $book_link);
						
			$this->outputAdditionalFields( $book, $bookTitle, $book_link, $instance );
		}
		//echo '</div>' 
		do_action('mbdb_widget_post_book_display');
	}
	
	function displayCover ($book, $book_link, $bookTitle) {
		$image_src = $book->cover;
		$image_id = $book->cover_id;
		//$image_src = get_post_meta( $mbdb_bookID, '_mbdb_cover', true );
		
	
		
		if ( $book_link != '' ) {
			do_action('mbdb_widget_pre_cover_link', $book, $book_link);
			echo '<A class="mbm-widget-link" HREF="' . esc_url($book_link) . '"> ';
		}
		$image_src = $this->getCover( $image_src );
		
		if ($image_src && $image_src !== '') {
			do_action('mbdb_widget_pre_image', $image_src);
			$alt = mbdb_get_alt_text( $image_id, __('Book Cover:', 'mooberry-book-manager') . ' ' . $bookTitle);
			echo '<div style="' . apply_filters('mbdb_book_widget_cover_span_style', 'padding:0;margin:0;') . '"><img class="mbm-widget-cover" style="' . apply_filters('mbdb_book_widget_cover_style', 'width:' . esc_attr($this->coverSize) . 'px;padding-top:10px;') . '" src="' . esc_url($image_src) . '" ' . $alt . '  /> </div>';
			do_action('mbdb_widget_post_image', $image_src);
		}
		if ($book_link != '') {
				echo '</A>';
				do_action('mbdb_widget_post_cover_link');
		}
	
	}
	
function displayTitle ( $book, $bookTitle, $book_link) {
	if ($this->displayBookTitle == 'yes') { 
	
		if ( $book_link != '' ) {
			do_action('mbdb_widget_pre_title_link', $book, $book_link);
			echo '<A class="mbm-widget-link" HREF="' . esc_url($book_link) . '"> ';
		}
		if ( $bookTitle != '' ) {
			do_action('mbdb_widget_pre_book_title', $bookTitle);
			echo '<P><div class="mbm-widget-title" style="' . apply_filters('mbdmb_book_widget_title_style', '') . '">' . esc_html($bookTitle) . '</div></P>';
			do_action('mbdb_widget_post_book_title', $bookTitle);
		}
		if ($book_link != '') {
			echo '</A>';
			do_action('mbdb_widget_post_title_link', $book, $book_link);
		}
	}
}

function displayAdditionalFields( $instance ) {
}
}
