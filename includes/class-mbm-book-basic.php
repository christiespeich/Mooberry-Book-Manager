<?php
/**
 * Book Object
 *
 * @package     MBDB
 * @copyright   Copyright (c) 2015, Mooberry Dreams
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5 ?
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Mooberry_Book_Manager_Book_Basic Class
 *
 * @since 3.5 ?
 */
 class Mooberry_Book_Manager_Book_Basic extends Mooberry_Book_Manager_CPT_Object { 
	protected $title;
	protected $summary;
	protected $excerpt;
	protected $excerpt_type;
	protected $kindle_preview;
	protected $subtitle;
	protected $release_date;
	protected $goodreads;
	protected $series_order;
	protected $cover;
	protected $cover_id;
	protected $publisher_id;
	protected $publisher;
	protected $additional_info;
	protected $slug;
	// bkacwards compatibility
	protected $book_id;
	
	public function __construct( $book = null, $id = null ) {
		if ( $id == null ) {
			//print_r($book);
			if ( $book != null  ) {
				$id = $book->book_id;
			} else {
				$id = 0;
			}
		}
			
		parent::__construct( $id, 'mbdb_book');
		
		
		$this->db_object = MBDB()->books_db;
		
		$this->postmeta_to_object = array(
			'_mbdb_summary'	=> 'summary',
			'_mbdb_excerpt'	=> 'excerpt',
			'_mbdb_excerpt_type'	=>	'excerpt_type',
			'_mbdb_kindle_preview'	=>	'kindle_preview',
			'_mbdb_additional_info'	=> 'additional_info',
			'_mbdb_subtitle'	=> 'subtitle',
			'_mbdb_cover'		=> 'cover',
			'_mbdb_cover_id'	=>	'cover_id',
			'_mbdb_published'	=> 'release_date',
			'_mbdb_publisherID'	=> 'publisher_id',
			'_mbdb_goodreads'	=> 'goodreads',
			'_mbdb_series_order'	=> 'series_order',
		);
		
		
		$this->id = $id;
		$this->book_id = $id;
		$this->title = '';
		$this->summary = '';
		$this->excerpt_type = 'text';
		$this->excerpt = '';
		$this->kindle_preview = '';
		$this->subtitle = '';
		$this->release_date = '';
		$this->goodreads = '';
		$this->series_order = '';
		$this->cover = '';
		$this->cover_id = '';
		$this->publisher_id = 0;
		$this->publisher = null;
		$this->additional_info = '';
		$this->slug = '';
		
		if ( $book == null ) {
			return $this;
		}
		
		$this->id = $book->book_id;
		$this->book_id = $book->book_id;
		$this->title = $book->post_title;
		$this->summary = $book->summary;
		$this->excerpt = $book->excerpt;
		$this->excerpt_type = $book->excerpt_type;
		$this->kindle_preview = $book->kindle_preview;
		$this->subtitle = $book->subtitle;
		$this->release_date = $book->release_date;
		$this->goodreads = $book->goodreads;
		$this->series_order = $book->series_order;
		$this->cover_id = $book->cover_id;
		$this->cover = $book->cover;
		$this->additional_info = $book->additional_info;
		$this->slug = $book->post_name;
		
		$this->set_publisher_id( $book->publisher_id );
	}
		
		public function has_kindle_preview() {
		
		return ( $this->excerpt_type == 'kindle' );
	}
	
	public function has_excerpt() {
		return ( $this->excerpt != '' || $this->has_kindle_preview());
	}
	
	// truncate trailing zeroes
	public function get_series_order() {
		if ( $this->series_order != '' ) {
			return floatval( $this->series_order );
		} else {
			return $this->series_order;
		}
	}
	
	public function is_published() {
		return ( $this->release_date != '' ) && ( strtotime($this->release_date) <= strtotime('now') );
	}
	
	
	
	public function has_publisher() {
		return ( $this->publisher != '' ) && ( $this->publisher instanceof Mooberry_Book_Manager_Publisher );
	}
	
	
	public function has_cover() {
		return $this->cover_id != ''; // && $this->cover_id != 0;
	}
	
	public function get_kindle_preview() {
		
		if ( strpos($this->kindle_preview, 'iframe' ) !== false || strpos($this->kindle_preview, 'href') !== false  ) {
			
			// iframe or link, convert into a ASIN
			preg_match_all('/asin=([a-zA-Z0-9]*)&/', $this->kindle_preview, $matches);
			
			if ( count( $matches ) > 1 ) {
				$this->kindle_preview = $matches[1][0];
				$this->db_object->save( array('_mbdb_kindle_preview' => $this->kindle_preview), $this->id );
			} else {
				$this->kindle_preview = '';
			}
		}
		return $this->kindle_preview;
	}
	
	public function get_cover_url( $size, $context ) {
		$image_src = '';
		
		if ( $this->has_cover() ) {
			
			$attachment_src = wp_get_attachment_image_src ( $this->cover_id, $size );
			
			if ( $attachment_src !== false && is_array( $attachment_src) ) {
				$image_src = $attachment_src[0];
			}
		}
	
		// get placeholder if necessary
		if ( !$image_src || $image_src == '' ) {
			if ( MBDB()->options->show_placeholder( $context ) ) {
				$image_src =  MBDB()->options->placeholder_image;
			}
		}
	
		if ( !$image_src || $image_src == '' ) {
			return '';
		} else {
			if (is_ssl()) {
				$image_src = preg_replace('/^http:/', 'https:', $image_src);
			}
			return $image_src;
		}
	}
	

	
	// if the publisher_id changes, set the publisher
	public function set_publisher_id( $publisher_id ) {
		if ( array_key_exists( $publisher_id, MBDB()->options->publishers ) ) {
			$this->publisher_id = $publisher_id;
			$this->publisher = clone MBDB()->options->publishers[ $publisher_id ];
		} else {
			$this->publisher_id = 0;
			$this->publisher = null;
		}
	}
	
	public function to_json() {
		$properties =  get_object_vars($this);
		$object = array();
		foreach ( $properties as $name => $value ) {
			$object[ $name ] =   $value ;
		}
		return json_encode( $object);
       
    }
	
	public function import ( $decoded ) {
		//error_log('beginning import ' . $decoded->title );
		$properties =  get_object_vars($this);
		foreach ( $properties as $name => $value ) {
			if ( !property_exists( $decoded, $name ) || in_array($name, array( 'id', 'db_object' ) ) ) {
				continue;
			}
			$this->$name =   $decoded->$name;	
		}
		
	}
	
	
	/**
	 * Magic __get function to dispatch a call to retrieve a private property
	 *
	 * @since 1.0
	 */
	public function __get( $key ) {

		if( method_exists( $this, 'get_' . $key ) ) {

			return call_user_func( array( $this, 'get_' . $key ) );

		} else {
			
			if ( property_exists( $this, $key ) ) {
				
				$ungettable_properties = array( 'db_object' );
				
				if ( !in_array( $key, $ungettable_properties ) ) {
				
					return $this->$key;

				}
		
			}
		
		}
	
		return new WP_Error( 'mbdb-invalid-property', sprintf( __( 'Can\'t get property %s', 'mooberry-book-manager' ), $key ) );
				
	}
	
	/**
	 * Magic __set function to dispatch a call to retrieve a private property
	 *
	 * @since 1.0
	 */
	public function __set( $key, $value ) {

		if( method_exists( $this, 'set_' . $key ) ) {

			return call_user_func( array( $this, 'set_' . $key ), $value );

		} else {

			if ( property_exists( $this, $key ) ) {
				
				$unsettable_properties = array('db_object', 'genres', 'series', 'tags', 'editors', 'illustrators', 'cover_artists', 'reviews', 'editions', 'buy_links', 'download_links' );
				
				if ( !in_array( $key, $unsettable_properties ) ) {
				
					$this->$key = $value;

				}
				
			}
		}
	
		return new WP_Error( 'mbdb-invalid-property', sprintf( __( 'Can\'t get property %s', 'mooberry-book-manager' ), $key ) );
		
	}	
	
	
	
 }
 