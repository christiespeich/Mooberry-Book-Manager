<?php


class MBDB_Book_CSV_Importer extends Mooberry_Dreams_CSV_Importer {


	public function __construct( $file, $import_process, $admin_notice_manager ) {
		parent::__construct( $file, $import_process, $admin_notice_manager );
		$this->label            = 'books';
		$this->admin_notice_key = 'MBDB_Import_Books_CSV';
	}
}
