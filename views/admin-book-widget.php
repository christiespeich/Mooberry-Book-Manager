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
		<label for="<?php echo $this->get_field_id('mbdb_widget_title'); ?>">Widget Title</label>
		<input class="widefat" id="<?php echo $this->get_field_id('mbdb_widget_title'); ?>" name="<?php echo $this->get_field_name('mbdb_widget_title'); ?>" type="text" value="<?php echo esc_html($mbdb_widget_title); ?>" />
		</p>
		
		<p>
		<label for="<?php echo $this->get_field_id('mbdb_widget_type'); ?>">Which book to show?</label>
		<select onchange="javascript:widget_type('<?php echo $this->get_field_id('mbdb_widget_type'); ?>', '<?php echo $this->get_field_id('bookdropdown'); ?>')" id="<?php echo $this->get_field_id('mbdb_widget_type'); ?>" name="<?php echo $this->get_field_name('mbdb_widget_type'); ?>">
			<option value="random" <?php echo ($mbdb_widget_type=='random') ? 'selected' : ''; ?>>Random Book</option>
			<option value="newest" <?php echo ($mbdb_widget_type=='newest') ? 'selected' : ''; ?>>Newest Book</option>
			<option value="coming-soon" <?php echo ($mbdb_widget_type=='coming-soon') ? 'selected' : ''; ?>>Future Book</option>
			<option value="specific" <?php echo ($mbdb_widget_type=='specific') ? 'selected' : ''; ?>>Specific Book</option>
		</select>
		</p>
		
		<div  id="<?php echo $this->get_field_id('bookdropdown'); ?>" style="display:<?php echo ($mbdb_widget_type=='specific') ? 'block' : 'none'; ?>">
		<p>
		
		<label for="<?php echo $this->get_field_id('mbdb_bookID'); ?>">Book:</label>
		<select name="<?php echo $this->get_field_name('mbdb_bookID'); ?>" id="<?php echo $this->get_field_id('mbdb_bookID'); ?>">
			<option value="0_0"></option>
			<?php mbdb_get_book_dropdown($mbdb_bookID); ?>
		
		</select>
		</P>
		</div>
		<p>
		<label for="<?php echo $this->get_field_id('mbdb_widget_show_title'); ?>">Show Book Title</label>
		<input type="checkbox" id="<?php echo $this->get_field_id('mbdb_widget_show_title'); ?>" name="<?php echo $this->get_field_name('mbdb_widget_show_title'); ?>" value="yes"   <?php echo $mbdb_widget_show_title=='yes' ? 'checked' : ''; ?> />
		</p>
		
		<p>
		<label for="<?php echo $this->get_field_id('mbdb_widget_cover_size'); ?>">Cover Size(pixels)</label>
		<input style="width:100px" id="<?php echo $this->get_field_id('mbdb_widget_cover_size'); ?>" name="<?php echo $this->get_field_name('mbdb_widget_cover_size'); ?>" type="number" min="50" value="<?php echo esc_attr($mbdb_widget_cover_size); ?>" /> <span class="fielddescription">(minimum 50)</span>
		
		</p>