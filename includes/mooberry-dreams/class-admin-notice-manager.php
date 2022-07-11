<?php
/**
 * Manages Admin Notices generated by the plugin
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Mooberry_Directory
 * @subpackage Mooberry_Directory/includes/vendor/mooberry-dreams
 */

/**
 * Manages Admin Notices generated by the plugin
 *
 * Maintains a list of admin notices generated by the plugin.
 * Display them and dismiss as needed.
 *
 * @author     Mooberry Dreams <mooberrydreams@mooberrydreams.com>
 */
if ( !class_exists('Mooberry_Dreams_Admin_Notice_Manager')) {
	class Mooberry_Dreams_Admin_Notice_Manager {

		/**
		 * The array of notices this plugin generates
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      array $plugins The notices this plugin generates
		 */
		private $notices;

		private $options_key;

		/**
		 * Initialize the collection used to maintain the notices.
		 *
		 * @since    1.0.0
		 */
		public function __construct( $key ) {
			$this->options_key = $key;
			$this->load_notices();

			add_action('admin_notices', array($this, 'display_notices'));

		}

		private function load_notices() {
			$notices = get_option( $this->options_key, array() );
			if ( ! is_array( $notices ) ) {
				$notices = array( $notices );
			}
			$this->notices = $notices;
		}

		public function get_notice( $key ) {
			if ( array_key_exists( $key, $this->notices ) ) {
				return $this->notices[ $key ];
			}

			return null;
		}

		public function count_notices() {
			return count( $this->notices );
		}

		/**
		 * Add a new notice to the collection and saves it to the database
		 *
		 * @param string $message  The message to display
		 * @param string $severity The severity/type of notice to be displayed
		 * @param string $key      A unique key for the notice
		 *
		 * @since    1.0.0
		 */
		public function add_new( $message, $severity, $key, $dismissible = false ) {
			$this->notices[ $key ] = array(
				'message'  => $message,
				'severity' => $severity,
				'key'      => $key,
				'dismissible' => $dismissible,
			);
			$this->save();
		}

		/**
		 *  Remove a notice and update the database
		 *
		 * @param string $key the notice to remove
		 */
		public function dismiss( $key ) {
			unset( $this->notices[ $key ] );
			$this->save();
		}


		/**
		 * Saves all notices to the database
		 */
		private function save() {
			update_option( $this->options_key, $this->notices );
		}

		/**
		 * Display all of the notices
		 *
		 * @since    1.0.0
		 */
		public function display_notices() {
			foreach ( $this->notices as $notice ) {
				$severity = isset( $notice['severity'] ) ? $notice['severity'] : '';
				$key      = isset( $notice['key'] ) ? $notice['key'] : '';
				$message  = isset( $notice['message'] ) ? $notice['message'] : '';
				$dismissible = isset($notice['dismissible']) && $notice['dismissible'] ? 'is-dismissible' : '';

				if ( $severity == '' || $key == '' || $message == '' ) {
					continue;
				}

				echo "<div class='notice " . esc_attr( $severity ) . " " . $dismissible . "' id='" . esc_attr( $key ) . "'><p>" . ( $message ) . "</p></div>";
			}
		}

	}
}
