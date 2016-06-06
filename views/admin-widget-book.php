		<p>
		<label for="<?php echo $this->get_field_id('mbdb_widget_type'); ?>"><?php _e('Which book to show?', 'mooberry-book-manager'); ?></label>
		<?php echo $widget_type_dropdown; ?>
		</p>
		
		<?php 
			do_action('mbdb_book_widget_add_fields', $instance, $this);
		?>
		
		<div  id="<?php echo $this->get_field_id('bookdropdown'); ?>" style="display:<?php echo ($this->widgetType=='specific') ? 'block' : 'none'; ?>">
		
		<p>
		<label for="<?php echo $this->get_field_id('mbdb_bookID'); ?>"><?php _e('Book:', 'mooberry-book-manager'); ?></label>
		<select name="<?php echo $this->get_field_name('mbdb_bookID'); ?>" id="<?php echo $this->get_field_id('mbdb_bookID'); ?>">
			<option value="0"></option>
			<?php mbdb_get_book_dropdown($this->bookID); ?>
		
		</select>
		</P>
		</div>	