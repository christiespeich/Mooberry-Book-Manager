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
	
	private function allows_html( $column ) {
		return (in_array($column, $this->db_object->columns_with_html()));
	}
	
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
	
	public function get_by_slug( $slug ) {
		$data = $this->db_object->get_by_slug($slug);
		return $data;
	}
	
	
	public function save_data_by_post_meta( $post_meta, $value, $id ) {
		$column = $this->post_meta_to_column( $post_meta );
		if ( $column !== false ) {
			// same data should be sanitized and some should retain HTML
			if ($this->allows_html($column)) {
				$value = wp_kses_post($value);
			} else {
				$value = mbdb_sanitize_field($value);	
			}
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
	
	private function post_meta_to_column( $post_meta ) {
		$fields = $this->db_object->map_postmeta_to_columns();
		if ( array_key_exists( $post_meta, $fields ) ) {
			return $fields[$post_meta];
		} else {
			return false;
		}
	}
	
	public function import() {
		return $this->db_object->import();
	}
	
	public function create_table() {
		$this->db_object->create_table();
	}
	
}