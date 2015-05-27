
window.addEventListener('load', onLoad, false);

function onLoad() {
	if (document.getElementById('_mbdb_book_grid_display') != null) {
		document.getElementById('_mbdb_book_grid_display').addEventListener('change', displayChange, false);
		displayChange();
	}
		
	if (document.getElementById('_mbdb_book_grid_books') != null) {
		document.getElementById('_mbdb_book_grid_books').addEventListener('change', displayChange, false);
	}
	
	
	if (document.getElementById('_mbdb_book_grid_group_by') != null) {
		document.getElementById('_mbdb_book_grid_group_by').addEventListener('change', displayChange, false);
	}
	
	if (document.getElementById('_mbdb_book_grid_genre_group_by') != null) {
		document.getElementById('_mbdb_book_grid_genre_group_by').addEventListener('change', displayChange, false);
	}
	
	if (document.getElementById('_mbdb_book_grid_cover_height_default') != null) {
		document.getElementById('_mbdb_book_grid_cover_height_default').addEventListener('change', displayChange, false);
	}
	
	if (document.getElementById('_mbdb_book_grid_books_across_default') != null) {
		document.getElementById('_mbdb_book_grid_books_across_default').addEventListener('change', displayChange, false);
	}
}


	function toggle_display(surroundingElementClass, dropdownID, childElementClass, dropdownValue, shouldDisplay) {
		var dropdown = document.getElementById(dropdownID);
		var surroundingElement = document.getElementsByClassName(surroundingElementClass);
		var childElement =  document.getElementsByClassName(childElementClass);
		
		var match = (shouldDisplay ? 'block' : 'none');
		var nomatch = (!shouldDisplay ? 'block' : 'none');
		
		if (surroundingElement[0].style.display == 'block') {
			if ( dropdown.value == dropdownValue) {
				childElement[0].style.display = match;
			} else {
				childElement[0].style.display = nomatch;
			}
		}
	}
	
	
	function booksAcrossChange() {		
		toggle_display('cmb2-id--mbdb-book-grid-books-across-default', '_mbdb_book_grid_books_across_default', 'cmb2-id--mbdb-book-grid-books-across', 'yes', false);
	}
	
	function coverHeightChange() {
		toggle_display('cmb2-id--mbdb-book-grid-cover-height-default', '_mbdb_book_grid_cover_height_default', 'cmb2-id--mbdb-book-grid-cover-height', 'yes', false);
	}
	
	
	
	function genreGroupByChange() {
		
		toggle_display('cmb2-id--mbdb-book-grid-genre-group-by', '_mbdb_book_grid_genre_group_by', 'cmb2-id--mbdb-book-grid-order', 'series', false);
		
		
	}

	
	function groupByChange() {
		
		toggle_display('cmb2-id--mbdb-book-grid-group-by', '_mbdb_book_grid_group_by', 'cmb2-id--mbdb-book-grid-genre-group-by', 'genre', true);
		toggle_display('cmb2-id--mbdb-book-grid-group-by', '_mbdb_book_grid_group_by', 'cmb2-id--mbdb-book-grid-order', 'series', false);
	//	genreGroupByChange();
		
		
		
	}
	
	function booksChange() {
		toggle_display('cmb2-id--mbdb-book-grid-books', '_mbdb_book_grid_books', 'cmb2-id--mbdb-book-grid-custom-select', 'custom', true);
		toggle_display('cmb2-id--mbdb-book-grid-books', '_mbdb_book_grid_books', 'cmb2-id--mbdb-book-grid-genre', 'genre', true);
		toggle_display('cmb2-id--mbdb-book-grid-books', '_mbdb_book_grid_books', 'cmb2-id--mbdb-book-grid-series', 'series', true);
		
	}
	
	function displayChange() {
		var display_grid = document.getElementById('_mbdb_book_grid_display');
		var books_select = document.getElementsByClassName('cmb2-id--mbdb-book-grid-books');
		var grid_order = document.getElementsByClassName('cmb2-id--mbdb-book-grid-order');
		var books_checkboxes = document.getElementsByClassName('cmb2-id--mbdb-book-grid-custom-select');
		var genre_checkboxes = document.getElementsByClassName('cmb2-id--mbdb-book-grid-genre');
		var series_checkboxes = document.getElementsByClassName('cmb2-id--mbdb-book-grid-series');
		var group_by = document.getElementsByClassName('cmb2-id--mbdb-book-grid-group-by');
		var genre_group_by = document.getElementsByClassName('cmb2-id--mbdb-book-grid-genre-group-by');
		var book_grid_cover_size = document.getElementsByClassName('cmb2-id--mbdb-book-grid-cover-height');
		var book_grid_books_across = document.getElementsByClassName('cmb2-id--mbdb-book-grid-books-across');
		var cover_height_default = document.getElementsByClassName('cmb2-id--mbdb-book-grid-cover-height-default');
		var books_across_default = document.getElementsByClassName('cmb2-id--mbdb-book-grid-books-across-default');

		
		if (display_grid.value == 'yes') {
			books_select[0].style.display = 'block';
			group_by[0].style.display = 'block'
			cover_height_default[0].style.display = 'block';
			books_across_default[0].style.display = 'block';
			booksChange();
			groupByChange();
			genreGroupByChange();
			coverHeightChange();
			booksAcrossChange();
			
		} else {
			books_select[0].style.display = 'none';
			group_by[0].style.display = 'none'
			book_grid_cover_size[0].style.display = 'none';
			book_grid_books_across[0].style.display = 'none';
			books_checkboxes[0].style.display = 'none';
			genre_checkboxes[0].style.display = 'none';
			series_checkboxes[0].style.display = 'none';
			genre_group_by[0].style.display  = 'none';
			grid_order[0].style.display = 'none';
			cover_height_default[0].style.display = 'none';
			books_across_default[0].style.display = 'none';
		
		}
		
	
	
	}
