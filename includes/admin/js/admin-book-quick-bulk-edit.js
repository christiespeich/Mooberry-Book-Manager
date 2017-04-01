jQuery( document ).ready(function() {

	
	// we create a copy of the WP inline edit post function
	var $wp_inline_edit = inlineEditPost.edit;
	
	// and then we overwrite the function with our own code
	inlineEditPost.edit = function( id ) {
	
		// "call" the original WP edit function
		// we don't want to leave WordPress hanging
		$wp_inline_edit.apply( this, arguments );
		
		// now we take care of our business
		
		// get the post ID
		var $post_id = 0;
		if ( typeof( id ) == 'object' )
			$post_id = parseInt( this.getId( id ) );
			
		if ( $post_id > 0 ) {
		
			// define the edit row
			var $edit_row = jQuery( '#edit-' + $post_id );
			
			// get the release date
			var $release_date = jQuery( '#release_date-' + $post_id ).text();
		
			// set the release date
			$edit_row.find( 'input[name="_mbdb_published"]' ).val( $release_date );
			
			// get the series order
			var $series_order = jQuery( '#series_order-' + $post_id ).text();
			
			// set the series order
			$edit_row.find( 'input[name="_mbdb_series_order"]').val( $series_order );
			
			// get the publisher
			var $publisher_id = jQuery( '#publisher_id-' + $post_id ).text();
			
			// set the publisher
			$edit_row.find( 'select[name="_mbdb_publisherID"]' ).val( $publisher_id );
			
			// get the subtitle
			var $subtitle = jQuery( '#subtitle-' + $post_id).text();
			
			// set the subtitle
			$edit_row.find( 'input[name="_mbdb_subtitle"]' ).val( $subtitle);
			
			// get the goodreads link
			var $goodreads = jQuery( '#goodreads-' + $post_id).text();
			
			// set goodreads
			$edit_row.find( 'input[name="_mbdb_goodreads"]' ).val( $goodreads );
			
		}
		
	};
	
	jQuery( '#bulk_edit' ).live( 'click', function() {
	
		// define the bulk edit row
		var $bulk_row = jQuery( '#bulk-edit' );
		
		// get the selected post ids that are being edited
		var $post_ids = new Array();
		$bulk_row.find( '#bulk-titles' ).children().each( function() {
			$post_ids.push( jQuery( this ).attr( 'id' ).replace( /^(ttle)/i, '' ) );
		});
		
		// get the custom fields
	
		var $publisher_id = $bulk_row.find( 'select[name="_mbdb_publisherID"]' ).val();
		
		// save the data
		jQuery.ajax({
			url: ajaxurl, // this is a variable that WordPress has already defined for us
			type: 'POST',
			async: false,
			cache: false,
			data: {
				action: 'bulk_quick_save_bulk_edit', // this is the name of our WP AJAX function that we'll set up next
				post_ids: $post_ids, // and these are the 2 parameters we're passing to our function
				_mbdb_publisherID: $publisher_id
			}
		});
		
	});
	
	
	
	
});