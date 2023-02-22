<?php

//abstract class Mooberry_Book_Manager_Widget extends WP_Widget implements iMooberry_Book_Manager_Widget {
//abstract class Mooberry_Book_Manager_Widget extends WP_Widget {
abstract class mbdb_widget extends WP_Widget {
	public $coverSize;
	public $displayBookTitle;
	public $widgetTitle;
	public $books;
	public $title;
	public $widget_ops;

	function __construct() {
		//print_r($this->widget_opts);
		parent::__construct( $this->widget_ops['classname'], $this->title, $this->widget_ops );
	}

	// display the admin form
	function form( $instance ) {
		$defaults = array(
			'mbdb_widget_title'      => '',
			'mbdb_widget_show_title' => 'yes',
			'mbdb_widget_cover_size' => 100,
		);
		$instance = wp_parse_args( (array) $instance, $defaults );

		// Check values
		do_action( 'mbdb_widget_pre_set_defaults' );

		do_action( 'mbdb_widget_post_set_defaults' );

		do_action( 'mbdb_widget_pre_get_data', $instance );

		$this->widgetTitle      = esc_attr( $instance['mbdb_widget_title'] );
		$this->displayBookTitle = esc_attr( $instance['mbdb_widget_show_title'] );
		$this->coverSize        = esc_attr( $instance['mbdb_widget_cover_size'] );
		do_action( 'mbdb_widget_post_get_data', $instance );


		// display the form
		do_action( 'mbdb_widget_pre_form', $instance, $this );

		include( plugin_dir_path( __FILE__ ) . '/admin/views/admin-widget.php' );

		do_action( 'mbdb_book_post_form', $instance, $this );

		return $instance;
	}

	// widget update
	function update( $new_instance, $old_instance ) {

		$instance = $old_instance;
		do_action( 'mbdb_widget_pre_update', $new_instance, $old_instance );

		$instance['mbdb_widget_title'] = strip_tags( $new_instance['mbdb_widget_title'] );

		if ( ! array_key_exists( 'mbdb_widget_show_title', $new_instance ) ) {
			$instance['mbdb_widget_show_title'] = 'no';
		} else {
			$instance['mbdb_widget_show_title'] = $new_instance['mbdb_widget_show_title'];
		}

		if ( ! array_key_exists( 'mbdb_widget_cover_size', $new_instance ) || $new_instance['mbdb_widget_cover_size'] < 50 ) {
			$instance['mbdb_widget_cover_size'] = 50;
		}
		$instance['mbdb_widget_cover_size'] = strip_tags( $new_instance['mbdb_widget_cover_size'] );

		do_action( 'mbdb_widget_post_update', $new_instance, $instance );

		return apply_filters( 'mbdb_widget_update', $instance, $new_instance );
	}

	// widget display
	function widget( $args, $instance ) {

		if ( ! $this->display_widget( $instance ) ) {
			return;
		}

		$this->get_data( $instance );

		if ( $this->books == null && ! $this->display_no_book() ) {
			return;
		}

		$this->output_widget_start( $args, $instance );

		$this->output_books( $instance );

		$this->output_widget_end( $args, $instance );
	}

	function get_data( $instance ) {
		$mbdb_widget_title = isset($instance['mbdb_widget_title']) ? $instance['mbdb_widget_title'] : '';
		$mbdb_widget_show_title = isset($instance['mbdb_widget_show_title']) ? $instance['mbdb_widget_show_title'] : '';
		$mbdb_widget_cover_size = isset($instance['mbdb_widget_cover_size']) ? $instance['mbdb_widget_cover_size'] : '';

		$this->widgetTitle      = apply_filters( 'mbdb_widget_title', $mbdb_widget_title );
		$this->displayBookTitle = apply_filters( 'mbdb_widget_show_title', $mbdb_widget_show_title );
		$this->coverSize        = apply_filters( 'mbdb_widget_cover_size', $mbdb_widget_cover_size );

		$this->books = array();
		do_action( 'mbdb_widget_pre_get_books', $instance );

		$this->books = $this->selectBook( $instance );

		$this->books = apply_filters( 'mbdb_widget_book', $this->books, $instance );

		do_action( 'mbdb_widget_post_get_books', $instance, $this->books );
	}

