<?php
/**
 *  This file does the book widget
 *
 */


class mbdb_book_widget2 extends mbdb_widget {

	public $widgetType;
	public $bookLimit;

	// constructor
	// 3.1 -- added customize_selected_refresh for WP 4.5
	function __construct( ) {

		$this->widget_ops = array('classname' => 'mbdb_book_widget2',
							'description' => __('Shows the cover of the book of your choosing with a link to the book page', 'mooberry-book-manager'),
							 'customize_selective_refresh' => true,
			);
		$this->title = __('Mooberry Book Manager Book',  'mooberry-book-manager');

		$this->bookLimit = 1;
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

		$options = apply_filters('mbdb_book_widget_options', MBDB()->get_widget_options() );
		$widget_type_dropdown = MBDB()->helper_functions->make_dropdown($this->get_field_id('mbdb_widget_type'), $options, $this->widgetType, 'no', 0, $this->get_field_name('mbdb_widget_type'));

		$selected_book = $instance[ 'mbdb_bookID' ];

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

		$this->widgetType = apply_filters('mbdb_widget_type', $instance['mbdb_widget_type']);

		parent::widget( $args, $instance );
	}


	 function selectBook( $instance ) {
		 // we're now going to return an array of books to
		 // handle returning more than one book
		 $this->books    = array();
		 $filter_bookIDs = apply_filters( 'mbdb_book_widget_filter_bookIDs', null, $instance, $this );

		 switch ( $this->widgetType ) {

			 case "newest":
				 // get book ID of most recent book
				 $this->bookLimit = apply_filters( 'mbdb_widget_newest_book_limit', $this->bookLimit );
				 $this->books = apply_filters( 'mbdb_widget_newest_book_list', new MBDB_Book_List( MBDB_Book_List_Enum::newest, 'release_date', 'DESC', null, null, $filter_bookIDs, $this->bookLimit, true ), $instance );

				 break;

			 case "coming-soon":
				 // get books with future or blank release dates
				 $this->bookLimit = apply_filters( 'mbdb_widget_coming_soon_book_limit', $this->bookLimit );
				 $this->books = apply_filters( 'mbdb_widget_coming_soon_book_list', new MBDB_Book_List( MBDB_Book_List_Enum::unpublished, 'title', 'ASC', null, null, $filter_bookIDs, $this->bookLimit, true ), $instance );
				 break;

			 case "specific":
				 $this->books[] = apply_filters( 'mbdb_widget_specific_book_list', MBDB()->book_factory->create_book( $instance['mbdb_bookID'] ), $instance );

				 break;
			 case 'random':
				 $this->bookLimit = apply_filters( 'mbdb_widget_random_book_limit', $this->bookLimit );
				 $this->books = apply_filters( 'mbdb_widget_random_book_list', new MBDB_Book_List( MBDB_Book_List_Enum::random, 'title', 'ASC', null, null, $filter_bookIDs, $this->bookLimit), $instance );

				 break;


		 }

		 $this->books = apply_filters( 'mbdb_book_widget_book', $this->books, $this->widgetType );
		 $this->bookLimit = apply_filters('mbdb_book_widget_limit', $this->bookLimit, $this->widgetType);

		 // even though book list slices the array, do it again because filters may have chnaged teh array (ie MA)
		 if ( is_array( $this->books ) ) {
			 if ( count( $this->books ) > $this->bookLimit ) {
				 $this->books = array_splice( $this->books, 0, $this->bookLimit );
			 }
		 } else {
			 if ( $this->books !== null ) {
				 $this->books->limit_books( $this->bookLimit );
			 } else {
				 $this->books = array();
			 }
		 }

		 return $this->books;
	 }

	function get_widget_type() {
		return $this->widgetType;
	}
}
