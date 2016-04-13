jQuery( document ).ready(function() {
	
	jQuery('.mbdb_admin_notice_dismiss').click(function (e) {
			
		mbdb_admin_notice_dismiss(this);
			
	});
	
	jQuery('#mbdb_3_1_remigrate').click( function (e) {
	
		mbdb_admin_3_1_remigrate();
	});
});


function mbdb_admin_notice_dismiss(btnClicked) {
	var key = jQuery(btnClicked).attr('data-admin-notice');
	
	var data = {
		'action': 'mbdb_admin_notice_dismiss',
		'admin_notice_key': key,
		'security': mbdb_admin_notice_ajax.dismiss_ajax_nonce
	};
	
	var mbdb_dismiss = jQuery.post(mbdb_admin_notice_ajax.ajax_url, data);
	
	mbdb_dismiss.done( function ( data ) {
		jQuery('#' + key).hide();
	});
}

function mbdb_admin_3_1_remigrate() {
	var data = {
		'action':	'mbdb_admin_3_1_remigrate',
		'security':	mbdb_admin_notice_ajax.remigrate_ajax_nonce
	};
	
	var mbdb_remigrate = jQuery.post( mbdb_admin_notice_ajax.ajax_url, data);
	
	mbdb_remigrate.done ( function (data ) {
		window.location.href = mbdb_admin_notice_ajax.redirect_url;
	});
	
}
