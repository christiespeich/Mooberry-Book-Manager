

			<ul>
				<li><a target="_new" href="http://www.mooberrydreams.com/support/mooberry-book-manager-support/"><?php _e('User Manual', 'mooberry-book-manager'); ?></a></li>
				<li><a target="_new" href="https://wordpress.org/plugins/mooberry-book-manager/faq/"><?php _e('FAQs', 'mooberry-book-manager'); ?></a></li>
				<li><a target="_new" href="https://wordpress.org/support/plugin/mooberry-book-manager"><?php _e('Support Forum', 'mooberry-book-manager'); ?></a></li>
			</ul>
			
		<?php // version 3.0
			$current_screen = get_current_screen();
			if (!$current_screen) {
				return;
			}
			
	
			$parent_base = $current_screen->parent_base;
			if ($parent_base == 'mbdb_options') {
		?>
		<h3><?php _e('Extensions Coming Soon', 'mooberry-book-manager'); ?></h3>
		<p><?php _e('Check out <a target="_new" href="http://www.mooberrydreams.com/">our website</a> to learn more about
		the upcoming extensions so Mooberry Book
		Manager can save you more time!', 'mooberry-book-manager'); ?></p>
			<?php } ?>
		<h3><?php _e('Tip Jar', 'mooberry-book-manager'); ?></h3>
		<p><?php _e('You can help support this plugin by leaving a tip in any amount from $1 on up. This is completely optional. Thank you for the support.', 'mooberry-book-manager'); ?>
</p>
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_new">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="QQQ59JX5S3S3U">
<input type="image" src="<?php echo MBDB_PLUGIN_URL . '/includes/assets/Leave_Tip_button.gif'; ?>" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
<?php // version 3.0
if ($parent_base == 'mbdb_options') {
		?>
		<img style="width:225px" src="<?php echo plugins_url('/images/logo.png', __FILE__) ?> ">
		
<?php }
?>
