<?php
/**
 *  This file does the book widget  
 *  
 */
 
 
class mbdb_book_widget2 extends mbdb_widget {

	// constructor
	// 3.1 -- added customize_selected_refresh for WP 4.5
	function __construct() {
		error_log('mbdb_book_widget2 construct');
		$widget_ops = array('classname' => 'mbdb_book_widget2', 
							'description' => __('Shows the cover of the book of your choosing with a link to the book page', 'mooberry-book-manager'),
							 'customize_selective_refresh' => true,);
		
		parent::__construct( 'Mooberry Book Manager '. _x('Book', 'noun', 'mooberry-book-manager'), $widget_ops  );

	}

	function setAdditionalFields ( $instance ) {
		if ( $instance && $instance != null ) {
			$this->bookID = esc_attr($instance['mbdb_bookID']);
			$this->widgetType = esc_attr($instance['mbdb_widget_type']);
		} else {
			$this->bookID = 0;
			$this->widgetType = 'random';
		}
	}
	
	function displayAdditionalFields ($instance) {
		$options = apply_filters('mbdb_book_widget_options', array('random' => __('Random Book', 'mooberry-book-manager'),
							'newest'	=>	__('Newest Book', 'mooberry-book-manager'),
							'coming-soon'	=>	__('Future Book', 'mooberry-book-manager'),
							'specific'	=>	__('Specific Book', 'mooberry-book-manager')
						)
					);
							
		$widget_type_dropdown = mbdb_dropdown($this->get_field_id('mbdb_widget_type'), $options, $this->widgetType, 'no', 0, $this->get_field_name('mbdb_widget_type'));
		?>
		<p>
		<label for="<?php echo $this->get_field_id('mbdb_widget_type'); ?>"><?php _e('Which book to show?', 'mooberry-book-manager'); ?></label>
		<?php echo $widget_type_dropdown; ?>
		</p>
		
		<div  id="<?php echo $this->get_field_id('bookdropdown'); ?>" style="display:<?php echo ($this->widgetType=='specific') ? 'block' : 'none'; ?>">
		
		<p>
		<label for="<?php echo $this->get_field_id('mbdb_bookID'); ?>"><?php _e('Book:', 'mooberry-book-manager'); ?></label>
		<select name="<?php echo $this->get_field_name('mbdb_bookID'); ?>" id="<?php echo $this->get_field_id('mbdb_bookID'); ?>">
			<option value="0"></option>
			<?php mbdb_get_book_dropdown(esc_attr($instance['mbdb_bookID'])); ?>
		
		</select>
		</P>
		</div>	
		<?php
	}
	
	function updateAdditionalFields( $instance, $new_instance ) {
		$instance['mbdb_bookID'] = strip_tags($new_instance['mbdb_bookID']);
		$instance['mbdb_widget_type'] = strip_tags($new_instance['mbdb_widget_type']);
		return $instance;
	}

	function selectBook( $instance ) {
		$book = null;
		switch ($this->widgetType) {
			
		
			case "newest":
				// get book ID of most recent book
				
				$book = apply_filters('mbdb_widget_newest_book_list', MBDB()->books->get_most_recent_book(), $instance);
				break;
			
			case "coming-soon":
				// get books with future or blank release dates
				$book = apply_filters('mbdb_widget_coming_soon_book_list', MBDB()->books->get_upcoming_book(), $instance); 
				break;
			
			case "specific":
				// make sure seected book is still a valid book
				$book = apply_filters('mbdb_widget_specific_book_list', MBDB()->books->get($this->bookID), $instance); 				
				break;
			//case 'random':
			default: // default to random
				// get book ID of a random book
				$book = apply_filters('mbdb_widget_random_book_list', MBDB()->books->get_random_book(), $instance);
				break;
		}
		return $book;
	}
}
