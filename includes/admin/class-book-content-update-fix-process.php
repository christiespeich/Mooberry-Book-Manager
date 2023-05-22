<?php


class MBDB_Book_Content_Update_Fix_Process extends WP_Background_Process {

	protected $action = 'mbdb_book_content_update_fix';


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
	protected function task( $post_id ) {

		$book = MBDB()->book_factory->create_book( $post_id );
		$_POST['_mbdb_summary'] = $book->summary;
		$_POST['_mbdb_cover_id'] = $book->cover_id;
		MBDB()->book_CPT->save_book($post_id);

		return false;
	}


	protected function complete() {
		parent::complete();
		mbdb_remove_admin_notice( $this->action);
	}
}
