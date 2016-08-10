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
 * MBDB_Edition Class
 *
 * @since 3.5 ?
 */
 class MBDB_Edition { 
 

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
	
	public function __construct( $edition ) {
		$this->format_id = mbdb_get_array_data( '_mbdb_format', $edition );
		$format = mbdb_get_array_data( $this->format_id, MBDB()->edition_formats );
		$this->format = mbdb_get_array_data( 'name', $format );
		$this->isbn = mbdb_get_array_data( '_mbdb_isbn', $edition );
		$this->language = mbdb_get_array_data( '_mbdb_language', $edition );
		$this->length = mbdb_get_array_data( '_mbdb_length', $edition );
		$this->height = mbdb_get_array_data( '_mbdb_height', $edition );
		$this->width = mbdb_get_array_data( '_mbdb_width', $edition );
		$this->unit = mbdb_get_array_data( '_mbdb_unit', $edition );
		$this->retail_price = mbdb_get_array_data( '_mbdb_retail_price', $edition );
		$this->currency = mbdb_get_array_data( '_mbdb_currency', $edition );
		$this->edition_title = mbdb_get_array_data( '_mbdb_edition_title', $edition );
		
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
				
				$unsettable_properties = array('format' );
				
				if ( !in_array( $key, $unsettable_properties ) ) {
				
					$this->$key = $value;

				}
				
			}
		}
	
		return new WP_Error( 'mbdb-invalid-property', sprintf( __( 'Can\'t get property %s', 'mooberry-book-manager' ), $key ) );
		
	}
}