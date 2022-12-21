jQuery( document ).ready(function() {
	// hide the bottom buy links if the excerpt is closed
	if (jQuery('.mbm-book-excerpt-read-more').is(":visible")) {
		jQuery('#mbm-book-links2').hide();
	}

	// handle the editions hide/show
	jQuery('.mbm-book-editions-toggle').on('click', function() {
		jQuery(this).siblings('.mbm-book-editions-subinfo').toggle();
		jQuery(this).toggleClass('mbm-book-editions-open');
	});

	// open the excerpt
	jQuery('.mbm-book-excerpt-read-more').on( 'click',  function() {
		jQuery(this).siblings('.mbm-book-excerpt-text-hidden').toggle();
		jQuery(this).toggle();
		jQuery('#mbm-book-links2').show();
	});

	// close the excerpt
	jQuery('.mbm-book-excerpt-collapse').on( 'click',  function() {
		jQuery(this).parent().toggle();
		jQuery(this).parent().siblings('.mbm-book-excerpt-read-more').toggle();
		jQuery('#mbm-book-links2').hide();
		jQuery('html, body').animate({
					scrollTop: (jQuery('.mbm-book-excerpt-read-more').offset().top - 100)
					},500);

	});

  // hover popup for grids and widgets
  jQuery(".mbdb_grid_image").on('mouseenter', mbdb_show_popup_card)
                                .on('mouseleave', mbdb_hide_popup_card );
  jQuery('.mbdb_book_widget').on('mouseenter',  mbdb_show_popup_card)
                                .on('mouseleave', mbdb_hide_popup_card );



}); // ready


 function mbdb_show_popup_card( event ) { //}, image, element ) {
    jQuery(this).find('.mbdb_book_info_popup').css({top: jQuery(this).height() * .75, left: jQuery(this).width() *.5 }).show();

}


  function mbdb_hide_popup_card() {
    jQuery(this).find('.mbdb_book_info_popup').hide();
  }
