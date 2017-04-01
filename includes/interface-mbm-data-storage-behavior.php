<?php
interface iMooberry_Book_Manager_Data_Storage_Behavior {
	
	public function get( $id, $cache_results = true );
	//public function get_all( $orderby = null, $order = null, $include_unpublished = false, $cache_results = true );
	public function get_data( $data_field, $id, $cache_results = true );
	public function get_by_slug( $slug, $cache_results = true );
	public function save ( $data, $id, $auto_increment = false, $type = '');
	public function override_data_access( $field );
	
	
	
	
	
}