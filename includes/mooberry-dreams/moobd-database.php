<?php
	

abstract class MOOBD_Database {
    protected $primary_key;
	protected $table_name;
	protected $version;
	
	protected $cache_keys = array();
	
	protected abstract function get_columns();
	protected abstract function create_table();
	
	//public abstract function import();
	
	public function __construct( $table_name ) {
		$this->table_name = $this->table( $table_name );
	}
	
	final protected function prefix() {
		global $wpdb;
		return $wpdb->prefix;
	}
	
    final protected function table( $table_name ) {
        return $this->prefix() . $table_name;
    }
	
	protected function get_column_defaults() {
		return array();
	}
	
	protected function column_exists($column) {
		$columns = $this->get_columns();
		return array_key_exists($column, $columns);
	}
	
	protected function validate_orderby( $orderby ) {
		if ( $orderby == '' || $orderby == null || ! $this->column_exists($orderby) ) {
			$orderby = $this->primary_key;
		} 
		return esc_sql( $orderby );
	}
	
	protected function validate_order( $order ) {
		if ( $order == null || ( $order != 'ASC' && $order != 'DESC' ) ) {
			$order = 'ASC';
		}
		return esc_sql( $order );
	}
	
	protected  function run_sql( $sql ) {
		global $wpdb;
		//$sql = $wpdb->prepare( $sql );
		$cache = $this->get_cache($sql);
		if (false !== $cache) {
			return $cache;
		}
		$data = $wpdb->get_results($sql);
		$this->set_cache( $data, $sql );
		return $data;
	}
	
	 public function get( $value ) {
		global $wpdb;
		$sql = $wpdb->prepare( "SELECT * FROM $this->table_name WHERE $this->primary_key = %s", $value );
		
		$cache = $this->get_cache($sql);
		if (false !== $cache) {
			return $cache;
		}
		
		$data = $wpdb->get_row($sql);
		$this->set_cache( $data, $sql );
		return $data;
    }
	
	protected function get_by( $column, $row_id ) {
		global $wpdb;
		
		$column = esc_sql( $column );
		
		$sql = $wpdb->prepare( "SELECT * FROM $this->table_name WHERE $column = %s LIMIT 1;", $row_id );
		
		$cache = $this->get_cache($sql);
		
		if (false !== $cache) {
			return $cache;
		}
		
		$data = $wpdb->get_row( $sql );
		
		$this->set_cache( $data, $sql );
		
		return $data;
		
	}
	
	protected function get_multiple ( $values, $orderby = null, $order = null) {
		
		$orderby = $this->validate_orderby( $orderby );
		
		$order = $this->validate_order( $order );
		
		if ( ! is_array( $values ) ) {
			$values  = array( $values );
		}
		
		$values = array_map('esc_sql', $values);
		$values = array_map('sanitize_title_for_query', $values);
		
		$sql =  $wpdb->prepare("SELECT * FROM $this->table_name WHERE $this->primary_key IN (%s) ORDER BY %s %s;", implode(',',$values), $orderby, $order);
		
		return $this->run_sql($sql);
		
	}
	
	protected function get_all ($orderby = null, $order = null ) {
		global $wpdb; 
		$orderby = $this->validate_orderby( $orderby );
		
		$order = $this->validate_order( $order );
			
		$sql = $wpdb->prepare( "SELECT * 
						FROM $this->table_name AS t
						ORDER BY %s %s;",
						$orderby, 
						$order);
		
		return $this->run_sql($sql);
	}
	
	public function get_count () {
		global $wpdb;
		$sql =  "SELECT count(*) AS number FROM $this->table_name";
		$results = $this->run_sql($sql);
		if (count($results)>0) {
			return $results[0]->number;
		} else {
			return null;
		}
	
	}
	
	public function save( $data, $id, $auto_increment = true, $type = '' ) {
		
		if ($type != '' ) {
			$type = '_' . $type;
		}
		
		$results = $this->get( $id );
		
		if ($results == null || empty($results) ) {
			
			// if the K is not auto-incrememnt, add it to the data array
			if ( ! $auto_increment ) {
				$data[$this->primary_key] = $id;
			}
			
			return $this->insert( $data, $type );
			
		} else {
			return $this->update( $id, $data, $type );
		}
	}
	
