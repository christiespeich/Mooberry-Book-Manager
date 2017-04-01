<?php
	

abstract class MOOBD_Database {
    protected $primary_key;
	protected $table;
	protected $version;
	protected $flush_on_update = false;
	
	//protected $prefix;
	//protected $blog_id;
	
	//protected $cache_keys = array();
	
	protected abstract function get_columns();
	protected abstract function create_table();
	public static function create_the_table() {
	}
	
	//public abstract function import();
	
	public function __construct( $table_name ) {
		global $wpdb;
	//	global $blog_id;
	//	$this->prefix = $wpdb->prefix; //$this->table_prefix();
				
				$this->table = $table_name;
				
	//	$this->table_name = $wpdb->prefix . $table_name; //$this->table( $table_name );
	//	$this->blog_id = $blog_id; //$this->current_blog_id();
	
	
	}
	
	

	
	protected function columns_with_html() {
		return array();
	}
	
	
	protected function allows_html( $column ) {
		return (in_array($column, $this->columns_with_html()));
	}
	
	/*
	final protected function current_blog_id() {
		global $wpdb;
		return $wpdb->blogid;
	}
	*/
	
	
    final public function table_name( ) {
		global $wpdb;
        return $wpdb->prefix . $this->table;
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
	
	protected  function run_sql( $sql, $cache_results = true ) {
		global $wpdb;
		//$sql = $wpdb->prepare( $sql );
		
			$cache = $this->get_cache($sql);
			if (false !== $cache) {
				return $cache;
			}
	
		
		$data = $wpdb->get_results($sql);
		
		
			if ($cache_results) {
				$this->set_cache( $data, $sql );
			} 
		
		return $data;
	}
	
	 public function get( $value, $cache_results = true ) {
		global $wpdb;
		/*$where = '';
		if ( $this->column_exists( 'blog_id' ) ) {
			$where = "AND blog_id = $this->blog_id";
		}*/

		$table = $this->table_name();
		
		$sql = $wpdb->prepare( "SELECT * FROM $table WHERE $this->primary_key = %s ", $value );
		
			$cache = $this->get_cache($sql);
			if (false !== $cache) {
				return $cache;
			}
		
		$data = $wpdb->get_row($sql);
		if ($cache_results) {
			$this->set_cache( $data, $sql );
		} 
		return $data;
    }
	
	protected function get_by( $column, $row_id, $cache_results = true ) {
		global $wpdb;
		
		$column = esc_sql( $column );
		/*
		$where = '';
		if ( $this->column_exists( 'blog_id' ) ) {
			$where = "AND blog_id = $this->blog_id";
		}
*/
		$table = $this->table_name();
		$sql = $wpdb->prepare( "SELECT * FROM $table WHERE $column = %s LIMIT 1;", $row_id );
		
			$cache = $this->get_cache($sql);
			
			if (false !== $cache) {
				return $cache;
			}
		$data = $wpdb->get_row( $sql );

			if ($cache_results) {
				$this->set_cache( $data, $sql );
			}
		return $data;
		
	}
	
	protected function get_multiple ( $values, $orderby = null, $order = null, $cache_results = false) {
		
		$orderby = $this->validate_orderby( $orderby );
		
		$order = $this->validate_order( $order );
		
		if ( ! is_array( $values ) ) {
			$values  = array( $values );
		}
		/*
		$where = '';
		if ( $this->column_exists( 'blog_id' ) ) {
			$where = "AND blog_id = $this->blog_id";
		}
*/
		$values = array_map('esc_sql', $values);
		$values = array_map('sanitize_title_for_query', $values);
		
		$table = $this->table_name();
		global $wpdb;
		$sql =  $wpdb->prepare("SELECT * FROM $table WHERE $this->primary_key IN (%s)  ORDER BY %s %s;", implode(',',$values), $orderby, $order);
		
		return $this->run_sql($sql, $cache_results);
		
	}
	
	// public for MA compat
	public function get_all ($orderby = null, $order = null, $cache_results = true ) {
		global $wpdb; 
		$orderby = $this->validate_orderby( $orderby );
		
		$order = $this->validate_order( $order );
		/*
		$where = '';
		if ( $this->column_exists( 'blog_id' ) ) {
			$where = "WHERE blog_id = $this->blog_id";
		}
*/
		$table = $this->table_name();
		$sql = $wpdb->prepare( "SELECT * 
						FROM $table AS t
						ORDER BY %s %s;",
						$orderby, 
						$order);
		
		return $this->run_sql($sql, $cache_results);
	}
	
	public function get_count () {
		global $wpdb;
		/*
		$where = '';
		if ( $this->column_exists( 'blog_id' ) ) {
			$where = "WHERE blog_id = $this->blog_id";
		}
*/
		$table = $this->table_name();
		$sql =  "SELECT count(*) AS number FROM $table ";
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
			//$data['blog_id'] = $this->blog_id;
			//error_log('inserting into database');
			return $this->insert( $data, $type );
			
		} else {
			//error_log('updating database');
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
		$table = $this->table_name();
		$success = $wpdb->insert( $table, $data, $column_formats );
		
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
		
		$pk = array( $this->primary_key => $row_id );
		/*
		if ( $this->column_exists( 'blog_id' ) ) {
			$pk['blog_id'] = $this->blog_id;
		}
		*/
		$this->clear_cache();
		$table = $this->table_name();
		$success = $wpdb->update( $table, $data, $pk, $column_formats );
		
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
/*
		$where = '';
		if ( $this->column_exists( 'blog_id' ) ) {
			$where = "AND blog_id = $this->blog_id";
		}
*/
		$table = $this->table_name();
		
        $success = $wpdb->query( $wpdb->prepare( "DELETE FROM $table WHERE $this->primary_key = %d ", $value ) );
		
		if (false === $success) {
			return false;
		}
		
		return true;
    }
	
	public function empty_table() {
		global $wpdb;
		
		$table = $this->table_name();
		$this->clear_cache();
		$success = $wpdb->query("TRUNCATE TABLE $table");
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
	
	protected function sanitize_field( $column, $value, $context = null ) {
	
		// same data should be sanitized and some should retain HTML
		if ($this->allows_html($column)) {
			if ( $context == null ) {
				$value = wp_kses_post($value);
			} else {
				$value = wp_kses(stripslashes_deep($value), $context );
			}
		} else {
			$value = strip_tags( stripslashes( $value ) );	
		}
	
		// values should be entered into the database as nulls not blanks
		// this affects fields such as published date and series order
		// this became a problem after adding in the override_remove hook
		if ($value == '') {
			$value = null;
		}	
		return $value;
	}
	
	
	
	protected function get_cache( $key ) {
		////error_log('getting cache: ' . md5( serialize($key)) . ' = ' . $key);
		$table = $this->table_name();
		$cache =  wp_cache_get( md5( serialize($key) ), $table );
		
		return $cache;
	}
	
	protected function set_cache( $data, $sql, $expires = 86400 ) {
		$key = md5( serialize($sql));
		////error_log('setting cache = : ' . $key . ' = ' . $sql);
		$table = $this->table_name();
		wp_cache_set( $key, $data, $table,  $expires );
		
		
		
	}
	
	protected function clear_cache() {
		
	/* 	//$this->delete_cache($this->table_name);
		$mbdb_cache = get_option('mbdb_cache');
		$table = $this->table_name();
		
		if (array_key_exists($table, $mbdb_cache)) {
			foreach ($mbdb_cache[$table] as $key) {
			//	//error_log('deleting ' . $key);
				wp_cache_delete($key, $table);
			}
			unset($mbdb_cache[$table]);
			update_option('mbdb_cache', $mbdb_cache);
		} */
		
		if ( !$this->flush_on_update) {
			return;
		}
		
		
		// unfortunately must flush entire cache because WP does not provide a way
		// to clear cache in a group, only by key
		wp_cache_flush();
		
		
		
		// clear WP Super Cache
		if (function_exists('wp_cache_clear_cache')) {
				wp_cache_clear_cache();		
		}

	}
	
	
	
	/*
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
	*/
}
