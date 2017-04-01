<?php
class MBDB_CMB_CPT implements iMooberry_Book_Manager_Data_Storage_Behavior {
	protected $post_type;
	
	public function __construct() {
		add_action( 'before_delete_post', array( $this, 'post_deleted') );
	}
	
	public function get( $id, $cache_results = true ) {
		// TODO Add caching
		$data = get_post( $id );
		return $data;
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
