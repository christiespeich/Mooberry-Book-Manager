<?php

/**
 * The Mooberry Book Manager Publisher CPT class is the class responsible for creating and managing
 * the mbdb_publisher Custom Post Type
 *
 * @package MBM
 */

/**
 * The Mooberry Book Manager Publisher CPT class is the class responsible for creating and managing
 * the mbdb_publisher Custom Post Type
 *
 *
 *
 * @since    4.0.0
 */
class Mooberry_Book_Manager_Publisher_CPT extends Mooberry_Book_Manager_CPT {


	public function __construct() {

		// initialize
		parent::__construct();

		$this->post_type     = 'mbdb_publisher';

		$this->args = array(
			'public'            => true,
			'show_ui'           => true,
			'show_in_menu'      => true,
			'menu_position'     => 20,
			'rewrite'           => array( 'slug' => 'publisher' ),
			'show_in_nav_menus' => true,
			'has_archive'       => true,
			'show_in_admin_bar' => true,
			'can_export'        => true,
			'capability_type'   => array( 'mbdb_publisher', 'mbdb_publishers' ),
			'supports'          => array( 'title' ),
		);

		add_shortcode( 'mbdb_publisher', array( $this, 'shortcode_publisher' ) );
		add_shortcode( 'publisher_book_list', array( $this, 'shortcode_publisher_book_list' ) );
		add_shortcode( 'publisher_photo', array( $this, 'shortcode_publisher_photo' ) );
		add_shortcode( 'publisher_website', array( $this, 'shortcode_publisher_website' ) );
		add_action( 'save_post_' . $this->post_type, array( $this, 'save_publisher' ) );

		//	add_filter( 'manage_' . $this->post_type . '_posts_columns', array( $this, 'set_custom_columns' ) );
		//	add_action( 'manage_' . $this->post_type . '_posts_custom_column' , array( $this, 'display_custom_columns'), 10, 2 );


	}

	public function register() {

		$this->singular_name = __( 'Publisher', 'mooberry-book-manager' );
		$this->plural_name   = __( 'Publishers', 'mooberry-book-manager' );
		parent::register();
}

	protected function set_data_object( $id = 0 ) {
		if ( $this->data_object == null || $this->data_object->id != $id ) {
			$this->data_object = new Mooberry_Book_Manager_Publisher( $id );
		}
	}

	/*
	function set_custom_columns($columns) {

		return $columns;
	}

	function display_custom_columns( $column, $post_id ) {
		switch ( $column ) {
		}
	}*/


	public function create_metaboxes() {


		$mbdb_publisher_metabox = new_cmb2_box( array(
				'id'           => 'mbdb_publisher_metabox',
				'title'        => __( 'Publisher Settings', 'mooberry-book-manager' ),
				'object_types' => array( $this->post_type ), //array( 'page' ),
				'context'      => 'normal',
				'priority'     => 'default',
				'show_names'   => true
			)
		);

		$mbdb_publisher_metabox->add_field( array(
				'name' => __( 'Website', 'mooberry-book-manager' ),
				'id'   => '_mbdb_publisher_website',
				'type' => 'text_url',
			)
		);

		$mbdb_publisher_metabox->add_field( array(
				'name'       => __( 'Logo', 'mooberry-book-manager' ),
				'id'         => 'image',
				'type'       => 'file',
				'attributes' => array(
					'size' => 45
				),
				'options'    => array(
					'add_upload_file_text' => __( 'Choose or Upload File', 'mooberry-book-manager' ),
				),
			)
		);

		$mbdb_publisher_metabox = apply_filters( 'mbdb_publisher_meta_boxes', $mbdb_publisher_metabox );

	}

	public function save_publisher( $post_id ) {
		// unhook this function so it doesn't loop infinitely
		remove_action( 'save_post_' . $this->post_type, array( $this, 'save_publisher' ) );

		// update the post, which calls save_post again
		wp_update_post( array(
			'ID'           => $post_id,
			'post_content' => '[mbdb_publisher]',
		) );

		// re-hook this function and mbdb_save_book_custom_table
		add_action( 'save_post_' . $this->post_type, array( $this, 'save_publisher' ) );
	}

