<?php
class Mooberry_Book_Manager_Apple_Books_Update_Process extends WP_Background_Process {

	/**
	 * @var string
	 */
	protected $action = 'apple_books_update';
	protected $key = 'apple_books_update_process';

	public function __construct() {
		parent::__construct();
		if ( $this->is_process_running() ) {
			$message = __( 'Mooberry Book Manager: Buy links for Apple Books currently being updated...', 'mooberry-book-manager' );
			//$message .= '&nbsp;&nbsp;<a href="' . admin_url('admin.php?page=mbdb_import_export') . '" class="button">' . __('Cancel Import on the Import/Export Page', 'mooberry-book-manager') . '</a>';
			$type = 'updated';
			//$key  = 'apple_books_update_process';
			MBDB()->helper_functions->set_admin_notice( $message, $type, $this->key );
		} else {
			if ( $this->is_queue_empty() ) {

				MBDB()->helper_functions->remove_admin_notice( $this->key );

			}
		}

	}

	public function update_links() {
		$query_args = array(
			'post_type'  => 'mbdb_book',
			'post_status' => array( 'draft','publish'),
			'posts_per_page' => '-1',
			'meta_query' => array(
				array(
					'key'     => '_mbdb_buylinks',
					'value'   => 'itunes.apple',
					'compare' => 'LIKE'
				),
			)
		);

		$posts= get_posts( $query_args );

		foreach ( $posts as $post ) {
			$this->push_to_queue( $post->ID );

		}

		$this->save();
		$this->dispatch();

	}

	public function cancel_process() {
		global $wpdb;

		$table  = $wpdb->options;
		$column = 'option_name';

		if ( is_multisite() ) {
			$table  = $wpdb->sitemeta;
			$column = 'meta_key';
		}

		$key = $this->identifier . '_batch_%';

		$query = $wpdb->get_results( $wpdb->prepare( "
			DELETE 
			FROM {$table}
			WHERE {$column} LIKE %s
			
		", $key ) );

		parent::cancel_process();

	}

	/*public function save() {
		parent::save();
		$this->data = array();
	}*/

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param mixed $item Queue item to iterate over
	 *
	 * @return mixed
	 */
	protected function task( $book_id ) {

		//$book = MBDB()->book_factory->create_book( $book_id );

		$buy_links = get_post_meta( $book_id, '_mbdb_buylinks', true );
		if ( is_array($buy_links)) {
			foreach ( $buy_links as $index => $buy_link ) {
				$buy_links[ $index ]['_mbdb_buylink'] = str_replace( 'itunes.apple.com', 'books.apple.com', $buy_link['_mbdb_buylink'] );
			}
			update_post_meta( $book_id, '_mbdb_buylinks', $buy_links );
		}

		return false;
	}

	/**
	 * Complete
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	protected function complete() {
		parent::complete();

		// Show notice to user or perform some other arbitrary task...
		$key     = 'mbdb_apple_book_update_complete';
		$message = __( 'Mooberry Book Manager: Apple Books buy links update complete!', 'mooberry-book-manager' );
		$message .= '&nbsp;&nbsp;<a href="#" class="button mbdb_admin_notice_dismiss" data-admin-notice="' . $key . '">' . __( 'Dismiss this notice', 'mooberry-book-manager' ) . '</a>';
		$type    = 'updated';

		MBDB()->helper_functions->set_admin_notice( $message, $type, $key );
		MBDB()->helper_functions->remove_admin_notice( $this->key );
		MBDB()->helper_functions->remove_admin_notice( 'mbdb_itunes_to_books_buylink' );
		update_option( 'mbdb_retailers_with_itunes', 'no' );


	}

}
