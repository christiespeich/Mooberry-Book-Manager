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
	private $sku;
	private $doi;
	private $language;
	private $length;
	private $height;
	private $width;
	private $unit;
	private $retail_price;
	private $currency;
	private $edition_title;
	private $custom_fields;

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

		$this->format        = '';
		$this->format_id     = '';
		$this->isbn          = '';
		$this->sku           = '';
		$this->doi           = '';
		$this->language      = '';
		$this->length        = '';
		$this->height        = '';
		$this->width         = '';
		$this->unit          = '';
		$this->retail_price  = '';
		$this->currency      = '';
		$this->edition_title = '';

		if ( is_array( $data ) ) {
			if ( array_key_exists( 'format_id', $data ) ) {
				$this->format_id = $data['format_id'];
				$this->format    = new Mooberry_Book_Manager_Edition_Format( $this->format_id );
			}

			$fields = array(
				'isbn',
				'sku',
				'doi',
				'language',
				'length',
				'height',
				'width',
				'unit',
				'retail_price',
				'currency',
				'edition_title',
			);

			foreach ( $data as $key => $value ) {
				if ( in_array( $key, $fields ) ) {
					$this->$key = $value;
				} else {
					// anything else is a custom field
					$this->custom_fields[ $key ] = $value;
				}
			}
		}
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
