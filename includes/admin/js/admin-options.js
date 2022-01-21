jQuery( document ).ready(function() {

  jQuery('#reset_meta_boxes')
    .on('click', function (e) {
      if (confirm(mbdb_admin_options_ajax.translation)) {
        reset_meta_boxes();
      }
    });

  jQuery('#mbdb_cancel_import')
    .on('click', function (e) {
      if (confirm(mbdb_admin_options_ajax.cancel_import_translation)) {
        mbdb_cancel_import();
      }
    });

  jQuery('#mbdb_create_tax_grid_page_button').on('click', mbdb_create_tax_grid_page);


  /* retailers page */
  mbdb_retailer_buttons_change();
  jQuery('[name=retailer_buttons]').on('change', mbdb_retailer_buttons_change );


  jQuery('[name$="[retailer_button_image]"]').on( 'change', mbdb_set_retailer_button_image_options);


  jQuery('#retailer_buttons_color').wpColorPicker({
                     change: function (event, ui) {
                       mbdb_retailer_test_button_color_change(jQuery('#mbdb_test_retailer_button'),                                                                                   'background-color',
                                                                      ui);
                     }
    });

  jQuery('#retailer_buttons_color_text').wpColorPicker({
                     change: function (event, ui) {
                       mbdb_retailer_test_button_color_change(jQuery('#mbdb_test_retailer_button'), 'color', ui);
                     }
  });

  jQuery('[id$="retailer_button_color"]').wpColorPicker({
                     change: function (event, ui) {
                       mbdb_retailer_test_button_color_change(jQuery(this).closest('.cmb-row').siblings('.mbdb_retailer_button_preview').find('.mbdb_retailer_button'), 'background-color', ui);
                     }
                   });

  jQuery('[id$="retailer_button_color_text"]').wpColorPicker({
                     change: function (event, ui) {
                       mbdb_retailer_test_button_color_change(jQuery(this).closest('.cmb-row').siblings('.mbdb_retailer_button_preview').find('.mbdb_retailer_button'), 'color', ui);
                     }
                   });

});

function reset_meta_boxes() {


	var data = {
			'action': 'mbdb_reset_meta_boxes',
			'security': mbdb_admin_options_ajax.ajax_nonce
	};
	var ajax = jQuery.post(mbdb_admin_options_ajax.ajax_url, data);

	ajax.done( function ( data ) {
		jQuery('#reset_complete').show();
		 jQuery('#reset_progress').hide();
	});

}

function mbdb_cancel_import() {
	var data = {
			'action':	'mbdb_cancel_import',
			'security':	mbdb_admin_options_ajax.ajax_cancel_import_nonce
	};
	jQuery('#mbdb_cancel_import_progress').show();
	var ajax = jQuery.post(mbdb_admin_options_ajax.ajax_url, data);
	ajax.done ( function (data) {
		jQuery('#mbdb_cancel_import_progress').hide();
		jQuery('#mbdb_cancel_results').empty().append( data);
	});

}

function mbdb_create_tax_grid_page() {
	var data = {
			'action':	'mbdb_add_tax_grid_page',
			'security':	mbdb_admin_options_ajax.ajax_create_tax_grid_page_nonce
	};
	jQuery('#mbdb_create_tax_grid_page_progress').show();
	var ajax = jQuery.post( mbdb_admin_options_ajax.ajax_url, data);
	ajax.done ( function ( data ) {
		jQuery('#mbdb_create_tax_grid_page_progress').hide();
		if ( data == 0 ) {
			result = mbdb_admin_options_ajax.create_tax_grid_page_fail_translation;
		} else {
			result = mbdb_admin_options_ajax.create_tax_grid_page_success_translation;
			jQuery('#mbdb_tax_grid_page').append('<option value="' + data + '">MBM Tax Grid Page</option>');
			jQuery('#mbdb_tax_grid_page').val( data );
		}
		jQuery('#mbdb_create_tax_grid_page_results').empty().append( result );
	});
}

function mbdb_retailer_test_button_color_change( element, property, ui ) {
  element.css(property, ui.color.toString());
}

function mbdb_retailer_buttons_change() {

  if ( jQuery('[name=retailer_buttons]:checked').val() === 'matching' ) {
    jQuery('.cmb2-id-retailer-buttons-color-title').show();
    jQuery('.cmb2-id-retailer-buttons-color').show();
    jQuery('.cmb2-id-retailer-buttons-color-text').show();
    jQuery('[name$="[retailer_button_image]"]').closest('.cmb-row').hide();
    jQuery('[id$="_retailer_test_button"]').closest('.cmb-row').hide();
    jQuery('[id$="retailer_button_color"]').closest('.cmb-row').hide();
    jQuery('[id$="retailer_button_color_text"]').closest('.cmb-row').hide();
    jQuery('[name$="[image]"]').closest('.cmb-row').hide();
  } else {
    jQuery('.cmb2-id-retailer-buttons-color-title').hide();
    jQuery('.cmb2-id-retailer-buttons-color').hide();
    jQuery('.cmb2-id-retailer-buttons-color-text').hide();
    jQuery('[name$="[retailer_button_image]"]').closest('.cmb-row').show();
    jQuery('[id$="_retailer_test_button"]').closest('.cmb-row').show();
    jQuery('[id$="retailer_button_color"]').closest('.cmb-row').show();
    jQuery('[id$="retailer_button_color_text"]').closest('.cmb-row').show();
    jQuery('[name$="[image]"]').closest('.cmb-row').show();

    jQuery.each(jQuery('[name$="[retailer_button_image]"]:checked'), mbdb_set_retailer_button_image_options);
  }
}



function mbdb_set_retailer_button_image_options() {
    var checked_option = jQuery(this);
    var parent = checked_option.closest('.cmb-type-radio');
   if ( checked_option.val() === 'button' ) {
     parent.nextAll('.mbdb_retailer_button_preview').first().show();
     parent.nextAll('.cmb-type-colorpicker').first().show().next().show();
     parent.nextAll('.cmb-type-file').first().hide();
   } else {
     parent.nextAll('.mbdb_retailer_button_preview').first().hide();
     parent.nextAll('.cmb-type-colorpicker').first().hide().next().hide();
     parent.nextAll('.cmb-type-file').first().show();
   }
}

