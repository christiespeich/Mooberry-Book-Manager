<?php
/**
 * Edition_Format Object
 *
 * @package     MBDB
 * @copyright   Copyright (c) 2015, Mooberry Dreams
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5 ?
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Mooberry_Book_Manager_Edition_Format Class
 *
 * @since 3.5 ?
 */
 class Mooberry_Book_Manager_Edition_Format { 
 
	private $name;
	private $id;
	
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
		$this->id = '';
		
		if ( is_array( $data ) ) {
			if ( array_key_exists( 'uniqueID', $data ) ) {
				$this->id = $data['uniqueID'];
				if ( array_key_exists( 'name', $data ) ) { 
					$this->name = $data['name'];
				}
			}
		} else {
			//$data = absint($data);
			if ( $data != 0 ) {
				if ( array_key_exists( $data, MBDB()->options->edition_formats ) ) {
					$this->id = $data;
					$this->name = MBDB()->options->edition_formats[$data]->name;
				}
			}
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
	
	public function import ( $json_string ) {
		$format = json_decode( $json_string );
		$properties =  get_object_vars($this);
		foreach ( $properties as $name => $value ) {	
				$this->$name =   $format->$name;
		}
		//if format exists, load it
		$existing_formats = MBDB()->options->edition_formats;
		foreach ( $existing_formats as $existing_format ) {
			if ( $existing_format->name == $format->name )  {
				$this->id = $existing_format->id;
				return;
			}
		}
		// otherwise write it to the database
		$this->id = MBDB()->helper_functions->uniqueID_generator();
		MBDB()->options->add_edition_format( $this );
			
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