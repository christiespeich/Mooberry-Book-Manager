<?php

if ( !class_exists('Mooberry_Dreams_CSV_Importer')) {
	abstract class Mooberry_Dreams_CSV_Importer {

		protected $file;
		protected $import_process;
		protected $admin_notice_manager;
		protected $admin_notice_key;
		protected $fileHandle;
		protected $column_names;
		protected $label;



		public function __construct( $file,  $import_process,  $admin_notice_manager ) {
			$this->file                 = $file;
			$this->import_process       = $import_process;
			$this->admin_notice_manager = $admin_notice_manager;
			$this->admin_notice_key     = '';
			$this->fileHandle           = fopen( $file['file'], "r" );
			$this->column_names         = fgetcsv( $this->fileHandle, 0, "," );
			$this->column_names         = array_map( 'trim', $this->column_names, array( "\xEF\xBB\xBF" ) );
			$this->label                = '';

		}


		public function import() {
			$count = 0;
			while ( ( $row = fgetcsv( $this->fileHandle, 0, "," ) ) !== false && count($row) == count($this->column_names) ) {
				$count ++;
				$data = array_combine( $this->column_names, $row );
				$this->import_process->push_to_queue( $data );

			}
			$this->import_process->save()->dispatch();
			$this->admin_notice_manager->add_new( 'Importing ' . $this->label . '.... ', 'notice', $this->admin_notice_key );


		}

	}


}
