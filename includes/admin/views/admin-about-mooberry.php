

			<ul>
				<li><a target="_blank" href="http://docs.mooberrybookmanager.com/"><?php _e('Documentation', 'mooberry-book-manager'); ?></a></li>
				<li><a target="_blank" href="https://wordpress.org/plugins/mooberry-book-manager/faq/"><?php _e('FAQs', 'mooberry-book-manager'); ?></a></li>
				<li><a target="_blank" href="https://wordpress.org/support/plugin/mooberry-book-manager"><?php _e('Support Forum', 'mooberry-book-manager'); ?></a></li>
			</ul>

		<?php // version 3.0
			$current_screen = get_current_screen();
			if (!$current_screen) {
				return;
			}


			$parent_base = $current_screen->parent_base;
			if ($parent_base == 'mbdb_options') {
		?>
		<h3><?php _e('Extensions Available Now', 'mooberry-book-manager'); ?></h3>
		<p><?php _e('Check out <a target="_blank" href="http://www.mooberrybookmanager.com/">our website</a> to learn more about these extensions so Mooberry Book Manager can save you more time!', 'mooberry-book-manager'); ?></p>
		<ul>
			<li><a target="_blank" href="http://www.mooberrybookmanager.com/downloads/additional-images/"><?php _e('Additional Images', 'mooberry-book-manager'); ?></a></li>
			<li><a target="_blank" href="http://www.mooberrybookmanager.com/downloads/advanced-grids/"><?php _e('Advanced Grids', 'mooberry-book-manager'); ?></a></li>
			<li><a target="_blank" href="http://www.mooberrybookmanager.com/downloads/advanced-widgets/"><?php _e('Advanced Widgets', 'mooberry-book-manager'); ?></a></li>
			<li><a target="_blank" href="https://www.mooberrybookmanager.com/downloads/custom-fields/"><?php _e('Custom Fields', 'mooberry-book-manager'); ?></a></li>

			<li><a target="_blank" href="http://www.mooberrybookmanager.com/downloads/multi-author/"><?php _e('Multi-Author', 'mooberry-book-manager'); ?></a></li>
			<li><a target="_blank" href="http://www.mooberrybookmanager.com/downloads/retail-links-redirect/"><?php _e('Retail Links Redirect', 'mooberry-book-manager'); ?></a></li>
		</ul>

			<?php } ?>
		<h3><?php _e('Tip Jar', 'mooberry-book-manager'); ?></h3>
		<p><?php _e('You can help support this plugin by leaving a tip in any amount. This is completely optional. Thank you for the support.', 'mooberry-book-manager'); ?>
</p>

<a target="_blank" href="https://www.paypal.me/mooberrydreams"><img src="<?php echo MBDB_PLUGIN_URL . '/includes/assets/Leave_Tip_button.gif'; ?>" border="0" alt="PayPal - The safer, easier way to pay online!"></a>
<?php // version 3.0
if ($parent_base == 'mbdb_options') {
		?>
		<img style="width:225px" src="<?php echo plugins_url('/images/logo.png', __FILE__) ?> ">

<?php }
?>
