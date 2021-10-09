<?php
abstract class MBDB_CMB_CPT implements iMooberry_Book_Manager_Data_Storage_Behavior {
	protected $post_type;

	abstract protected function  add_post_meta( $object, $postmeta );

	public function __construct() {
		add_action( 'before_delete_post', array( $this, 'post_deleted') );
	}

	// gets all data for a custom post type
	// only this class knows how everything is stored in the datbase
	// so when another class needs an object of this CPT, we need to get all the info
	// and return it as an object
	// id can be an id from the posts table
	// or it can be an array of postmeta fields if coming from the preview button
	public function get( $id, $cache_results = true ) {

		if ( !is_array( $id ) ) {
			//$object = parent::get( $id, $cache_results );
			$object = get_post( $id );

			if ( $object == null ) {
				return null;
			}
			// TODO add cachign?
			$postmeta = get_post_meta( $id );
		} else {
			$postmeta = $id;
			$object = get_post( $id['id']);
			//$object = parent::get( $postmeta['id'], $cache_results ); //get_post ( $postmeta['id'] );

		}
		$object = $this->add_post_meta( $object, $postmeta );
		return $object;
	}

	public function get_data( $data_field, $id, $cache_results = true ) {
		// TODO caching
		return get_post_meta( $id, $data_field, true );
	}

	public function get_by_slug( $slug, $cache_results = true ) {
		// TODO cahching
		return get_page_by_path( $slug, OBJECT, $this->post_type );
	}

	public function save ( $data, $id, $auto_increment = false, $type = '') {
		// do nothing by default because CMB handles it
	}

	public function override_data_access( $postmeta ) {
		// never override CMB
		return false;
	}
}
