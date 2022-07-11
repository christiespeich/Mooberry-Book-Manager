jQuery( document ).ready(function() {
	jQuery('#mbdb_export').on('click', mbdb_export);
  jQuery('#mbdb_export_csv').on('click', mbdb_export_csv);
  jQuery('#mbdb_export_csv_columns').on('click', mbdb_export_csv_columns);
	jQuery('#mbdb_import_novelist').on('click', mbdb_import_novelist);
//	jQuery('#mbdb_import_button').bind('click', mbdb_import);

	//jQuery('[name=submit-cmb]').hide();

});

function mbdb_import_novelist() {
	jQuery('#mbdb_import_novelist_progress')
	  .show();
	jQuery('#mbdb_results')
	  .empty();

	var data = {
		'action': 'mbdb_import_novelist',
		'import_novelist': mbdb_admin_options_import_export_ajax.import_novelist_nonce
	};

	var ajax = jQuery.post(mbdb_admin_options_import_export_ajax.ajax_url, data);

	ajax.fail(function (data) {
		jQuery('#mbdb_import_novelist_progress')
		  .hide();
		jQuery('#mbdb_results')
		  .empty()
		  .append('Import Failed.');
	});
	ajax.done(function (data) {
		//jQuery('#mbdb_results').empty().append('Import Complete.');
		window.location = data;
	});

}
function mbdb_export_csv_columns() {
  mbdb_export_csv_file(true);
}

function mbdb_export_csv() {
  mbdb_export_csv_file(false);
}

function mbdb_export_csv_file( columns_only ) {
  jQuery('#mbdb_export_csv_progress').show();
	jQuery('#mbdb_results').empty();

	var data = {
		'action': 'mbdb_export_csv',
		'data':	jQuery(':input').serializeArray(),
    'columns_only': columns_only,
		'export_nonce': mbdb_admin_options_import_export_ajax.export_nonce
	};

	var ajax = jQuery.post(mbdb_admin_options_import_export_ajax.ajax_url, data);

	ajax.done( function (data ) {
		// open a new window to download file
		window.open(data);

	});
	ajax.always( function ( data ) {
		jQuery('#mbdb_export_csv_progress').hide();
	});
	ajax.fail ( function (data ) {
		jQuery('#mbdb_results').empty().append('Export Failed.');
	});

}

function mbdb_export() {
	jQuery('#mbdb_export_progress').show();
	jQuery('#mbdb_results').empty();

	var data = {
		'action': 'mbdb_export',
		'data':	jQuery(':input').serializeArray(),
		'export_nonce': mbdb_admin_options_import_export_ajax.export_nonce
	};

	var ajax = jQuery.post(mbdb_admin_options_import_export_ajax.ajax_url, data);

	ajax.done( function (data ) {
		//jQuery('#mbdb_results').empty().append(data);
		//console.log(data);
		// open a new window to download file
		window.open(data);
				//jQuery('#mbdb_results').empty().append(data);
		//window.open('export.php');

	});
	ajax.always( function ( data ) {
		jQuery('#mbdb_export_progress').hide();
	});
	ajax.fail ( function (data ) {
		jQuery('#mbdb_results').empty().append('Export Failed.');
	});

}

/*
function mbdb_import() {

	jQuery('#light').show();
	jQuery('#fade').show();



	jQuery('#mbdb_import_progress').show();

	var data = {
		'action': 'mbdb_import',
		'filename': 'export.txt',
		'import_nonce': mbdb_admin_options_import_export_ajax.import_nonce
	};
	console.log(jQuery('#mbdb_import_file'));
	var ajax = jQuery.post(mbdb_admin_options_import_export_ajax.ajax_url, data);

	ajax.done( function (data ) {
		jQuery('#mbdb_results').empty().append(data);
		//console.log(data);
		// open a new window to download file
		//window.open(data);
	});
	ajax.always( function ( data ) {
		jQuery('#mbdb_import_progress').hide();
	});
	ajax.fail ( function (data ) {
		jQuery('#mbdb_results').empty().append('Import Failed.');
	});

}

*/
