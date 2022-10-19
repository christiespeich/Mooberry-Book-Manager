<?php
class Mooberry_Book_Manager_Novelist_Import_Process extends WP_Background_Process {

	/**
	 * @var string
	 */
	protected $action = 'import_novelist_books';

	private $novelist_to_mbm_retailers;
	private $novelist_to_mbm_genres;
	private $novelist_to_mbm_series;
	private $mbm_publishers_by_name;
	private $mbm_paperback_format;
	private $mbm_kindle_format;


	public function __construct() {
		parent::__construct();
		if ( $this->is_process_running() ) {
			$message = __( 'Books currently being imported from Novelist.', 'mooberry-book-manager' );
			//$message .= '&nbsp;&nbsp;<a href="' . admin_url('admin.php?page=mbdb_import_export') . '" class="button">' . __('Cancel Import on the Import/Export Page', 'mooberry-book-manager') . '</a>';
			$type = 'updated';
			$key  = 'mbdb_import_novelist_books_process';
			MBDB()->helper_functions->set_admin_notice( $message, $type, $key );
		} else {
			if ( $this->is_queue_empty() ) {
				MBDB()->helper_functions->remove_admin_notice( 'mbdb_import_novelist_books_process' );

			}
		}

	}

	public function set_up_data() {
		// one time tasks
		// align retailers by name
		$mbm_retailers         = MBDB()->options->retailers;
		$mbm_retailers_by_name = array();
		foreach ( $mbm_retailers as $mbm_retailer ) {
			$mbm_retailers_by_name[ $mbm_retailer->name ] = $mbm_retailer;
		}
		$this->novelist_to_mbm_retailers = array();
		$novelist_settings               = get_option( 'novelist_settings', array() );
		if ( array_key_exists( 'purchase_links', $novelist_settings ) ) {
			foreach ( $novelist_settings['purchase_links'] as $retailer ) {
				$name = isset( $retailer['name'] ) ? $retailer['name'] : '';
				$id   = isset( $retailer['id'] ) ? $retailer['id'] : '';
				if ( ! array_key_exists( $name, $mbm_retailers_by_name ) ) {
					// if it's barnes-noble, match that up to Barnes & Noble if available
					if ( $id == 'barnes-noble' && array_key_exists( 'Barnes and Noble', $mbm_retailers_by_name ) ) {
						$mbm_retailer = $mbm_retailers_by_name['Barnes and Noble'];

					} else {
						// create new retailer
						$mbm_retailer = new Mooberry_Book_Manager_Retailer( array(
							'uniqueID' => mbdb_uniqueID_generator( '' ),
							'name'     => $name
						) );
						MBDB()->options->add_retailer( $mbm_retailer );
					}
				} else {
					$mbm_retailer = $mbm_retailers_by_name[ $name ];
				}
				$this->novelist_to_mbm_retailers[ $id ] = $mbm_retailer;
			}
		}

		// create genres
		$this->novelist_to_mbm_genres = array();
		$novelist_genres              = get_terms( array( 'taxonomy' => 'novelist-genre', 'hide_empty' => false ) );
		if ( ! is_wp_error( $novelist_genres ) ) {
			foreach ( $novelist_genres as $novelist_genre ) {
				$mbm_genre = term_exists( $novelist_genre->name, 'mbdb_genre' );
				if ( $mbm_genre === null ) {
					// create genre
					$mbm_genre = wp_insert_term( $novelist_genre->name, 'mbdb_genre' );
				}
				$this->novelist_to_mbm_genres[ $novelist_genre->term_id ] = intval( $mbm_genre['term_id'] );

			}
		}

		// create series
		$this->novelist_to_mbm_series = array();
		$novelist_series              = get_terms( array( 'taxonomy' => 'novelist-series', 'hide_empty' => false ) );
		if ( ! is_wp_error( $novelist_series ) ) {
			foreach ( $novelist_series as $a_novelist_series ) {
				$mbm_series = term_exists( $a_novelist_series->name, 'mbdb_series' );
				if ( $mbm_series === null ) {
					// create series
					$mbm_series = wp_insert_term( $a_novelist_series->name, 'mbdb_series' );
				}
				$this->novelist_to_mbm_series[ $a_novelist_series->term_id ] = intval( $mbm_series['term_id'] );

			}
		}

		// publishers
		$this->mbm_publishers_by_name = array();

			$publisher_ids = get_posts( array(
				'post_type'      => 'mbdb_publisher',
				'fields'         => 'ids',
				'posts_per_page' => - 1
			) );
			foreach ( $publisher_ids as $publisher_id ) {
				$publisher = new Mooberry_Book_Manager_Publisher( $publisher_id );
				$this->mbm_publishers_by_name[$publisher->name] = $publisher;
			}




		// put page count as paperback by default and asin has kindle
		// creat paperback format if needed
		$formats                    = MBDB()->options->edition_formats;
		$this->mbm_paperback_format = null;
		$this->mbm_kindle_format    = null;
		foreach ( $formats as $format ) {
			if ( $format->name == 'Paperback' ) {
				$this->mbm_paperback_format = $format;
			}
			if ( $format->name == 'Kindle' ) {
				$this->mbm_kindle_format = $format;
			}
		}
		if ( $this->mbm_paperback_format === null ) {
			// create new form
			$this->mbm_paperback_format = new Mooberry_Book_Manager_Edition_Format( array(
				'uniqueID' => mbdb_uniqueID_generator( '' ),
				'name'     => 'Paperback'
			) );
			MBDB()->options->add_edition_format( $this->mbm_paperback_format );
			//$mbm_paperback_format = $mbm_paperback_format->id;
		}

		if ( $this->mbm_kindle_format === null ) {
			$this->mbm_kindle_format = new Mooberry_Book_Manager_Edition_Format( array(
				'uniqueID' => mbdb_uniqueID_generator( '' ),
				'name'     => 'Kindle'
			) );
			MBDB()->options->add_edition_format( $this->mbm_kindle_format );
		}


		//save to the database
		update_option( 'mbm_novelist_to_mbm_retailers', $this->novelist_to_mbm_retailers );
		update_option( 'mbm_novelist_to_mbm_genres', $this->novelist_to_mbm_genres );
		update_option( 'mbm_novelist_to_mbm_series', $this->novelist_to_mbm_series );
		update_option( 'mbm_publishers_by_name', $this->mbm_publishers_by_name );
		update_option( 'mbm_paperback_format', $this->mbm_paperback_format );
		update_option( 'mbm_kindle_format', $this->mbm_kindle_format );
	}

