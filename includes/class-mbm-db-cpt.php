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
abstract class MBDB_DB_CPT extends MOOBD_Database implements iMooberry_Book_Manager_Data_Storage_Behavior {
	
	protected $post_type;
	protected $taxonomies;
	// bakcwards compat with MA
	protected $table_name;
	public abstract function map_postmeta_to_columns();
	
	
	
	public function __construct( $table_name ) {
	//	//error_log('creating db cpt object');
		add_filter('posts_join', array( $this, 'search_join' ) );
		add_filter('posts_where', array( $this, 'search_where' ) );
		add_filter('posts_groupby', array( $this, 'search_groupby' ) );	
		
		add_action( 'before_delete_post', array( $this, 'post_deleted') );
		
		$this->flush_on_update = true;
		
		$this->taxonomies = array();
		
		parent::__construct( $table_name );
			// bakcwards compat with MA
			$this->table_name = $this->table_name();
	}
	
	// should be abstract but can't due to backwards compatibility w/ MA
	public function postmeta_fields() {
		return array();
	}
	
	public static function activate() {
		@static::create_the_table();
	}
	
	/*
	public function in_custom_table( $data_field ) {
		$fields = $this->map_postmeta_to_columns();
		return ( array_key_exists( $data_field, $fields ) );
	}
	*/
	public function get($id, $cache_results = true) {
		global $wpdb;
		
				$table = $this->table_name();
					
				$sql =  "SELECT * 
				FROM $table AS t
				JOIN " . $wpdb->posts . " p ON p.id = t." . $this->primary_key . "
				WHERE p.id = $id ";
			
				
					$cache = $this->get_cache($sql);
					if (false !== $cache) {
						return $cache;
					}
				
				global $wpdb;
				$data =  $wpdb->get_row($sql);
				if ($cache_results) {
					$this->set_cache( $data, $sql );
				}	
		
		return $data;
		
	}
	
	// backwards compat with MA
	public function get_multiple_with_posts( $ids, $orderby = null, $order = null, $include_unpublished = false, $cache_results = false) {
	
		global $wpdb;
		if ( !is_array( $ids ) ) {
			$ids = array( $ids );
		}
		$ids = array_map('absint', $ids);
		
		if (count($ids)==0) {
			return null;
		}
		
		$orderby = $this->validate_orderby( $orderby );
		
		$order = $this->validate_order( $order );
		
		$join = ' JOIN ' . $wpdb->posts . ' p ON p.id = t.' . $this->primary_key;
		
		$where = '';
		if ( ! $include_unpublished ) {
			$where = ' AND p.post_status = "publish" ';
		} 
		
		// %d, %d, %d, [...]
		$key_ids = $this->get_in_format( $ids, '%d' );
		
		/*
		if ( $this->column_exists( 'blog_id' ) ) {
			$where .= " AND blog_id = $this->blog_id";
		}
*/
		$table = $this->table_name();
		$sql = $wpdb->prepare( "SELECT * 
						FROM $table AS t
						$join
						WHERE $this->primary_key IN ($key_ids) 
						$where
						ORDER BY %s %s;",
						array_merge($ids , 
							array($orderby ,
									$order))
					);

		return $this->run_sql($sql, $cache_results);
		
	}
	
	
	public function get_data( $data_field, $id, $cache_results = true ) {
		if ( $this->column_exists($data_field) ) {
			$data = $this->get( $id, $cache_results );
			if ($data != null) {
				return $data->{$data_field};
			}
		} else {
			if ( in_array( $data_field, $this->taxonomies ) ) {
				$terms = get_the_terms( $id, $data_field );
				
				if ( !is_array($terms) ) {
					return array();
				} else {
					return $terms;
				}
			} else {			
				return get_post_meta($id, $data_field, true);
			}
		}
		return null;	
	}
	
	public function get_by_slug( $slug, $cache_results = true ) {
		global $wpdb;
		
		$slug = esc_sql( $slug );
		$slug = sanitize_title_for_query( $slug );
	
		$table = $this->table_name();
		$sql = $wpdb->prepare("SELECT * 
						FROM {$wpdb->posts} AS p
						JOIN {$table} AS a ON a.{$this->primary_key} = p.ID
						WHERE post_name = %s  AND 
						post_type = %s;",
						$slug,
						$this->post_type);
				
		$data = $this->run_sql($sql, $cache_results);
		if (count($data)==1) {
			return $data[0];
		} else {
			return null;
		}
	}
	
	public function save( $data, $id, $auto_increment = false, $type = '') {	
	
		// no data to save. Not an error just no rows updated/inserted
		if (!is_array($data)) {
			return 0;
		}
		
		if ($type == '') {
			$type = $this->post_type;
		}
		
		// data comes in as
		// ( post_meta_id => value )
		// must be rewritten to
		// ( column => value )
		$columns = $this->map_postmeta_to_columns();
		foreach( $data as $post_meta => $value ) {
			if (array_key_exists($post_meta, $columns)) {
				$column = $columns[ $post_meta ];
				$new_data[ $column ] = $this->sanitize_field( $column, $value, $type );
			} 
		}
		
		// no data to save. Not an error just no rows updated/inserted
		if (!isset($new_data) || !is_array($new_data)) {
			return 0;
		}
	
		wp_cache_delete( $id, $this->post_type );
		//error_log('delete cached ' . $this->post_type  . ': ' . $id );
			
	
		// does not have a regular PK because it gets it from the posts table 
		//so the "primary key" field has to be added as one to insert (not auto-increment)
		return parent::save( $new_data, $id, false, $type );
		
	}
	
	
	public function override_data_access( $postmeta ) {
		return array_key_exists( $postmeta, $this->map_postmeta_to_columns() );
	}
	
	// runs when a post is emptied from the trash
	public function post_deleted ( $id ) {
		global $post_type;   
		if ( $post_type != $this->post_type ) {
			return;
		}
		
		parent::delete( $id );
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
			echo '<p>' . __('Migrating', 'mooberry-book-manager') . ' ' . $post->post_title . '...';
			$new_row = array();
			$post_data = get_post_meta($post->ID);
			foreach ( $columns as $post_meta => $column) {
				if (array_key_exists( $post_meta, $post_data)) {
					$new_row[$post_meta] = $post_data[$post_meta][0];
				}
				echo '.';
			}
			if (count($new_row)>0) {
				
				//$new_row['blog_id'] = $this->blog_id;
				$success = $this->save($new_row, $post->ID);
			} else {
				$success = true;
			}
			
		/* 	$results = $this->get( $post->ID );
		
			if ($results == null || empty($results) ) {
				$new_row[$this->primary_key] = $post->ID;
				$success = $this->insert($new_row);
			} else {
				$success = $this->update( $post->ID, $new_row);
			} */
			if (!$success) {
				echo '<b>' . __('Error!', 'mooberry-book-manager') . '</b></p>';
				return false;
			}
			echo '<b>' . __('Success!', 'mooberry-book-manager') . '</b></p>';
			
		}
		mbdb_remove_admin_notice('3_1_migrate');
		mbdb_remove_admin_notice('3_1_remigreate');
		return true;
	}

	/****************************************************************
	 *  			SEARCHING
	 *  
	 ****************************************************************/
 
	public function search_where( $where ) {
		
		
		return $where;
	}
	
	public function search_join( $join ) {
		
		global $wpdb;
		$table = $this->table_name();
		if( is_search() && strpos( $join, $table ) == false ) {
			$join .= ' LEFT JOIN ' . $table . ' ON ' . $wpdb->posts . '.ID = ' . $table . '.'  . $this->primary_key . ' ';
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
