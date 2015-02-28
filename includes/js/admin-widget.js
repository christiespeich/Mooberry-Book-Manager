
	function widget_type($typeElementID, $bookdropdownID) {
	
	if(document.getElementById($typeElementID).value=="specific") {
		
	document.getElementById($bookdropdownID).style.display = 'block';

	} else {
			document.getElementById($bookdropdownID).style.display = 'none';
		}
	}
