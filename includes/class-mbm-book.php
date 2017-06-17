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
 * Mooberry_Book_Manager_Book Class
 *
 * @since 3.5 ?
 */
 class Mooberry_Book_Manager_Book extends Mooberry_Book_Manager_Book_Basic { 
 
	//private $book_idbook_id;
	
	protected $editions;
	protected $reviews;
	protected $buy_links;
	protected $download_links;
	protected $genres;
	protected $tags;
	protected $series;
	protected $editors;
	protected $illustrators;
	protected $cover_artists;
	
	
	
	public function __construct( $id = 0 ) {
		
		//parent::__construct( $id, 'mbdb_book');
		
		$this->db_object = MBDB()->books_db;
		
		$this->editions = array();
		$this->reviews = array();
		$this->buy_links = array();
		$this->download_links = array();
		$this->genres = array();
		$this->tags = array();
		$this->series = array();
		$this->editors = array();
		$this->illustrators = array();
		$this->cover_artists = array();
		
		
	
		if ( $id == '0' ) {
			parent::__construct();
	
			return;
			
		}
		
		$book = wp_cache_get( $id, 'mbdb_book' );
		
		if ( $book !== false ) {
	
			$properties = get_object_vars( $book );
			
			foreach ( $properties as $property => $value ) {
				$this->$property = $value;
			}
			
			return $this;
		}
		
		
		$book = $this->db_object->get( $id );	
		// if ( $book == null ) {
			// $book_id = null;
		// } else {
			
			// $book_id = $book->book_id;
		// }
		if ( !is_numeric($id) ) {
			if ( $book != null ) {
				$id = $book->ID;
			} else {
				$id = null;
			}
		}
		
		parent::__construct( $book, $id );
		
		
		if ( $book != null ) {           
			
			
			foreach ( $book->editions as $edition ) {
				// last one could be blank because there are auto-selected
				// items in formats so the allow_last_blank function
				// doesn't work
				
				if ( is_array($edition) && array_key_exists( 'format_id', $edition ) && $edition['format_id'] != '' && $edition['format_id'] != 0 ) {
					$this->editions[] = new Mooberry_Book_Manager_Edition( $edition );
				}
			}
			
			foreach ($book->reviews as $review ) {
				if ( is_array($review) && array_key_exists( 'review', $review ) && $review[ 'review' ] != '' ) { 
					$this->reviews[] = new Mooberry_Book_Manager_Review( $review );
				}
			}
		
			foreach ( $book->buy_links as $link ) {
				if ( is_array($link) && array_key_exists( 'link', $link ) && $link[ 'link' ] != '' && $link[ 'link' ] != '0' ) { 
					$this->buy_links[] = new Mooberry_Book_Manager_Buy_Link( $link );
				}
			}
			
			foreach ( $book->download_links as $link ) {
				if ( is_array($link) && array_key_exists( 'link', $link ) && $link[ 'link' ] != '' && $link[ 'link' ] != '0' ) { 
					$this->download_links[] = new Mooberry_Book_Manager_Download_Link( $link );
				}
			}
			
			$this->genres = $book->genres;
			$this->series = $book->series;
			$this->tags = $book->tags;
			$this->editors = $book->editors;
			$this->illustrators = $book->illustrators;
			$this->cover_artists = $book->cover_artists;
			
			
		}				
		wp_cache_set( $id, $this, 'mbdb_book');
		
	}
	
	public function is_standalone() {
		return ( $this->series == '' ) || ( count($this->series) == 0 );
	}
	
	public function has_editions() {
		return ( count( $this->editions ) > 0 );
	}
	
	public function has_reviews() {
		return ( count( $this->reviews ) > 0 );
	}
	
	public function has_buy_links() {
		return ( count( $this->buy_links ) > 0 );
	}
	
	public function has_download_links() {
		return ( count( $this->download_links ) > 0 );
	}
	
	public function has_series() {
		return !$this->is_standalone();
	}
	
	public function has_genres() {
		return ( $this->genres != '' ) && ( count( $this->genres) >0 );
	}
	
	public function has_tags() {
		return ( $this->tags != '' ) && ( count( $this->tags) >0 );
	}
	
	public function has_editors() {
		return ( $this->editors != '' ) && ( count( $this->editors) >0 );
	}
	
	public function has_illustrators() {
		return ( $this->illustrators != '' ) && ( count( $this->illustrators) >0 );
	}
	
	public function has_cover_artists() {
		return ( $this->cover_artists != '' ) && ( count( $this->cover_artists) >0 );	
	}
	
	
	public function get_taxonomy_list( $terms, $delimiter ) {
		$output = '';
		foreach ( $terms as $term ) {
			$output .= $term->name . $delimiter;
		}
		// remove last delimiter and space
		return rtrim( $output, $delimiter  );	
	}
	
	public function get_series_list( $delimiter = ', ' ) {
		return $this->get_taxonomy_list( $this->series, $delimiter);
	}
	
	public function get_genre_list( $delimiter = ', ') {
		return $this->get_taxonomy_list( $this->genres, $delimiter);
	}
	
	public function get_retailer_list( $delimiter = ', ' ) {
		$output = '';
		foreach ( $this->buy_links as $buy_link ) { 
			$output .= $buy_link->retailer->name . $delimiter;
		}
		return rtrim( $output, $delimiter );
	}
	
	public function get_edition_format_list( $delimiter = ', ' ) {
		$output = '';
		foreach ( $this->editions as $edition ) { 
			$output .= $edition->format->name . $delimiter;
		}
		return rtrim( $output, $delimiter );
	}
	
	public function to_json() {
		//parent::to_json();
		$properties =  get_object_vars($this);
		$object = array();
		foreach ( $properties as $name => $value ) {
			$object[ $name ] =   $value ;
		}
		
		$objects = array( 'editions', 'reviews', 'buy_links', 'download_links', 'publisher');
		foreach ( $objects as $property ) {
			$object[ $property ] = array();
			if ( is_array( $this->$property ) ) {
				foreach ( $this->$property as $obj ) {
					$object[ $property ][] = $obj->to_json();
				}
			}
		}
		
		return json_encode( $object);
       
    }
	
	public function import ( $decoded ) {
		//error_log('beginning import ' . $decoded->title );
		
		parent::import( $decoded );
		
		$properties =  get_object_vars($this);
		//error_log(print_r($properties, true));
		$objects = array( 'editions', 'reviews', 'buy_links', 'download_links', 'publisher', 'cover', 'cover_id', 'publisher_id', 'db_object', 'id' );
		foreach ( $properties as $name => $value ) {
			if ( !property_exists( $decoded, $name ) ) {
				continue;
			}
			if ( !in_array( $name, $objects ) ) {
				$this->$name =   $decoded->$name;	
			} else {
				switch  ($name)  {
					case 'editions':
						//error_log('beginning import editions ' . $decoded->title );
						$this->editions = array();
						foreach ( $decoded->editions as $edition  ) {
							$new_edition = new Mooberry_Book_Manager_Edition();
							$new_edition->import( $edition );
							if ( $new_edition->format->name != '' ) {
								$this->editions[] = $new_edition;
							}
						}
							
						break;
					case 'reviews':
					$this->reviews = array();
					//error_log('beginning reviews ' . $decoded->title );
						foreach( $decoded->reviews as $review ) {
							$new_review = new Mooberry_Book_Manager_Review();
							$new_review->import( $review );
							if ( $new_review->review != '' ) {
								$this->reviews[] = $new_review;
							}
						}
						break;
					case 'buy_links':
					$this->buy_links = array();
					//error_log('beginning buy links ' . $decoded->title );
						foreach( $decoded->buy_links as $buy_link ) {
							$new_buy_link = new Mooberry_Book_Manager_Buy_Link();
							$new_buy_link->import( $buy_link );
							//error_log($new_buy_link->retailer->name );
							if ( $new_buy_link->retailer->name != '' ) {
								$this->buy_links[] = $new_buy_link;
							}
						}
						break;
					case 'download_links':
					$this->download_links = array();
					//error_log('beginning import dwonlaod links' . $decoded->title );
						foreach( $decoded->download_links as $download_link ) {
							$new_download_link = new Mooberry_Book_Manager_Download_Link();
							$new_download_link->import( $download_link );
							if ( $new_download_link->download_format->name != '' ) {
								$this->download_links[] = $new_download_link;
							}
						}
						break;
					case 'publisher':
					//error_log('beginning import publisher' . $decoded->title );
						if ( $decoded->publisher_id != 0 ) {
							$new_publisher = new Mooberry_Book_Manager_Publisher(  );
							$new_publisher->import ( $decoded->publisher );
							$this->set_publisher_id( $new_publisher->id );
						}					
						break;
					case 'cover':
					//error_log('beginning import cover' . $decoded->title );
						// Need to require these files
						if ( !function_exists('media_handle_upload') ) {
							require_once(ABSPATH . "wp-admin" . '/includes/image.php');
							require_once(ABSPATH . "wp-admin" . '/includes/file.php');
							require_once(ABSPATH . "wp-admin" . '/includes/media.php');
						}
						$url = $decoded->cover;
						if ( $url == '' ) {
							$this->cover_id = 0;
							$this->cover = '';
							
							break;
						}
						$tmp = download_url( $url );
						if( is_wp_error( $tmp ) ){
							// download failed, handle error
						}
						$post_id = 0;
						$file_array = array();

						// Set variables for storage
						// fix file filename for query strings
						preg_match('/[^\?]+\.(jpg|jpe|jpeg|gif|png)/i', $url, $matches);
						$file_array['name'] = basename($matches[0]);
						$file_array['tmp_name'] = $tmp;

						// If error storing temporarily, unlink
						if ( is_wp_error( $tmp ) ) {
							@unlink($file_array['tmp_name']);
							$file_array['tmp_name'] = '';
						}

						// do the validation and storage stuff
						$id = media_handle_sideload( $file_array, $post_id);

						// If error storing permanently, unlink
						if ( is_wp_error($id) ) {
							@unlink($file_array['tmp_name']);
							$this->cover = '';
							$this->cover_id = 0;
						}
						$this->cover = wp_get_attachment_url( $id );
						$this->cover_id = $id;
						
						// update sizes
						$fullsizepath = get_attached_file( $id );

						if ( false !== $fullsizepath && file_exists($fullsizepath) ) {
							wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $fullsizepath ) );
						}
						break;
						
				} 
				
			}				
		}
		//error_log('about to save ' . $this->title );
		
		$success = $this->db_object->save_all( $this );
		//error_log('just saved ' . $this->title );
		if ( is_array($success) ) {
			error_log(print_r($success, true ));
		}
		return $success;
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
 