<?php

if ( !class_exists( 'Mooberry_Dreams_Background_Process' )) {
	abstract class Mooberry_Dreams_Background_Process extends WP_Background_Process {

		protected $admin_notice_manager;


		public function __construct( $admin_notice_manager ) {
			parent::__construct();
			$this->set_admin_notice_manager($admin_notice_manager);
		}

		public function set_admin_notice_manager(  $admin_notice_manager ) {
			$this->admin_notice_manager = $admin_notice_manager;
			if ( $this->is_queue_empty() ) {
				$this->admin_notice_manager->dismiss( $this->action );
			}
		}

		/**
		 * Complete
		 *
		 * Override if applicable, but ensure that the below actions are
		 * performed, or, call parent::complete().
		 */
		protected function complete() {
			parent::complete();
			$this->admin_notice_manager->dismiss( $this->action );


		}

		public function is_queue_empty() {
		return parent::is_queue_empty();
	}
	}
}
