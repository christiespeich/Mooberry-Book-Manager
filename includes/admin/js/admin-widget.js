jQuery( document ).ready(function() {

  // Pre WP 5.8
  jQuery("#widgets-right").on("change", "[id$='-mbdb_widget_type']", mbdb_book_type_change );

  // WP 5.8
  jQuery( document ).on( 'widget-added', function () {
    mbdb_book_type_change();
    jQuery("[id$='-mbdb_widget_type']").on("change",  mbdb_book_type_change );
  });


});


function mbdb_book_type_change() {
  // select the div that is two parents up and then grab it's child with bookdropdown in id
  dropdown = jQuery(this)
    .parentsUntil('div')
    .parent()
    .children("[id$='bookdropdown']")
    .filter('div');
  if (this.value === 'specific') {
    dropdown.show();
  } else {
    dropdown.hide();
  }
}