	function output_widget_start( $args, $instance ) {

		do_action( 'mbdb_widget_pre_display', $instance );

		//output

		extract( $args );

		echo $before_widget;

		echo $before_title . esc_html( $this->widgetTitle ) . $after_title;

	}

	function output_widget_end( $args, $instance ) {

		extract( $args );
		echo $after_widget;

		do_action( 'mbdb_widget_post_display' );
	}

	function output_books( $instance ) {
		// print_r($this->books);
		//output

		if ( $this->books == null || (is_array($this->books) && count($this->books) == 0 ) ) {
			//$this->books = array(new Mooberry_Book_Manager_Book( 0 ));
			$this->books = array( MBDB()->book_factory->create_book( 0 ) );
			//print_r('initialize book in widget');
		} else {
			// bkwards compat with AW.... force it into the Book List object
			// AW = book_id, v4 = id
			if ( is_array( $this->books ) || get_class( $this->books ) != 'MBDB_Book_List' ) {
				if ( is_array( $this->books ) ) {
					foreach ( $this->books as $book ) {
						if ( property_exists( $book, 'id' ) ) {
							$ids[] = $book->id;
						} else {
							if ( property_exists( $book, 'book_id')) {
								$ids[] = $book->book_id;
							} else {
								if ( property_exists( $book, 'ID')) {
									$ids[] = $book->ID;
								}
							}

						}
					}
				} else {
					if ( property_exists( $this->books, 'id' ) ) {
						$ids[] = $this->books->id;
					} else {

						$ids[] = $this->books->book_id;
					}
				}
				//print_r('get books for widget?');
				$this->books = new MBDB_Book_List( 'custom', 'title', 'ASC', $ids );

			}
		}


		foreach ( $this->books as $book ) {

			//$this->books->title = apply_filters('mbdb_widget_book_title', $this->book->title);

			do_action( 'mbdb_widget_pre_book_display' );

			if ( $book->id == 0 ) {
				echo apply_filters( 'mbdb_widget_no_books_found', '<em>' . __( 'No books found', 'mooberry-book-manager' ) . '</em>' );
			} else {
				$book->book_id = $book->id;
				//	print_r($book->book_id);
				$this->output_cover( $book, $instance );

				$this->output_title( $book, $instance );
				do_action( 'mbdb_widget_post_title', $book, $instance );

			}
			do_action( 'mbdb_widget_post_book_display', $book, $instance );
		}
		do_action( 'mbdb_widget_post_books_display', $this->books, $instance );
	}

