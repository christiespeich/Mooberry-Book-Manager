jQuery( document ).ready(function() {
	// only display publisher drop down if there are any
	// (first option = blank)
	// show a note to go to settings to add publishers only if there aren't any
	if (jQuery('#_mbdb_publisherID option').length == 1) {
		jQuery('#_mbdb_publisherID').hide();
	} else {
		jQuery('.cmb2-id--mbdb-publisherID .cmb2-metabox-description').hide();
	}
	
	// default editions to closed
	jQuery('#mbdb_editions_metabox').toggleClass('closed', display_editions == 'no' );
	
	
	// show/hide excerpt options
	jQuery('#_mbdb_excerpt_type').on('change', mbdb_excerpt_type_change);
	mbdb_excerpt_type_change();
	
	
}); // document ready

function mbdb_excerpt_type_change() {
	var excerpt_type = jQuery('#_mbdb_excerpt_type').val();
	var excerpt_text = jQuery('.cmb2-id--mbdb-excerpt');
	var kindle_preview = jQuery('.cmb2-id--mbdb-kindle-preview');
	
	if ( excerpt_type == 'text' ) {
		excerpt_text.show();
		kindle_preview.hide();
	} else {
		excerpt_text.hide();
		kindle_preview.show();
	}
	
	
}