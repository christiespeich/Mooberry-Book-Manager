jQuery( document ).ready(function() {
	
	
	
	jQuery('#publish').bind('click', mbdb_save_book_list_order);
	
	// bind the change event on all the drop downs in the book grid section
	jQuery('#cmb2-metabox-mbdb_book_grid_metabox').children().find('select').bind('change', displayChange);
	
	// set visibility of everything as needed
	displayChange();
	
	jQuery('.cmb2-id--mbdb-book-grid-custom input').bind( 'change', book_selection_change );
	
	// make the grid sortable
	jQuery('#_mbdb_book_grid_book_list').sortable({
		opacity: 0.5,
		placeholder : 'ui-state-highlight',
		cursor: 'pointer',
		create: mbdb_book_list_order_update,
		update: mbdb_book_list_order_update,
		deactivate: function () {
				window.unsaved_changes = true;
			}
	});
	
});

function displayChange () {
	if (jQuery('#_mbdb_book_grid_display').val() == 'yes') {
	
		// text_to_translate comes from the PHP
		var label1 = text_to_translate.label1;
		var label2 = text_to_translate.label2;
		var group_by_options = text_to_translate.groupby;
		var custom_sort = text_to_translate.custom_sort;
		
		var selected = [];
		
		// show the options that don't change
		jQuery('.cmb2-id--mbdb-book-grid-books').show();
		jQuery('.cmb2-id--mbdb-book-grid-group-by-level-1').show();
		jQuery('.cmb2-id--mbdb-book-grid-order').show();
		jQuery('.cmb2-id--mbdb-book-grid-cover-height-default').show();
		
		
		// SELECT 
		// books to show and convert _ to -
		var books = jQuery('#_mbdb_book_grid_books').val().replace('_','-');

		
		// show the one that's selected
		jQuery('.cmb2-id--mbdb-book-grid-' + books).show();
		// hide the multichecks that aren't selected
		jQuery('#cmb2-metabox-mbdb_book_grid_metabox').children('.cmb-type-multicheck').not('.cmb2-id--mbdb-book-grid-' + books).hide();
		
		// book selection doesn't follow the same naming convention as tags, genre, and series selection
		// but renaming it would cause too much other code to change :-/
		// if (books == 'custom') {
			// jQuery('.cmb2-id--mbdb-book-grid-custom-select').show();
		// } else {
			// jQuery('.cmb2-id--mbdb-book-grid-custom-select').hide();
		// }
		
		// GROUP BY 
		
		// hide the warning by default
		jQuery('.cmb2-id--mbdb-book-grid-warning').hide();
		
		level = 1;
		var groupby_dropdown = jQuery('#_mbdb_book_grid_group_by_level_' + level);
		
		// loop through all visible groupby drop downs
		// groupby_downdown = current groupby drop down
		while (groupby_dropdown.is(':visible')) {
			// grab the selected item
			 var selected_group = groupby_dropdown.val();
			 var selected_group_text = groupby_dropdown.find('option:selected').text();
			 // add it to the array of selected items
			 selected.push(selected_group);
			 // if neither none nor series is selected
			 if (selected_group != 'none' && selected_group != 'series') {
				// show new drop down
					var next_level = jQuery('#_mbdb_book_grid_group_by_level_' + (level + 1));
					next_level.parents('.cmb-row').show();
					next_level.parents('.cmb-row').find('.cmb-th').find('label').text(label1 + ' ' + selected_group_text + ' ' + label2);
				// grab the selected item
				var next_selected = next_level.val();
				
				// populate it with everything that hasn't been selected yet
				// 1. remove all options
				next_level.find('option').remove();				
				// 2. add all options
				jQuery.each(group_by_options, function(key, value) {   
					if (jQuery.inArray(key, selected) == -1) {
						next_level.append(jQuery('<option>', { value : key }).text(value)); 
					}
				});				
				// reselect the selected option if it still exists, otherwise "none"
				next_level.val(next_selected);
				if (next_level.val() == null) {
					next_level.val('none');
				}
				
				// show the warning if level >4
				if (level > 4) {
					jQuery('.cmb2-id--mbdb-book-grid-warning').show();
				} else {
					jQuery('.cmb2-id--mbdb-book-grid-warning').hide();
				}
			 } else {
				// remove all following group by drop downs
				x = level+1;
				var dropdown = jQuery('#_mbdb_book_grid_group_by_level_' + x)
				while (dropdown.length > 0) {
					
					dropdown.parents('.cmb-row').hide();
					x++;
					dropdown = jQuery('#_mbdb_book_grid_group_by_level_' + x)
				}
			 }
			 // move to the next drop down
			 level++;
			 groupby_dropdown = jQuery('#_mbdb_book_grid_group_by_level_' + level);
		}
		/*
		
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
		*/
			

		// ORDER BY
			
		// Add custom when selecting custom books and not grouping
		if (jQuery('#_mbdb_book_grid_books').val() == 'custom' && jQuery('#_mbdb_book_grid_group_by_level_1').val() == 'none') {
			// only add if it's not already there
			if (jQuery("#_mbdb_book_grid_order option[value='custom']").length == 0 ) {
				jQuery('#_mbdb_book_grid_order').append(jQuery('<option></option>').val('custom').html(custom_sort));
			}
		} else {
			jQuery("#_mbdb_book_grid_order option[value='custom']").remove();
		}
		
		// order should be hidden if series is selected
		// get the last visible group-by drop down
		if (jQuery('.cmb2-id--mbdb-book-grid-group-by-level-1').nextAll("[class*='-group-by-level']").andSelf().filter(':visible:last').find('select').val() == 'series') {
			jQuery('.cmb2-id--mbdb-book-grid-order').hide();
		} else {
			jQuery('.cmb2-id--mbdb-book-grid-order').show();
		}
		
		// if order is visible and set to custom, show the sorting grid
		
		if ( jQuery('.cmb2-id--mbdb-book-grid-order').is(":visible") && jQuery('#_mbdb_book_grid_order').val() == 'custom' ) {
			jQuery('#_mbdb_bookd_grid_custom_order').show();
		} else {
			jQuery('#_mbdb_bookd_grid_custom_order').hide();
		}
		
		
		
		// cover height 
		if (jQuery('#_mbdb_book_grid_cover_height_default').val() == 'yes') {
			jQuery('.cmb2-id--mbdb-book-grid-cover-height').hide();
		} else {
			jQuery('.cmb2-id--mbdb-book-grid-cover-height').show();
		}
		
		// additional content
		jQuery('.cmb2-id--mbdb-book-grid-description-bottom').show();
		
	} else {
		// hide all the book grid divs if "no" is selected
		jQuery('.cmb2-id--mbdb-book-grid-display').nextAll('div').hide();
	}
}


