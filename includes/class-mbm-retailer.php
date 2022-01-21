<?php
/**
 * Retailer Object
 *
 * @package     MBDB
 * @copyright   Copyright (c) 2015, Mooberry Dreams
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5 ?
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Mooberry_Book_Manager_Retailer Class
 *
 * @since 3.5 ?
 */
 class Mooberry_Book_Manager_Retailer {

	private $name;
	private $logo;
	private $affiliate_code;
	private $affiliate_position;
	private $id;
	private $is_amazon;
	protected $button_background_color;
	protected $button_text_color;
	protected $button_or_image;

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

		$this->name = '';
		$this->logo = '';
		$this->affiliate_code = '';
		$this->affiliate_position = '';
		$this->id = '';
		$this->is_amazon = false;
		$this->button_background_color = '';
		$this->button_text_color = '';
		$this->button_or_image = '';

		if ( is_array( $data ) ) {
			if ( array_key_exists( 'uniqueID', $data ) ) {
				$this->id = $data['uniqueID'];
				if (array_key_exists( 'image', $data ) ) {
					$this->logo = $data['image'];
				}
				if ( array_key_exists( 'name', $data ) ) {
					$this->name = $data['name'];
				}
				if (array_key_exists( 'affiliate_code', $data ) ) {
					$this->affiliate_code = $data['affiliate_code'];
				}
				if ( array_key_exists( 'affiliate_position', $data ) ) {
					$this->affiliate_position = $data['affiliate_position'];
				}
				if ( array_key_exists( 'retailer_button_color', $data)) {
					$this->button_background_color = $data['retailer_button_color'];
				}
				if ( array_key_exists( 'retailer_button_color_text', $data)) {
					$this->button_text_color = $data['retailer_button_color_text'];
				}
				if ( array_key_exists( 'retailer_button_image', $data)) {
					$this->button_or_image = $data['retailer_button_image'];
				}
			}
		} else {
			//$data = absint($data);
			if ( $data != 0 ) {
				if ( array_key_exists( $data, MBDB()->options->retailers ) ) {
					$this->id = $data;
					$this->name = MBDB()->options->retailers[$data]->name;
					$this->logo = MBDB()->options->retailers[$data]->logo;
					$this->affiliate_code = MBDB()->options->retailers[$data]->affiliate_code;
					$this->affiliate_position = MBDB()->options->retailers[$data]->affiliate_position;
					$this->button_background_color = MBDB()->options->retailers[$data]->button_background_color;
					$this->button_text_color = MBDB()->options->retailers[$data]->button_text_color;
					$this->button_or_image = MBDB()->options->retailers[$data]->button_or_image;

				}
			}
		}

		if ( strtolower( substr($this->name, 0, 6)) == 'amazon' ) {
			$this->is_amazon = true;
		}
		/*
		$publisher = mbdb_get_array_data( $id , MBDB()->publishers );

		$this->id = $id;
		$this->website = mbdb_get_array_data( 'website', $publisher );
		$this->name = mbdb_get_array_data( 'name', $publisher );
		*/

	}

	public function to_json() {
		$properties =  get_object_vars($this);
		$object = array();
		foreach ( $properties as $name => $value ) {
			$object[ $name ] =   $value ;
		}
		return json_encode( $object);

    }

	public function import( $json_string ) {
		$retailer = json_decode( $json_string );
		$properties =  get_object_vars($this);
		foreach ( $properties as $name => $value ) {
				$this->$name =   $retailer->$name;
		}

		// if retailer exists, load it
		$existing_retailers = MBDB()->options->retailers;
		foreach ( $existing_retailers as $existing_retailer ) {
			if ( $existing_retailer->name == $retailer->name ) {
				$this->id = $existing_retailer->id;
				return;
			}
		}
		// otherwise, add to database
		$this->id =	MBDB()->helper_functions->uniqueID_generator();
		MBDB()->options->add_retailer( $this );

	}


	public function has_affiliate_code() {
		return ($this->affiliate_code != '' );
	}

	public function get_affiliate_position() {
		if ( $this->has_affiliate_code() ) {
			if ( $this->affiliate_position == '' ) {
				$this->affiliate_position = 'after';
			}
			return $this->affiliate_position;
		}
		return '';
	}

	public function has_logo_image() {
		return ( $this->logo != '' );
	}

	public function get_logo_url() {

	}

	public function uses_logo() {
		return MBDB()->options->retailer_buttons == 'individual' && $this->button_or_image == 'image';
	}

	public function uses_button() {
		return MBDB()->options->retailer_buttons == 'matching' || $this->button_or_image == 'button';
	}

	public function get_button_color() {
		if ( $this->uses_button() ) {
			if ( MBDB()->options->retailer_buttons == 'matching' ) {
				return MBDB()->options->retailer_buttons_background_color;
			}

			return $this->button_background_color;
		}
		return '';

	}

	public function get_button_color_text() {
		if ( $this->uses_button() ) {
			if ( MBDB()->options->retailer_buttons == 'matching' ) {
				return MBDB()->options->retailer_buttons_text_color;
			}
			return $this->button_text_color;
		}
		return '';

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

				$unsettable_properties = array( 'id' );

				if ( !in_array( $key, $unsettable_properties ) ) {

					$this->$key = $value;
					return true;

				}

			}
		}

		return new WP_Error( 'mbdb-invalid-property', sprintf( __( 'Can\'t get property %s', 'mooberry-book-manager' ), $key ) );

	}


}
