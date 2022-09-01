<?php



class MBDB_DB_Publisher extends MBDB_CMB_CPT {

	public function __construct() {
		$this->post_type = 'mbdb_publisher';
	}



	protected function add_post_meta( $publisher, $postmeta = null ) {

		$publisher->website = '';
		if ( $postmeta == null ) {
			$postmeta = get_post_meta( $publisher->ID );
		}

		if ( isset($postmeta['_mbdb_publisher_website']) ) {
			$publisher->website = $postmeta['_mbdb_publisher_website'][0];
		}
		if ( isset($postmeta['image']) ) {
			$publisher->logo = $postmeta['image'][0];
		}
		if ( isset($postmeta['image_id']) ) {
			$publisher->logo_id = $postmeta['image_id'][0];
		}

		return $publisher;
	}

	public function get_by_slug( $slug, $cache_results = true ) {
		$publisher = parent::get_by_slug( $slug );
		$publisher = $this->add_post_meta( $publisher );
		return $publisher;
	}




}
