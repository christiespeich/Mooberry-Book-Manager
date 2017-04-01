<?php
/**
 * Buy Link Object
 *
 * @package     MBDB
 * @copyright   Copyright (c) 2015, Mooberry Dreams
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5 ?
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Mooberry_Book_Manager_Buy_Link Class
 *
 * @since 3.5 ?
 */
class Mooberry_Book_Manager_Buy_Link extends Mooberry_Book_Manager_Book_Link { 

	private $retailer;
	
	public function __construct( $data = array()) {
		
		
		
		if ( array_key_exists( 'retailerID', $data ) && array_key_exists( 'link', $data ) ) {
			parent::__construct( $data['retailerID'], $data['link'] );
			$this->retailer = new Mooberry_Book_Manager_Retailer( $data['retailerID'] );
		} else {
			parent::__construct( );
			$this->retailer = new Mooberry_Book_Manager_Retailer();
		}
	}
	
	public function to_json() {
		$properties =  get_object_vars($this);
		$object = array();
		foreach ( $properties as $name => $value ) {
			$object[ $name ] =   $value ;
		}
		$property = 'retailer';
		$object[ $property ] = $this->$property->to_json();
		
		return json_encode( $object);
       
    }
	
	public function import( $json_string ) {
		parent::import( $json_string );
		
		if ( $this->link == '' ) {
			return;
		}
		$decoded = json_decode( $json_string );
		
		$new_retailer = new Mooberry_Book_Manager_Retailer();
		$new_retailer->import( $decoded->retailer );
		$this->retailer = $new_retailer;
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
				
				$unsettable_properties = array( 'retailer' );
				
				if ( !in_array( $key, $unsettable_properties ) ) {
				
					$this->$key = $value;

				}
				
			}
		}
	
		return new WP_Error( 'mbdb-invalid-property', sprintf( __( 'Can\'t get property %s', 'mooberry-book-manager' ), $key ) );
		
	}
}
