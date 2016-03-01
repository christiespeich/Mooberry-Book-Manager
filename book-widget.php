<?php
/**
 *  This file does the book widget  
 *  
 */
 
 
class mbdb_book_widget extends WP_Widget {

	// constructor
	function __construct() {
		$widget_ops = array('classname' => 'mbdb_book_widget', 'description' => __('Shows the cover of the book of your choosing with a link to the book page', 'mooberry-book-manager'));
		
		parent::__construct('mbdb_book_widget', 'Mooberry Book Manager '. _x('Book', 'noun', 'mooberry-book-manager'), $widget_ops  );

	}

	// widget form creation
	function form($instance) {	
		$instance = wp_parse_args((array) $instance);
		// Check values
		if( $instance) {
			 do_action('mbdb_widget_pre_get_data', $instance);
			 $mbdb_widget_title = esc_attr($instance['mbdb_widget_title']);
			 $mbdb_widget_show_title = esc_attr($instance['mbdb_widget_show_title']);
			 $mbdb_widget_type = esc_attr($instance['mbdb_widget_type']);
			 $mbdb_bookID = esc_attr($instance['mbdb_bookID']);
			 $mbdb_widget_cover_size = esc_attr($instance['mbdb_widget_cover_size']);
			 do_action('mbdb_widget_post_get_data', $instance);
		} else {
			 do_action('mbdb_widget_pre_set_defaults'); 
			 $mbdb_widget_title = '';
			 $mbdb_bookID=0;		
			 $mbdb_widget_show_title = 'yes';
			 $mbdb_widget_type = 'random';
			 $mbdb_widget_cover_size = 100;
			 $mbdb_book_title = '';
			 do_action('mbdb_widget_post_set_defaults'); 
		}
		
		$options = apply_filters('mbdb_book_widget_options', array('random' => __('Random Book', 'mooberry-book-manager'),
							'newest'	=>	__('Newest Book', 'mooberry-book-manager'),
							'coming-soon'	=>	__('Future Book', 'mooberry-book-manager'),
							'specific'	=>	__('Specific Book', 'mooberry-book-manager')
						)
					);
							
		$widget_type_dropdown = mbdb_dropdown($this->get_field_id('mbdb_widget_type'), $options, $mbdb_widget_type, 'no', 0, $this->get_field_name('mbdb_widget_type'));
		include( plugin_dir_path(__FILE__)  . '/views/admin-book-widget.php');
	}

	// widget update
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		do_action('mbdb_widget_pre_update', $new_instance, $old_instance);
		
		$instance['mbdb_widget_title'] = strip_tags($new_instance['mbdb_widget_title']);
		
		$instance['mbdb_bookID'] = strip_tags($new_instance['mbdb_bookID']);
		
		$instance['mbdb_widget_show_title'] = strip_tags($new_instance['mbdb_widget_show_title']);
		
		$instance['mbdb_widget_type'] = strip_tags($new_instance['mbdb_widget_type']);
		
		if ($new_instance['mbdb_widget_cover_size'] < 50) {
			$new_instance['mbdb_widget_cover_size'] = 50;
		}
		
		$instance['mbdb_widget_cover_size'] = strip_tags($new_instance['mbdb_widget_cover_size']);
		
		do_action('mbdb_widget_post_update', $new_instance, $instance);
		
		return apply_filters('mbdb_widget_update', $instance, $new_instance);
	}

	// widget display
	function widget($args, $instance) {
		extract($args);
		
		$mbdb_bookID  = $instance['mbdb_bookID'];
		
		$mbdb_widget_type = apply_filters('mbdb_widget_type', $instance['mbdb_widget_type']);
		
	
		$book = null;
		do_action('mbdb_widget_pre_get_books', $instance);
			
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
		$book = apply_filters('mbdb_widget_book', $book, $instance);
		
		do_action('mbdb_widget_post_get_books', $instance, $book);
		
		//output
		if ($book == null) {
			$mbdb_bookID = 0;
			$mbdb_book_title = '';
		} else {
			$mbdb_bookID = $book->book_id;
			$mbdb_book_title = get_the_title($mbdb_bookID);
		}
		
		$mbdb_widget_title = apply_filters('mbdb_widget_title', $instance['mbdb_widget_title']);
		$mbdb_widget_show_title = apply_filters('mbdb_widget_show_title', $instance['mbdb_widget_show_title']);
		$mbdb_cover_size = apply_filters('mbdb_widget_cover_size', $instance['mbdb_widget_cover_size']);
		$mbdb_bookID = apply_filters('mbdb_widget_bookID', $mbdb_bookID);
		$mbdb_book_title = apply_filters('mbdb_widget_book_title', $mbdb_book_title);
		
		do_action('mbdb_widget_pre_display');
		echo $before_widget;
		
		echo $before_title . esc_html($mbdb_widget_title) . $after_title;
		
		if ($mbdb_bookID == 0) {
			echo apply_filters('mbdb_widget_no_books_found', '<em>' . __('No books found', 'mooberry-book-manager') . '</em>');
		} else {
			$image_src = $book->cover;
			$image_id = $book->cover_id;
			//$image_src = get_post_meta( $mbdb_bookID, '_mbdb_cover', true );
			
			$book_link = get_permalink( $mbdb_bookID );
			
			if ( $book_link != '' ) {
				do_action('mbdb_widget_pre_link', $book_link);
				echo '<A class="mbm-widget-link" HREF="' . esc_url($book_link) . '"> ';
			}
			if (!$image_src || $image_src == '') { 
				// v3.0 check for placeholder image setting
				$show_placeholder_cover = mbdb_get_option('show_placeholder_cover');
				if (is_array($show_placeholder_cover)) {
					if (in_array('widget', $show_placeholder_cover)) {
						$image_src = mbdb_get_option('coming-soon');
					}
				}
			}
			if ($image_src && $image_src !== '') {
				do_action('mbdb_widget_pre_image', $image_src);
				$alt = mbdb_get_alt_text( $image_id, __('Book Cover:', 'mooberry-book-manager') . ' ' . $mbdb_book_title);
				echo '<img class="mbm-widget-cover" style="width:' . esc_attr($mbdb_cover_size) . 'px;padding-top:10px;" src="' . esc_url($image_src) . '" ' . $alt . '  /> ';
				do_action('mbdb_widget_post_image', $image_src);
			}
			if ($mbdb_widget_show_title == 'yes') { 
				if ( $mbdb_book_title != '' ) {
					do_action('mbdb_widget_pre_book_title', $mbdb_book_title);
					echo '<P><span class="mbm-widget-title">' . esc_html($mbdb_book_title) . '</span></P>';
					do_action('mbdb_widget_post_book_title', $mbdb_book_title);
				}
			}
			if ($book_link != '') {
				echo '</A>';
				do_action('mbdb_widget_post_link');
			}
			
		}
		//echo '</div>' . 
		echo $after_widget;
		do_action('mbdb_widget_post_display');
	}
	
}
