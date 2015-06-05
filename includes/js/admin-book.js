jQuery( document ).ready(function() {
	// only display publisher drop down if there are any
	// (first option = blank)
	// show a note to go to settings to add publishers only if there aren't any
	if (jQuery('#_mbdb_publisherID option').length == 1) {
		jQuery('#_mbdb_publisherID').hide();
	} else {
		jQuery('.cmb2-id--mbdb-publisherID .cmb2-metabox-description').hide();
	}
}); // document ready