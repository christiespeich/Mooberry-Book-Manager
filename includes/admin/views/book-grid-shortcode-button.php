<style>
	<!-- .ui-dialog { z-index: 99999 !important; } -->
   
	.ui-dialog .ui-dialog-titlebar-close span { margin-left: -8px; margin-top: -8px; }
  </style>
	<a  id="mbdb_add_book_grid_<?php echo $editor_id; ?>" class="button"><?php _e('Add Book Grid', 'mooberry-book-manager'); ?></a>
<div id="mbdb_book_grid_shortcode_dialog_<?php echo $editor_id; ?>" title="<?php _e('Add Book Grid', 'mooberry-book-manager'); ?>">
  
 
      <label for="mbdb_book_grids"><?php echo __('Book Grid:', 'mooberrry-book-manager'); ?></label>
	  <?php echo MBDB()->helper_functions->make_dropdown( 'mbdb_book_grids_' . $editor_id, $grids, null, 'no' ); ?>
      
 
      <!-- Allow form submission with keyboard without duplicating the dialog button -->
      <input type="submit" tabindex="-1" style="position:absolute; top:-1000px">
 </div>