    protected function insert( $data, $type = '' ) {
		
		global $wpdb;
		
		// Set default values
		$data = wp_parse_args( $data, $this->get_column_defaults() );
		
		do_action( 'mbdb_pre_insert' . $type, $data );

		// Initialise column format array
		$column_formats = $this->get_columns();

		// Force fields to lower case
		$data = array_change_key_case( $data );

		// White list columns
		$data = array_intersect_key( $data, $column_formats );

		// Reorder $column_formats to match the order of columns given in $data
		$data_keys = array_keys( $data );
		$column_formats = array_merge( array_flip( $data_keys ), $column_formats );
		
		$this->clear_cache();
		
		$success = $wpdb->insert( $this->table_name, $data, $column_formats );
		
		do_action( 'mbdb_post_insert' . $type, $wpdb->insert_id, $data );

		return $success;		
	}
    
	protected function update( $row_id, $data = array(), $type = '' ) {
		
		global $wpdb;
		
		// Row ID must be positive integer
		$row_id = absint( $row_id );
		
		if( empty( $row_id ) ) {
			return false;
		}
		
		do_action( 'mbdb_pre_update' . $type, $data, $row_id );
		
		// Initialise column format array
		$column_formats = $this->get_columns();
		
		// Force fields to lower case
		$data = array_change_key_case( $data );
		
		// White list columns
		$data = array_intersect_key( $data, $column_formats );
		
		// Reorder $column_formats to match the order of columns given in $data
		$data_keys = array_keys( $data );
		$column_formats = array_merge( array_flip( $data_keys ), $column_formats );
		
		$this->clear_cache();
		
		$success = $wpdb->update( $this->table_name, $data, array( $this->primary_key => $row_id ), $column_formats );
		
		do_action( 'mbdb_post_update' . $type, $data, $row_id, $success );
		
		if ( false === $success ) {
			return false;
		}
		return true;
		
	}
    
	protected function delete( $value ) {
        global $wpdb;
		
		// Row ID must be positive integer
		$value = absint( $value );
		if( empty( $value ) ) {
			return false;
		}

		$this->clear_cache();
		
        $success = $wpdb->query( $wpdb->prepare( "DELETE FROM $this->table_name WHERE $this->primary_key = %d", $value ) );
		
		if (false === $success) {
			return false;
		}
		
		return true;
    }
	
    protected function insert_id() {
        global $wpdb;
        return $wpdb->insert_id;
    }
	
	protected function get_in_format( $list, $format ) {
		
		// prepare the right amount of placeholders
		// if you're looing for strings, use '%s' instead
		$placeholders = array_fill(0, count($list), $format );

		// glue together all the placeholders...
		// $format = '%d, %d, %d, %d, %d, [...]'
		return implode(', ', $placeholders);
	}
	
	
    protected function time_to_date( $time ) {
        return gmdate( 'Y-m-d H:i:s', $time );
    }
	
    protected function now() {
        return $this->time_to_date( time() );
    }
	
    protected function date_to_time( $date ) {
        return strtotime( $date . ' GMT' );
    }
	
	protected function get_cache( $key ) {
		
		$cache =  wp_cache_get( md5( serialize($key) ), $this->table_name );
		return $cache;
	}
	
	protected function set_cache( $data, $key ) {
		wp_cache_set( md5( serialize($key)), $data, $this->table_name,  24*60*60);
	}
	
	protected function clear_cache() {
		
		$this->delete_cache($this->table_name);
	}
	
	
	// deletes all keys in the cache for a given group
	private  function delete_cache($group) {
		global $wp_object_cache;
		$cache = $wp_object_cache->__get( 'cache' );
		
		 if ( ! isset( $cache[ $group ] ) )
			 return false;
		 
		foreach ($cache[$group] as $key => $value )  {
			
			wp_cache_delete($key, $group);
		}
		
	}
}