<?php
/**
 *  This file is the base template class for all MBDB Book Widgets
 *  
 *  Added v3.1.x
 *  
 */
 
 
abstract class mbdb_widget extends WP_Widget {
	
	public $coverSize;
	public $displayBookTitle;
	public $widgetTitle;
	public $bookID;
	public $book;
	
	
	public $title;
	public $widget_ops;

	//constructor
	function __construct( ) {
		parent::__construct( $this->widget_ops['classname'], $this->title, $this->widget_ops  );
	}

	// widget form creation
	function form($instance) {	
//	error_log('mbdb widget form');
		
		$defaults = array( 'mbdb_widget_title' => '',
							'mbdb_widget_show_title'	=> 'yes',
							'mbdb_widget_cover_size' => 100,
					);
		$instance = wp_parse_args((array) $instance, $defaults);
	//	error_log( print_r($instance, true));
		// Check values
		do_action('mbdb_widget_pre_set_defaults'); 
		 
		 do_action('mbdb_widget_post_set_defaults'); 
		 
			 do_action('mbdb_widget_pre_get_data', $instance);
			 
				$this->widgetTitle = esc_attr( $instance['mbdb_widget_title'] );
				$this->displayBookTitle = esc_attr($instance['mbdb_widget_show_title']);
				$this->coverSize = esc_attr($instance['mbdb_widget_cover_size']);
			 do_action('mbdb_widget_post_get_data', $instance);
		
		
		// display the form
		do_action('mbdb_widget_pre_form', $instance, $this);
		
		include( plugin_dir_path(__FILE__)  . '/views/admin-widget.php');
		
		do_action('mbdb_book_post_form', $instance, $this); 
		return $instance;
	}

	
	// widget update
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		do_action('mbdb_widget_pre_update', $new_instance, $old_instance);
	
		$instance['mbdb_widget_title'] = strip_tags($new_instance['mbdb_widget_title']);
		
		if (!array_key_exists('mbdb_widget_show_title', $new_instance)) {
			$instance['mbdb_widget_show_title'] = 'no';
		} else {
			$instance['mbdb_widget_show_title'] = 'yes';
		}
		
		if (!array_key_exists('mbdb_widget_cover_size', $new_instance) || $new_instance['mbdb_widget_cover_size'] < 50) {
			$new_instance['mbdb_widget_cover_size'] = 50;
		}
		$instance['mbdb_widget_cover_size'] = strip_tags($new_instance['mbdb_widget_cover_size']);
		
		do_action('mbdb_widget_post_update', $new_instance, $instance);
		
		return apply_filters('mbdb_widget_update', $instance, $new_instance);
	}
	

	// widget display
	function widget($args, $instance) {
	
		
		if ( !$this->displayWidget( $instance ) ) {
			return;
		}
	
		$this->getData($instance);
		
		if ( $this->book == null && !$this->displayNoBook() ) {
			return;
		}
		
		$this->outputWidgetStart( $args, $instance );
		
		$this->outputBook( $instance );
		
		$this->outputWidgetEnd( $args, $instance );
	}
	
	function getData($instance) {
		
		$this->widgetTitle = apply_filters('mbdb_widget_title', $instance['mbdb_widget_title']);
		$this->displayBookTitle = apply_filters('mbdb_widget_show_title', $instance['mbdb_widget_show_title']);
		$this->coverSize = apply_filters('mbdb_widget_cover_size', $instance['mbdb_widget_cover_size']);
		
		$this->book = null;
		do_action('mbdb_widget_pre_get_books', $instance);
	
		$this->book = $this->selectBook($instance);
		
		$this->book = apply_filters('mbdb_widget_book', $this->book, $instance);
		
		do_action('mbdb_widget_post_get_books', $instance, $this->book);
		
		
		
		
		
	}
		
		
	function outputWidgetStart( $args, $instance) {
		
		do_action('mbdb_widget_pre_display', $instance);

		//output
		
		extract($args);

		echo $before_widget;
		
		echo $before_title . esc_html($this->widgetTitle) . $after_title;
				
	}
	
	function outputWidgetEnd( $args, $instance ) {
			
		extract($args);	 
		echo $after_widget;
		
		do_action('mbdb_widget_post_display');
	}
	
	protected function outputBook ( $instance ) {
		//output
		if ($this->book == null) {
			$this->bookID = 0;
			$bookTitle = '';
		} else {
			$this->bookID = $this->book->book_id;
			$bookTitle = get_the_title( $this->bookID );
		}
		
		$bookTitle = apply_filters('mbdb_widget_book_title', $bookTitle);
		
		do_action('mbdb_widget_pre_book_display');
		
		if ($this->bookID == 0) {
			echo apply_filters('mbdb_widget_no_books_found', '<em>' . __('No books found', 'mooberry-book-manager') . '</em>');
		} else {
			$book_link = get_permalink( $this->bookID );
			
			$this->outputCover( $this->book, $book_link, $bookTitle, $instance );
			
			$this->outputTitle( $this->book, $bookTitle, $book_link, $instance );
						
		}
		//echo apply_filters('mbdb_widget_post_book_output', '', $this->book, $instance);
		do_action('mbdb_widget_post_book_display', $this->book, $instance);

	}
	
	protected function outputCover ($book, $book_link, $bookTitle, $instance) {
	
		$image_id = $book->cover_id;
		
		if ( $book_link != '' ) {
			do_action('mbdb_widget_pre_cover_link', $book, $book_link);
			echo '<A class="mbm-widget-link" HREF="' . esc_url($book_link) . '"> ';
		}
		
		// 3.4.1 -- uses get_attachemnt_image_src
		$image_src = '';
		$attachment_src = wp_get_attachment_image_src ( $image_id, 'medium' );
		if ( $attachment_src !== false) {
			$image_src = $attachment_src[0];
		}
	
		$image_src = mbdb_get_cover( $image_src, 'widget' );
		
		if ($image_src && $image_src !== '') {
			do_action('mbdb_widget_pre_image', $image_src);
			$alt = mbdb_get_alt_text( $image_id, __('Book Cover:', 'mooberry-book-manager') . ' ' . $bookTitle);
			echo '<div style="' . apply_filters('mbdb_book_widget_cover_span_style', 'padding:0;margin:0;', $instance) . '"><img class="mbm-widget-cover" style="' . apply_filters('mbdb_book_widget_cover_style', 'width:' . esc_attr($this->coverSize) . 'px;margin:10px 0;') . '" src="' . esc_url($image_src) . '" ' . $alt . '  /> </div>';
			do_action('mbdb_widget_post_image', $image_src);
		}
		if ($book_link != '') {
				echo '</A>';
				do_action('mbdb_widget_post_cover_link');
		}
	
	}
	
	protected function outputTitle ( $book, $bookTitle, $book_link, $instance) {
		if ($this->displayBookTitle == 'yes') { 
		
			if ( $book_link != '' ) {
				do_action('mbdb_widget_pre_title_link', $book, $book_link);
				echo '<A class="mbm-widget-link" HREF="' . esc_url($book_link) . '"> ';
			}
			if ( $bookTitle != '' ) {
				do_action('mbdb_widget_pre_book_title', $bookTitle);
				echo '<P class="mbm-widget-title" style="' . apply_filters('mbdb_book_widget_title_style', '', $instance) . '">' . esc_html($bookTitle) . '</P>';
				do_action('mbdb_widget_post_book_title', $bookTitle);
			}
			if ($book_link != '') {
				echo '</A>';
				do_action('mbdb_widget_post_title_link', $book, $book_link);
			}
		}
	}
	
	// must be implemented by child classes
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
