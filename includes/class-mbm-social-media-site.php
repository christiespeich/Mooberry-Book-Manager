<?php
class Mooberry_Book_Manager_Social_Media_Site {

	private $name;
	private $logo;
	private $logo_id;
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
		$this->logo = '';
		$this->logo_id = 0;
		$this->id = '';
		
		if ( is_array( $data ) ) {
			if ( array_key_exists( 'uniqueID', $data ) ) {
				$this->id = $data['uniqueID'];
				if (array_key_exists( 'image', $data ) ) {
					$this->logo = $data['image'];
				}
				if (array_key_exists( 'image_id', $data ) ) {
					$this->logo_id = $data['image_id'];
				}
				if ( array_key_exists( 'name', $data ) ) { 
					$this->name = $data['name'];
				}
			}
		} else {
			$data = absint($data);
			if ( $data != 0 ) {
				if ( array_key_exists( $data, MBDB()->options->social_media_sites ) ) {
					$this->id = $data;
					$this->name = MBDB()->options->social_media_sites[$data]->name;
					$this->logo = MBDB()->options->social_media_sites[$data]->logo;
					$this->logo_id = MBDB()->options->social_media_sites[$data]->logo_id;
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
		$site = json_decode( $json_string );
		$properties =  get_object_vars($this);
		foreach ( $properties as $name => $value ) {	
				$this->$name =   $site->$name;
		}
		
		// if retailer exists, load it
		$existing_sites = MBDB()->options->social_media_sites;
		foreach ( $existing_sites as $existing_site ) {
			if ( $existing_site->name == $site->name ) {
				$this->id = $existing_site->id;
				return;
			}
		}
		// otherwise, add to database
		$this->id =	MBDB()->helper_functions->uniqueID_generator();
		MBDB()->options->add_social_media_site( $this );
		
	}
	
	public function has_logo_image() {
		return ( $this->logo != '' );
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
