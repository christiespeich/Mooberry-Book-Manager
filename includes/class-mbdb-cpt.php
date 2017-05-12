<?php
// backwards compatibility
abstract class MBDB_CPT {

	protected $db_object;
	
	public function create_table() {
		return $this->db_object->create_table();
	}
	
	// bckwds compatibility with MA
	//public function get_all( $order_by, $order ) {
	public function get_all ($orderby = null, $order = null, $include_unpublished = false, $cache_results = true ) {
		
		global $wpdb;
		
		//	$orderby = $this->validate_orderby( $orderby );
		if ($orderby == null) {
			$orderby = 'p.id'; //$this->primary_key;
		}
		//$order = $this->db_object->validate_order( $order );
		
		$where = '';
		$join = ' JOIN ' . $wpdb->posts . ' p ON p.id = t.author_id';
		
		if ( ! $include_unpublished ) {
			$where = ' WHERE p.post_status = "publish" ';
			
		} 
		/*
		if ( $this->column_exists( 'blog_id' ) ) {
			$where .= " AND blog_id = $this->blog_id";
		}
		*/
		$table = $this->db_object->table_name();
		$sql =  "SELECT * 
				FROM $table AS t
				$join
				$where
				ORDER BY  
				$orderby 
				$order";

		
			global $wpdb;
		return  $wpdb->get_results($sql);
	}
	
	
	public function get_data_by_post_meta( $post_meta, $id) {
		$data = $this->db_object->get( $id );
		if ($data == null) {
			return false;
		}
		$column = $this->post_meta_to_column($post_meta);
		if ( $column !== false ) {		
			return $data->{$column};
		}
		
		return false;
	}
	
	public function post_meta_to_column( $post_meta ) {
		$fields = $this->db_object->map_postmeta_to_columns();
		if ( array_key_exists( $post_meta, $fields ) ) {
			return $fields[$post_meta];
		} else {
			return false;
		}
	}
	
	public function in_custom_table( $data_field ) {
		$fields = $this->db_object->map_postmeta_to_columns();
		return ( array_key_exists( $data_field, $fields ) );
		
	}
	
	public function save( $object, $id) {
		////error_log('mbdb_cpt->save');
		$this->db_object->save($object, $id);
	}
	
	public function get( $id ) {
		if ( !is_array($id) ) {
			return $this->db_object->get($id);
		} else {
			return $this->db_object->get_multiple_authors($id);
			
		}
	}
	
	public function get_data( $data_field, $id ) {
		return $this->db_object->get_data( $data_field, $id, false );
	}
	
	
}