<?php


class MBDB_Import_Books_CSV_Process extends Mooberry_Dreams_Background_Process {


	/**
	 * @var string
	 */
	protected $action = 'MBDB_Import_Books_CSV';

	public function __construct( $admin_notice_manager ) {
		parent::__construct( $admin_notice_manager );
		$this->label = ' books ';
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
	protected function task( $book_data ) {

		$columns = array(
			'title'      => 'title',
			'summary'    => 'summary',
			'subtitle'   => 'subtitle',
			'goodreads'  => 'goodreads',
			'reedsy'     => 'reedsy',
			'google'     => 'google_books',
			'order'      => 'series_order',
			'additional' => 'additional_info',
			'date'       => 'release_date',
		);


		$book = new stdClass();

		foreach ( $columns as $column => $property ) {
			if ( $column == 'summary' || $column == 'additional') {
				$book->$property = isset( $book_data[ $column ] ) ? sanitize_post( $book_data[ $column ] ) : '';
			} else {
				$book->$property = isset( $book_data[ $column ] ) ? sanitize_text_field( $book_data[ $column ] ) : '';
			}
		}


		$taxonomies = array(
			'genres'        => array( 'property' => 'genres', 'taxonomy' => 'mbdb_genre' ),
			'tags'          => array( 'property' => 'tags', 'taxonomy' => 'mbdb_tag' ),
			'series'        => array( 'property' => 'series', 'taxonomy' => 'mbdb_series' ),
			'editors'       => array( 'property' => 'editors', 'taxonomy' => 'mbdb_editor' ),
			'illustrators'  => array( 'property' => 'illustrators', 'taxonomy' => 'mbdb_illustrator' ),
			'cover_artists' => array( 'property' => 'cover_artists', 'taxonomy' => 'mbdb_cover_artist' ),
		);

		foreach ( $taxonomies as $column => $info ) {
			if ( isset( $book_data[ $column ] ) ) {
				$terms                     = explode( ',', $book_data[ $column ] );
				$book->{$info['property']} = array();
				foreach ( $terms as $term_slug ) {
					$term_slug = trim( $term_slug );
					if ( $term_slug != '' ) {
						$term = term_exists( $term_slug, $info['taxonomy'] );
						if ( ! is_array( $term ) ) {
							$term = wp_insert_term( sanitize_text_field( $term_slug ), $info['taxonomy'] );
							if ( is_wp_error( $term ) ) {
								continue;
							}
						}
						$book->{$info['property']}[] = get_term( $term['term_id'] );
					}
				}

			}
		}


		// publisher
		//TODO: import
		if ( isset( $book_data['publisher'] ) ) {
			$publisher_name = sanitize_text_field( $book_data['publisher'] );
			$publisher_id = 0;

			$publisher_ids = get_posts( array(
				'name'           => trim( $publisher_name ),
				'post_type'      => 'mbdb_publisher',
				'fields'         => 'ids',
				'posts_per_page' => - 1
			) );

			// if not found, add it
			if ( count( $publisher_ids ) == 0 ) {
				$new_publisher_id = wp_insert_post( array(
					'post_status'    => 'publish',
					'post_type'      => 'mbdb_publisher',
					'post_title'     => trim( $publisher_name ),
					'post_publisher' => wp_get_current_user()->ID
				) );
				if ( ! is_wp_error( $new_publisher_id ) ) {
					$publisher_id = $new_publisher_id;
				}
			} else {
				$publisher_id = $publisher_ids[0];
			}



			$publisher          = new Mooberry_Book_Manager_Publisher( $publisher_id );
			$book->publisher    = $publisher->to_json();
			$book->publisher_id = $publisher->id;


		}


		// imprint
		if ( isset( $book_data['imprint'] ) ) {
			$mbm_imprints = MBDB()->options->imprints;
			$found        = false;
			if ( is_array( $mbm_imprints ) ) {

				foreach ( $mbm_imprints as $imprint ) {
					if ( $imprint->name == $book_data['imprint'] ) {
						$found = true;
						break;
					}
				}
			}
			if ( ! $found ) {
				$imprint = new Mooberry_Book_Manager_Imprint( array(
					'name'     => sanitize_text_field( $book_data['imprint'] ),
					'uniqueID' => mbdb_uniqueID_generator( '' )
				) );
				MBDB()->options->add_imprint( $imprint );
			}
			$book->imprint    = $imprint->to_json();
			$book->imprint_id = $imprint->id;

		}

		// excerpt
		$excerpt_type = isset( $book_data['excerpt_type'] ) ? sanitize_text_field( $book_data['excerpt_type'] ) : '';
		if ( $excerpt_type == 'text' ) {
			$book->excerpt = sanitize_post( $book_data['excerpt'] );
		}
		if ( $excerpt_type == 'kindle' ) {
			$book->kindle_preview = sanitize_text_field( $book_data['excerpt'] );
		}
		$book->excerpt_type = $excerpt_type;


		// buy links
		$retailers       = MBDB()->options->retailers;
		$book->buy_links = array();
		if ( is_array( $retailers ) ) {
			foreach ( $retailers as $retailer ) {
				if ( isset( $book_data[ 'retailer_' . $retailer->name . '_link' ] ) ) {
					$buy_link          = new Mooberry_Book_Manager_Buy_Link( array(
						'retailerID' => $retailer->id,
						'link'       => sanitize_url( $book_data[ 'retailer_' . $retailer->name . '_link' ] )
					) );
					$book->buy_links[] = $buy_link->to_json();
				}
			}
		}


		// download links
		$download_formats     = MBDB()->options->download_formats;
		$book->download_links = array();
		if ( is_array( $download_formats ) ) {
			foreach ( $download_formats as $download_format ) {
				if ( isset( $book_data[ 'download_' . $download_format->name . '_link' ] ) ) {
					$download_link          = new Mooberry_Book_Manager_Download_Link( array(
						'formatID' => $download_format->id,
						'link'     => sanitize_url( $book_data[ 'download_' . $download_format->name . '_link' ] )
					) );
					$book->download_links[] = $download_link->to_json();
				}
			}
		}

		// formats
		$formats        = MBDB()->options->edition_formats;
		$book->editions = array();
		if ( is_array( $formats ) ) {
			foreach ( $formats as $format ) {
				$edition        = array();
				$edition_fields = array(
					'isbn'     => 'isbn',
					'doi'      => 'doi',
					'sku'   =>  'sku',
					'language' => 'language',
					'pages'    => 'length',
					'height'   => 'height',
					'width'    => 'width',
					'unit'     => 'unit',
					'price'    => 'retail_price',
					'currency' => 'currency',
					'title'    => 'edition_title',
				);
				foreach ( $edition_fields as $edition_field => $property ) {
					if ( isset( $book_data[ 'format_' . $format->name . '_' . $edition_field ] ) ) {
						$edition[ $property ] = sanitize_text_field( $book_data[ 'format_' . $format->name . '_' . $edition_field ] );
					}
				}
				if ( count( $edition ) > 0 ) {
					$edition['format_id'] = $format->id;
					$book_edition         = new Mooberry_Book_Manager_Edition( $edition );
					$book->editions[]     = $book_edition->to_json();
				}
			}
		}

		// cover
		// if integer, it is attachment id
		if ( isset( $book_data['cover'] ) ) {
			if ( intval( $book_data['cover'] ) == $book_data['cover'] ) {
				$book->cover_id = intval( $book_data['cover'] );
				$book->cover    = wp_get_attachment_url( $book->cover_id );
			} else {
				$book->cover = sanitize_url( $book_data['cover'] );
			}
		}

		// reviews
		$review_columns = array( 'name'=> 'reviewer_name',
			'link'=> 'url',
			'website'=>'website_name',
			'review'=>'review',);
		$book->reviews = array();
		for($x =1; $x<6; $x++) {
			$review = array();
			foreach ( $review_columns as $column => $property ) {
			if ( isset($book_data['review' . $x . '_' . $column])) {
				if ( $column=='link') {
					$review[ $property ] = sanitize_url( $book_data[ 'review' . $x . '_' . $column ] );
				} else {
					$review[ $property ] = sanitize_text_field( $book_data[ 'review' . $x . '_' . $column ] );
				}
			}

			}
			if ( count($review) > 0 ) {
				$new_review = new Mooberry_Book_Manager_Review($review);
				$book->reviews[] = $new_review->to_json();
			}
		}

		// book = the book object that will be saved to the database
		// book_data = a book's data pulled from the CSV file (one row)
		$book = apply_filters('mbdb_pre_cvs_import_book', $book, $book_data );

		$mbm_book = MBDB()->book_factory->create_book();
		$new_book_id = $mbm_book->import( $book );
		if ( !is_array($new_book_id)) {
			$book->id = $new_book_id;
		}

		// book = the book object that was saved to the database
		// book_data = a book's data pulled from the CSV file (one row)
		$mbm_book = apply_filters('mbdb_post_cvs_import_book', $mbm_book, $book_data );


		return false;

	}
}