function book_selection_change() {
	
	book_list = jQuery('#_mbdb_book_grid_book_list');
	
	// empty check list
	jQuery( '#_mbdb_book_grid_book_list li').remove();
	
	// add all selected books
	console.log(jQuery('.cmb2-id--mbdb-book-grid-custom input:checkbox:checked'));
	jQuery('.cmb2-id--mbdb-book-grid-custom input:checkbox:checked').each( function () {
		console.log(jQuery(this).val());
		var li = jQuery('<li>')
			.attr('id', 'mbdb_custom_book_order_book_' + jQuery(this).val() )
			.addClass('ui-state-default')
			.text( jQuery(this).next('label').text() )
			.prepend('<span class="ui-icon"></span>')
			.appendTo(book_list);
			mbdb_book_list_order_update();
			/*
			book_list.append('<li id="' + jQuery(this).val() + '">' & jQuery(this).next('label').text() + '<li>');
			//.text( jQuery(this).next('label').text() )
			*/
			console.log(li);
		});
	
	
}

function mbdb_book_list_order_update() {
	console.log('update');
	// remove all the classes and add ui-icon on all of the items in the grid
	jQuery('#_mbdb_book_grid_book_list li span').removeClass().addClass('ui-icon');
	// add a down arrow to the first item
	jQuery('#_mbdb_book_grid_book_list li:first span').addClass('ui-icon-arrowthick-1-s');
	// add an up arrow to the last item
	jQuery('#_mbdb_book_grid_book_list li:last span').addClass('ui-icon-arrowthick-1-n');
	// add an up and down arrow to any non-first and non-last item
	jQuery('#_mbdb_book_grid_book_list li').not(':first').not(':last').children('span').addClass('ui-icon-arrowthick-2-n-s');
}

// save the sorted grid via ajax
function mbdb_save_book_list_order() {
	var data = {
			'action': 'save_book_list_order',
			'pageID': jQuery('#post_ID').val(),
			'books': jQuery('#_mbdb_book_grid_book_list').sortable('serialize'),
			'security': book_grid_ajax_object.security
	};
	
	jQuery.post(ajax_object.ajax_url, data, mbdb_book_list_order_after_save);
}
    
// function that's called after the save grid ajax
// not really anything to do...
function mbdb_book_list_order_after_save() {
	
}