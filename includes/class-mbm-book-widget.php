<?php
/**
 *  This file does the book widget  
 *  
 */
 
 
class mbdb_book_widget2 extends mbdb_widget {

	public $widgetType;
	
	// constructor
	// 3.1 -- added customize_selected_refresh for WP 4.5
	function __construct( ) {
		
		$this->widget_ops = array('classname' => 'mbdb_book_widget2', 
							'description' => __('Shows the cover of the book of your choosing with a link to the book page', 'mooberry-book-manager'),
							 'customize_selective_refresh' => true,);
		$this->title = __('Mooberry Book Manager Book',  'mooberry-book-manager');
		
		parent::__construct( );

	}
	
	 function form( $instance ) {
		$defaults = array(
						'mbdb_bookID'	=>	0,
						'mbdb_widget_type'	=> 'random',
					);
		$instance = wp_parse_args( $instance, $defaults );
		
		$instance = array_merge($instance, parent::form( $instance ));

		$this->widgetType = esc_attr($instance['mbdb_widget_type']);
		
		$options = apply_filters('mbdb_book_widget_options', array(
							'random' => __('Random Book', 'mooberry-book-manager'),
							'newest'	=>	__('Newest Book', 'mooberry-book-manager'),
							'coming-soon'	=>	__('Future Book', 'mooberry-book-manager'),
							'specific'	=>	__('Specific Book', 'mooberry-book-manager')
						)
					);
		$widget_type_dropdown = MBDB()->helper_functions->make_dropdown($this->get_field_id('mbdb_widget_type'), $options, $this->widgetType, 'no', 0, $this->get_field_name('mbdb_widget_type'));

		$selected_book = $instance[ 'mbdb_bookID' ];
		//error_log('make drop down for widget');
		$book_list = MBDB()->helper_functions->get_all_books();
			
		$book_list_dropdown = MBDB()->helper_functions->make_dropdown( $this->get_field_id('mbdb_bookID'), $book_list, $selected_book, 'yes', '0', $this->get_field_name('mbdb_bookID'));
		
		include( plugin_dir_path(__FILE__)  . '/admin/views/admin-widget-book.php');
		return $instance;
	}
	
	 function update( $new_instance, $old_instance ) {
		
		$instance = parent::update( $new_instance, $old_instance);
		$instance['mbdb_bookID'] = strip_tags($new_instance['mbdb_bookID']);
		$instance['mbdb_widget_type'] = strip_tags($new_instance['mbdb_widget_type']);
		return apply_filters('mbdb_book_widget_update', $instance, $new_instance);
	}
	
	 function widget( $args, $instance ) {
		
		
		//$this->bookID  = $instance['mbdb_bookID'];
		$this->widgetType = apply_filters('mbdb_widget_type', $instance['mbdb_widget_type']);
		
		parent::widget( $args, $instance );
	}
	
	
	 function selectBook( $instance ) {
		// we're now going to return an array of books to
		// handle returning more than one book
		$this->books = array();
		$filter_bookIDs = apply_filters('mbdb_book_widget_filter_bookIDs', null, $instance, $this);
		//$book_list = new MBDB_Book_List();
		$limit = 1;
		switch ($this->widgetType) {
		
			case "newest":
				// get book ID of most recent book
				//$this->books[] = apply_filters('mbdb_widget_newest_book_list', $book_list->get_most_recent_book( $filter_bookIDs ), $instance);
				$limit = apply_filters('mbdb_widget_newest_book_limit', 1);
			//	print_r('get newest');
				
				$this->books = apply_filters('mbdb_widget_newest_book_list', new MBDB_Book_List( MBDB_Book_List_Enum::newest, 'release_date', 'DESC', null, null, $filter_bookIDs, $limit, true ), $instance );
				
				break;
			
			case "coming-soon":
				// get books with future or blank release dates
				$limit = apply_filters('mbdb_widget_coming_soon_book_limit', 1);
				//print_r('get coming soon');
				//$this->books = apply_filters('mbdb_widget_coming_soon_book_list', $book_list->get_upcoming_books( $filter_bookIDs, $limit ), $instance); 
				$this->books = apply_filters('mbdb_widget_coming_soon_book_list', new MBDB_Book_List( MBDB_Book_List_Enum::unpublished, 'title', 'ASC', null, null, $filter_bookIDs, $limit, true ), $instance );
				break;
			
			case "specific":
				// make sure seected book is still a valid book
				//$this->books[] = apply_filters('mbdb_widget_specific_book_list', new Mooberry_Book_Manager_Book( $instance['mbdb_bookID'] ), $instance);
				$this->books[] = apply_filters('mbdb_widget_specific_book_list', MBDB()->book_factory->create_book( $instance['mbdb_bookID'] ), $instance);				
//print_r('get specific book for widget');				
				break;
			case 'random':
			//default: // default to random
				// get book ID of a random book
				$limit = apply_filters('mbdb_widget_random_book_limit', 1);
			//	print_r('get random');
				//$this->books = apply_filters('mbdb_widget_random_book_list', $book_list->get_random_books( $filter_bookIDs, $limit ), $instance);
				$this->books = apply_filters('mbdb_widget_random_book_list', new MBDB_Book_List( MBDB_Book_List_Enum::random, 'title', 'ASC', null, null, $filter_bookIDs, $limit ), $instance );
				
				break;
			
				
		}
	
		$this->books =  apply_filters('mbdb_book_widget_book', $this->books, $this->widgetType);
		// even though book list slices the array, do it again because filters may have chnged teh array (ie MA)
		if ( count($this->books) > $limit ) {
			if ( is_array($this->books) ) {
				 $this->books = array_splice($this->books, 0, $limit);
			} else {
				$this->books->limit_books($limit);
			}
		}			
		return $this->books;
	}
	
	function get_widget_type() {
		return $this->widgetType;
	}
}
