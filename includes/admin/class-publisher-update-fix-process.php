<?php


class MBDB_Publisher_Update_Fix_Process extends WP_Background_Process {

	protected $action = 'mbdb_publisher_update_fix';
	protected $old_publishers;
	protected $new_publishers;


	public function __construct() {
		parent::__construct();

		$this->old_publishers = get_option( 'mbdb_old_publishers', array() );
		$this->new_publishers = get_option( 'mbdb_new_publishers', array() );
	}


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

		$book           = MBDB()->book_factory->create_book( $book_id );
		$book_publisher = $book->publisher_id;
		if ( ! $book_publisher || $book_publisher == '' ) {
			return false;
		}
		if ( array_key_exists( $book_publisher, $this->old_publishers ) ) {
			$this->set_publisher( $this->old_publishers[ $book_publisher ], $book );
		} else {
			$this->set_publisher( $book->publisher->name, $book );

		}

		return false;
	}

	private function set_publisher( $publisher_name, $book ) {
		if ( array_key_exists( $publisher_name, $this->new_publishers ) ) {
			$publisher_id = $this->new_publishers[ $publisher_name ];
		} else {
			$publisher_id = wp_insert_post( array(
				'post_type'   => 'mbdb_publisher',
				'post_status' => 'publish',
				'post_title'  => $publisher_name,
			) );
		}

		global $wpdb;
		$sql = 'update ' . $wpdb->prefix . 'mbdb_books set publisher_id="' . $publisher_id . '" where book_id=' . $book->id;
		$wpdb->query( $sql );
	}

	protected function complete() {
		parent::complete();
		global $wpdb;

		// delete duplicate publishers
		$sql = "select min(ID) from $wpdb->posts  WHERE post_type = 'mbdb_publisher' group by post_title";
		$ids = $wpdb->get_col( $sql );

		$sql = "DELETE FROM $wpdb->posts   WHERE post_type = 'mbdb_publisher' and ID not in ( " . join( ',', $ids ) . ")";
		$wpdb->query( $sql );


		mbdb_remove_admin_notice( 'mbdb_update_publishers' );

		$message = __( 'Mooberry Book Manager: Finished checking for duplicate publishers and correcting the issue!', 'mooberr-book-manager' );
		$message .= '&nbsp;&nbsp;<a href="#" class="button mbdb_admin_notice_dismiss" data-admin-notice="mbdb_update_publishers_done">' . __( 'Dismiss this notice', 'mooberry-book-manager' ) . '</a>';

		MBDB()->helper_functions->set_admin_notice( $message, 'updated', 'mbdb_update_publishers_done' );
	}
}
