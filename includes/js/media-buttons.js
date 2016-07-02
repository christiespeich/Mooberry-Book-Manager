jQuery( document ).ready(function() {
	
	// button_label comes from the PHP
	var add_button_label = button_label.add_button;
	var cancel_button_label = button_label.cancel_button;
	
	var dialog_buttons = {}; 
	dialog_buttons[add_button_label] = addGrid;
	dialog_buttons[cancel_button_label] = function(){ jQuery(this).dialog('close'); }   
	
	
	book_grid_shortcode_dialog = jQuery( "#mbdb_book_grid_shortcode_dialog" ).dialog({
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
	
	jQuery("#mbdb_add_book_grid" ).button().on( "click", function() {
      book_grid_shortcode_dialog.dialog( "open" );
    });
	
	
	
});

function addGrid() {
	window.send_to_editor('[mbm_book_grid id="' + jQuery('#mbdb_book_grids').val() + '"]');
	 book_grid_shortcode_dialog.dialog( "close" );
}
