<?php
/**
 * Download Link Object
 *
 * @package     MBDB
 * @copyright   Copyright (c) 2015, Mooberry Dreams
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5 ?
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Mooberry_Book_Manager_Download_Link Class
 *
 * @since 3.5 ?
 */
class Mooberry_Book_Manager_Download_Link extends Mooberry_Book_Manager_Book_Link { 

	private $download_format;
	
	public function __construct( $data = array() ) {
		if ( array_key_exists( 'formatID', $data ) && array_key_exists( 'link', $data ) ) {
			parent::__construct( $data['formatID'], $data['link'] );
			$this->download_format = new Mooberry_Book_Manager_Download_Format( $data['formatID' ] );
		} else {
			parent::__construct(  );
			$this->download_format = new Mooberry_Book_Manager_Download_Format();
		
		}
	
	}
	
	public function to_json() {
		$properties =  get_object_vars($this);
		$object = array();
		foreach ( $properties as $name => $value ) {
			$object[ $name ] =   $value ;
		}
		$property = 'download_format';
		$object[ $property ] = $this->$property->to_json();
		return json_encode( $object);
       
    }
	
	public function import( $json_string ) {
		parent::import( $json_string );
		
		if ( $this->link == '' ) {
			return;
		}
		
		$decoded = json_decode( $json_string );
		$new_format = new Mooberry_Book_Manager_Download_Format();
		$new_format->import( $decoded->download_format );
		$this->download_format = $new_format;
		
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
			
			$ungettable_properties = array( );
			
			if ( property_exists( $this, $key ) ) {
				
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
				
				$unsettable_properties = array( 'download_format' );
				
				if ( !in_array( $key, $unsettable_properties ) ) {
				
					$this->$key = $value;

				}
				
			}
		}
	
		return new WP_Error( 'mbdb-invalid-property', sprintf( __( 'Can\'t set property %s', 'mooberry-book-manager' ), $key ) );
		
	}
}