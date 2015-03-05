
	
	if (document.getElementById('_mbdb_book_grid_books') != null) {
		document.getElementById('_mbdb_book_grid_books').addEventListener('change', booksChange, false);
		booksChange();
	}
	
	if (document.getElementById('_mbdb_book_grid_display') != null) {
		document.getElementById('_mbdb_book_grid_display').addEventListener('change', displayChange, false);
		displayChange();
	}
	
	if (document.getElementById('_mbdb_book_grid_group_by') != null) {
		document.getElementById('_mbdb_book_grid_group_by').addEventListener('change', groupByChange, false);
		groupByChange();
	}
	
	if (document.getElementById('_mbdb_book_grid_genre_group_by') != null) {
		document.getElementById('_mbdb_book_grid_genre_group_by').addEventListener('change', genreGroupByChange, false);
		genreGroupByChange();
	}
	
	function genreGroupByChange() {
		var genre_group_by = document.getElementById('_mbdb_book_grid_genre_group_by');
		var grid_order =  document.getElementsByClassName('cmb2-id--mbdb-book-grid-order');
		
		if (genre_group_by.value == 'series') {
			grid_order[0].style.display = 'none';
		} else {
			grid_order[0].style.display = 'block';
			
		}
		
	}
	
	
	function groupByChange() {
		var group_by = document.getElementById('_mbdb_book_grid_group_by');
		var genre_group_by = document.getElementsByClassName('cmb2-id--mbdb-book-grid-genre-group-by');
		var grid_order =  document.getElementsByClassName('cmb2-id--mbdb-book-grid-order');
		
		if (group_by.value == 'genre') {
			genre_group_by[0].style.display = 'block';
		} else {
			genre_group_by[0].style.display = 'none';
		}
		
		if (group_by.value == 'series') {
			grid_order[0].style.display = 'none';
		} else {
			grid_order[0].style.display = 'block';
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
		var books_select = document.getElementsByClassName('cmb2-id--mbdb-book-grid-books');
		var grid_order = document.getElementsByClassName('cmb2-id--mbdb-book-grid-order');
		var books_checkboxes = document.getElementsByClassName('cmb2-id--mbdb-book-grid-custom-select');
		var genre_checkboxes = document.getElementsByClassName('cmb2-id--mbdb-book-grid-genre');
		var series_checkboxes = document.getElementsByClassName('cmb2-id--mbdb-book-grid-series');
		var group_by = document.getElementsByClassName('cmb2-id--mbdb-book-grid-group-by');
		var genre_group_by = document.getElementsByClassName('cmb2-id--mbdb-book-grid-genre-group-by');
		var book_grid_cover_size = document.getElementsByClassName('cmb2-id--mbdb-book-grid-cover-height');
		var book_grid_books_across = document.getElementsByClassName('cmb2-id--mbdb-book-grid-books-across');

		if (display_grid.value == 'yes') {
			books_select[0].style.display = 'block';
			group_by[0].style.display = 'block'
			book_grid_cover_size[0].style.display = 'block';
			book_grid_books_across[0].style.display = 'block';
			booksChange();
			groupByChange();
			genreGroupByChange();
			// books_select.removeAttribute("disabled");
			// grid_order.removeAttribute("disabled");
			// book_grid_cover_size.removeAttribute("disabled");
			// book_grid_books_across.removeAttribute("disabled");
			// group_by.removeAttribute("disabled");
			
			// if (books_checkboxes[0] != null) {
				// books_checkboxes[0].removeAttribute("disabled");
			// }
			// if (genre_checkboxes[0] != null) {
				// genre_checkboxes[0].removeAttribute("disabled");
			// }
			// if (series_checkboxes[0] != null) {
				// series_checkboxes[0].removeAttribute("disabled");
			// }
			
			// if (genre_group_by != null) {
				// genre_group_by.removeAttribute("disabled");
			// }
			
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
			// books_select.setAttribute("disabled", true);
			// grid_order.setAttribute("disabled", true);
			// book_grid_cover_size.setAttribute("disabled", true);
			// book_grid_books_across.setAttribute("disabled", true);
			// group_by.setAttribute("disabled", true);
			// if (books_checkboxes[0] != null) {
				// books_checkboxes[0].setAttribute("disabled", true);
			// }
			// if (genre_checkboxes[0] != null) {
				// genre_checkboxes[0].setAttribute("disabled", true);
			// }
			// if (series_checkboxes[0] != null) {
				// series_checkboxes[0].setAttribute("disabled", true);
			// }
			// if (genre_group_by != null) {
				// genre_group_by.setAttribute("disabled", true);
			// }
		
		}
		
	
	
	}