	public function cancel_process() {
		global $wpdb;

		$table  = $wpdb->options;
		$column = 'option_name';

		if ( is_multisite() ) {
			$table  = $wpdb->sitemeta;
			$column = 'meta_key';
		}

		$key = $this->identifier . '_batch_%';

		$query = $wpdb->get_results( $wpdb->prepare( "
			DELETE
			FROM {$table}
			WHERE {$column} LIKE %s

		", $key ) );

		parent::cancel_process();

	}

	public function save() {
		parent::save();
		$this->data = array();
	}

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param mixed $item Queue item to iterate over
	 *
	 * @return mixed
	 */
	protected function task( $novelist_book ) {
		$post_meta = get_post_meta( $novelist_book->ID );

		$book_data        = new stdClass();
		$book_data->title = $novelist_book->post_title;
		$release_date     = $this->get_meta_data( $post_meta, 'novelist_pub_date_timestamp' );
		if ( $release_date != '' ) {
			$book_data->release_date = date( 'Y-m-d', $release_date );
		}
		$book_data->summary         = $this->get_meta_data( $post_meta, 'novelist_synopsis' );
		$book_data->goodreads       = $this->get_meta_data( $post_meta, 'novelist_goodreads' );
		$book_data->additional_info = $this->get_meta_data( $post_meta, 'novelist_extra' );
		$book_data->excerpt         = $this->get_meta_data( $post_meta, 'novelist_excerpt' );
		$book_data->series_order    = $this->get_meta_data( $post_meta, 'novelist_series' );
		$cover_id                   = $this->get_meta_data( $post_meta, 'novelist_cover' );
		$book_data->cover_id        = $cover_id;
		$book_data->cover           = wp_get_attachment_url( $cover_id );


		//load from the database
		$this->novelist_to_mbm_retailers = get_option( 'mbm_novelist_to_mbm_retailers', array() );
		$this->novelist_to_mbm_genres    = get_option( 'mbm_novelist_to_mbm_genres', array() );
		$this->novelist_to_mbm_series    = get_option( 'mbm_novelist_to_mbm_series', array() );
		$this->mbm_publishers_by_name    = get_option( 'mbm_publishers_by_name', array() );
		$this->mbm_paperback_format      = get_option( 'mbm_paperback_format', new Mooberry_Book_Manager_Edition() );
		$this->mbm_kindle_format         = get_option( 'mbm_kindle_format', new Mooberry_Book_Manager_Edition() );

		// buy links
		$purchase_links = unserialize( $this->get_meta_data( $post_meta, 'novelist_purchase_links' ) );
		$mbm_buylinks   = array();
		if ( $purchase_links ) {
			foreach ( $purchase_links as $retailer => $purchase_link ) {
				if ( isset( $this->novelist_to_mbm_retailers[ $retailer ] ) ) {
					//$mbm_buylinks[]   = $novelist_to_mbm_retailers[ $retailer ]->to_json();
					$mbm_retailer   = $this->novelist_to_mbm_retailers[ $retailer ];
					$mbm_buylink    = new Mooberry_Book_Manager_Buy_Link( array(
						'link'       => $purchase_link,
						'retailerID' => $mbm_retailer->id
					) );
					$mbm_buylinks[] = $mbm_buylink->to_json();
				}

			}
		}

		$book_data->buy_links = $mbm_buylinks;

		// page count, isbn, and asin
		$book_data->editions = array();
		$page_count          = $this->get_meta_data( $post_meta, 'novelist_pages' );
		$isbn                = $this->get_meta_data( $post_meta, 'novelist_isbn' );
		$asin                = $this->get_meta_data( $post_meta, 'novelist_asin' );
		if ( $page_count != '' || $isbn != '' ) {
			$new_edition           = new Mooberry_Book_Manager_Edition( array(
				'format_id' => $this->mbm_paperback_format->id,
				'length'    => $page_count,
				'isbn'      => $isbn,
			) );
			$book_data->editions[] = $new_edition->to_json();
		}
		if ( $asin != '' ) {
			$new_edition           = new Mooberry_Book_Manager_Edition( array(
				'format_id' => $this->mbm_kindle_format->id,
				'isbn'      => $asin,
			) );
			$book_data->editions[] = $new_edition->to_json();

			$book_data->excerpt_type   = 'kindle';
			$book_data->kindle_preview = $asin;
		}


		// Publishers
		// TODO: import
		$publisher = $this->get_meta_data( $post_meta, 'novelist_publisher' );
		if ( $publisher != '' ) {
			$mbm_publisher = new Mooberry_Book_Manager_Publisher();
			if ( array_key_exists( $publisher, $this->mbm_publishers_by_name ) ) {
				$mbm_publisher = $this->mbm_publishers_by_name[ $publisher ];
			} else {
				// create publisher
				$new_publisher_id = wp_insert_post( array(
					'post_status'    => 'publish',
					'post_type'      => 'mbdb_publisher',
					'post_title'     => trim( $publisher ),
					'post_publisher' => wp_get_current_user()->ID
				) );
				if ( ! is_wp_error( $new_publisher_id ) ) {
					$mbm_publisher = new Mooberry_Book_Manager_Publisher( $new_publisher_id );
				}
			}

			$book_data->publisher    = $mbm_publisher->to_json();
			$book_data->publisher_id = $mbm_publisher->id;

		}

		// genre
		$genres     = get_the_terms( $novelist_book->ID, 'novelist-genre' );
		$mbm_genres = array();
		if ( $genres ) {
			foreach ( $genres as $genre ) {
				if ( isset( $this->novelist_to_mbm_genres[ $genre->term_id ] ) ) {
					$mbm_genre_term_id = $this->novelist_to_mbm_genres[ $genre->term_id ];
					$mbm_genres[]      = get_term( $mbm_genre_term_id, 'mbdb_genre' );
				}
			}
		}
		$book_data->genres = $mbm_genres;

		// series
		$series     = get_the_terms( $novelist_book->ID, 'novelist-series' );
		$mbm_series = array();
		if ( $series ) {
			foreach ( $series as $a_series ) {
				if ( isset( $this->novelist_to_mbm_series[ $a_series->term_id ] ) ) {
					$mbm_series_term_id = $this->novelist_to_mbm_series[ $a_series->term_id ];
					$mbm_series[]       = get_term( $mbm_series_term_id, 'mbdb_series' );
				}
			}
		}
		$book_data->series = $mbm_series;


		$mbm_book = MBDB()->book_factory->create_book();
		$mbm_book->import( $book_data );

		return false;
	}

	private function get_meta_data( $array, $field, $default = '' ) {
		return isset( $array[ $field ] ) ? $array[ $field ][0] : $default;
	}


	/**
	 * Complete
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	protected function complete() {
		parent::complete();

		// Show notice to user or perform some other arbitrary task...
		$key     = 'mbdb_import_novelist_books_complete';
		$message = __( 'Novelist Book import complete!', 'mooberry-book-manager' );
		$message .= '&nbsp;&nbsp;<a href="#" class="button mbdb_admin_notice_dismiss" data-admin-notice="' . $key . '">' . __( 'Dismiss this notice', 'mooberry-book-manager' ) . '</a>';
		$type    = 'updated';

		MBDB()->helper_functions->set_admin_notice( $message, $type, $key );

	}

	public function is_queue_empty() {
		return parent::is_queue_empty();
	}

}
