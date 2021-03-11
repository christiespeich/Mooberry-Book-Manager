jQuery( document ).ready(function() {

	jQuery('.mbdb_admin_notice_dismiss')
    .on('click', function (e) {
		  mbdb_admin_notice_dismiss(this);
	  });

	jQuery('#mbdb_3_1_remigrate')
	  .on('click', function (e) {
		  mbdb_admin_3_1_remigrate();
	  });

	jQuery('#mbdb_book_grid_placeholder_dismiss')
	  .on('click', function (e) {
		  mbdb_book_grid_placeholder_dismiss();
	  });

	jQuery('#mbdb_3_4_12_update')
	  .on('click', function (e) {
		  mbdb_3_4_12_update();
	  });

	jQuery('#mbdb_update_apple_links_button')
	  .on('click', function (e) {
		  mbdb_update_apple_books_links();
	  })

});


function mbdb_admin_notice_dismiss(btnClicked) {
	var key = jQuery(btnClicked)
	  .attr('data-admin-notice');

	var data = {
		'action': 'mbdb_admin_notice_dismiss',
		'admin_notice_key': key,
		'security': mbdb_admin_notice_ajax.dismiss_ajax_nonce
	};

	var mbdb_dismiss = jQuery.post(mbdb_admin_notice_ajax.ajax_url, data);

	mbdb_dismiss.done(function (data) {
		jQuery('#' + key)
		  .hide();
	});
}

function mbdb_admin_3_1_remigrate() {
	var data = {
		'action': 'mbdb_admin_3_1_remigrate',
		'security': mbdb_admin_notice_ajax.remigrate_ajax_nonce
	};

	var mbdb_remigrate = jQuery.post(mbdb_admin_notice_ajax.ajax_url, data);

	mbdb_remigrate.done(function (data) {
		window.location.href = mbdb_admin_notice_ajax.redirect_url;
	});

}

function mbdb_book_grid_placeholder_dismiss() {

	jQuery('#mbdb_book_grid_dismiss_loader')
	  .show();

	var data = {
		'action': 'mbdb_book_grid_placeholder_dismiss',
		'security': mbdb_admin_notice_ajax.book_grid_placeholder_dismiss_nonce
	};

	var mbdb_book_grid_dismiss = jQuery.post(mbdb_admin_notice_ajax.ajax_url, data);

	mbdb_book_grid_dismiss.done(function (data) {
		jQuery('#mbdb_book_grid_placeholder')
		  .hide();
	});
}

function mbdb_3_4_12_update() {
	jQuery('#mbdb_3_4_12_loading')
	  .show();
	var original_url = window.location.href;
	var data = {
		'action': 'mbdb_3_4_12_update',
		'security': mbdb_admin_notice_ajax.mbdb_3_4_12_update_nonce
	};

	var mbdb_3_4_12_update = jQuery.post(mbdb_admin_notice_ajax.ajax_url, data);

	mbdb_3_4_12_update.done(function (data) {
		//jQuery('#3_4_12_tax_fix').hide();
	});

	mbdb_3_4_12_update.always(function (data) {
		//	jQuery('#mbdb_3_4_12_loading').hide();
		window.location.href = original_url;
	});
}

function mbdb_update_apple_books_links() {
	var data = {
		'action': 'mbdb_update_apple_books_links',
		'update_apple_books_links_nonce': mbdb_admin_notice_ajax.update_apple_books_link_nonce
	};
	var original_url = window.location.href;
	var ajax = jQuery.post(mbdb_admin_notice_ajax.ajax_url, data);

	ajax.done(function (data) {
		window.location.href = original_url;
	});
}
