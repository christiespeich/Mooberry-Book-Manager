jQuery( document ).ready(function() {
	jQuery('.mbm-book-editions-toggle').click ( function() {
		jQuery(this).siblings('.mbm-book-editions-subinfo').toggle();
		jQuery(this).toggleClass('mbm-book-editions-open');
	});
	jQuery('.mbm-book-excerpt-read-more').click ( function() {
		jQuery(this).siblings('.mbm-book-excerpt-text-hidden').toggle();
		jQuery(this).toggle();
	});
	jQuery('.mbm-book-excerpt-collapse').click ( function() {
		jQuery(this).parent().toggle();
		jQuery(this).parent().siblings('.mbm-book-excerpt-read-more').toggle();
		jQuery('html, body').animate({
					scrollTop: (jQuery('.mbm-book-excerpt-read-more').offset().top - 100)
					},500);
		
	});
}); // ready