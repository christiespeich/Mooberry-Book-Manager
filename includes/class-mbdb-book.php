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
 * MBDB_Book Class
 *
 * @since 3.5 ?
 */
 class MBDB_Book { 
 
	private $book_id = 0;
	private $title = '';
	private $summary = '';
	private $excerpt = '';
	private $subtitle = '';
	private $release_date = '';
	private $goodreads = '';
	private $series_order = '';
	private $cover = '';
	private $cover_id = '';
	private $publisher = '';
	private $additional_info = '';
	private $editions = '';
	private $reviews = '';
	private $buy_links = '';
	private $download_links = '';
	private $genres = '';
	private $tags = '';
	private $series = '';
	private $editors = '';
	private $illustrators = '';
	private $cover_artists = '';
	
	private $db_object;
	
	public function __construct( $id ) {
		
		$this->book_id = $id;
		$this->db_object = new MBDB_DB_Books();
		
		$book = $this->db_object->get( $id );
		
		if ( $book == null ) {
			return;
		}
		
		$this->title = $book->post_title;
		$this->summary = $book->summary;
		$this->excerpt = $book->excerpt;
		$this->subtitle = $book->subtitle;
		$this->release_date = $book->release_date;
		$this->goodreads = $book->goodreads;
		$this->series_order = $book->series_order;
		$this->cover_id = $book->cover_id;
		$this->cover = $book->cover;
		$this->additional_info = $book->additional_info;
		
		$this->genres = get_the_terms( $id, 'mbdb_genre' );
		$this->series = get_the_terms( $this->book_id, 'mbdb_series' );
		$this->tags = get_the_terms( $this->book_id, 'mbdb_tag' );
		$this->editors = get_the_terms( $this->book_id, 'mbdb_editor' );
		$this->illustrators = get_the_terms( $this->book_id, 'mbdb_illustrator' );
		$this->cover_artists = get_the_terms( $this->book_id, 'mbdb_cover_artist' );
		
		if ( $book->publisher_id != 0 && $book->publisher_id != '' ) {
			$this->publisher =  new MBDB_Publisher( $book->publisher_id );
		}
		
		$this->load_editions();
		$this->load_reviews();
		$this->buy_links = $this->load_buy_links( );
		$this->download_links = $this->load_download_links();
		
	}
	
	public function is_published() {
		
		return ( $this->release_date != '' ) && ( strtotime($this->release_date) <= strtotime('now') );
	}
	
	public function is_standalone() {
		
		return ( $this->series == '' ) || ( count($this->series) == 0 );
	}
	
	public function has_editions() {
		//because editions have default language, currency, and unit
		// there's always at least one eiditon
		// the only way to check that there aren't any editions is to check format id
		if ( $this->editions == '' || count($this->editions) == 0) {
			return false;
		}
		
		return $this->editions[0]->format_id != '';
		
	}
	
	public function has_reviews() {
		return ( $this->reviews != '' ) && ( count( $this->reviews ) > 0 );
	}
	
	public function has_buy_links() {
		return ( $this->buy_links != '' ) && ( count( $this->buy_links ) > 0 );
	}
	
	public function has_download_links() {
		return ( $this->download_links != '' ) && ( count( $this->download_links ) > 0 );
	}
	
	public function has_publisher() {
		return ( $this->publisher != '' ) && ( $this->publisher instanceof MBDB_Publisher );
	}
	
	public function has_cover() {
		return $this->cover_id != '';
	}
	
	public function get_cover_html( $size, $context, $class ) {
		
		$image_src = '';
		
		if ( $this->has_cover() ) {
	
			$attachment_src = wp_get_attachment_image_src ( $this->cover_id, $size );
			if ( $attachment_src !== false) {
				$image_src = $attachment_src[0];
			}
		}
	
		// get placeholder if necessary
		if ( !$image_src || $image_src == '' ) {
			$image_src = mbdb_get_placeholder_cover( $context );
		}
	
		if ( !$image_src || $image_src == '' ) {
			return '';
		} 
		
		$image_html = '<img ';
		
		// get alt text		
		$alt = mbdb_get_alt_text( $this->cover_id,  __('Book Cover:', 'mooberry-book-manager') . ' ' . $this->title );
		$image_html .= 'src="' . esc_url($image_src) . '" ' . $alt . '>';
	
		return apply_filters('mbdb_book_cover_html',  '<span class="' . $class . '">' . $image_html . '</span>');
	}
	
	public function save() {
		
		$this->db_object->save_object( $this );
	}
	
	
	private function load_editions() {
		$editions = get_post_meta( $this->book_id, '_mbdb_editions', true );
		if ( !is_array( $editions ) ) {
			return array();
		}
		foreach ( $editions as $edition ) {
			$this->editions[] = new MBDB_Edition( $edition );
		}
	}
	
	private function load_reviews() {
		$reviews = get_post_meta( $this->book_id, '_mbdb_reviews', true );
		if ( !is_array( $reviews ) ) {
			return array();
		}
		foreach ($reviews as $review ) {
			$this->reviews[] = new MBDB_Review( $review );
		}
	}
	
	private function load_buy_links( ) {
		$links = get_post_meta( $this->book_id, '_mbdb_buylinks', true );
		if ( !is_array( $links ) ) {
			return array();
		}
		foreach ( $links as $link ) {
			$book_links[] = new MBDB_Book_Link( array(
													'id'	=>	$link['_mbdb_retailerID'],
													'link'	=>	$link['_mbdb_buylink']
												),
												MBDB()->retailers
											);
		}
		return $book_links;
	}
	
	private function load_download_links( ) {
		$links = get_post_meta( $this->book_id, '_mbdb_downloadlinks', true );
		if ( !is_array( $links ) ) {
			return array();
		}
		foreach ( $links as $link ) {
			$book_links[] = new MBDB_Book_Link( array(
													'id'	=>	$link['_mbdb_formatID'],
													'link'	=>	$link['_mbdb_downloadlink']
												),
												MBDB()->formats
											);
		}
		return $book_links;
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

			return call_user_func( array( $this, 'set_' . $key ), $key, $value );

		} else {

			if ( property_exists( $this, $key ) ) {
				
				$unsettable_properties = array('db_object', 'genres', 'series', 'tags', 'editors', 'illustrators', 'cover_artists', 'publishers', 'reviews', 'editions', 'buy_links', 'download_links' );
				
				if ( !in_array( $key, $unsettable_properties ) ) {
				
					$this->$key = $value;

				}
				
			}
		}
	
		return new WP_Error( 'mbdb-invalid-property', sprintf( __( 'Can\'t get property %s', 'mooberry-book-manager' ), $key ) );
		
	}
	
 }