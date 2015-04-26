<?php
class mbdb_book_widget extends WP_Widget {

	// constructor
	function __construct() {
		$widget_ops = array('classname' => 'mbdb_book_widget', 'description' => __('Shows the cover of the book of your choosing with a link to the book page', 'mooberry-book-manager'));
		parent::WP_Widget('mbdb_book_widget', 'Mooberry Book Manager '. _x('Book', 'noun', 'mooberry-book-manager'), $widget_ops  );

	}

	// widget form creation
	function form($instance) {	
		$instance = wp_parse_args((array) $instance);
		// Check values
		if( $instance) {
			 do_action('mbdb_widget_before_get_data', $instance);
			 $mbdb_widget_title = esc_attr($instance['mbdb_widget_title']);
			 $mbdb_widget_show_title = esc_attr($instance['mbdb_widget_show_title']);
			 $mbdb_widget_type = esc_attr($instance['mbdb_widget_type']);
			 $mbdb_bookID = esc_attr($instance['mbdb_bookID']);
			 $mbdb_widget_cover_size = esc_attr($instance['mbdb_widget_cover_size']);
			 do_action('mbdb_widget_after_get_data', $instance);
		} else {
			 do_action('mbdb_widget_before_set_defaults'); 
			 $mbdb_widget_title = '';
			 $mbdb_bookID=0;		
			 $mbdb_widget_show_title = 'yes';
			 $mbdb_widget_type = 'random';
			 $mbdb_widget_cover_size = 100;
			 $mbdb_book_title = '';
			 do_action('mbdb_widget_after_set_defaults'); 
		}
		include( plugin_dir_path(__FILE__)  . '/views/admin-book-widget.php');
	}

	// widget update
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		do_action('mbdb_widget_before_update', $new_instance, $old_instance);
		$instance['mbdb_widget_title'] = strip_tags($new_instance['mbdb_widget_title']);
		$instance['mbdb_bookID'] = strip_tags($new_instance['mbdb_bookID']);
		$instance['mbdb_widget_show_title'] = strip_tags($new_instance['mbdb_widget_show_title']);
		$instance['mbdb_widget_type'] = strip_tags($new_instance['mbdb_widget_type']);
		if ($new_instance['mbdb_widget_cover_size'] < 50) {
			$new_instance['mbdb_widget_cover_size'] = 50;
		}
		$instance['mbdb_widget_cover_size'] = strip_tags($new_instance['mbdb_widget_cover_size']);
		do_action('mbdb_widget_after_update', $new_instance, $instance);
		return apply_filters('mbdb_widget_update', $instance, $new_instance);
	}

	// widget display
	function widget($args, $instance) {
		extract($args);
		$mbdb_bookID  = $instance['mbdb_bookID'];
		$mbdb_widget_type = apply_filters('mbdb_widget_type', $instance['mbdb_widget_type']);
		do_action('mbdb_widget_before_get_books', $instance);
		switch ($mbdb_widget_type) {
			case 'random':
				// get book ID of a random book
				$mbdb_books = apply_filters('mbdb_widget_random_book_list', mbdb_get_books_list( 'all', null, 'title', 'ASC', null, null ));
				if ( count($mbdb_books)>0 ) {		
					$randomID = rand(0,count($mbdb_books)-1);
					$mbdb_bookID = $mbdb_books[$randomID]->ID;
					$mbdb_book_title =  $mbdb_books[$randomID]->post_title;
				} else {
					$mbdb_bookID =  0;
					$mdbd_book_title =  '';
				}
				break;
			case "newest":
				// get book ID of most recent book
				$mbdb_books = apply_filters('mbdb_widget_newest_book_list', mbdb_get_books_list( 'published', null, '_mbdb_published', 'DESC', null, null));
				// we just need the first one
				if (count($mbdb_books)>0) {
					$mbdb_bookID = $mbdb_books[0]->ID;
					$mbdb_book_title = $mbdb_books[0]->post_title;
				} else {
					$mbdb_bookID = 0;
					$mbdb_book_title = '';
				}
				break;
			case "coming-soon":
				// get books with future or blank release dates
				$mbdb_books = apply_filters('mbdb_widget_coming_soon_book_list', mbdb_get_books_list('unpublished', null, '', null, null, null) );
				// choose a random one		
				if (count($mbdb_books) > 0) {
					$randomID = rand(0, count($mbdb_books)-1);
					$mbdb_bookID = $mbdb_books[$randomID]->ID;
					$mbdb_book_title = $mbdb_books[$randomID]->post_title;
				} else {
					$mbdb_bookID = 0;
					$mbdb_book_title = '';
				}
				break;
			case "specific":
				// make sure seected book is still a valid book
				$mbdb_books = apply_filters('mbdb_widget_specific_book_list', mbdb_get_books_list('all', array($mbdb_bookID), '', null, null, null));
				if (count($mbdb_books) > 0) {
					$mbdb_book_title = $mbdb_books[0]->post_title;
				} else {
					$mbdb_bookID = 0;
					$mbdb_book_title = '';
				}
				break;
		}
		//output
		$mbdb_widget_title = apply_filters('mbdb_widget_title', $instance['mbdb_widget_title']);
		$mbdb_widget_show_title = apply_filters('mbdb_widget_show_title', $instance['mbdb_widget_show_title']);
		$mbdb_cover_size = apply_filters('mbdb_widget_cover_size', $instance['mbdb_widget_cover_size']);
		$mbdb_bookID = apply_filters('mbdb_widget_bookID', $mbdb_bookID);
		$mbdb_book_title = apply_filters('mbdb_widget_book_title', $mbdb_book_title);
		
		do_action('mbdb_widget_before_display');
		echo $before_widget;
		echo '<div>';
		echo $before_title . esc_html($mbdb_widget_title) . $after_title;
		
		if ($mbdb_bookID == 0) {
			echo apply_filters('mbdb_widget_no_books_found', '<em>' . __('No books found', 'mooberry-book-manager') . '</em>');
		} else {
			$image_src = get_post_meta( $mbdb_bookID, '_mbdb_cover', true );
			$book_link = get_permalink( $mbdb_bookID );
			
			if ( $book_link != '' ) {
				do_action('mbdb_widget_before_link', $book_link);
				echo '<A class="mbm-widget-link" HREF="' . esc_url($book_link) . '"> ';
			}
			if ($image_src != '') { 
				do_action('mbdb_widget_before_image', $image_src);
				echo '<img class="mbm-widget-cover" style="width:' . esc_attr($mbdb_cover_size) . 'px" src="' . esc_url($image_src) . '" /> ';
				do_action('mbdb_widget_after_image', $image_src);
			}
			if ($mbdb_widget_show_title == 'yes') { 
				if ( $mbdb_book_title != '' ) {
					do_action('mbdb_widget_before_book_title', $mbdb_book_title);
					echo '<P><span class="mbm-widget-title">' . esc_html($mbdb_book_title) . '</span></P>';
					do_action('mbdb_widget_after_book_title', $mbdb_book_title);
				}
			}
			if ($book_link != '') {
				echo '</A>';
				do_action('mbdb_widget_after_link');
			}
			
		}
		echo '</div>' . $after_widget;
		do_action('mbdb_widget_after_display');
	}
	
}
