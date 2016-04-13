<?php
/**
 * Customer Object
 *
 * @package     EDD
 * @subpackage  Classes/Customer
 * @copyright   Copyright (c) 2015, Chris Klosowski
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.3
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Customer Class
 *
 * @since 2.3
 */
abstract class MBDB_CPT {

	protected $db_object;
	
	abstract public function __construct();
	
	
	
	public function get( $id, $orderby = null, $order = null, $include_unpublished = false ) {
		return $this->db_object->get ( $id, $orderby, $order, $include_unpublished );
	}
	
	public function get_all( $orderby = null, $order = null, $include_unpublished = false) {
		return $this->db_object->get( null, $orderby, $order, $include_unpublished );
	}
		
	public function get_data( $data_field, $id) {
		return $this->db_object->get_data( $data_field, $id );
	}
	
	public function get_data_by_post_meta( $post_meta, $id ) {
		return $this->db_object->get_data_by_post_meta( $post_meta, $id );
	}
	
	
	
	public function get_by_slug( $slug ) {
		$data = $this->db_object->get_by_slug($slug);
		return $data;
	}
	
	public function save( $data, $id ) {
		
		$success = $this->db_object->save( $data, $id );
		
		if ($success === false) {
			return false;
		} else {
			return $success;
		}
		
	}
	

	/*
	public function save_data_by_post_meta( $post_meta, $value, $id ) {
		$column = $this->post_meta_to_column( $post_meta );
		if ( $column !== false ) {
			$value = $this->sanitize_field( $column, $value );
			$success = $this->db_object->save( array( $column => $value ), $id );
			if ($success === false) {
				return false;
			} else {
				return $success;
			}
		} else {
			return false;
		}
		
	}
	*/
	
	
	public function in_custom_table( $post_meta ) {
		$column = $this->db_object->post_meta_to_column( $post_meta );
		if ($column === false) {
			return false;
		} else {
			return true;
		}
	}
	
	
	public function import() {
		return $this->db_object->import();
	}
	
	public function create_table() {
		$this->db_object->create_table();
	}
	
	public function empty_table() {
		$this->db_object->empty_table();
	}
	
}