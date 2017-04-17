<?php
// this exists to keep backwards compatibility with AW using register_widget function
class Mooberry_Book_Manager_Simple_Widget_Factory extends WP_Widget_Factory {
	public function register_widget( $widget_class ) {
			
				parent::register( $widget_class );
			
		}
}