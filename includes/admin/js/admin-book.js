var MBMisDirty = false;
var MBMPublishClicked = false;


	
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
	
	/*
	// trigger warning if not saved
	jQuery(':input').not('#publish').on('change', mbdb_input_change);
	jQuery('#publish').on('click', mbdb_save);
	
	jQuery( window ).bind('beforeunload', function() {
		console.log('beforeunload');
		// if the publish button was clicked, no need to check
		// if fields are dirty
		if ( !MBMPublishClicked ) {
			summary = typeof tinymce !== 'undefined' && tinymce.get( '_mbdb_summary' );
			excerpt = typeof tinymce !== 'undefined' && tinymce.get( '_mbdb_excerpt' );
			
			if ( summary !== null ) {
				summaryisDirty = summary.isDirty();
			} else {
				summaryisDirty = false;
			}
			if ( excerpt !== null ) {
				excerptisDirty = excerpt.isDirty();
			} else {
				excerptisDirty = false;
			}
			
			
			if ( MBMisDirty || summaryisDirty || excerptisDirty  ) {
				return 'The changes you made will be lost if you navigate away from this page.';	
			}
		}
	 });
	*/
	
	
	
}); // document ready

function mbdb_save() {
	MBMisDirty = false;
	MBMPublishClicked = true;
}

function mbdb_input_change() {	
	MBMisDirty = true;
}


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