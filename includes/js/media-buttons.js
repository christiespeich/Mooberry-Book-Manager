jQuery( document ).ready(function() {
	
	book_grid_shortcode_dialog = jQuery( "#mbdb_book_grid_shortcode_dialog" ).dialog({
      autoOpen: false,
     // height: 250,
      width: 300,
      modal: true,
      buttons: {
        "Add Book Grid": addGrid,
        Cancel: function() {
          book_grid_shortcode_dialog.dialog( "close" );
        }
      },
      close: function() {
    
       // allFields.removeClass( "ui-state-error" );
      }
    });
	
	jQuery("#mbdb_add_book_grid" ).button().on( "click", function() {
      book_grid_shortcode_dialog.dialog( "open" );
    });
	
	
	
});

function addGrid() {
	window.send_to_editor('[mbm_book_grid id="' + jQuery('#mbdb_book_grids').val() + '"]');
	 book_grid_shortcode_dialog.dialog( "close" );
}
