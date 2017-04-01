jQuery( document ).ready(function() {
	
	//jQuery("[id$='-mbdb_widget_type']").filter('select').bind('change', function() {
		jQuery("#widgets-right").on("change", "[id$='-mbdb_widget_type']", function(event) {
		// select the div that is two parents up and then grab it's child with bookdropdown in id
		dropdown = jQuery(this).parentsUntil('div').parent().children("[id$='bookdropdown']").filter('div');
		if (this.value == 'specific') {
			dropdown.show();
		} else {
			dropdown.hide();
		}
	});
	
	
	
	
});






