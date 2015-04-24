<?php

/**
 * Represents the view for the widget administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   Book_Manager
 * @author    Mooberry Dreams <bookmanager@mooberrydreams.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2014 Mooberry Dreams
 */
 
?>

<p>
		<label for="<?php echo $this->get_field_id('mbdb_widget_title'); ?>"><? _e('Widget Title', 'mooberry-book-manager'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('mbdb_widget_title'); ?>" name="<?php echo $this->get_field_name('mbdb_widget_title'); ?>" type="text" value="<?php echo esc_html($mbdb_widget_title); ?>" />
		</p>
		
		<p>
		<label for="<?php echo $this->get_field_id('mbdb_widget_type'); ?>"><?php _e('Which book to show?', 'mooberry-book-manager'); ?></label>
		<select onchange="javascript:widget_type('<?php echo $this->get_field_id('mbdb_widget_type'); ?>', '<?php echo $this->get_field_id('bookdropdown'); ?>')" id="<?php echo $this->get_field_id('mbdb_widget_type'); ?>" name="<?php echo $this->get_field_name('mbdb_widget_type'); ?>">
			<option value="random" <?php echo ($mbdb_widget_type=='random') ? 'selected' : ''; ?>><?php _e('Random Book', 'mooberry-book-manager'); ?></option>
			<option value="newest" <?php echo ($mbdb_widget_type=='newest') ? 'selected' : ''; ?>><?php _e('Newest Book', 'mooberry-book-manager'); ?></option>
			<option value="coming-soon" <?php echo ($mbdb_widget_type=='coming-soon') ? 'selected' : ''; ?>><?php _e('Future Book', 'mooberry-book-manager'); ?></option>
			<option value="specific" <?php echo ($mbdb_widget_type=='specific') ? 'selected' : ''; ?>><?php _e('Specific Book', 'mooberry-book-manager'); ?></option>
		</select>
		</p>
		
		<div  id="<?php echo $this->get_field_id('bookdropdown'); ?>" style="display:<?php echo ($mbdb_widget_type=='specific') ? 'block' : 'none'; ?>">
		<p>
		
		<label for="<?php echo $this->get_field_id('mbdb_bookID'); ?>"><?php _e('Book:', 'mooberry-book-manager'); ?></label>
		<select name="<?php echo $this->get_field_name('mbdb_bookID'); ?>" id="<?php echo $this->get_field_id('mbdb_bookID'); ?>">
			<option value="0"></option>
			<?php mbdb_get_book_dropdown($mbdb_bookID); ?>
		
		</select>
		</P>
		</div>
		<?php do_action('mbdb_book_widget_fields'); ?>
		<p>
		<label for="<?php echo $this->get_field_id('mbdb_widget_show_title'); ?>"><?php _e('Show Book Title', 'mooberry-book-manager'); ?></label>
		<input type="checkbox" id="<?php echo $this->get_field_id('mbdb_widget_show_title'); ?>" name="<?php echo $this->get_field_name('mbdb_widget_show_title'); ?>" value="yes"   <?php echo $mbdb_widget_show_title=='yes' ? 'checked' : ''; ?> />
		</p>
		
		<p>
		<label for="<?php echo $this->get_field_id('mbdb_widget_cover_size'); ?>"><?php _e('Cover Size(pixels)', 'mooberry-book-manager'); ?></label>
		<input style="width:100px" id="<?php echo $this->get_field_id('mbdb_widget_cover_size'); ?>" name="<?php echo $this->get_field_name('mbdb_widget_cover_size'); ?>" type="number" min="50" value="<?php echo esc_attr($mbdb_widget_cover_size); ?>" /> <span class="fielddescription"><?php _e('(minimum 50)', 'mooberry-book-manager'); ?></span>
		
		</p>