jQuery( document ).ready(function() {
	
	jQuery('#reset_meta_boxes').click(function (e) {
			if (confirm(mbdb_admin_options_ajax.translation)) {
				reset_meta_boxes();
			}
	});
	
	jQuery(document).on({
    ajaxStart: function() { jQuery('#reset_progress').show();    },
     ajaxStop: function() { jQuery('#reset_progress').hide(); }    
});
	
		
		
		
	/*
	jQuery('#mbdb_settings_metabox').submit(function (e) {
		var bad_name = [];
		
		// data_obj comes from PHP
		var reserved_terms = data_obj.reserved_terms;
		var flag = false;
		jQuery('input[type=text][id$=slug]').each(function (idx) {
			if (jQuery.inArray( this.value, reserved_terms ) > -1 ) {			
				jQuery(this).css('border', 'solid 1px red');
				bad_name.push(jQuery(this).parent().prev().children('label[for=' + this.id + ']').text()); //.text());
			}
		});
		if (bad_name.length >0) {
			e.preventDefault();
		}
	});
	*/
});
/*
function popup( link ) {
	if (! window.focus) return true; 
	var href; 
	if (typeof(mylink) == 'string') 
		href=mylink; 
	else 
		href=mylink.href; 
	window.open(href, 'Reserved Terms', 'width=400,height=200,scrollbars=yes'); 
	return false;
}
*/
function reset_meta_boxes() {

	
	var data = {
			'action': 'mbdb_reset_meta_boxes',
			'security': mbdb_admin_options_ajax.ajax_nonce
	};
	var ajax = jQuery.post(mbdb_admin_options_ajax.ajax_url, data);
	
	ajax.done( function ( data ) {
		jQuery('#reset_complete').show();
	});
	
	

	
	
}
