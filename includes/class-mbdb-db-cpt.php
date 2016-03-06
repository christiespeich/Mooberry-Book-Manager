<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if (!class_exists('MOOBD_Database')) {
	return;
}

/**
 * EDD_Customer Class
 *
 * @since 2.3
 */
abstract class MBDB_DB_CPT extends MOOBD_Database {

	protected $post_type;

	public abstract function map_postmeta_to_columns();
	
	public function __construct( $table_name ) {
		add_filter('posts_join', array( $this, 'search_join' ) );
		add_filter('posts_where', array( $this, 'search_where' ) );
		add_filter('posts_groupby', array( $this, 'search_groupby' ) );	
		
		parent::__construct( $table_name );
			
	}
	
	public function columns_with_html() {
		return array();
	}
	
	public function get($ids = null, $orderby = null, $order = null, $include_unpublished = false) {
		if ($ids == null) {
			$data = $this->get_all($orderby, $order, $include_unpublished);
		} else {
			if (is_array($ids)) {
				$data = $this->get_multiple_with_posts( $ids, $orderby, $order, $include_unpublished );
			} else {
				$sql =  "SELECT * 
				FROM $this->table_name AS t
				JOIN " . $this->prefix() . "posts p ON p.id = t." . $this->primary_key . "
				WHERE p.id = $ids";

				global $wpdb;
				$data =  $wpdb->get_row($sql);
			}
		}
		return $data;
	}
	
	private function get_multiple_with_posts( $ids, $orderby = null, $order = null, $include_unpublished = false) {
	
		global $wpdb;
		if ( !is_array( $ids ) ) {
			$ids = array( $ids );
		}
		$ids = array_map('absint', $ids);
		
		$orderby = $this->validate_orderby( $orderby );
		
		$order = $this->validate_order( $order );
		
		$join = ' JOIN ' . $this->prefix() . 'posts p ON p.id = t.' . $this->primary_key;
		
		if ( ! $include_unpublished ) {
			$where = ' AND p.post_status = "publish" ';
		} 
		
		// %d, %d, %d, [...]
		$key_ids = $this->get_in_format( $ids, '%d' );
		
		$sql = $wpdb->prepare( "SELECT * 
						FROM $this->table_name AS t
						$join
						WHERE $this->primary_key IN ($key_ids)
						$where
						ORDER BY %s %s;",
						array_merge($ids , 
							array($orderby ,
									$order))
					);

		return $this->run_sql($sql);
		
	}

	
	protected function get_all ($orderby = null, $order = null, $include_unpublished = false ) {
		
		
		//	$orderby = $this->validate_orderby( $orderby );
		if ($orderby == null) {
			$orderby = $this->primary_key;
		}
		$order = $this->validate_order( $order );
		
		$where = '';
		$join = ' JOIN ' . $this->prefix() . 'posts p ON p.id = t.' . $this->primary_key;
		
		if ( ! $include_unpublished ) {
			$where = ' WHERE p.post_status = "publish" ';
			
		} 
		
		$sql =  "SELECT * 
				FROM $this->table_name AS t
				$join
				$where
				ORDER BY  
				$orderby 
				$order";

		return $this->run_sql($sql);
	}
	
	public function get_data( $data_field, $id ) {
		if ( $this->column_exists($data_field) ) {
			$data = $this->get( $id );
			if ($data != null) {
				return $data->{$data_field};
			}
		} else {
			$data = get_post_meta($id, $data_field, true);
			return $data;
		}	
		return null;	
	}
	
	public function get_by_slug( $slug ) {
		global $wpdb;
		
		$slug = esc_sql( $slug );
		$slug = sanitize_title_for_query( $slug );
	
		
		$sql = $wpdb->prepare("SELECT * 
						FROM {$this->prefix()}posts AS p
						JOIN $this->table_name AS a ON a.{$this->primary_key} = p.ID
						WHERE post_name = %s AND 
						post_type = %s;",
						$slug,
						$this->post_type);
				
		$data = $this->run_sql($sql);
		if (count($data)==1) {
			return $data[0];
		} else {
			return null;
		}
	}
	
	public function save( $data, $id, $auto_increment = false, $type = '') {	
		// does not have a regular PK because it gets it from the posts table 
		//so the "primary key" field has to be added as one to insert (not auto-increment)
		if ($type == '') {
			$type = $this->post_type;
		}
		return parent::save( $data, $id, false, $type );
		
	}
	
	public function import() {
		// bring in the posts
		$args = array('posts_per_page' => -1,
					'post_type' => $this->post_type,
		);
		
		$posts = get_posts( $args  );
		wp_reset_postdata();
		
		$columns = $this->map_postmeta_to_columns();
	
		foreach ($posts as $post) {
			$new_row = array();
			$post_data = get_post_meta($post->ID);
			foreach ( $columns as $post_meta => $column) {
				if (array_key_exists( $post_meta, $post_data)) {
					$new_row[$column] = $post_data[$post_meta][0];
				}
			}
			$new_row[$this->primary_key] = $post->ID;
			$success = $this->insert($new_row);
			if (!$success) {
				return false;
			}
			
		}
		
		return true;
	}

	/****************************************************************
	 *  			SEARCHING
	 *  
	 ****************************************************************/
 
	abstract public function search_where( $where );
	
	public function search_join( $join ) {
		global $wpdb;
		
		if( is_search() ) {
			$join .= ' LEFT JOIN ' . $this->table_name . ' ON ' . $wpdb->posts . '.ID = ' . $this->table_name . '.' . $this->primary_key . ' ';
	  }

	  return $join;
	}
	
	
	public function search_groupby( $groupby ) {
		global $wpdb;

	  if( !is_search() ) {
		return $groupby;
	  }

	  // we need to group on post ID

	  $mygroupby = "{$wpdb->posts}.ID";

	  if( preg_match( "/$mygroupby/", $groupby )) {
		// grouping we need is already there
		return $groupby;
	  }
	  if( !strlen(trim($groupby))) {
		// groupby was empty, use ours
		return $mygroupby;
	  }

	  // wasn't empty, append ours
	  return $groupby . ", " . $mygroupby;
	}

	
}
