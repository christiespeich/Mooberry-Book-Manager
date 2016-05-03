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
			
			 $this->widgetTitle = esc_attr( $instance['mbdb_widget_title'] );
			 $this->displayBookTitle = esc_attr($instance['mbdb_widget_show_title']);
			 $this->coverSize = esc_attr($instance['mbdb_widget_cover_size']);
			 $this->setAdditionalFields( $instance );
			 
			 do_action('mbdb_widget_post_get_data', $instance);
		} else {
			 do_action('mbdb_widget_pre_set_defaults'); 
			 
			 $this->widgetTitle = '';
			 $this->displayBookTitle = 'yes';
			 $this->coverSize = 100;
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
		
		if (!array_key_exists('mbdb_widget_show_title', $new_instance)) {
			$instance['mbdb_widget_show_title'] = 'no';
		} else {
			$instance['mbdb_widget_show_title'] = 'yes';
		}
		
		if ($new_instance['mbdb_widget_cover_size'] < 50) {
			$new_instance['mbdb_widget_cover_size'] = 50;
		}
		$instance['mbdb_widget_cover_size'] = strip_tags($new_instance['mbdb_widget_cover_size']);
		/*
		$this->coverSize = $instance['mbdb_widget_cover_size'];
		$this->displayBookTitle = $instance['mbdb_widget_show_title'];
		$this->widgetTitle = $instance['mbdb_widget_title'];
		*/
		$instance = $this->updateAdditionalFields( $instance, $new_instance );
		
		do_action('mbdb_widget_post_update', $new_instance, $instance);
		
		return apply_filters('mbdb_widget_update', $instance, $new_instance);
	}
	

	// widget display
	final function widget($args, $instance) {
		extract($args);
		
		if ( !$this->displayWidget( $instance ) ) {
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
		
		if ( $book == null && !$this->displayNoBook() ) {
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
	
	protected final function outputBook ( $book, $instance ) {
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
			
			$this->outputCover( $book, $book_link, $bookTitle );
			
			$this->outputTitle( $book, $bookTitle, $book_link);
						
		}
		do_action('mbdb_widget_post_book_display');
	}
	
	protected final function outputCover ($book, $book_link, $bookTitle) {
		$image_src = $book->cover;
		$image_id = $book->cover_id;
		
		if ( $book_link != '' ) {
			do_action('mbdb_widget_pre_cover_link', $book, $book_link);
			echo '<A class="mbm-widget-link" HREF="' . esc_url($book_link) . '"> ';
		}
		
		$image_src = mbdb_get_cover( $image_src, 'widget' );
		
		if ($image_src && $image_src !== '') {
			do_action('mbdb_widget_pre_image', $image_src);
			$alt = mbdb_get_alt_text( $image_id, __('Book Cover:', 'mooberry-book-manager') . ' ' . $bookTitle);
			echo '<div style="' . apply_filters('mbdb_book_widget_cover_span_style', 'padding:0;margin:0;') . '"><img class="mbm-widget-cover" style="' . apply_filters('mbdb_book_widget_cover_style', 'width:' . esc_attr($this->coverSize) . 'px;margin-top:10px;') . '" src="' . esc_url($image_src) . '" ' . $alt . '  /> </div>';
			do_action('mbdb_widget_post_image', $image_src);
		}
		if ($book_link != '') {
				echo '</A>';
				do_action('mbdb_widget_post_cover_link');
		}
	
	}
	
	protected final function outputTitle ( $book, $bookTitle, $book_link) {
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
	
	// empty fuction means optional for subclasses to implement
	// this gets additional fields for outputting
	protected function getAdditionalFields( $instance ) {
	}
	
	// empty fuction means optional for subclasses to implement
	// this sets additional fields for form
	protected function setAdditionalFields( $instance ) {	
	}

	// empty fuction means optional for subclasses to implement
	// this updates additional fields when form is saved
	protected function updateAdditionalFields( $instance, $new_instance ) {
		return $instance;
	}
	
	// empty fuction means optional for subclasses to implement
	// this outputs additional fields for widget display
	protected function outputAdditionalFields ( $book, $instance ) {
	}
	
	// empty fuction means optional for subclasses to implement
	// this displays additional fields on widget form
	protected function displayAdditionalFields( $instance ) {
	}
	
	// abstract means subclasses must implement
	// expects a book object or null if no book
	protected abstract function selectBook( $instance );
		
	// implemented non-final function means subclasses can optionally override
	// always display the widget by default
	protected function displayWidget( $instance ) {
		return true;
	}
	
	// implemented non-final function means subclasses can optionally override
	// always display "no book" message by default
	protected function displayNoBook() {
		return true;
	}
}
