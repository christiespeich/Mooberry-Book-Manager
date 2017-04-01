<?php

/**
 * Represents the view for the base mbdb widget administration dashboard.
 *
 *
 * @package   Book_Manager
 * @author    Mooberry Dreams <bookmanager@mooberrydreams.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2014 Mooberry Dreams
 */
 
?>

	<p>
		<label for="<?php echo $this->get_field_id('mbdb_widget_title'); ?>"><?php _e('Widget Title:', 'mooberry-book-manager'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('mbdb_widget_title'); ?>" name="<?php echo $this->get_field_name('mbdb_widget_title'); ?>" type="text" value="<?php echo esc_html($this->widgetTitle); ?>" />
	</p>
		
	

	<p>
		<label for="<?php echo $this->get_field_id('mbdb_widget_show_title'); ?>"><?php _e('Show Book Title', 'mooberry-book-manager'); ?></label>
		<input type="checkbox" id="<?php echo $this->get_field_id('mbdb_widget_show_title'); ?>" name="<?php echo $this->get_field_name('mbdb_widget_show_title'); ?>" value="yes"   <?php echo $this->displayBookTitle == 'yes' ? 'checked' : ''; ?> />
	</p>
		
	<p>
		<label for="<?php echo $this->get_field_id('mbdb_widget_cover_size'); ?>"><?php _e('Cover Size(pixels)', 'mooberry-book-manager'); ?></label>
		<input style="width:100px" id="<?php echo $this->get_field_id('mbdb_widget_cover_size'); ?>" name="<?php echo $this->get_field_name('mbdb_widget_cover_size'); ?>" type="number" min="50" value="<?php echo esc_attr($this->coverSize); ?>" /> <span class="fielddescription"><?php _e('(minimum 50)', 'mooberry-book-manager'); ?></span>
		
	</p>
	