	function output_cover( $book, $instance ) {

		/*$size        = 'medium';
		$wide_enough = intval( get_option( "medium_size_w", 0 ) );
		$wp_sizes    = get_intermediate_image_sizes();
		$coverSize   = intval( $this->coverSize );
		foreach ( $wp_sizes as $wp_size ) {
			if ( $wp_size == 'thumbnail') {
				continue;
			}
			$width = intval( get_option( "{$wp_size}_size_w", 0 ) );
			// if we find a size that has more width than our desired cover size AND it's smaller than our currently
			// selected size, then it's a better fit and we should choose that one
			// however, $wide_enough could be the wrong size if the starting size is too small
			// so use the new size in that case also
			if ( $width >= $coverSize && ( $width < $wide_enough || $wide_enough < $coverSize ) ) {
				$wide_enough = $width;
				$size        = $wp_size;
			}
		}
		global $_wp_additional_image_sizes;

		foreach ( $_wp_additional_image_sizes as $image_name => $image_props ) {
			$image_size_width = isset( $image_props['width'] ) ? intval( $image_props['width'] ) : 0;
			// if we find a size that has more width than our desired cover size AND it's smaller than our currently
			// selected size, then it's a better fit and we should choose that one
			// however, $wide_enough could be the wrong size if the starting size is too small
			// so use the new size in that case also
			if ( $image_size_width >= $coverSize && ( $image_size_width < $wide_enough || $wide_enough < $coverSize ) ) {
				$wide_enough = $image_size_width;
				$size        = $image_name;
			}
		}*/

		$url = $book->get_cover_url( 'medium', 'widget' );

		//$url = $book->get_cover_url( $size, 'widget' );
		$alt = MBDB()->helper_functions->get_alt_attr( $book->cover_id, __( 'Book Cover:', 'mooberry-book-manager' ) . ' ' . $book->title );

		if ( isset( $url ) && $url != '' ) {

			do_action( 'mbdb_widget_pre_image', $url, $book );
			echo '<div class="mbdb_book_widget" style="' . apply_filters( 'mbdb_book_widget_cover_span_style', 'padding:0;margin:10px 0; position:relative; ', $instance ) . '">';
			echo MBDB()->helper_functions->get_popup_card_html( $book );
		if ( $book->permalink != '' ) {
			do_action( 'mbdb_widget_pre_cover_link', $book, $book->permalink );
			echo '<A class="mbm-widget-link" HREF="' . esc_url( $book->permalink ) . '"> ';
		}
			$cover_image = '<img class="mbm-widget-cover" style="' . apply_filters( 'mbdb_book_widget_cover_style', 'width:' . esc_attr( $this->coverSize ) . 'px;', $instance ) . '" src="' . esc_url( $url ) . '" ' . $alt . ' data-book="' . $book->id . '" />';
			echo MBDB()->helper_functions->maybe_add_ribbon( $cover_image, $book, 'widget' );
			echo '</div>';
			do_action( 'mbdb_widget_post_image', $url, $book->id );
		}

		if ( $book->permalink != '' ) {
			echo '</A>';
			do_action( 'mbdb_widget_post_cover_link' );
		}

	}

	function output_title( $book, $instance ) {
		if ( $this->displayBookTitle == 'yes' ) {

			if ( $book->permalink != '' ) {
				do_action( 'mbdb_widget_pre_title_link', $book, $book->permalink );
				echo '<A class="mbm-widget-link" HREF="' . esc_url( $book->permalink ) . '"> ';
			}
			if ( $book->title != '' ) {
				do_action( 'mbdb_widget_pre_book_title', $book->title );
				echo '<P class="mbm-widget-title" style="' . apply_filters( 'mbdb_book_widget_title_style', '', $instance ) . '">' . esc_html( $book->title ) . '</P>';
				do_action( 'mbdb_widget_post_book_title', $book->title );
			}
			if ( $book->permalink != '' ) {
				echo '</A>';
				do_action( 'mbdb_widget_post_title_link', $book, $book->permalink );
			}
		}
	}

	// must be implemented by child classes
	// could not change the name because of backwards compatibility
	// with advanced widgets
	abstract protected function selectBook( $instance );

	// implemented non-final function means subclasses can optionally override
	// always display the widget by default
	protected function display_widget( $instance ) {
		return true;
	}

	// implemented non-final function means subclasses can optionally override
	// always display "no book" message by default
	function display_no_book() {
		return true;
	}


	// backwards compatibility for AW
	public function getData( $instance ) {
		 $this->get_data( $instance );
	}

	public function outputWidgetStart( $args, $instance ) {
		 $this->output_widget_start( $args, $instance );
	}

	public function outputWidgetEnd( $args, $instance ) {
		 $this->output_widget_end( $args, $instance );
	}

	public function outputBook( $instance ) {
		 $this->output_books( $instance );
	}

	public function outputCover( $book, $link, $title, $instance ) {
		 $this->output_cover( $book, $instance );
	}

	public function outputTitle( $book, $title, $link, $instance ) {
		 $this->output_title( $book, $instance );
	}

	/*	protected function selectBook( $instance ) {
			return $this->select_books( $instance );
		}
		*/
	protected function displayWidget( $instance ) {
		return $this->display_widget( $instance );
	}

	protected function displayNoBook() {
		return $this->display_no_book();
	}
}
