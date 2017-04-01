<?php
/**
 * Edition Object
 *
 * @package     MBDB
 * @copyright   Copyright (c) 2015, Mooberry Dreams
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5 ?
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Mooberry_Book_Manager_Edition Class
 *
 * @since 3.5 ?
 */
 class Mooberry_Book_Manager_Edition { 
 
	private $format;
	private $format_id;
	private $isbn;
	private $language;
	private $length;
	private $height;
	private $width;
	private $unit;
	private $retail_price;
	private $currency;
	private $edition_title;
	
	// data is either an id to be loaded from the database
	// or an array of data already pulled from the databsae in the format
	//	['uniqueID'] = 
	//  ['website'] =
	//  ['name'] =
	//
	// if data == 0 then this is a new publisher not in the database
	// all values are initialized to empty strings
	//
	public function __construct( $data = 0 ) {
		
		$this->format 		= '';
		$this->format_id 	= '';
		$this->isbn 		= '';
		$this->language 	= '';
		$this->length 		= '';
		$this->height 		= '';
		$this->width 		= '';
		$this->unit 		= '';
		$this->retail_price = '';
		$this->currency 	= '';
		$this->edition_title = '';
		
		if ( is_array( $data ) ) {
			// TODO: get format based on format_id
			//	if (array_key_exists( 'format', $data ) ) {
			//		$this->format = $data['format'];
			//	}
			////error_log(print_r($data, true));
				if ( array_key_exists( 'format_id', $data ) ) { 
					$this->format_id = $data['format_id'];
					$this->format = new Mooberry_Book_Manager_Edition_Format( $this->format_id );
				}
				
				if (array_key_exists( 'isbn', $data ) ) {
					$this->isbn = $data['isbn'];
				}
				if ( array_key_exists( 'language', $data ) ) { 
					$this->language = $data['language'];
				}
				if (array_key_exists( 'length', $data ) ) {
					$this->length = $data['length'];
				}
				if ( array_key_exists( 'height', $data ) ) { 
					$this->height = $data['height'];
				}
				if (array_key_exists( 'width', $data ) ) {
					$this->width = $data['width'];
				}
				if ( array_key_exists( 'unit', $data ) ) { 
					$this->unit = $data['unit'];
				}
				if ( array_key_exists( 'retail_price', $data ) ) { 
					$this->retail_price = $data['retail_price'];
				}
				if (array_key_exists( 'currency', $data ) ) {
					$this->currency = $data['currency'];
				}
				if ( array_key_exists( 'edition_title', $data ) ) { 
					$this->edition_title = $data['edition_title'];
				}
		}/* else {
			$data = absint($data);
			if ( $data != 0 ) {
				
					// TODO: get format based on format_id
					//$this->format 		= MBDB()->editions[$data]->format;
					$this->format_id 	= MBDB()->editions[$data]->format_id;
					$this->isbn 		= MBDB()->editions[$data]->isbn;
					$this->language 	= MBDB()->editions[$data]->language;
					$this->length 		= MBDB()->editions[$data]->length;
					$this->height 		= MBDB()->editions[$data]->height;
					$this->width 		= MBDB()->editions[$data]->width;
					$this->unit 		= MBDB()->editions[$data]->unit;
					$this->retail_price = MBDB()->editions[$data]->retail_price;
					$this->currency 	= MBDB()->editions[$data]->currency;
					$this->edition_title = MBDB()->editions[$data]->edition_title;
				}
			} 
		} */
	}

	public function to_json() {
		$properties =  get_object_vars($this);
		$object = array();
		foreach ( $properties as $name => $value ) {
			$object[ $name ] =   $value ;
		}
		$property = 'format';
		$object[ $property ] = $this->$property->to_json();
		
		return json_encode( $object);
       
    }
	
	public function import( $json_string ) {
		$decoded = json_decode( $json_string );
		$properties =  get_object_vars($this);
		foreach ( $properties as $name => $value ) {
			if ( $name != 'format' )  {
				$this->$name =   $decoded->$name;
			} else {
				$new_format = new Mooberry_Book_Manager_Edition_Format();
				$new_format->import( $decoded->format );
				$this->format = $new_format;
			}
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
				
				$ungettable_properties = array(  );
				
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
				
				$unsettable_properties = array('format', 'id' );
				
				if ( !in_array( $key, $unsettable_properties ) ) {
				
					$this->$key = $value;

				}
				
			}
		}
	
		return new WP_Error( 'mbdb-invalid-property', sprintf( __( 'Can\'t get property %s', 'mooberry-book-manager' ), $key ) );
		
	}
}