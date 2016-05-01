<?php
/**
 *  This file is the base template class for all MBDB Book Widgets
 *  
 *  Added v3.1.x
 *  
 */
 
 
abstract class mbdb_widget extends WP_Widget {
	
	protected $coverSize;
	protected $displayBookTitle;
	protected $widgetTitle;
	protected $bookID;

	

	// constructor

	function __construct( $title, $widget_ops) {
		
		parent::__construct($widget_ops['classname'], $title, $widget_ops  );

	}

	// widget form creation
	final function form($instance) {	
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
		
		// display the form
		do_action('mbdb_widget_pre_form', $instance, $this);
		
		include( plugin_dir_path(__FILE__)  . '/views/admin-widget.php');
		
		do_action('mbdb_book_post_form', $instance, $this); 
		
		$this->displayAdditionalFields( $instance );
	}

	
	// widget update
	final function update($new_instance, $old_instance) {
		$instance = $old_instance;
		do_action('mbdb_widget_pre_update', $new_instance, $old_instance);
		
		$instance['mbdb_widget_title'] = strip_tags($new_instance['mbdb_widget_title']);
		$instance['mbdb_widget_show_title'] = strip_tags($new_instance['mbdb_widget_show_title']);
		
		if ($new_instance['mbdb_widget_cover_size'] < 50) {
			$new_instance['mbdb_widget_cover_size'] = 50;
		}
		$instance['mbdb_widget_cover_size'] = strip_tags($new_instance['mbdb_widget_cover_size']);
		
		$instance = $this->updateAdditionalFields( $instance, $new_instance );
		
		do_action('mbdb_widget_post_update', $new_instance, $instance);
		
		return apply_filters('mbdb_widget_update', $instance, $new_instance);
	}
	

	// widget display
	final function widget($args, $instance) {
		extract($args);
		
		if ( !$this->displayWidget() ) {
			return;
		}
		
		$this->widgetTitle = apply_filters('mbdb_widget_title', $instance['mbdb_widget_title']);
		$this->displayBookTitle = apply_filters('mbdb_widget_show_title', $instance['mbdb_widget_show_title']);
		$this->coverSize = apply_filters('mbdb_widget_cover_size', $instance['mbdb_widget_cover_size']);
		
		$this->getAdditionalFields( $instance );
	
		$book = null;
		do_action('mbdb_widget_pre_get_books', $instance);
		
		$book = $this->selectBook($instance);
		
		$book = apply_filters('mbdb_widget_book', $book, $instance);
		
		do_action('mbdb_widget_post_get_books', $instance, $book);
		
		if ( $book == null && !$this->displayNoBook ) {
			return;
		}
		
		do_action('mbdb_widget_pre_display');

		//output
		echo $before_widget;
		
		echo $before_title . esc_html($this->widgetTitle) . $after_title;
				
		$this->outputBook( $book, $instance );
		
		$this->outputAdditionalFields( $book, $instance );
		 
		echo $after_widget;
		
		do_action('mbdb_widget_post_display');
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
