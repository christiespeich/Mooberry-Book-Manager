jQuery( document ).ready(function() {
	// hide the bottom buy links if the excerpt is closed
	if (jQuery('.mbm-book-excerpt-read-more').is(":visible")) {
		jQuery('#mbm-book-links2').hide();
	}
	
	// handle the editions hide/show
	jQuery('.mbm-book-editions-toggle').click ( function() {
		jQuery(this).siblings('.mbm-book-editions-subinfo').toggle();
		jQuery(this).toggleClass('mbm-book-editions-open');
	});
	
	// open the excerpt
	jQuery('.mbm-book-excerpt-read-more').click ( function() {
		jQuery(this).siblings('.mbm-book-excerpt-text-hidden').toggle();
		jQuery(this).toggle();
		jQuery('#mbm-book-links2').show();
	});
	
	// close the excerpt
	jQuery('.mbm-book-excerpt-collapse').click ( function() {
		jQuery(this).parent().toggle();
		jQuery(this).parent().siblings('.mbm-book-excerpt-read-more').toggle();
		jQuery('#mbm-book-links2').hide();
		jQuery('html, body').animate({
					scrollTop: (jQuery('.mbm-book-excerpt-read-more').offset().top - 100)
					},500);
		
	});
}); // ready