jQuery( document ).ready(function() {
	
	// button_label comes from the PHP
	var add_button_label = button_label.add_button;
	var cancel_button_label = button_label.cancel_button;
	
	var dialog_buttons = {}; 
	dialog_buttons[add_button_label] = addGrid;
	dialog_buttons[cancel_button_label] = function(){ jQuery(this).dialog('close'); }   
	
	
	book_grid_shortcode_dialog = jQuery( "[id^=mbdb_book_grid_shortcode_dialog]" ).dialog({
      autoOpen: false,
     // height: 250,
      width: 'auto',
      modal: true,
      buttons: dialog_buttons /*{
        add_button_label: addGrid,
        Cancel: function() {
          book_grid_shortcode_dialog.dialog( "close" );
        }
      },
      close: function() {
    
       // allFields.removeClass( "ui-state-error" );
      } */
    });
	
	jQuery("[id^=mbdb_add_book_grid_]" ).button().on( "click", function() {
		// because there could be multiple editors with shortcode buttons
		// we have to use RegEx to extract the name of the editor
		// to know we're getting the right one
		editor = getEditor( 'mbdb_add_book_grid', jQuery(this)[0].id );
		jQuery( "#mbdb_book_grid_shortcode_dialog_" + editor ).dialog( "open" );
    });
	
	
	
});

function regexEscape(str) {
    return str.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&')
}

// because there could be multiple editors with shortcode buttons
// we have to use RegEx to extract the name of the editor
// to know we're getting the right one
function getEditor( element_id, editor_id ) {
	
	element_id = regexEscape( element_id );
	var regExp = new RegExp( element_id + '_([a-zA-z_]+)');
	
	var matches = regExp.exec(editor_id);
	//matches[1] contains the value between the parentheses
	if ( matches ) {
		editor = matches[1];
	} else {
		editor = '';
	}
	
	return editor;
}

function addGrid() {
	// because there could be multiple editors with shortcode buttons
	// we have to use RegEx to extract the name of the editor
	// to know we're getting the right one
	editor = getEditor( 'mbdb_book_grid_shortcode_dialog', jQuery(this)[0].id );
	window.send_to_editor('[mbm_book_grid id="' + jQuery('#mbdb_book_grids_' + editor).val() + '"]');
	 jQuery( "#mbdb_book_grid_shortcode_dialog_" + editor ).dialog( "close" );
}
