<?php

// base class that extends functionality for CPTs that save to a custom
// CPTs that don't save to a custom table can use this base class as their data object
abstract class Mooberry_Book_Manager_CPT_Object  {

	protected $postmeta_to_object;
	 protected $db_object;  // type = interface iMooberry_Book_Manager_Data_Storage_Behavior
	protected $permalink;
	protected $post_type;
	protected $id;

	public function __construct( $id = 0, $post_type = '' ) {
		$this->postmeta_to_object = array();
		if ( !is_array($id) ) {
			$this->permalink = get_the_permalink( $id );
			$this->id = $id;
		}
	}

	public function get_by_postmeta( $postmeta ) {
		if ( $this->db_object->override_data_access( $postmeta ) ) {
			if ( array_key_exists( $postmeta, $this->postmeta_to_object ) ) {
				$property = $this->postmeta_to_object[ $postmeta ];
				if ( property_exists( $this, $property ) ) {
					return $this->__get($property);
				}
			}
		}
		return false;
	}

	public function set_by_postmeta( $postmeta, $value ) {
		// returns true if this should override CMB's saving
		if ($this->db_object->override_data_access(  $postmeta ) ) {
			$property = false;
			if ( array_key_exists( $postmeta, $this->postmeta_to_object ) ) {
				$property = $this->postmeta_to_object[ $postmeta ];
			}
			if ( $property !== false && property_exists( $this, $property ) ) {
				$this->{$property} = $value;
				return true;
			}
		}
		return false;
	}

	public function save( ) {

		$data = array();
		foreach ( $this->postmeta_to_object as $postmeta => $property ) {
			if ( property_exists( $this, $property ) ) {
				$data[ $postmeta ] = $this->{$property};
			}
		}
		return $this->db_object->save( $data, $this->id );
	}

/*	public function save( $post_id, $post = null ) {

		if ( get_post_type() != $this->post_type) {
			return;
		}

		$this->data_object->save();

		//MBDB()->books->save( $mbdb_edit_book, $post_id );

	}
	*/
	public function to_json() {
		$properties =  get_object_vars($this);
		$object = array();
		foreach ( $properties as $name => $value ) {
			$object[ $name ] =   $value ;
		}
		return json_encode( $object);

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
