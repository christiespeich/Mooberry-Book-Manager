<?php
class Mooberry_Book_Manager_Import_Process extends WP_Background_Process {

    /**
     * @var string
     */
    protected $action = 'import_books';

	
	public function __construct() {
		parent::__construct();
		if ( $this->is_process_running() ) {
			$message = __('Books currently being imported.', 'mooberry-book-manager');
			$message .= '&nbsp;&nbsp;<a href="' . admin_url('admin.php?page=mbdb_import_export') . '" class="button">' . __('Cancel Import on the Import/Export Page', 'mooberry-book-manager') . '</a>';
			$type = 'updated';
			$key = 'mbdb_import_books_process';
			MBDB()->helper_functions->set_admin_notice( $message, $type, $key);
		} else {
			if ( $this->is_queue_empty() ) {
				MBDB()->helper_functions->remove_admin_notice('mbdb_import_books_process');
				
			}
		}
	}
	
	public function cancel_process() {
		global $wpdb;

			$table        = $wpdb->options;
			$column       = 'option_name';
			
			if ( is_multisite() ) {
				$table        = $wpdb->sitemeta;
				$column       = 'meta_key';
			}

			$key = $this->identifier . '_batch_%';

			$query = $wpdb->get_results( $wpdb->prepare( "
			DELETE 
			FROM {$table}
			WHERE {$column} LIKE %s
			
		", $key ) );
		
		parent::cancel_process();
		
	}
	
	public function save() {
		parent::save();
		$this->data = array();
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
    protected function task( $book ) {
        // Actions to perform
		//print_r($book);
		//$new_book = new Mooberry_Book_Manager_Book();
		$new_book = MBDB()->book_factory->create_book();
		//error_log('setting task');
		$success = $new_book->import( $book );
		//sleep(5);
		// if ( $success !== true ) {
			// if ( ! $this->is_queue_empty() ) {
				// $batch = $this->get_batch();
				// $this->delete( $batch->key );
			// }
		// }
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
		$key = 'mbdb_import_books_complete';
		$message = __('Book import complete!', 'mooberry-book-manager');
		$message .= '&nbsp;&nbsp;<a href="#" class="button mbdb_admin_notice_dismiss" data-admin-notice="' . $key . '">' . __('Dismiss this notice', 'mooberry-book-manager') . '</a>';
		$type = 'updated';
		
		MBDB()->helper_functions->set_admin_notice( $message, $type, $key);
		
    }
	
	public function is_queue_empty() {
		return parent::is_queue_empty();
	}

}
