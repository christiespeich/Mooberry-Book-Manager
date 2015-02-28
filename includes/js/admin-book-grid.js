
	if (document.getElementById('_mbdb_book_grid_order') != null) {
		document.getElementById('_mbdb_book_grid_order').addEventListener('change', orderByChange, false);
		orderByChange();
		}
	
	if (document.getElementById('_mbdb_book_grid_books') != null) {
		document.getElementById('_mbdb_book_grid_books').addEventListener('change', booksChange, false);
		booksChange();
	}
	
	if (document.getElementById('_mbdb_book_grid_display') != null) {
		document.getElementById('_mbdb_book_grid_display').addEventListener('change', displayChange, false);
		displayChange();
	}
	
	
	
	
	
	function orderByChange() {
		var grid_order = document.getElementById('_mbdb_book_grid_order');
		var genre_order = document.getElementsByClassName('cmb2-id--mbdb-book-grid-genre-order');
		
		
		if (grid_order.value == 'genre') {
			genre_order[0].style.display = 'block';
			
		} else {
			genre_order[0].style.display = 'none';
			
		}
		
	}
	
	function booksChange() {
		var books_select = document.getElementById('_mbdb_book_grid_books');
		var books_checkboxes = document.getElementsByClassName('cmb2-id--mbdb-book-grid-custom-select');
		var genre_checkboxes = document.getElementsByClassName('cmb2-id--mbdb-book-grid-genre');
		var series_checkboxes = document.getElementsByClassName('cmb2-id--mbdb-book-grid-series');
	
		
		
		if (books_select.value == 'custom') {
			books_checkboxes[0].style.display = 'block';
		} else {
			books_checkboxes[0].style.display = 'none';
		}
		if (books_select.value == 'genre') {
			genre_checkboxes[0].style.display = 'block';
		
		} else {
			genre_checkboxes[0].style.display = 'none';
		}
		if (books_select.value == 'series') {
			series_checkboxes[0].style.display = 'block';
		
		} else {
			series_checkboxes[0].style.display = 'none';
		}
	}
	
	function displayChange() {
		var display_grid = document.getElementById('_mbdb_book_grid_display');
		var books_select = document.getElementById('_mbdb_book_grid_books');
		var grid_order = document.getElementById('_mbdb_book_grid_order');
		var books_checkboxes = document.getElementsByClassName('cmb2-id--mbdb-book-grid-custom-select');
		var genre_checkboxes = document.getElementsByClassName('cmb2-id--mbdb-book-grid-genre');
		var series_checkboxes = document.getElementsByClassName('cmb2-id--mbdb-book-grid-series');
		var series_order = document.getElementsByClassName('cmb2-id--mbdb-book-grid-series-order');
		var book_grid_cover_size = document.getElementById('_mbdb_book_grid_cover_height');
		var book_grid_books_across = document.getElementById('_mbdb_book_grid_books_across');

		if (display_grid.value == 'yes') {
			books_select.removeAttribute("disabled");
			grid_order.removeAttribute("disabled");
			book_grid_cover_size.removeAttribute("disabled");
			book_grid_books_across.removeAttribute("disabled");
			if (books_checkboxes[0] != null) {
				books_checkboxes[0].removeAttribute("disabled");
			}
			if (genre_checkboxes[0] != null) {
				genre_checkboxes[0].removeAttribute("disabled");
			}
			if (series_checkboxes[0] != null) {
				series_checkboxes[0].removeAttribute("disabled");
			}
			if (series_order[0] != null) {
				series_order[0].removeAttribute("disabled");
			}
			
		} else {
			books_select.setAttribute("disabled", true);
			grid_order.setAttribute("disabled", true);
				book_grid_cover_size.setAttribute("disabled", true);
			book_grid_books_across.setAttribute("disabled", true);
			if (books_checkboxes[0] != null) {
				books_checkboxes[0].setAttribute("disabled", true);
			}
			if (genre_checkboxes[0] != null) {
				genre_checkboxes[0].setAttribute("disabled", true);
			}
			if (series_checkboxes[0] != null) {
				series_checkboxes[0].setAttribute("disabled", true);
			}
			if (series_order[0] != null) {
				series_order[0].setAttribute("disabled", true);
			}
		
		}
		
	
	
	}