	public function shortcode_publisher( $atts, $content ) {
		global $post;
		if ( $post == null ) {

			return $content;
		} else {
			$publisherID = $post->ID;
		}
		$this->set_data_object($publisherID);

		$content               = '<div id="mbdb_publisher_page">';
		$content               .= apply_filters( 'mbdb_publisher_profile_pre_photo', $content, $publisherID );
		$publisher_page_layout = '[publisher_photo publisherid="' . $publisherID . '" width="200" wrap="yes" align="left"]';
		$publisher_page_layout = apply_filters( 'mbdb_publisher_profile_post_photo', $publisher_page_layout, $publisherID );


		if ( $this->data_object->website !== '' ) {
			$publisher_page_layout .= '<p><strong>' . __( 'Website: ', 'mooberry-book-manager' );
			$publisher_page_layout .= '</strong>[publisher_website publisherid="' . $publisherID . '" ]</p>';
		}

		$publisher_page_layout = apply_filters( 'mbdb_publisher_profile_pre_book_list', $publisher_page_layout, $publisherID );

		$publisher_page_layout .= '<br clear="all"><h2>' . __( 'Books Published By ', 'mooberry-book-manager' ) . $post->post_title . '</h2>';


		$publisher_page_layout .= '[publisher_book_list publisherid="' . $publisherID . '"]';

		$publisher_page_layout .= '</div>';
		$content               .= stripslashes( $publisher_page_layout );
		$content               = preg_replace( '/\\n/', '<br>', $content );
		$content               = apply_filters( 'mbdb_publisher_content', $content );
		$content               = do_shortcode( $content );


		return $content;

	}


	public function shortcode_publisher_book_list( $attr, $content ) {
		$attr = shortcode_atts( array(
			'publisherid' => '',
		), $attr );

		$this->set_data_object( $attr['publisherid'] != '' ? $attr['publisherid'] : 0 );

		// author books grid
		$selected_ids = $this->data_object->id;

		$groups[1] = 'none';
		$groups[2] = 'none';
		$sort      = mbdb_set_sort( $groups, 'pubdateD' );


		// start off the recursion by getting the first group
		$mbdb_book_grid_cover_height = MBDB()->options->book_grid_default_height;
		$author_grid                 = new Mooberry_Book_Manager_Publisher_Book_Grid( $selected_ids, $groups, $sort, $mbdb_book_grid_cover_height );
		$books                       = $author_grid->book_list;

		$content .= $author_grid->display_grid( $books, 0 );


		return $content;
	}


	function shortcode_publisher_photo( $attr, $content ) {

		$attr      = shortcode_atts( array(
			'width'       => 300,
			'align'       => 'right',
			'wrap'        => 'yes',
			'publisherid' => '',
			'publisher'   => '',
		), $attr );


			$this->set_data_object( $attr['publisherid'] != '' ? $attr['publisherid'] : 0 );

		if ( $this->data_object->logo_id == 0 ) {
			return apply_filters( 'mbdb_shortcode_publisher_photo', '<span class="mbm-publisher-photo"><span class="mbm-publisher-photo"></span></span>' );
		}

		$image_src = '';

		$attachment_src = wp_get_attachment_image_src( $this->data_object->logo_id, 'medium' );

		if ( $attachment_src !== false ) {
			$image_src = $attachment_src[0];
		}

		$image_html = '';
		if ( isset( $image_src ) && $image_src != '' ) {
			$image_html = '<img style="width:' . esc_attr( $attr['width'] ) . 'px" src="' . esc_url( $image_src ) . '" ';
			if ( $attr['wrap'] == 'yes' ) {
				$image_html .= 'class="align' . esc_attr( $attr['align'] ) . '">';
			} else {
				$image_html .= 'style="float:' . esc_attr( $attr['align'] ) . '"><div style="clear:' . esc_attr( $attr['align'] ) . '"> &nbsp;</div>';
			}
		}

		return apply_filters( 'mbdb_shortcode_publisher_photo', '<span class="mbm-publisher-photo">' . $image_html . '</span>' );
	}


	function shortcode_publisher_website( $attr, $content ) {
		$attr = shortcode_atts( array(
			'publisherid' => '',

		), $attr );

			$this->set_data_object( $attr['publisherid'] != ''  ? $attr['publisherid'] : 0 );

		if ( $this->data_object->website != '' ) {

			return '<a class="mbm-publisher-website" href="' . $this->data_object->website . '">' . $this->data_object->website . '</a>';
		}

		return '';

	}
}
