<?php

//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
    exit();
	
remove_role('mbdb_librarian');
remove_role('mbdb_master_librarian');