<?php
/**
 * Publisher Object
 *
 * @package     MBDB
 * @copyright   Copyright (c) 2015, Mooberry Dreams
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5 ?
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Mooberry_Book_Manager_Publisher Class
 *
 * @since 3.5 ?
 */
 class Mooberry_Book_Manager_Publisher extends Mooberry_Book_Manager_CPT_Object {


	private $name;
	private $website;
	private $logo;
	private $logo_id;


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

		parent::__construct($data);

		$this->postmeta_to_object = array(
			 '_mbdb_publisher_website'                => 'website',
				'image' =>  'logo',
			'image_id'  =>  'logo_id',
		);

		$this->db_object = new MBDB_DB_Publisher();
		$this->website = '';
		$this->name = '';
		$this->logo = '';
		$this->logo_id = 0;


		if ( is_array( $data ) ) {
			// TODO: get publisher by array of data
			// is this needed??
		} else {
			$id = absint($data);
			if ( $id != 0 ) {
				 $publisher = $this->db_object->get( $id );
				 if ( $publisher) {
					 $this->website = $publisher->website;
					 $this->name = $publisher->post_title;
					 $this->logo = $publisher->logo;
					 $this->logo_id = $publisher->logo_id;
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
		return json_encode( $object);

    }

	public function import( $json_string ) {
		$publisher = json_decode( $json_string );
		$properties =  get_object_vars($this);
		foreach ( $properties as $name => $value ) {
				$this->$name =   $publisher->$name;
		}

		// TODO: import

		/*// if publisher exists, load it
		$existing_publishers = MBDB()->options->publishers;
		foreach ( $existing_publishers as $existing_publisher ) {
			if ( $existing_publisher->name == $publisher->name ) {
				$this->id = $existing_publisher->id;
				return;
			}
		}
		// otherwise, add to database
		$this->id =	MBDB()->helper_functions->uniqueID_generator();
		MBDB()->options->add_publisher( $this );*/

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
