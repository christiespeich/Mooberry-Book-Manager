jQuery( document ).ready(function() {
	
	
	// bind the change event on all the drop downs in the book grid section
	jQuery('#cmb2-metabox-mbdb_book_grid').children().find('select').bind('change', displayChange);
	
	// set visibility of everything as needed
	displayChange();
});

function displayChange () {
	if (jQuery('#_mbdb_book_grid_display').val() == 'yes') {
	
		// show the options that don't change
		jQuery('.cmb2-id--mbdb-book-grid-books').show();
		jQuery('.cmb2-id--mbdb-book-grid-group-by').show();
		jQuery('.cmb2-id--mbdb-book-grid-order').show();
		jQuery('.cmb2-id--mbdb-book-grid-cover-height-default').show();
		
		// books to show
		var books = jQuery('#_mbdb_book_grid_books').val();
		// show the one that's selected
		jQuery('.cmb2-id--mbdb-book-grid-' + books).show();
		// hide the multichecks that aren't selected
		jQuery('#cmb2-metabox-mbdb_book_grid').children('.cmb-type-taxonomy-multicheck').not('.cmb2-id--mbdb-book-grid-' + books).hide();
		
		// book selection doesn't follow the same naming convention as tags, genre, and series selection
		// but renaming it would cause too much other code to change :-/
		if (books == 'custom') {
			jQuery('.cmb2-id--mbdb-book-grid-custom-select').show();
		} else {
			jQuery('.cmb2-id--mbdb-book-grid-custom-select').hide();
		}
		
		// group by
		// show the 2nd group by drop down that's selected
		var groupby = jQuery('#_mbdb_book_grid_group_by').val();
		jQuery('.cmb2-id--mbdb-book-grid-' + groupby + '-group-by').show();
		// and hide the others
		jQuery('.cmb2-id--mbdb-book-grid-group-by').nextAll("[class$='-group-by']").not('.cmb2-id--mbdb-book-grid-' + groupby + '-group-by').hide();
		
		
		// show the next level group by drop downs
		var groupby2 = jQuery('#_mbdb_book_grid_' + groupby + '_group_by').val();
		jQuery('.cmb2-id--mbdb-book-grid-' + groupby + '-' + groupby2 + '-group-by').show();
		// and hide the others
		jQuery('.cmb2-id--mbdb-book-grid-' + groupby + '-' + groupby2 + '-group-by').nextAll("[class$='-group-by']").not('.cmb2-id--mbdb-book-grid-' + groupby + '-' + groupby2 + '-group-by').hide();
		
				
		// order should be hidden if series is selected
		// get the last visible group-by drop down
		if (jQuery('.cmb2-id--mbdb-book-grid-group-by').nextAll("[class$='-group-by']").andSelf().filter(':visible:last').find('select').val() == 'series') {
			jQuery('.cmb2-id--mbdb-book-grid-order').hide();
		} else {
			jQuery('.cmb2-id--mbdb-book-grid-order').show();
		}
		
		// cover height
		if (jQuery('#_mbdb_book_grid_cover_height_default').val() == 'yes') {
			jQuery('.cmb2-id--mbdb-book-grid-cover-height').hide();
		} else {
			jQuery('.cmb2-id--mbdb-book-grid-cover-height').show();
		}
		
	} else {
		// hide all the book grid divs if "no" is selected
		jQuery('.cmb2-id--mbdb-book-grid-display').nextAll('div').hide();
	}
}