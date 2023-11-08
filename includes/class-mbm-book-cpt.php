<?php

/**
 * The Mooberry Book Manager Book CPT class is the class responsible for creating and managing
 * the mbdb_book Custom Post Type
 *
 * @package MBM
 */

/**
 * The Mooberry Book Manager Book CPT class is the class responsible for creating and managing
 * the mbdb_book Custom Post Type
 *
 *
 *
 * @since    4.0.0
 */
class Mooberry_Book_Manager_Book_CPT extends Mooberry_Book_Manager_CPT {

	protected $standard_taxonomies;

	public function __construct() {

		// initialize
		parent::__construct();


		$this->post_type = 'mbdb_book';


		$this->default_single_template = MBDB()->options->book_page_template;

		// let individual CPTs choose whether to add post class?
		add_filter( 'post_class', array( $this, 'add_post_class' ) );

		// support Duplicate Post
		add_action( 'dp_duplicate_post', array( $this, 'duplicate_book' ), 10, 2 );


		if ( MBDB()->options->get_mbdb_book_seo_enabled() == 'yes' ) {
			add_filter( 'wp_head', array( $this, 'meta_tags' ) );
		}
		if ( MBDB()->options->override_wpseo( 'og' ) ) {
			add_filter( 'wpseo_opengraph_title', array( $this, 'override_wp_seo_meta' ) );
			add_filter( 'wpseo_opengraph_url', array( $this, 'override_wp_seo_meta' ) );
			add_filter( 'wpseo_opengraph_desc', array( $this, 'override_wp_seo_meta' ) );
			add_filter( 'wpseo_opengraph_image', array( $this, 'override_wp_seo_meta' ) );
		}
		if ( MBDB()->options->override_wpseo( 'twitter' ) ) {
			add_filter( 'wpseo_twitter_title', array( $this, 'override_wp_seo_meta' ) );
			add_filter( 'wpseo_twitter_card_type', array( $this, 'override_wp_seo_meta' ) );
			add_filter( 'wpseo_twitter_description', array( $this, 'override_wp_seo_meta' ) );
			add_filter( 'wpseo_twitter_image', array( $this, 'override_wp_seo_meta' ) );
		}
		if ( MBDB()->options->override_wpseo( 'description' ) ) {
			add_filter( 'wpseo_metadesc', array( $this, 'override_wp_seo_meta' ) );
		}

		//add_action('init', array( $this, 'allow_comments') );


		add_filter( 'template_include', array( $this, 'single_template' ), 99 );
		add_filter('get_post_metadata', array($this,'post_page_template_meta'), 10, 3);
		add_filter( 'manage_' . $this->post_type . '_posts_columns', array( $this, 'columns' ) );
		add_action( 'cmb2_admin_init', array( $this, 'create_taxonomy_metaboxes' ) );
		add_action( 'add_meta_boxes_' . $this->post_type, array( $this, 'mbd_metabox' ), 10 );
		add_action( 'admin_init', array( $this, 'switch_tax_ids_and_name' ) );
		add_action( 'save_post_' . $this->post_type, array( $this, 'save_book' ) );
		add_filter( 'the_excerpt', array( $this, 'set_book_excerpt' ) );

		add_filter( 'wp_kses_allowed_html', array( $this, 'kses_allowed_html' ), 10, 2 );
		add_action( 'add_meta_boxes_' . $this->post_type, array( $this, 'reorder_taxonomy_boxes' ) );

		add_filter( 'relevanssi_content_to_index', array( $this, 'index_extra_content_for_relevanssi'), 10, 2 );
		add_filter( 'searchwp\source\post\attributes\content', array( $this, 'index_extra_content_for_searchwp'), 10, 2);


		add_shortcode( 'book_title', array( $this, 'shortcode_title' ) );
		add_shortcode( 'book_cover', array( $this, 'shortcode_cover' ) );
		add_shortcode( 'book_subtitle', array( $this, 'shortcode_subtitle' ) );
		add_shortcode( 'book_summary', array( $this, 'shortcode_summary' ) );
		add_shortcode( 'book_imprint', array( $this, 'shortcode_imprint' ) );
		add_shortcode( 'book_publisher', array( $this, 'shortcode_publisher' ) );
		add_shortcode( 'book_published', array( $this, 'shortcode_published' ) );
		add_shortcode( 'book_goodreads', array( $this, 'shortcode_goodreads' ) );
		add_shortcode( 'book_reedsy', array( $this, 'shortcode_reedsy' ) );
		add_shortcode( 'book_google_books', array( $this, 'shortcode_google_books' ) );
		add_shortcode( 'book_excerpt', array( $this, 'shortcode_excerpt' ) );
		add_shortcode( 'book_additional_info', array( $this, 'shortcode_additional_info' ) );
		add_shortcode( 'book_genre', array( $this, 'shortcode_genre' ) );
		add_shortcode( 'book_reviews', array( $this, 'shortcode_reviews' ) );
		add_shortcode( 'book_buylinks', array( $this, 'shortcode_buylinks' ) );
		add_shortcode( 'book_downloadlinks', array( $this, 'shortcode_downloadlinks' ) );
		add_shortcode( 'book_serieslist', array( $this, 'shortcode_serieslist' ) );
		add_shortcode( 'book_series', array( $this, 'shortcode_series' ) );
		add_shortcode( 'book_tags', array( $this, 'shortcode_tags' ) );
		add_shortcode( 'book_illustrator', array( $this, 'shortcode_illustrator' ) );
		add_shortcode( 'book_editor', array( $this, 'shortcode_editor' ) );
		add_shortcode( 'book_translator', array( $this, 'shortcode_translator' ) );
		add_shortcode( 'book_narrator', array( $this, 'shortcode_narrator' ) );
		add_shortcode( 'book_cover_artist', array( $this, 'shortcode_cover_artist' ) );
		add_shortcode( 'book_links', array( $this, 'shortcode_links' ) );
		add_shortcode( 'book_editions', array( $this, 'shortcode_editions' ) );
		add_shortcode( 'mbdb_book', array( $this, 'shortcode_book' ) );
		add_shortcode( 'book_kindle_preview', array( $this, 'shortcode_kindle_preview' ) );
		add_shortcode( 'book_back_to_grid', array( $this, 'shortcode_back_to_grid'));

	}

	public function register() {


		$this->singular_name           = __( 'Book', 'mooberry-book-manager' );
		$this->plural_name             = __( 'Books', 'mooberry-book-manager' );
		$this->set_up_taxonomies();

		$this->args = array(
			'public'          => true,
			'rewrite'         => array( 'slug' => 'book' ),
			'menu_icon'       => 'dashicons-book-alt',
			'capability_type' => array( 'mbdb_book', 'mbdb_books' ),
			'supports'        => array( 'title', 'comments', 'author' ),
			'taxonomies'      => array_keys( $this->taxonomies ),
			'show_in_rest'    => true,
			'rest_base'       => 'books',
			'can_export'      => false,
			'has_archive'     => true,
		);


		parent::register();
	}
	public function index_extra_content_for_relevanssi( $content, $post ) {
		return $this->index_extra_content( $content, $post->ID );

	}

	public function index_extra_content_for_searchwp( $content, $args ) {
		return $this->index_extra_content( $content, $args['post']->ID );
	}


	protected function index_extra_content( $content, $book_id) {
		if ( get_post_type( $book_id ) == $this->post_type ) {
			$book    = MBDB()->books_db->get( $book_id );
			$content .= ' ' . $book->summary . ' ' . $book->additional_info;
			$content = apply_filters( 'mbdb_book_content_to_index', $content, $book );
		}

		return $content;
	}

	public function get_taxonomies() {
		return $this->taxonomies;
	}

	public function get_standard_taxonomies() {
		return $this->standard_taxonomies;
	}

	public function get_custom_taxonomies() {
		return array_diff_key( array_keys( $this->taxonomies ), $this->standard_taxonomies );
	}


	protected function set_up_taxonomies() {
		$this->standard_taxonomies = array(
			'mbdb_genre',
			'mbdb_tag',
			'mbdb_series',
			'mbdb_illustrator',
			'mbdb_cover_artist',
			'mbdb_editor',
			'mbdb_narrator',
			'mbdb_translator',
		);

		$tax_args = array(
			'meta_box_cb'          => 'post_categories_meta_box',
			'capabilities'         => array(
				'manage_terms' => 'manage_genre_terms', //'manage_categories',
				'edit_terms'   => 'manage_genre_terms', //'manage_categories',
				'delete_terms' => 'manage_genre_terms',
				'assign_terms' => 'assign_genre_terms',
			),
			'meta_box_sanitize_cb' => 'taxonomy_meta_box_sanitize_cb_checkboxes'  // version 5.1+
		);

		$tax_args['rewrite'] = array( 'slug' => MBDB()->options->get_tax_grid_slug( 'mbdb_genre' ) );

		$tax_args['show_in_quick_edit'] = ( version_compare( '5.1', get_bloginfo( 'version' ) ) > 0 );  // only show in quick edit if below 5.1 due to a bug

		$this->taxonomies['mbdb_genre'] = new Mooberry_Book_Manager_Taxonomy( 'mbdb_genre', $this->post_type, __( 'Genre', 'mooberry-book-manager' ), __( 'Genres', 'mooberry-book-manager' ), $tax_args );

		$tax_args['capabilities'] = array(
			'manage_terms' => 'manage_series_terms', //'manage_categories',
			'edit_terms'   => 'manage_series_terms', //'manage_categories',
			'delete_terms' => 'manage_series_terms',
			'assign_terms' => 'assign_series_terms',
		);
		$tax_args['rewrite'] = array( 'slug' => MBDB()->options->get_tax_grid_slug( 'mbdb_series' ) );

		$this->taxonomies['mbdb_series'] = new Mooberry_Book_Manager_Taxonomy( 'mbdb_series', $this->post_type, __( 'Series', 'mooberry-book-manager' ), __( 'Series', 'mooberry-book-manager' ), $tax_args );


		$tax_args['capabilities'] = array(
			'manage_terms' => 'manage_tag_terms', //'manage_categories',
			'edit_terms'   => 'manage_tag_terms', //'manage_categories',
			'delete_terms' => 'manage_tag_terms',
			'assign_terms' => 'assign_tag_terms',
		);
$tax_args['rewrite'] = array( 'slug' => MBDB()->options->get_tax_grid_slug( 'mbdb_tag' ) );

		$this->taxonomies['mbdb_tag'] = new Mooberry_Book_Manager_Taxonomy( 'mbdb_tag', $this->post_type, __( 'Tag', 'mooberry-book-manager' ), __( 'Tags', 'mooberry-book-manager' ), $tax_args );

		$tax_args['show_admin_column']  = false;
		$tax_args['show_in_quick_edit'] = false;

		$tax_args['capabilities'] = array(
			'manage_terms' => 'manage_editor_terms', //'manage_categories',
			'edit_terms'   => 'manage_editor_terms', //'manage_categories',
			'delete_terms' => 'manage_editor_terms',
			'assign_terms' => 'assign_editor_terms',
		);
$tax_args['rewrite'] = array( 'slug' => MBDB()->options->get_tax_grid_slug( 'mbdb_editor' ) );

		$this->taxonomies['mbdb_editor'] = new Mooberry_Book_Manager_Taxonomy( 'mbdb_editor', $this->post_type, _x( 'Editor', 'general taxonomy name', 'mooberry-book-manager' ), _x( 'Editors', 'general tax name plural',  'mooberry-book-manager' ), $tax_args );


		$tax_args['capabilities'] = array(
			'manage_terms' => 'manage_illustrator_terms', //'manage_categories',
			'edit_terms'   => 'manage_illustrator_terms', //'manage_categories',
			'delete_terms' => 'manage_illustrator_terms',
			'assign_terms' => 'assign_illustrator_terms',
		);

$tax_args['rewrite'] = array( 'slug' => MBDB()->options->get_tax_grid_slug( 'mbdb_illustrator' ) );
		$this->taxonomies['mbdb_illustrator'] = new Mooberry_Book_Manager_Taxonomy( 'mbdb_illustrator', $this->post_type, __( 'Illustrator', 'mooberry-book-manager' ), __( 'Illustrators', 'mooberry-book-manager' ), $tax_args );

		$tax_args['capabilities'] = array(
			'manage_terms' => 'manage_cover_artist_terms', //'manage_categories',
			'edit_terms'   => 'manage_cover_artist_terms', //'manage_categories',
			'delete_terms' => 'manage_cover_artist_terms',
			'assign_terms' => 'assign_cover_artist_terms',
		);
		$tax_args['rewrite'] = array( 'slug' => MBDB()->options->get_tax_grid_slug( 'mbdb_cover_artist' ) );

		$this->taxonomies['mbdb_cover_artist'] = new Mooberry_Book_Manager_Taxonomy( 'mbdb_cover_artist', $this->post_type, __( 'Cover Artist', 'mooberry-book-manager' ), __( 'Cover Artists', 'mooberry-book-manager' ), $tax_args );

		$tax_args['capabilities'] = array(
			'manage_terms' => 'manage_narrator_terms', //'manage_categories',
			'edit_terms'   => 'manage_narrator_terms', //'manage_categories',
			'delete_terms' => 'manage_narrator_terms',
			'assign_terms' => 'assign_narrator_terms',
		);
		$tax_args['rewrite'] = array( 'slug' => MBDB()->options->get_tax_grid_slug( 'mbdb_narrator' ) );

		$this->taxonomies['mbdb_narrator'] = new Mooberry_Book_Manager_Taxonomy( 'mbdb_narrator', $this->post_type, __( 'Narrator', 'mooberry-book-manager' ), __( 'Narrators', 'mooberry-book-manager' ), $tax_args );

		$tax_args['capabilities'] = array(
			'manage_terms' => 'manage_translator_terms', //'manage_categories',
			'edit_terms'   => 'manage_translator_terms', //'manage_categories',
			'delete_terms' => 'manage_translator_terms',
			'assign_terms' => 'assign_translator_terms',
		);
		$tax_args['rewrite'] = array( 'slug' => MBDB()->options->get_tax_grid_slug( 'mbdb_translator' ) );

		$this->taxonomies['mbdb_translator'] = new Mooberry_Book_Manager_Taxonomy( 'mbdb_translator', $this->post_type, __( 'Translator', 'mooberry-book-manager' ), __( 'Translators', 'mooberry-book-manager' ), $tax_args );



	}


	// TODO: add slug as possible parameter for creating a book object
	protected function set_data_object( $id = 0 ) {
		//$this->data_object = new Mooberry_Book_Manager_Book( $id );
		$this->data_object = MBDB()->book_factory->create_book( $id );

	}


	// Remove the author and comments columns from the CPT list
	public function columns( $columns ) {
		//print_r($columns);
		//unset( $columns['author'] );
		$columns['author'] = 'User';
		unset( $columns['comments'] );

		return apply_filters( 'mbdb_book_columns', $columns );
	}

	protected function handle_quick_edit_data( $field, $value ) {
		// format published date
		if ( $field == '_mbdb_published' ) {
			if ( $value != '' ) {
				$published = strtotime( $value );

				if ( $published !== false ) {
					$value = date( 'Y-m-d', $published );
				}
			}
		}

		return $value;
	}

	public function create_metaboxes() {
		//$publishers = MBDB()->helper_functions->create_array_from_objects( MBDB()->options->publishers, 'name', true );
		$publishers = MBDB()->helper_functions->get_publishers_array(true);

		$imprints = MBDB()->helper_functions->create_array_from_objects( MBDB()->options->imprints, 'name', true );

		$bulk_edit_publishers = array(
			                        '0'  => __( '— No Change —', 'mooberry-book-manager' ),
			                        '-1' => __( '— No Publisher —', 'mooberry-book-manager' ),
		                        ) + $publishers;

		$this->bulk_edit_fields = apply_filters( 'mbdb_book_bulk_edit_fields', array(
				'_mbdb_publisherID' => array(
					'fieldset_class' => 'inline-edit-col-left',
					'fieldset_style' => '',
					'label'          => __( 'Publisher', 'mooberry-book-manager' ),
					'field'          => MBDB()->helper_functions->make_dropdown( '_mbdb_publisherID', $bulk_edit_publishers, '0', 'no', - 1, '_mbdb_publisherID' ),
					'description'    => '',
				),
			)
		);

		$this->quick_edit_fields = apply_filters( 'mbdb_book_quick_edit_fields', array(
				'_mbdb_publisherID'  => array(
					'fieldset_class' => 'inline-edit-col-left',
					'fieldset_style' => '',
					'label'          => __( 'Publisher', 'mooberry-book-manager' ),
					'field'          => MBDB()->helper_functions->make_dropdown( '_mbdb_publisherID', $publishers, null, 'no', - 1, '_mbdb_publisherID' ),
					'description'    => '',
				),
				'_mbdb_published'    => array(
					'fieldset_class' => '',
					'fieldset_style' => 'width:50%;',
					'label'          => __( 'Release Date', 'mooberry-book-manager' ),
					'field'          => '<input type="text" value="" id="_mbdb_published" name="_mbdb_published" style="width:10em;">',
					'description'    => '(<strong>mm/dd/yyyy</strong> or <strong>dd-mm-yyyy</strong> or <strong>yyyy-mm-dd</strong>)',
				),
				'_mbdb_series_order' => array(
					'fieldset_style' => 'width:50%;',
					'fieldset_class' => '',
					'label'          => __( 'Series Order', 'mooberry-book-manager' ),
					'field'          => '<input type="number" value="" step="any" min="0" name="_mbdb_series_order" id="_mbdb_series_order" style="width:3.5em;">',
					'description'    => '',
				),
			)

		);


		// SUMMARY

		$mbdb_summary_metabox = new_cmb2_box( array(
				'id'           => 'mbdb_summary_metabox',
				'title'        => __( 'Summary', 'mooberry-book-manager' ),
				'object_types' => array( $this->post_type, ),
				'context'      => 'normal',
				'priority'     => 'high',
				'show_names'   => false,
			)
		);

		$mbdb_summary_metabox->add_field( array(
				'name'            => __( 'Summary', 'mooberry-book-manager' ),
				'id'              => '_mbdb_summary',
				'type'            => 'wysiwyg',
				'sanitization_cb' => false,
				'options'         => array(
					'wpautop'       => true,
					// use wpautop?
					'media_buttons' => true,
					// show insert/upload button(s)
					'textarea_rows' => 10,
					// rows="..."
					'tabindex'      => '',
					'editor_css'    => '',
					// intended for extra styles for both visual and HTML editors buttons, needs to include the `<style>` tags, can use "scoped".
					'editor_class'  => '',
					// add extra class(es) to the editor textarea
					'teeny'         => false,
					// output the minimal editor config used in Press This
					'dfw'           => false,
					// replace the default fullscreen with DFW (needs specific css)
					'tinymce'       => true,
					// load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
					'quicktags'     => true
					// load Quicktags, can be used to pass settings directly to Quicktags using an array()
				),
			)
		);


		// EXCERPT
		$mbdb_excerpt_metabox = new_cmb2_box( array(
				'id'           => 'mbdb_excerpt_metabox',
				'title'        => __( 'Excerpt', 'mooberry-book-manager' ),
				'object_types' => array( $this->post_type, ), // Post type
				'context'      => 'normal',
				'priority'     => 'high',
				'show_names'   => true, // Show field names on the left
			)
		);


		$mbdb_excerpt_metabox->add_field( array(
				'name'    => __( 'Excerpt Style', 'mooberry-book-manager'),
				'id'      => '_mbdb_excerpt_type',
				'default' => 'text',
				'type'    => 'select',
				'options' => array(
					'text'   => __( 'Enter Excerpt Directly', 'mooberry-book-manager' ),
					'kindle' => __( 'Use Kindle Live Preview', 'mooberry-book-manager' ),
				),
			)
		);

		$mbdb_excerpt_metabox->add_field( array(
				'name'            => __( 'Excerpt', 'mooberry-book-manager' ),
				'id'              => '_mbdb_excerpt',
				'type'            => 'wysiwyg',
				'sanitization_cb' => false,
				'options'         => array(
					'wpautop'       => true,
					// use wpautop?
					'media_buttons' => true,
					// show insert/upload button(s)
					'textarea_rows' => 15,
					// rows="..."
					'tabindex'      => '',
					'editor_css'    => '',
					// intended for extra styles for both visual and HTML editors buttons, needs to include the `<style>` tags, can use "scoped".
					'editor_class'  => '',
					// add extra class(es) to the editor textarea
					'teeny'         => false,
					// output the minimal editor config used in Press This
					'dfw'           => false,
					// replace the default fullscreen with DFW (needs specific css)
					'tinymce'       => true,
					// load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
					'quicktags'     => true
					// load Quicktags, can be used to pass settings directly to Quicktags using an array()
				),
			)
		);

		/* $mbdb_excerpt_metabox->add_field( array(
			'name'    => __('Kindle Live Preview Code', 'mooberry-book-manager'),
			'id'      => '_mbdb_kindle_preview',
			'type'    => 'text_small',
			'sanitization_cb'	=> false, //'kindle_preview_sanitization',
			)
		);
		 */
		$mbdb_excerpt_metabox->add_field( array(
				'name'            => __( 'ASIN', 'mooberry-book-manager' ),
				'id'              => '_mbdb_kindle_preview',
				'type'            => 'text_small',
				'sanitization_cb' => false,
			)
		);


		// REVIEWS
		$mbdb_reviews_metabox = new_cmb2_box( array(
				'id'           => 'mbdb_reviews_metabox',
				'title'        => _x( 'Reviews', 'noun: book reviews', 'mooberry-book-manager' ),
				'object_types' => array( $this->post_type, ), // Post type
				'context'      => 'normal',
				'priority'     => 'high',

				'show_names' => true, // Show field names on the left
			)
		);

		$mbdb_reviews_metabox->add_field( array(
				'id'          => '_mbdb_reviews',
				'type'        => 'group',
				'description' => __( 'Add reviews of your book', 'mooberry-book-manager' ),
				'options'     => array(
					'group_title'   => _x( 'Reviews', 'noun', 'mooberry-book-manager' ) . ' {#}',
					// {#} gets replaced by row number
					'add_button'    => __( 'Add Review', 'mooberry-book-manager' ),
					'remove_button' => __( 'Remove Review', 'mooberry-book-manager' ),
					'sortable'      => true,
					// beta
				),
			)
		);

		$mbdb_reviews_metabox->add_group_field( '_mbdb_reviews', array(
				'name'            => __( 'Reviewer Name', 'mooberry-book-manager' ),
				'id'              => 'mbdb_reviewer_name',
				'type'            => 'text_medium',
				'sanitization_cb' => array( $this, 'validate_reviews' ),
			)
		);

		$mbdb_reviews_metabox->add_group_field( '_mbdb_reviews', array(
				'name'       => _x( 'Review Link', 'noun: URL to book review', 'mooberry-book-manager' ),
				'id'         => 'mbdb_review_url',
				'type'       => 'text_url',
				'desc'       => 'http://www.someWebsite.com/',
				'attributes' => array(
					'pattern' => '^(https?:\/\/)?([\da-zA-Z\.-]+)\.([a-zA-Z\.]{2,6}).*',
				),
			)
		);

		$mbdb_reviews_metabox->add_group_field( '_mbdb_reviews', array(
				'name' => _x( 'Review Website Name', 'noun: name of website of book review', 'mooberry-book-manager' ),
				'id'   => 'mbdb_review_website',
				'type' => 'text_medium',
			)
		);

		$mbdb_reviews_metabox->add_group_field( '_mbdb_reviews', array(
				'name' => _x( 'Review', 'noun: book review', 'mooberry-book-manager' ),
				'id'   => 'mbdb_review',
				'type' => 'textarea',
			)
		);

		// ADDITIONAL INFORMATION

		$mbdb_additional_info_metabox = new_cmb2_box( array(
				'id'           => 'mbdb_additional_info_metabox',
				'title'        => __( 'Additional Information', 'mooberry-book-manager' ),
				'object_types' => array( $this->post_type, ), // Post type
				'context'      => 'normal',
				'priority'     => 'high',
				'show_names'   => true, // Show field names on the left
			)
		);

		$mbdb_additional_info_metabox->add_field( array(
				'name'            => __( 'Additional Information', 'mooberry-book-manager' ),
				'id'              => '_mbdb_additional_info',
				'type'            => 'wysiwyg',
				'sanitization_cb' => false,
				'description'     => __( 'Any additional information you want to display on the page. Will be shown at the bottom of the page, after the reviews.', 'mooberry-book-manager' ),
				'options'         => array(
					'wpautop'       => true,
					// use wpautop?
					'media_buttons' => true,
					// show insert/upload button(s)
					'textarea_rows' => 15,
					// rows="..."
					'tabindex'      => '',
					'editor_css'    => '',
					// intended for extra styles for both visual and HTML editors buttons, needs to include the `<style>` tags, can use "scoped".
					'editor_class'  => '',
					// add extra class(es) to the editor textarea
					'teeny'         => false,
					// output the minimal editor config used in Press This
					'dfw'           => false,
					// replace the default fullscreen with DFW (needs specific css)
					'tinymce'       => true,
					// load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
					'quicktags'     => true,
					// load Quicktags, can be used to pass settings directly to Quicktags using an array()
				),
			)
		);

		// EDITIONS

		$mbdb_editions_metabox = new_cmb2_box( array(
				'id'           => 'mbdb_editions_metabox',
				'title'        => __( 'Formats', 'mooberry-book-manager' ),
				'object_types' => array( $this->post_type, ), // Post type
				'context'      => 'normal',
				'priority'     => 'high',
				'show_names'   => true, // Show field names on the left
			)
		);

		$mbdb_editions_metabox->add_field( array(
				'id'          => '_mbdb_editions',
				'type'        => 'group',
				'description' => __( "List the details of your book's hardcover, paperback, and e-book editions.  This is completely optional. If you choose to enter any, only the format field is required.", 'mooberry-book-manager' ),
				'options'     => array(
					'group_title'   => __( 'Format', 'mooberry-book-manager' ) . ' {#}',
					// {#} gets replaced by row number
					'add_button'    => __( 'Add New Format', 'mooberry-book-manager' ),
					'remove_button' => __( 'Remove Format', 'mooberry-book-manager' ),
					'sortable'      => true,
					// beta
				),
			)
		);

		// add emtpy option to editions array	with key = 0
		//$editions = array_column(MBDB()->edition_formats, 'name', 'uniqueID');
		//array_unshift( $editions, '' );
		$edition_formats = MBDB()->helper_functions->create_array_from_objects( MBDB()->options->edition_formats, 'name', true );

		$mbdb_editions_metabox->add_group_field( '_mbdb_editions', array(
				'name'            => _x( 'Format', 'noun: format of a book', 'mooberry-book-manager' ),
				'id'              => '_mbdb_format',
				'type'            => 'select',
				'sanitization_cb' => array( $this, 'validate_editions' ),
				'options'         => $edition_formats,
				'description'     => __( 'Add more formats in Settings', 'mooberry-book-manager' ),
			)
		);

		$mbdb_editions_metabox->add_group_field( '_mbdb_editions', array(
				'name' => __( 'EAN/ISBN', 'mooberry-book-manager' ),
				'id'   => '_mbdb_isbn',
				'type' => 'text_medium',
			)
		);

		$mbdb_editions_metabox->add_group_field( '_mbdb_editions', array(
				'name' => __( 'DOI (Digital Object Identifier)', 'mooberry-book-manager' ),
				'id'   => '_mbdb_doi',
				'type' => 'text_medium',
			)
		);


		$mbdb_editions_metabox->add_group_field( '_mbdb_editions', array(
				'name' => __( 'SKU', 'mooberry-book-manager' ),
				'id'   => '_mbdb_sku',
				'type' => 'text_medium',
			)
		);

		$mbdb_editions_metabox->add_group_field( '_mbdb_editions', array(
				'name'    => __( 'Language', 'mooberry-book-manager' ),
				'id'      => '_mbdb_language',
				'type'    => 'select',
				'options' => MBDB()->options->languages,
				'default' => MBDB()->options->default_language,
			)
		);

		$mbdb_editions_metabox->add_group_field( '_mbdb_editions', array(
				'name'       => __( 'Number of Pages', 'mooberry-book-manager' ),
				'id'         => '_mbdb_length',
				'type'       => 'text_small',
				'attributes' => array(
					'type'    => 'number',
					'pattern' => '\d*',
					'min'     => 1,
				),
			)
		);

		$mbdb_editions_metabox->add_group_field( '_mbdb_editions', array(
				'name'       => __( 'Height', 'mooberry-book-manager' ),
				'id'         => '_mbdb_height',
				'type'       => 'text_small',
				'attributes' => array(
					'type' => 'number',
					'step' => 'any',
					'min'  => 0,
				),
			)
		);

		$mbdb_editions_metabox->add_group_field( '_mbdb_editions', array(
				'name'       => __( 'Width', 'mooberry-book-manager' ),
				'id'         => '_mbdb_width',
				'type'       => 'text_small',
				'attributes' => array(
					'type' => 'number',
					'step' => 'any',
					'min'  => 0,
				),
			)
		);

		$mbdb_editions_metabox->add_group_field( '_mbdb_editions', array(
				'name'    => _x( 'Unit', 'units of measurement', 'mooberry-book-manager' ),
				'id'      => '_mbdb_unit',
				'type'    => 'select',
				'options' => MBDB()->options->units,
				'default' => MBDB()->options->default_unit,
			)
		);

		$mbdb_editions_metabox->add_group_field( '_mbdb_editions', array(
				'name'       => __( 'Suggested Retail Price', 'mooberry-book-manager' ),
				'id'         => '_mbdb_retail_price',
				'type'       => 'text_small',
				'desc'       => __( 'Do not enter the currency symbol. That will be determined by the currency selected below.', 'mooberry-book-manager' ),
				'attributes' => array(
					'pattern' => '^\d*([.,]\d{2}$)?',
					'min'     => 0,
				),
			)
		);

		$mbdb_editions_metabox->add_group_field( '_mbdb_editions', array(
				'name'    => __( 'Currency', 'mooberry-book-manager' ),
				'id'      => '_mbdb_currency',
				'type'    => 'select',
				'options' => MBDB()->options->currencies,
				'default' => MBDB()->options->default_currency,
			)
		);

		$mbdb_editions_metabox->add_group_field( '_mbdb_editions', array(
				'name' => __( 'Edition Title', 'mooberry-book-manager' ),
				'id'   => '_mbdb_edition_title',
				'type' => 'text_medium',
				'desc' => __( 'First Edition, Second Edition, etc.', 'mooberry-book-manager' ),
			)
		);


		// COVER

		$mbdb_cover_image_metabox = new_cmb2_box( array(
				'id'           => 'mbdb_cover_image_metabox',
				'title'        => _x( 'Cover', 'noun', 'mooberry-book-manager' ),
				'object_types' => array( $this->post_type, ), // Post type
				'context'      => 'side',
				'priority'     => 'default',
				'show_names'   => false, // Show field names on the left
			)
		);

		$mbdb_cover_image_metabox->add_field( apply_filters(
				'mbdb_cover_image_metabox_args', array(
					'name'       => _x( 'Book Cover', 'noun', 'mooberry-book-manager' ),
					'id'         => '_mbdb_cover',
					'type'       => 'file',
					'allow'      => array( 'attachment' ), // limit to just attachments with array( 'attachment' )
					'column'     => array( 'position' => 2 ),
					'display_cb' => array( $this, 'display_cover_column' ),
				)
			)
		);

		// BOOK INFO

		$mbdb_bookinfo_metabox = new_cmb2_box( array(
				'id'           => 'mbdb_bookinfo_metabox',
				'title'        => __( 'Book Details', 'mooberry-book-manager' ),
				'object_types' => array( $this->post_type, ), // Post type
				'context'      => 'side',
				'priority'     => 'default',
				'show_names'   => true, // Show field names on the left
			)
		);

		$mbdb_bookinfo_metabox->add_field( array(
				'name' => __( 'Subtitle', 'mooberry-book-manager' ),
				'id'   => '_mbdb_subtitle',
				'type' => 'text',
			)
		);

		$mbdb_bookinfo_metabox->add_field( array(
				'name'            => __( 'Release Date', 'mooberry-book-manager' ),
				'id'              => '_mbdb_published',
				'type'            => 'text_date',
				'desc'            => 'yyyy/mm/dd',
				'date_format'     => 'Y/m/d',
				'sanitization_cb' => array( MBDB()->helper_functions, 'format_date' ),
				'column'          => array(
					'position' => 5,
				),
				'display_cb'      => array( $this, 'display_release_date_column' ),
			)
		);

		// add emtpy option to publishers array	with key = 0
		//$publishers = array_column(MBDB()->publishers, 'name', 'uniqueID');
		//array_unshift( $publishers, '' );
		/*
		foreach (MBDB()->publishers as $id => $publisher) {
			$publishers[ $id ] = $publisher->name;
		}
		array_unshift( $publishers, '' );
		*/


		$mbdb_bookinfo_metabox->add_field( array(
				'name'       => __( 'Publisher', 'mooberry-book-manager' ),
				'id'         => '_mbdb_publisherID',
				'type'       => 'select',
				'options'    => $publishers,
			//	'desc'       => __( 'Set up Publishers in Settings.', 'mooberry-book-manager' ),
				'column'     => array(
					'position' => 8,
				),
				'display_cb' => array( $this, 'display_publisher_column' ),
			)
		);


		$mbdb_bookinfo_metabox->add_field( array(
				'name'       => __( 'Imprint', 'mooberry-book-manager' ),
				'id'         => '_mbdb_imprintID',
				'type'       => 'select',
				'options'    => $imprints,
				'desc'       => __( 'Set up Imprints in Settings.', 'mooberry-book-manager' ),

			)
		);

		$mbdb_bookinfo_metabox->add_field( array(
				'name'       => __( 'Goodreads Link', 'mooberry-book-manager' ),
				'id'         => '_mbdb_goodreads',
				'type'       => 'text_url',
				'desc'       => 'http://www.goodreads.com/your/Unique/Text/',
				'attributes' => array(
					'pattern' => '^(https?:\/\/)?www.goodreads.com.*',
				),
			)
		);

		$mbdb_bookinfo_metabox->add_field( array(
				'name'       => __( 'Reedsy Discovery Link', 'mooberry-book-manager' ),
				'id'         => '_mbdb_reedsy',
				'type'       => 'text_url',
				'desc'       => 'https://www.reedsy.com/discovery/book/your-unique-text',
				'attributes' => array(
					'pattern' => '^(https?:\/\/)?www.reedsy.com/discovery/book/.*',
				),
			)
		);

		$mbdb_bookinfo_metabox->add_field( array(
				'name'       => __( 'Google Books Link', 'mooberry-book-manager' ),
				'id'         => '_mbdb_google_books',
				'type'       => 'text_url',
				//'desc'       => 'https://www.google.com/books/edition/your-unique-text',
				//'attributes' => array(
//					'pattern' => '^(https?:\/\/)?www.google.com/books/edition/.*',
//				),
			)
		);

		$mbdb_bookinfo_metabox->add_field( array(
				'name'       => __( 'Series Order', 'mooberry-book-manager' ),
				'id'         => '_mbdb_series_order',
				'desc'       => __( '(leave blank if not part of a series)', 'mooberry-book-manager' ),
				'type'       => 'text_small',
				'attributes' => array(
					'type' => 'number',
					'step' => 'any',
					'min'  => 0,
				),
				'column'     => array(
					'position' => 7,
				),
				'display_cb' => array( $this, 'display_series_order_column' ),
			)
		);

		// BUYLINKS
		$mbdb_buylinks_metabox = new_cmb2_box( array(
				'id'           => 'mbdb_buylinks_metabox',
				'title'        => _x( 'Retailer Links', 'noun: URLs to book retailers', 'mooberry-book-manager' ),
				'object_types' => array( $this->post_type, ), // Post type
				'context'      => 'side',
				'priority'     => 'default',
				'show_names'   => true, // Show field names on the left
			)
		);

		$mbdb_buylinks_metabox->add_field( array(
				'id'          => '_mbdb_buylinks',
				'type'        => 'group',
				'description' => __( 'Add links where readers can purchase your book', 'mooberry-book-manager' ),
				'options'     => array(
					'group_title'   => _x( 'Retailer Link', 'noun', 'mooberry-book-manager' ) . ' {#}',
					// {#} gets replaced by row number
					'add_button'    => __( 'Add Retailer Link', 'mooberry-book-manager' ),
					'remove_button' => __( 'Remove Retailer Link', 'mooberry-book-manager' ),
					'sortable'      => true,
					// beta
				),
			)
		);

		// add emtpy option to retailers array	with key = 0
		//$retailers = array_column(MBDB()->retailers, 'name', 'uniqueID');
		//array_unshift( $retailers, '' );
		$retailers = MBDB()->helper_functions->create_array_from_objects( MBDB()->options->retailers, 'name', true );


		$mbdb_buylinks_metabox->add_group_field( '_mbdb_buylinks', array(
				'name'            => __( 'Retailer', 'mooberry-book-manager' ),
				'id'              => '_mbdb_retailerID',
				'type'            => 'select',
				'options'         => $retailers,
				'sanitization_cb' => array( $this, 'validate_retailers' ),
				'description'     => __( 'Add more retailers in Settings', 'mooberry-book-manager' ),
			)
		);

		$mbdb_buylinks_metabox->add_group_field( '_mbdb_buylinks', array(
				'name'       => _x( 'Link', 'noun: URL', 'mooberry-book-manager' ),
				'id'         => '_mbdb_buylink',
				'type'       => 'text_url',
				'desc'       => 'http://www.someWebsite.com/',
				'attributes' => array(
					'pattern' => MBDB()->helper_functions->url_validation_pattern(),
				),
			)
		);

		// DOWNLOAD LINKS
		$mbdb_downloadlinks_metabox = new_cmb2_box( array(
				'id'           => 'mbdb_downloadlinks_metabox',
				'title'        => _x( 'Download Links', 'noun', 'mooberry-book-manager' ),
				'object_types' => array( $this->post_type, ), // Post type
				'context'      => 'side',
				'priority'     => 'low',
				'show_names'   => true, // Show field names on the left

			)
		);

		$mbdb_downloadlinks_metabox->add_field( array(
				'id'          => '_mbdb_downloadlinks',
				'type'        => 'group',
				'description' => __( 'If your book is available to download for free, add the links for each format.', 'mooberry-book-manager' ),
				'options'     => array(
					'group_title'   => _x( 'Download Link', 'noun', 'mooberry-book-manager' ) . ' {#}',
					// {#} gets replaced by row number
					'add_button'    => __( 'Add Download Link', 'mooberry-book-manager' ),
					'remove_button' => __( 'Remove Download Link', 'mooberry-book-manager' ),
					'sortable'      => true,
					// beta
				),
			)
		);

		// add emtpy option to formats array	with key = 0
		//$formats = array_column(MBDB()->formats, 'name', 'uniqueID');
		//array_unshift( $formats, '' );
		$download_formats = MBDB()->helper_functions->create_array_from_objects( MBDB()->options->download_formats, 'name', true );


		$mbdb_downloadlinks_metabox->add_group_field( '_mbdb_downloadlinks', array(
				'name'            => _x( 'Format', 'noun', 'mooberry-book-manager' ),
				'id'              => '_mbdb_formatID',
				'type'            => 'select',
				'options'         => $download_formats,
				'sanitization_cb' => array( $this, 'validate_downloadlinks' ),
				'description'     => __( 'Add more formats in Settings', 'mooberry-book-manager' ),
			)
		);

		$mbdb_downloadlinks_metabox->add_group_field( '_mbdb_downloadlinks', array(
				'name'       => _x( 'Link', 'noun', 'mooberry-book-manager' ),
				'id'         => '_mbdb_downloadlink',
				'type'       => 'text_url',
				'desc'       => 'http://www.someWebsite.com/',
				'attributes' => array(
					'pattern' => MBDB()->helper_functions->url_validation_pattern(),
				),
			)
		);


		$mbdb_summary_metabox         = apply_filters( 'mbdb_summary_metabox', $mbdb_summary_metabox );
		$mbdb_editions_metabox        = apply_filters( 'mbdb_editions_metabox', $mbdb_editions_metabox );
		$mbdb_excerpt_metabox         = apply_filters( 'mbdb_excerpt_metabox', $mbdb_excerpt_metabox );
		$mbdb_reviews_metabox         = apply_filters( 'mbdb_reviews_metabox', $mbdb_reviews_metabox );
		$mbdb_additional_info_metabox = apply_filters( 'mbdb_additional_info_metabox', $mbdb_additional_info_metabox );
		$mbdb_cover_image_metabox     = apply_filters( 'mbdb_cover_image_metabox', $mbdb_cover_image_metabox );
		$mbdb_bookinfo_metabox        = apply_filters( 'mbdb_bookinfo_metabox', $mbdb_bookinfo_metabox );
		$mbdb_buylinks_metabox        = apply_filters( 'mbdb_buylinks_metabox', $mbdb_buylinks_metabox );
		$mbdb_downloadlinks_metabox   = apply_filters( 'mbdb_downloadlinks_metabox', $mbdb_downloadlinks_metabox );

		$this->metaboxes = array(
			$mbdb_summary_metabox,
			$mbdb_editions_metabox,
			$mbdb_excerpt_metabox,
			$mbdb_reviews_metabox,
			$mbdb_additional_info_metabox,
			$mbdb_cover_image_metabox,
			$mbdb_bookinfo_metabox,
			$mbdb_buylinks_metabox,
			$mbdb_downloadlinks_metabox,
		);

	}

	/**
	 *  Add meta box for "Need help with Mooberry Book Manager?"
	 *
	 *
	 *
	 * @since  2.0 ?
	 *
	 *
	 * @access public
	 */
	public function mbd_metabox() {

		add_meta_box( 'mbdb_mbd_metabox', __( 'Need help with Mooberry Book Manager?', 'mooberry-book-manager' ), array(
			$this,
			'display_mbdb_metabox',
		), $this->post_type, 'side', 'core' );
	}

	public function display_mbdb_metabox( $post, $args ) {

		include "admin/views/admin-about-mooberry.php";
	}


	// term meta
	public function create_taxonomy_metaboxes() {
		$cmb_term = new_cmb2_box( array(
			'id'               => 'mbdb_taxonomy_websites',
			'title'            => 'test',
			// Doesn't output for term boxes
			'object_types'     => array( 'term' ),
			// Tells CMB2 to use term_meta vs post_meta
			'taxonomies'       => array( 'mbdb_illustrator', 'mbdb_cover_artist', 'mbdb_editor' ),
			// Tells CMB2 which taxonomies should have these fields
			'new_term_section' => true,
			// Will display in the "Add New Category" section
		) );

		$cmb_term->add_field( array(
			'name' => esc_html__( 'Website', 'mooberry-book-manager' ),
			'id'   => 'mbdb_website',
			'type' => 'text_url',
		) );


		$cmb_term = new_cmb2_box( array(
			'id'               => 'mbdb_taxonomy_grid_descriptions',
			'title'            => 'test', // Doesn't output for term boxes
			'object_types'     => array( 'term' ), // Tells CMB2 to use term_meta vs post_meta
			'taxonomies'       => array(
				'mbdb_genre',
				'mbdb_tag',
				'mbdb_series',
				'mbdb_illustrator',
				'mbdb_cover_artist',
				'mbdb_editor',
			), // Tells CMB2 which taxonomies should have these fields
			'new_term_section' => true, // Will display in the "Add New Category" section
		) );

		$cmb_term->add_field( array(
			'name'            => esc_html__( 'Book Grid Description', 'mooberry-book-manager' ),
			'desc'            => esc_html__( 'The Book Grid Description is displayed above the auto-generated grid for this page, ex. ', 'mooberry-book-manager' ) . home_url( 'series/series-name' ),
			'id'              => 'mbdb_tax_grid_description',
			'type'            => 'wysiwyg',
			'sanitization_cb' => array( MBDB()->helper_functions, 'sanitize_wysiwyg' ),
			'options'         => array(
				'wpautop'       => true,
				// use wpautop?
				'media_buttons' => true,
				// show insert/upload button(s)
				'textarea_rows' => 10,
				// rows="..."
				'tabindex'      => '',
				'editor_css'    => '',
				// intended for extra styles for both visual and HTML editors buttons, needs to include the `<style>` tags, can use "scoped".
				'editor_class'  => '',
				// add extra class(es) to the editor textarea
				'teeny'         => false,
				// output the minimal editor config used in Press This
				'dfw'           => false,
				// replace the default fullscreen with DFW (needs specific css)
				'tinymce'       => true,
				// load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
				'quicktags'     => true
				// load Quicktags, can be used to pass settings directly to Quicktags using an array()
			),
		) );

		$cmb_term->add_field( array(
			'name'            => esc_html__( 'Book Grid Description (Bottom)', 'mooberry-book-manager' ),
			'desc'            => esc_html__( 'The Book Grid Description is displayed below the auto-generated grid for this page, ex. ', 'mooberry-book-manager' ) . home_url( 'series/series-name' ),
			'id'              => 'mbdb_tax_grid_description_bottom',
			'type'            => 'wysiwyg',
			'sanitization_cb' => array( MBDB()->helper_functions, 'sanitize_wysiwyg' ),
			'options'         => array(
				'wpautop'       => true,
				// use wpautop?
				'media_buttons' => true,
				// show insert/upload button(s)
				'textarea_rows' => 10,
				// rows="..."
				'tabindex'      => '',
				'editor_css'    => '',
				// intended for extra styles for both visual and HTML editors buttons, needs to include the `<style>` tags, can use "scoped".
				'editor_class'  => '',
				// add extra class(es) to the editor textarea
				'teeny'         => false,
				// output the minimal editor config used in Press This
				'dfw'           => false,
				// replace the default fullscreen with DFW (needs specific css)
				'tinymce'       => true,
				// load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
				'quicktags'     => true
				// load Quicktags, can be used to pass settings directly to Quicktags using an array()
			),
		) );

	}

	public function display_cover_column( $field_args, $field ) {
		global $post;
		if ( $this->data_object == null || $post->ID != $this->data_object->id ) {
			//	$this->set_data_object( $post->ID );
			$this->set_data_object( $this->data_object->id );
		}
		$data = $this->data_object->cover;
		//$data = $this->get_meta_data( '', 0, array( 'field_id' => $field_args['id'] ) );
		if ( $data != '' ) {
			$data = '<img src="' . $data . '" width="100px"/>';
		}
		$this->display_column( $field_args['id'], $data, $data, $this->data_object );
	}

	public function display_publisher_column( $field_args, $field ) {

		global $post;
		if ( $this->data_object == null || $post->ID != $this->data_object->id ) {
			$this->set_data_object( $this->data_object->id );
		}
		$data =  $this->data_object->has_publisher() ? $this->data_object->publisher->name : '';

		$this->display_column( 'publisher_id', $data, $field->value, $this->data_object );

	}

	public function display_release_date_column( $field_args, $field ) {
		global $post;
		if ( $this->data_object == null || $post->ID != $this->data_object->id ) {
			//$this->set_data_object( $post->ID );
			$this->set_data_object( $this->data_object->id );
		}
		$book     = $field->args['display_cb'][0]->data_object;
		$data     = $field->value;
		$raw_data = '';
		if ( ! empty( $data ) ) {
			$format   = get_option( 'date_format' );
			$raw_data = date( 'Y-m-d', strtotime( $data ) );
			$data     = date_i18n( $format, strtotime( $data ) );
		}

		// output 2 hidden fields for fields we want to be able to quick edit even if they aren't shown
		echo '<span id="subtitle-' . $book->id . '" style="display:none;">' . $book->subtitle . '</span>';
		echo '<span id="goodreads-' . $book->id . '" style="display:none;">' . $book->goodreads . '</span>';

		$this->display_column( 'release_date', $data, $raw_data, $book );

	}

	public function display_series_order_column( $field_args, $field ) {
		$book = $field->args['display_cb'][0]->data_object;
		$data = $book->series_order;

		$this->display_column( 'series_order', $data, $data, $book );
	}

	protected function display_column( $column, $data, $raw_data, $book ) {
		do_action( 'mbdb_book_pre_mbdb_' . $column . '_column', $column, $data, $book );
		echo '<div id="' . $column . '-' . $book->id . '_column">';
		echo apply_filters( 'mbdb_book_mbdb_' . $column . '_column', $data, $book, $book->id, $column );
		echo '<span id="' . $column . '-' . $book->id . '" style="display:none;">' . $raw_data . '</span>';
		echo '</div>';
		do_action( 'mbdb_book_post_mbdb_' . $column . '_column', $column, $data, $book );
	}


	public function set_book_excerpt( $content ) {
		// if we're in the admin side and the post type is mbdb_book then we're showign the list of books
		// truncate the excerpt
		if ( is_admin() && get_post_type() == $this->post_type ) {
			$content = trim( substr( $content, 0, 50 ) );
			if ( strlen( $content ) > 0 ) {
				$content .= '...';
			}
		}
		// v3.1
		// if we're not on the admin side and it's a book post and main query
		// don't display the excerpt
		if ( get_post_type() == $this->post_type && is_main_query() && ! is_admin() ) {

			// this weeds out content in the sidebar and other odd places
			// thanks joeytwiddle for this update
			if ( ! in_the_loop() || ! is_main_query() ) {
				return $content;
			}
			// we DO want the excerpt on the serach page
			if ( is_search() ) {
				return $content;
			}

			return '';
		}

		return $content;
	}



	/**********************************************************
	 *
	 * Saving the book post
	 *
	 *******************************************************/

	/**
	 *  Set the book's excerpt to a portion of the summary
	 *  Also make sure the post_content has the shortcode
	 *
	 *
	 * @param  [int] $post_id id of post being saved
	 * @param  [object] $post    post object of post being saved
	 *
	 *
	 * @access public
	 * @since
	 * @since  3.0 Added shortcode
	 *
	 */

	public function save_book( $post_id, $post = null ) {

		// if the post object is null then we are creating a new book
		// and must pull values from the GET/POST vars ???
		if ( $post == null ) {
			if ( array_key_exists( '_mbdb_summary', $_POST ) && $_POST['_mbdb_summary'] ) {
				$summary = $_POST['_mbdb_summary'];
			} elseif ( array_key_exists( '_mbdb_summary', $_GET ) && $_GET['_mbdb_summary'] ) {
				$summary = $_GET['_mbdb_summary'];
			} else {
				$summary = '';
			}
		} else {
			// the post has been saved already so pull from the database
			//$book = new Mooberry_Book_Manager_Book( $post_id );
			$book = MBDB()->book_factory->create_book( $post_id );
			//MBDB()->books->get($post_id);

			if ( $book != null ) {
				$summary = $book->summary;
			} else {
				$summary = '';
			}
		}

		// 4.2
		// use featured image if selected
		if ( MBDB()->options->use_featured_image ) {
			if ( isset( $_POST['_mbdb_cover_id'] ) ) {
				if ( $_POST['_mbdb_cover_id'] != '' ) {
					$cover_id = intval( $_POST['_mbdb_cover_id'] );
					MBDB()->helper_functions->set_attach_id( $post_id, $cover_id );
				} else {
					MBDB()->helper_functions->remove_attach_id( $post_id );
				}
			}
		}

		// unhook this function so it doesn't loop infinitely
		// and mbdb_save_book_custom_table so it doesn't run twice
		remove_action( 'save_post_' . $this->post_type, array( $this, 'save_book' ) );
		//remove_action( 'publish_mbdb_book', 'mbdbbs_send_fave_author_email', 20, 2 );
		//remove_action('save_post', array( $this, 'save' ), 20 );

		// update the post, which calls save_post again
		wp_update_post( array(
			'ID'           => $post_id,
			'post_excerpt' => strip_tags( $summary ),
			'post_content' => '[mbdb_book]',
		) );

		// re-hook this function and mbdb_save_book_custom_table
		add_action( 'save_post_' . $this->post_type, array( $this, 'save_book' ) );
		//add_action( 'publish_mbdb_book', 'mbdbbs_send_fave_author_email', 20, 2 );
		//add_action( 'save_post', array( $this, 'save'), 20);
	}

	public function kindle_preview_sanitization( $content, $args, $obj ) {

		return $this->sanitize_field( $content );

	}

// 3.4.12
// when taxonomies are displayed as checkboxes instead of like tags, they take
// ids as input but when they are like tags, they take names as input
// because the taxonomies have been displayed like categories evn though they
// are not hierarchical, the id to name has to be translated upon saving

	public function switch_tax_ids_and_name() {
		// WP version 5.1+ does this automatically so bail if it's that version
		if ( version_compare( get_bloginfo( 'version' ), '5.1', '>=' ) ) {
			return;
		}
		if ( ! isset( $_POST['post_type'] ) || $_POST['post_type'] != $this->post_type ) {
			return;
		}
		if ( isset( $_POST['_inline_edit'] ) ) {
			return;
		}

		if ( isset( $_POST['tax_input'] ) && is_array( $_POST['tax_input'] ) ) {
			$new_tax_input = array();
			foreach ( $_POST['tax_input'] as $tax => $terms ) {
				if ( is_array( $terms ) ) {
					$taxonomy = get_taxonomy( $tax );

					//if( !$taxonomy->hierarchical ) {
					//
					if ( in_array( $taxonomy->name, array_keys( $this->taxonomies ) ) ) { //array('mbdb_genre', 'mbdb_series', 'mbdb_cover_artist', 'mbdb_tag', 'mbdb_editor', 'mbdb_illustrator'))) {

						// $terms = array_map( 'intval', array_filter( $terms ) );
						$term_names = array();
						foreach ( $terms as $term_id ) {
							$term = get_term( $term_id );
							if ( ! is_wp_error( $term ) ) {
								$term_names[] = $term->name;
							}
						}
						//$terms = array_map( 'get_term', $terms );
					}
				}
				$new_tax_input[ $tax ] = $term_names;
			}
			$_POST['tax_input'] = $new_tax_input;
		}
	}


	/*********************************************************
	 *
	 *  DISPLAY EDIT PAGE
	 *
	 **********************************************************/

	protected function get_taxonomies_on_top() {

		$taxonomies = array();

		$custom_taxonomies = $this->get_custom_taxonomies();
		foreach ( $custom_taxonomies as $custom_taxonomy ) {
			$taxonomies[] = 'tagsdiv-' . $custom_taxonomy;
		}

		return array_merge( $taxonomies, array( 'tagsdiv-mbdb_tag', 'tagsdiv-mbdb_series', 'tagsdiv-mbdb_genre' ) );
	}

	protected function get_taxonomies_on_bottom() {
		return array( 'tagsdiv-mbdb_cover_artist', 'tagsdiv-mbdb_illustrator', 'tagsdiv-mbdb_editor' );
	}

	function reorder_taxonomy_boxes() {

		global $wp_meta_boxes;


		$taxonomies = $this->get_taxonomies_on_top();

		// remove the cover to be readded before the taxonomies
		$cover = $wp_meta_boxes['mbdb_book']['side']['default']['mbdb_cover_image_metabox'];
		unset( $wp_meta_boxes['mbdb_book']['side']['default']['mbdb_cover_image_metabox'] );

		foreach ( $taxonomies as $taxID ) {
			if ( isset( $wp_meta_boxes['mbdb_book']['side']['core'][ $taxID ] ) ) {
				$tax = $wp_meta_boxes['mbdb_book']['side']['core'][ $taxID ];
				unset( $wp_meta_boxes['mbdb_book']['side']['core'][ $taxID ] );

				if ( array_key_exists( 'default', $wp_meta_boxes['mbdb_book']['side'] ) ) {
					$wp_meta_boxes['mbdb_book']['side']['default'] = array( $taxID => $tax ) + $wp_meta_boxes['mbdb_book']['side']['default'];
				} else {
					$wp_meta_boxes['mbdb_book']['side']['default'] = array( $taxID => $tax );
				}
			}
		}

		// move these to the bottom
		$taxonomies = $this->get_taxonomies_on_bottom();
		foreach ( $taxonomies as $taxID ) {
			if ( isset( $wp_meta_boxes['mbdb_book']['side']['core'][ $taxID ] ) ) {
				$tax = $wp_meta_boxes['mbdb_book']['side']['core'][ $taxID ];
				unset( $wp_meta_boxes['mbdb_book']['side']['core'][ $taxID ] );

				if ( array_key_exists( 'low', $wp_meta_boxes['mbdb_book']['side'] ) ) {
					$wp_meta_boxes['mbdb_book']['side']['low'][ $taxID ] = $tax;
				} else {
					$wp_meta_boxes['mbdb_book']['side']['low'] = array( $taxID => $tax );
				}
			}
		}

		// now add cover above the taxonomies
		$wp_meta_boxes['mbdb_book']['side']['default'] =
			array( 'mbdb_cover_image_metabox' => $cover ) +
			$wp_meta_boxes['mbdb_book']['side']['default'];
	}


	/**************************************************************.
	 *
	 *  VALIDATION
	 *
	 **************************************************************/
	public function validate_editions( $field, $args, $obj ) {

		do_action( 'mbdb_before_validate_editions', $field );

		if ( ! array_key_exists( '_mbdb_editions', $_POST ) ) {
			return $this->sanitize_field( $field );
		}

		if ( ! is_array( $_POST['_mbdb_editions'] ) ) {
			return $this->sanitize_field( $field );
		}

		$flag = false;
		foreach ( $_POST['_mbdb_editions'] as $editionID => $edition ) {
			// if any field is filled out, format must be selected
			$message = __( 'Editions require at least the format. Please check edition #%s.', 'mooberry-book-manager' );
			// only the format field is required
			// set flag = true if validation fails
			$is_width  = $this->is_array_element_set( '_mbdb_width', $edition );
			$is_height = $this->is_array_element_set( '_mbdb_height', $edition );
			$is_price  = $this->is_array_element_set( '_mbdb_retail_price', $edition );
			$is_isbn   = $this->is_array_element_set( '_mbdb_isbn', $edition );
			$is_doi   = $this->is_array_element_set( '_mbdb_doi', $edition );
			$is_sku   = $this->is_array_element_set( '_mbdb_sku', $edition );
			$is_length = $this->is_array_element_set( '_mbdb_length', $edition );
			$is_title  = $this->is_array_element_set( '_mbdb_edition_title', $edition );

			$is_format = $this->is_array_element_set( '_mbdb_format', $edition ) && $edition['_mbdb_format'] != '0';

			$is_others = ( $is_isbn || $is_doi || $is_sku || $is_length || $is_width || $is_height || $is_price || $is_title );

			// format is required
			$flag = ! $is_format;

			// if width or height is filled in, the other one must be also
			if ( ( $is_width || $is_height ) && ! ( $is_width && $is_height ) ) {
				$flag    = true;
				$message = __( 'If width or height is specified, both must be. Please check edition #%s.', 'mooberry-book-manager' );
			}

			// if it's the last element and both sides of the check are empty, ignore the error
			// because CMB2 will automatically delete it from the repeater group
			$flag = $this->allow_blank_last_elements( $is_format, $is_others, '_mbdb_editions', $editionID, $flag );

			// if flag is true, there is an error
			if ( $flag ) {
				break;
			}

		}

		do_action( 'mbdb_validate_editions_before_msg', $field, $flag, $edition );

		$this->display_msg_if_invalid( $flag, '_mbdb_editions', $edition, apply_filters( 'mbdb_validate_editions_msg', $message ) );

		do_action( 'mbdb_validate_editions_after_msg', $field, $flag, $edition );

		return $this->sanitize_field( $field );
	}

	public function validate_reviews( $field ) {
		do_action( 'mbdb_before_validate_reviews', $field );

		if ( ! array_key_exists( '_mbdb_reviews', $_POST ) ) {
			return $this->sanitize_field( $field );
		}

		if ( ! is_array( $_POST['_mbdb_reviews'] ) ) {
			return $this->sanitize_field( $field );
		}

		$flag = false;
		foreach ( $_POST['_mbdb_reviews'] as $reviewID => $review ) {
			// if the review doesn't exist, then the others can't exist either
			// but if review does exist, then at least one of the others has to also
			// set flag = true if validation fails
			$is_reviewer_name  = $this->is_array_element_set( 'mbdb_reviewer_name', $review );
			$is_review_url     = $this->is_array_element_set( 'mbdb_review_url', $review );
			$is_review_website = $this->is_array_element_set( 'mbdb_review_website', $review );

			$is_others = ( $is_reviewer_name || $is_review_url || $is_review_website );

			$is_review = $this->is_array_element_set( 'mbdb_review', $review );
			$flag      = ! ( $is_review && $is_others );

			// if it's the last element and both sides of the check are empty, ignore the error
			// because CMB2 will automatically delete it from the repeater group
			$flag = $this->allow_blank_last_elements( $is_review, $is_others, '_mbdb_reviews', $reviewID, $flag );

			if ( $flag ) {
				break;
			}
		}
		do_action( 'mbdb_validate_reviews_before_msg', $field, $flag, $review );

		$this->display_msg_if_invalid( $flag, '_mbdb_reviews', $review, apply_filters( 'mbdb_validate_reviews_msg', __( 'Reviews require review text and at least one other field. Please check review #%s.', 'mooberry-book-manager' ) ) );

		do_action( 'mbdb_validate_reviews_after_msg', $field, $flag, $review );

		return $this->sanitize_field( $field );
	}

	public function validate_downloadlinks( $field ) {
		if ( ! array_key_exists( '_mbdb_downloadlinks', $_POST ) ) {
			return $this->sanitize_field( $field );
		}

		if ( ! is_array( $_POST['_mbdb_downloadlinks'] ) ) {
			return $this->sanitize_field( $field );
		}

		$this->validate_all_group_fields( '_mbdb_downloadlinks', '_mbdb_formatID', array( '_mbdb_downloadlink' ), __( 'Download links require all fields filled out. Please check download link #%s.', 'mooberry-book-manager' ) );

		return $this->sanitize_field( $field );
	}

	public function validate_retailers( $field ) {
		if ( ! array_key_exists( '_mbdb_buylinks', $_POST ) ) {
			return $this->sanitize_field( $field );
		}

		if ( ! is_array( $_POST['_mbdb_buylinks'] ) ) {
			return $this->sanitize_field( $field );
		}
		$this->validate_all_group_fields( '_mbdb_buylinks', '_mbdb_retailerID', array( '_mbdb_buylink' ), __( 'Retailer links require all fields filled out. Please check retailer link #%s.', 'mooberry-book-manager' ) );

		return $this->sanitize_field( $field );
	}

	// since 3.0
	public function validate_social_media( $field ) {
		if ( ! array_key_exists( '_mbdb_social_media_links', $_POST ) ) {
			return $this->sanitize_field( $field );
		}

		if ( ! is_array( $_POST['_mbdb_social_media_links'] ) ) {
			return $this->sanitize_field( $field );
		}

		$this->validate_all_group_fields( '_mbdb_social_media_links', '_mbdb_social_mediaID', array( '_mbdb_social_media_link' ), __( 'Social Media Links require all fields filled out. Please check social media link #%s.', 'mooberry-book-manager' ) );

		return $this->sanitize_field( $field );
	}

	public function override_wp_seo_meta( $tag ) {
		if ( is_single() ) {
			if ( get_post_type() == $this->post_type ) {
				return '';
			}
		}

		return $tag;
	}

	function meta_tags() {
		if ( is_single() ) {
			if ( get_post_type() == $this->post_type ) {
				global $post;
				$this->set_data_object( $post->ID );

				$summary = $this->data_object->summary;
				if ( strlen( $summary ) > 150 ) {
					$summary = substr( $summary, 0, 150 ) . '...';
				}


				if ( ! MBDB_WPSEO_INSTALLED || MBDB()->options->override_wpseo( 'description' ) ) {
					$series_info   = '';
					$genre_info    = '';
					$retailer_info = '';
					if ( $this->data_object->is_standalone() ) {
						$series_info = __( 'Standalone book', 'mooberry-book-manager' );
					} else {
						$series_info = sprintf( __( 'Book %d in the %s series', 'mooberry-book-manager' ), $this->data_object->series_order, $this->data_object->get_series_list() );
					}
					if ( $this->data_object->has_genres() ) {

						$genre_info = ' ' . sprintf( _n( 'in the %s genre', 'in the %s genres', count( $this->data_object->genres ), 'mooberry-book-manager' ), $this->data_object->genre_list );
					}
					if ( $this->data_object->has_buy_links() ) {
						$retailer_info = ' ' . sprintf( __( 'Available from %s.', 'mooberry-book-manager' ), $this->data_object->get_retailer_list() );
					}


					echo '<meta name="description" content="' . esc_attr( strip_tags( apply_filters( 'mbdb_book_meta_description', $series_info . $genre_info . '.' . $retailer_info, $this->data_object ) ) ) . ' ' . esc_attr( strip_tags( $summary ) ) . '">';
				}

				if ( $this->data_object->has_cover() ) {
					$image = wp_get_attachment_metadata( $this->data_object->cover_id );
					if ( ! MBDB_WPSEO_INSTALLED || MBDB()->options->override_wpseo( 'og' ) ) {
						if ( is_array( $image ) && array_key_exists( 'height', $image ) && array_key_exists( 'width', $image ) ) {

							echo '<meta property="og:image"              content="' . esc_attr( strip_tags( apply_filters( 'mbdb_book_meta_og_image', $this->data_object->cover, $this->data_object ) ) ) . '" />';
							echo '<meta property="og:image:width"			content="' . esc_attr( strip_tags( apply_filters( 'mbdb_book_meta_og_image_width', $image['width'], $this->data_object ) ) ) . '" />';
							echo '<meta property="og:image:height"			content="' . esc_attr( strip_tags( apply_filters( 'mbdb_book_meta_og_image_height', $image['height'], $this->data_object ) ) ) . '" />';
						}
					}
					if ( ! MBDB_WPSEO_INSTALLED || MBDB()->options->override_wpseo( 'twitter' ) ) {
						echo '<meta name="twitter:image" content="' . esc_attr( strip_tags( $this->data_object->cover ) ) . '" />';
						$alt = MBDB()->helper_functions->get_alt_attr( $this->data_object->cover_id, __( 'Book Cover:', 'mooberry-book-manager' ) . ' ' . $this->data_object->title );
						echo '<meta name="twitter:image:alt" content="' . esc_attr( strip_tags( $alt ) ) . '">';
					}
				}
				if ( $this->data_object->has_editions() ) {
					foreach ( $this->data_object->editions as $edition ) {
						if ( $edition->isbn != '' ) {
							echo '<meta property="og:type" content="books.book" />';
							echo '<meta property="books:isbn" content="' . esc_attr( strip_tags( $edition->isbn ) ) . '" />';
							break;
						}
					}
				}
				if ( ! MBDB_WPSEO_INSTALLED || MBDB()->options->override_wpseo( 'og' ) ) {
					?>

                    <meta property="og:url"
                          content="<?php echo esc_attr( strip_tags( $this->data_object->permalink ) ); ?> "/>
                    <meta property="og:title"
                          content="<?php echo esc_attr( strip_tags( $this->data_object->title ) ); ?>"/>
                    <meta property="og:description" content="<?php echo esc_attr( strip_tags( $summary ) ); ?>"/>
					<?php
				}
				if ( ! MBDB_WPSEO_INSTALLED || MBDB()->options->override_wpseo( 'twitter' ) ) {
					?>
                    <!-- twitter cards -->
                    <meta name="twitter:card" content="summary">
                    <meta name="twitter:title"
                          content="<?php echo esc_attr( strip_tags( $this->data_object->title ) ); ?>">
                    <meta name="twitter:description"
                          content="<?php echo esc_attr( strip_tags( $this->data_object->summary ) ); ?>">
					<?php
				}
			}
		}
	}

	function allow_comments( $comments ) {
		//print_r(MBDB()->options->comments_on_books);

		if ( MBDB()->options->comments_on_books == '' || ! MBDB()->options->comments_on_books ) {
			remove_post_type_support( $this->post_type, 'comments' );
		} else {
			add_post_type_support( $this->post_type, 'comments' );
		}
	}

	public function duplicate_book( $new_post_id, $post ) {
		$book          = MBDB()->book_factory->create_book( $post->ID );
		$book->id      = $new_post_id;
		$book->book_id = $new_post_id;
		$book->save_all();


	}


	/************************************************************
	 *
	 *  DISPLAY
	 *  SHORTCODES
	 *
	 ************************************************************/

	protected function set_book( $slug = '' ) {

		if ( $slug == '' ) {
			global $post;
			if ( $post == null ) {
				$book_id = 0;
			} else {
				$book_id = $post->ID;
			}
			if ( ! isset( $this->data_object ) || $this->data_object->id != $book_id ) {
				$this->set_data_object( $book_id );
			}
		} else {

			if ( ! isset( $this->data_object ) || $this->data_object->slug != $slug ) {
				$object = MBDB()->books_db->get_by_slug( $slug );
				if ( $object ) {
					$slug = $object->ID;
				} else {
					$slug = 0;
				}
				$this->set_data_object( $slug );
			}
		}
	}


	protected function output_blank_data( $classname, $blank_output, $book = null ) {
		return apply_filters( 'mbdb_shortcode_' . $classname, '<span class="mbm-book-' . $classname . '"><span class="mbm-book-' . $classname . '-blank">' . esc_html( $blank_output ) . '</span></span>', $book );
	}

	public function shortcode_title( $attr, $content ) {
		$attr = shortcode_atts( array(
			'book'  => '',
			'blank' => '',
		), $attr );

		if ( $attr['book'] == '' ) {
			global $post;
			if ( $post == null ) {
				return '';
			} else {
				return $post->post_title;
			}
		} else {
			$this->set_book( $attr['book'] );

			return $this->data_object->title;
		}
	}

	public function shortcode_cover( $attr, $content ) {

		$attr = shortcode_atts( array(
			'width' => '',
			'align' => 'right',
			'wrap'  => 'yes',
			'book'  => '',
		), $attr );

		$this->set_book( $attr['book'] );

		$url = $this->data_object->get_cover_url( 'large', 'page' );
		$alt = MBDB()->helper_functions->get_alt_attr( $this->data_object->cover_id, __( 'Book Cover:', 'mooberry-book-manager' ) . ' ' . $this->data_object->title );

		if ( isset( $url ) && $url != '' ) {
			$image_html = '<img id="mbdb_book_cover" src="' . esc_url( $url ) . '" ' . $alt . ' itemprop="image" />';

			$image_html = MBDB()->helper_functions->maybe_add_ribbon( $image_html, $this->data_object, 'page');



			return apply_filters( 'mbdb_shortcode_cover', '<span class="mbm-book-cover">' . $image_html . '</span>' );
		} else {
			return $this->output_blank_data( 'cover', '' );
		}
	}

	public function shortcode_subtitle( $attr, $content ) {
		$attr = shortcode_atts( array(
			'book'  => '',
			'blank' => '',
		), $attr );

		$this->set_book( $attr['book'] );

		if ( $this->data_object->subtitle == '' ) {
			return $this->output_blank_data( 'subtitle', $attr['blank'] );
		}

		return apply_filters( 'mbdb_shortcode_subtitle', '<span class="mbm-book-subtitle"><span class="mbm-book-subtitle-text">' . esc_html( $this->data_object->subtitle ) . '</span></span>' );

	}

	public function shortcode_summary( $attr, $content ) {
		$attr = shortcode_atts( array(
			'label' => '',
			'after' => '',
			'blank' => '',
			'book'  => '',
		), $attr );

		$this->set_book( $attr['book'] );

		if ( $this->data_object->summary == '' ) {
			return $this->output_blank_data( 'summary', $attr['blank'], $this->data_object );
		}
		//error_log('summary');
		$output = '<div class="mbm-book-summary"><span class="mbm-book-summary-label">' . esc_html( $attr['label'] ) . '</span><span class="mbm-book-summary-text">';
		$output .= $this->get_wysiwyg_output( $this->data_object->summary );

		$output .= '</span><span class="mbm-book-summary-after">' . esc_html( $attr['after'] ) . '</span></div>';

		return apply_filters( 'mbdb_shortcode_summary', $output, $this->data_object );

	}

	public function shortcode_publisher( $attr, $content ) {
		$attr = shortcode_atts( array(
			'label' => '',
			'after' => '',
			'blank' => '',
			'book'  => '',
		), $attr );

		$this->set_book( $attr['book'] );

		if ( ! $this->data_object->has_publisher() ) {
			return $this->output_blank_data( 'publisher', $attr['blank'] );
		}

		$mbdb_publisher        = $this->data_object->publisher->name;

			$text = '<A class="mbm-book-publisher-link" HREF="' . get_permalink($this->data_object->publisher_id ). '" ><span class="mbm-book-publisher-text">' . esc_html( $mbdb_publisher ) . '</span></a>';


		return apply_filters( 'mbdb_shortcode_publisher', '<span class="mbm-book-publisher"><span class="mbm-book-publisher-label">' . esc_html( $attr['label'] ) . '</span>' . $text . '<span class="mbm-book-publisher-after">' . esc_html( $attr['after'] ) . '</span></span>' );
	}

	public function shortcode_imprint( $attr, $content ) {
		$attr = shortcode_atts( array(
			'label' => '',
			'after' => '',
			'blank' => '',
			'book'  => '',
		), $attr );

		$this->set_book( $attr['book'] );

		if ( ! $this->data_object->has_imprint() ) {
			return $this->output_blank_data( 'imprint', $attr['blank'] );
		}

		$mbdb_imprint        = $this->data_object->imprint->name;
		$mbdb_imprintwebsite = $this->data_object->imprint->website;

		if ( $mbdb_imprintwebsite == '' ) {
			$text = '<span class="mbm-book-imprint-text">' . esc_html( $mbdb_imprint ) . '</span>';
		} else {
			$text = '<A class="mbm-book-imprint-link" HREF="' . esc_url( $mbdb_imprintwebsite ) . '" target="_new"><span class="mbm-book-imprint-text">' . esc_html( $mbdb_imprint ) . '</span></a>';
		}

		return apply_filters( 'mbdb_shortcode_imprint', '<span class="mbm-book-imprint"><span class="mbm-book-imprint-label">' . esc_html( $attr['label'] ) . '</span>' . $text . '<span class="mbm-book-imprint-after">' . esc_html( $attr['after'] ) . '</span></span>' );
	}

	public function shortcode_published( $attr, $content ) {
		$attr = shortcode_atts( array(
			'format'            => 'short',
			'published_label'   => __( 'Published:', 'mooberry-book-manager' ),
			'unpublished_label' => __( 'Available on:', 'mooberry-book-manager' ),
			'after'             => '',
			'blank'             => '',
			'book'              => '',
		), $attr );
		$this->set_book( $attr['book'] );

		if ( ! $this->data_object->has_published_date() ) {
			return $this->output_blank_data( 'published', $attr['blank'] );
		}
		//error_log('published');
		switch ( $attr['format'] ) {
			case 'short':
				/* translators: short date format. see http://php.net/date */
				$format = _x( 'm/d/Y', 'short date format. see http://php.net/date', 'mooberry-book-manager' );
				break;
			case 'long':
				/* translators: long date format. see http://php.net/date */
				$format = _x( 'F j, Y', 'long date format. see http://php.net/date', 'mooberry-book-manager' );
				break;
			case 'default':
				$format = get_option( 'date_format' );
				break;
		}
		if ( $this->data_object->is_published() ) {
			$label = $attr['published_label'];
		} else {
			$label = $attr['unpublished_label'];
		}

		return apply_filters( 'mbdb_shortcode_published', '<span class="mbm-book-published"><span class="mbm-book-details-published-label"><span class="mbm-book-published-label">' . esc_html( $label ) . '</span></span> <span class="mbm-book-published-text" itemprop="datePublished" content="' . esc_attr( $this->data_object->release_date ) . '">' . date_i18n( $format, strtotime( $this->data_object->release_date ) ) . '</span><span class="mbm-book-published-after">' . esc_html( $attr['after'] ) . '</span></span>' );

	}

	public function shortcode_goodreads( $attr, $content ) {
		$attr = shortcode_atts( array(
			'text'  => __( 'View on Goodreads', 'mooberry-book-manager' ),
			'label' => '',
			'after' => '',
			'blank' => '',
			'book'  => '',
		), $attr );
		//error_log('goodreads');
		$this->set_book( $attr['book'] );
		$goodreads_link = $this->data_object->goodreads;
		if ( $goodreads_link == '' ) {
			return $this->output_blank_data( 'goodreads', $attr['blank'] );
		}
		$goodreads_image = MBDB()->options->goodreads_image;

		if ( $goodreads_image == '' ) {
			return apply_filters( 'mbdb_shortcode_goodreads', '<div class="mbm-book-goodreads"><span class="mbm-book-goodreads-label">' . esc_html( $attr['label'] ) . '</span><A class="mbm-book-goodreads-link" HREF="' . esc_url( $goodreads_link ) . '" target="_new"><span class="mbm-book-goodreads-text">' . $attr['text'] . '</span></A><span class="mbm-book-goodreads-after">' . esc_html( $attr['after'] ) . '</span></div>' );
		} else {
			$alt = __( 'Add to Goodreads', 'mooberry-book-manager' );
			$url = esc_url( $goodreads_image );
			if ( is_ssl() ) {
				$url = preg_replace( '/^http:/', 'https:', $url );
			}

			return apply_filters( 'mbdb_shortcode_goodreads', '<div class="mbm-book-goodreads"><span class="mbm-book-goodreads-label">' . esc_html( $attr['label'] ) . '</span><A class="mbm-book-goodreads-link" HREF="' . esc_url( $goodreads_link ) . '" target="_new"><img class="mbm-book-goodreads-image" src="' . $url . '"' . $alt . '/></A><span class="mbm-book-goodreads-after">' . esc_html( $attr['after'] ) . '</span></div>' );
		}
	}

	public function shortcode_reedsy( $attr, $content ) {
		$attr = shortcode_atts( array(
			'text'  => __( 'View on Reedsy', 'mooberry-book-manager' ),
			'label' => '',
			'after' => '',
			'blank' => '',
			'book'  => '',
		), $attr );
		//error_log('reedsy');
		$this->set_book( $attr['book'] );
		$reedsy_link = $this->data_object->reedsy;
		if ( $reedsy_link == '' ) {
			return $this->output_blank_data( 'reedsy', $attr['blank'] );
		}
		$reedsy_image = MBDB()->options->reedsy_image;

		if ( $reedsy_image == '' ) {
			return apply_filters( 'mbdb_shortcode_reedsy', '<div class="mbm-book-reedsy"><span class="mbm-book-reedsy-label">' . esc_html( $attr['label'] ) . '</span><A class="mbm-book-reedsy-link" HREF="' . esc_url( $reedsy_link ) . '" target="_new"><span class="mbm-book-reedsy-text">' . $attr['text'] . '</span></A><span class="mbm-book-reedsy-after">' . esc_html( $attr['after'] ) . '</span></div>' );
		} else {
			$alt = __( 'Add to Reedsy', 'mooberry-book-manager' );
			$url = esc_url( $reedsy_image );
			if ( is_ssl() ) {
				$url = preg_replace( '/^http:/', 'https:', $url );
			}

			return apply_filters( 'mbdb_shortcode_reedsy', '<div class="mbm-book-reedsy"><span class="mbm-book-reedsy-label">' . esc_html( $attr['label'] ) . '</span><A class="mbm-book-reedsy-link" HREF="' . esc_url( $reedsy_link ) . '" target="_new"><img class="mbm-book-reedsy-image" src="' . $url . '"' . $alt . '/></A><span class="mbm-book-reedsy-after">' . esc_html( $attr['after'] ) . '</span></div>' );
		}
	}


	public function shortcode_google_books( $attr, $content ) {
		$attr = shortcode_atts( array(
			'text'  => __( 'View on Google Books', 'mooberry-book-manager' ),
			'label' => '',
			'after' => '',
			'blank' => '',
			'book'  => '',
		), $attr );
		//error_log('google_books');
		$this->set_book( $attr['book'] );
		$google_books_link = $this->data_object->google_books;
		if ( $google_books_link == '' ) {
			return $this->output_blank_data( 'google_books', $attr['blank'] );
		}
		$google_books_image = MBDB()->options->google_books_image;

		if ( $google_books_image == '' ) {
			return apply_filters( 'mbdb_shortcode_google_books', '<div class="mbm-book-google-books"><span class="mbm-book-google-books-label">' . esc_html( $attr['label'] ) . '</span><A class="mbm-book-google-books-link" HREF="' . esc_url( $google_books_link ) . '" target="_new"><span class="mbm-book-google-books-text">' . $attr['text'] . '</span></A><span class="mbm-book-google-books-after">' . esc_html( $attr['after'] ) . '</span></div>' );
		} else {
			$alt = __( 'Add to Google Books', 'mooberry-book-manager' );
			$url = esc_url( $google_books_image );
			if ( is_ssl() ) {
				$url = preg_replace( '/^http:/', 'https:', $url );
			}

			return apply_filters( 'mbdb_shortcode_google_books', '<div class="mbm-book-google_books"><span class="mbm-book-google-books-label">' . esc_html( $attr['label'] ) . '</span><A class="mbm-book-google-books-link" HREF="' . esc_url( $google_books_link ) . '" target="_new"><img class="mbm-book-google-books-image" src="' . $url . '"' . $alt . '/></A><span class="mbm-book-google-books-after">' . esc_html( $attr['after'] ) . '</span></div>' );
		}
	}


	public function shortcode_excerpt( $attr, $content ) {
		$attr = shortcode_atts( array(
			'label'  => '',
			'after'  => '',
			'blank'  => '',
			'length' => '0',
			'book'   => '',
		), $attr );

		$this->set_book( $attr['book'] );


		if ( $this->data_object->has_kindle_preview() ) {

			$affiliate_codes = array();
			$aff_arg         = '';
			$retailers       = MBDB()->options->retailers;
			if ( is_array( $retailers ) ) {
				foreach ( $retailers as $retailer ) {
					if ( $retailer->is_amazon && $retailer->has_affiliate_code ) {
						$affiliate_codes[] = trim( $retailer->affiliate_code );
					}
				}
				if ( count( $affiliate_codes ) != 0 ) {
					$affiliate_codes = array_unique( $affiliate_codes );
					$aff_arg = $affiliate_codes[0];
					// if affiliate code starts with ? and URL already contains ?, use & to make it an additional URL argument.
					if ( substr( $aff_arg, 0, 1 ) == '?' ) {
						$aff_arg = '&' . substr( $aff_arg, 1 );
					}

					$aff_arg = ' affiliate="' . $aff_arg . '" ';
				}
			}
			return apply_filters( 'mbdb_shortcode_excerpt_kindle_preview', do_shortcode( '[book_kindle_preview asin="' . $this->data_object->kindle_preview . '" ' . $aff_arg . ' ]' ) );
		}


		$excerpt = $this->data_object->excerpt;

		if ( $excerpt == '' ) {
			return $this->output_blank_data( 'excerpt', $attr['blank'] );
		}
		$excerpt  = wpautop( $excerpt );
		$excerpt1 = '';
		$excerpt2 = '';
		if ( $attr['length'] == 0 ) {
			$excerpt1 = $excerpt;
			$excerpt2 = '';
		} else {
			if ( preg_match( '/^(.{1,' . $attr['length'] . '}<\/p>)(.*)/s', $excerpt, $match ) ) {
				$excerpt1 = $match[1];
				$excerpt2 = $match[2];
			} else {
				// if we're here there's probably no paragraph tags for whatever reason
				// so take teh first 1000 characters ending on a sentence
				if ( preg_match( '/^(.{1,1000}[.?!"”])(.*)/s', $excerpt, $match ) ) {
					$excerpt1 = $match[1];
					$excerpt2 = $match[2];
				} else {
					// just grab the first 1000 characters no matter where that ends up
					if ( strlen( $excerpt ) > 1000 ) {
						$excerpt1 = substr( $excerpt, 0, 999 );
						$excerpt2 = substr( $excerpt, 1000 );
					} else {
						$excerpt1 = $excerpt;
						$excerpt2 = '';
					}
				}
			}
		}
		$html_output = '<div class="mbm-book-excerpt">
		<span class="mbm-book-excerpt-label">' . esc_html( $attr['label'] ) . '</span>
		<span class="mbm-book-excerpt-text">';
		$html_output .= $this->get_wysiwyg_output( $excerpt1 );

		if ( trim( $excerpt2 ) != '' ) {
			$html_output .= '<a name="more" class="mbm-book-excerpt-read-more">' . __( 'READ MORE', 'mooberry-book-manager' ) . '</a>
			<span class="mbm-book-excerpt-text-hidden">';
			$html_output .= $this->get_wysiwyg_output( $excerpt2 );
			$html_output .= '<a class="mbm-book-excerpt-collapse" name="collapse">' . __( 'COLLAPSE', 'mooberry-book-manager' ) . '</a></span>';
		}

		$html_output .= ' </span><span class="mbm-book-excerpt-after">' . esc_html( $attr['after'] ) . '</span></div>';

		return apply_filters( 'mbdb_shortcode_excerpt', $html_output );

	}

	public function shortcode_kindle_preview( $attr, $content ) {
		$attr = shortcode_atts( array(
			'asin'      => '',
			'affiliate' => '',
		), $attr );



		return '<div class="mbm-book-excerpt"><span class="mbm-book-excerpt-label">Excerpt:</span><iframe type="text/html" width="100%" height="650" frameborder="0" allowfullscreen style="max-width:100%" src="https://read.amazon.com/kp/card?asin=' . esc_attr( $attr['asin'] ) . '&preview=inline&linkCode=kpe' . esc_attr( $attr['affiliate'] ) . '" ></iframe></div>';

	}

	public function shortcode_additional_info( $attr, $content ) {
		$attr = shortcode_atts( array(
			'blank' => '',
			'book'  => '',
		), $attr );

		$this->set_book( $attr['book'] );

		$additional_info = $this->data_object->additional_info;
		if ( $additional_info == '' ) {
			return $this->output_blank_data( 'additional_info', $attr['blank'] );
		}
		$html_output = '<div class="mbm-book-additional-info">';
		$html_output .= $this->get_wysiwyg_output( $additional_info );
		$html_output .= '</div>';

		return apply_filters( 'mbdb_shortcode_additional_info', $html_output );

	}

	protected function get_tax_grid_link($term, $taxonomy ) {
		if ( is_wp_error($term)) {
			return '';
		}
			// check if using permalinks
			if ( get_option( 'permalink_structure' ) != '' ) {
				$permalink = MBDB()->options->get_tax_grid_slug( $taxonomy );
				$link = home_url( $permalink . '/' . $term->slug );
			} else {
				$tax_grid_page = MBDB()->options->get_tax_grid_page();
				$permalink = get_permalink( $tax_grid_page );
				$link =  $permalink . '&the-taxonomy=' . $taxonomy . '&the-term=' . $term->slug . '&post_type=mbdb_tax_grid';
			}
			return $link;
	}

	// public needed for other screens to access
	public function output_taxonomy( $classname, $mbdb_terms, $permalink, $taxonomy, $attr ) {
		//error_log('taxonomy ' . $taxonomy);
		if ( $attr['delim'] == 'comma' ) {
			$delim  = ', ';
			$after  = '';
			$before = '';
			$begin  = '';
			$end    = '';
		} else {
			$delim  = '</li><li class="' . $classname . '-listitem">';
			$before = '<li class="' . $classname . '-listitem">';
			$after  = '</li>';
			$begin  = '<ul class="' . $classname . '-list">';
			$end    = '</ul>';
		}

		$list = '';
		$list .= $before;
		foreach ( $mbdb_terms as $term ) {

			$list .= '<a class="' . $classname . '-link" href="';

			$list .= $this->get_tax_grid_link( $term, $taxonomy );

			$itemprop = '';
			if ( $taxonomy == 'mbdb_genre' ) {
				$itemprop = ' itemprop="genre" ';
			}
			$list .= '"><span class="' . $classname . '-text" ' . $itemprop . '>' . $term->name . '</span></a>';
			if ( function_exists( 'get_term_meta' ) ) {
				//if ( in_array( $term->taxonomy, mbdb_taxonomies_with_websites() ) ) {
				$website = get_term_meta( $term->term_id, 'mbdb_website', true );
				if ( $website != '' ) {
					$list .= ' (<a class="' . $classname . '-website" href="' . $website . '" target="_new">' . __( 'Website', 'mooberry-book-manager' ) . '</a>)';
				}
				//}
			}

			$list .= $delim;
		}

		// there's an extra $delim added to the string
		if ( $attr['delim'] == 'list' ) {
			// trim off the last </li> by cutting the entire $delim off and then adding in the </li> back in
			$list = substr( $list, 0, strripos( $list, $delim ) ) . '</li>';
		} else {
			// trim off the last space and comma
			$list = substr( $list, 0, - 2 );
		}

		return apply_filters( 'mbdb_shortcode_' . $permalink . '_taxonomy', '<div class="' . $classname . '" style="display:inline;">' . $begin . $list . $end . '</div>' );

	}


	protected function shortcode_taxonomy( $attr, $taxonomy, $default_permalink, $property ) {
		$attr = shortcode_atts( array(
			'delim' => 'comma',
			'blank' => '',
			'book'  => '',
		), $attr );

		$permalink = MBDB()->options->get_tax_grid_slug( $taxonomy );

		$this->set_book( $attr['book'] );

		if ( property_exists( $this->data_object, $property ) && method_exists( $this->data_object, 'has_' . $property ) ) {
			if ( call_user_func( array( $this->data_object, 'has_' . $property ) ) ) {
				return $this->output_taxonomy( 'mbm-book-' . $default_permalink, $this->data_object->$property, $permalink, $taxonomy, $attr );
			}
		}

		return $this->output_blank_data( $permalink . '_taxonomy', $attr['blank'] );
	}

	public function shortcode_genre( $attr, $content ) {
		return $this->shortcode_taxonomy( $attr, 'mbdb_genre', 'genre', 'genres' );
	}

	public function shortcode_reviews( $attr, $content ) {
		$attr = shortcode_atts( array(
			'label' => '',
			'after' => '',
			'blank' => '',
			'book'  => '',
		), $attr );

		$this->set_book( $attr['book'] );
		$book = $this->data_object;
		if ( ! $book->has_reviews() ) {
			return $this->output_blank_data( 'reviews', $attr['blank'] );
		}
		$review_html = '';
		foreach ( $book->reviews as $review ) {
			$reviewer_name  = $review->reviewer_name;
			$review_url     = $review->url;
			$review_website = $review->website_name;
			$review_html    .= '<span class="mbm-book-reviews-block"><span class="mbm-book-reviews-header">';
			if ( $reviewer_name != '' ) {
				$review_html .= '<span class="mbm-book-reviews-reviewer-name">' . esc_html( $reviewer_name ) . '</span> ';
			}
			if ( $review_url != '' || $review_website != '' ) {
				$review_html .= __( 'on ', 'mooberry-book-manager' );
			}
			if ( $review_url != '' ) {
				$review_html .= '<A class="mbm-book-reviews-link" HREF="' . esc_url( $review_url ) . '" target="_new"><span class="mbm-book-reviews-website">';
				if ( $review_website == '' ) {
					$review_html .= esc_html( $review_url );
				} else {
					$review_html .= esc_html( $review_website );
				}
				$review_html .= '</span></A>';
			} else {
				if ( $review_website != '' ) {
					$review_html .= '<span class="mbm-book-reviews-website">' . esc_html( $review_website ) . '</span>';
				}
			}
			if ( $reviewer_name != '' ) {
				$review_html .= ' ' . __( 'wrote', 'mooberry-book-manager' );
			}
			$review_html .= ':</span>';
			$review_html .= ' <blockquote class="mbm-book-reviews-text">' . wpautop( wp_kses_post( $review->review ) ) . '</blockquote></span>';
		}

		return apply_filters( 'mbdb_shortcode_reviews', '<div class="mbm-book-reviews"><span class="mbm-book-reviews-label">' . esc_html( $attr['label'] ) . '</span>' . $review_html . '<span class="mbm-book-reviews-after">' . esc_html( $attr['after'] ) . '</span></div>' );

	}

	public function output_buylinks( $buylinks, $attr, $book_id = 0 ) {
		$classname = 'mbm-book-buy-links';
//error_log('output_buylinks');
		$buy_links_html = '';
		$img_size       = '';
		if ( $attr['align'] == 'vertical' ) {
			if ( $attr['size'] ) {
				$attr['width'] = $attr['size'];
			}
		} else {
			if ( $attr['size'] ) {
				$attr['height'] = $attr['size'];
			}
		}
		if ( $attr['width'] ) {
			$img_size = "width:" . esc_attr( $attr['width'] );
		}
		if ( $attr['height'] ) {
			$img_size = "height:" . esc_attr( $attr['height'] );
		}

		foreach ( $buylinks as $mbdb_buylink ) {
			//error_log('next link');
			$retailer = $mbdb_buylink->retailer;

			// 3.5 this filter for backwards compatibility
			$mbdb_buylink = apply_filters( 'mbdb_buy_links_output', $mbdb_buylink, $mbdb_buylink->retailer, $book_id );
			// 3.5 add affiliate codes
			$mbdb_buylink = apply_filters( 'mbdb_buy_links_pre_affiliate_code', $mbdb_buylink, $mbdb_buylink->retailer, $book_id );
			// backwards compatibility with multi-author?!?!
			// will have to convert to arrays
			//error_log('make array');
			$retailer_array     = MBDB()->helper_functions->object_to_array( $mbdb_buylink->retailer );
			$mbdb_buylink_array = array();

			$mbdb_buylink_array['_mbdb_retailerID'] = $retailer->id;


			// this filter strictly for backwards compatibility with MA ??
			$retailer_array = apply_filters( 'mbdb_buy_links_retailer_pre_affiliate_code', $retailer_array, $mbdb_buylink_array, $book_id );
			// convert array back to an object
			//error_log('back to object');
			$retailer = MBDB()->helper_functions->array_to_object( $retailer_array, $retailer );
			$link     = $mbdb_buylink->link;
			// Does the retailer have an affiliate code?
			if ( $retailer->has_affiliate_code() ) {
				//array_key_exists('affiliate_code', $retailer) && $retailer['affiliate_code'] != '') {
				//error_log('has affilitate code');

				// append or prepend the code
				if ( $retailer->affiliate_position == 'before' ) {
					$link = $retailer->affiliate_code . $link;
				} else {
					$aff_arg = $retailer->affiliate_code;
					// if affiliate code starts with ? and URL already contains ?, use & to make it an additional URL argument.
					if ( substr( $aff_arg, 0, 1 ) == '?' && strpos( $link, '?' ) !== false ) {
						$aff_arg = '&' . substr( $aff_arg, 1 );
					}
					$link .= $aff_arg;

				}
			}
			$mbdb_buylink = apply_filters( 'mbdb_buy_links_post_affiliate_code', $mbdb_buylink, $retailer, $book_id, $link );


			// 3.5.6
			// 4.12 - only show this text if doing images
			if ( $retailer->id == '13' && $retailer->uses_logo() ) {
				$buy_links_html .= '<span class="amazon_available_on_text">' . __( 'Available on', 'mooberry-book-manager' ) . ' <br/></span>';
			}


			$link = '<A HREF="' . esc_url( $link ) . '" TARGET="_new" ';


			// 4.12 add in buttons
			if ( $retailer->uses_logo() && $retailer->has_logo_image() ) {
					$alt            = __( 'Buy Now:', 'mooberry-book-manager' ) . ' ' . $retailer->name;
					$buy_links_html .=  $link . ' class="' . $classname . '-link"> <img class="' . $classname . '-image" style="' . esc_attr( $img_size ) . '" src="' . esc_url( $retailer->logo ) . '" alt="' . $alt . '" /></a>';
				} else {
				if ( $retailer->uses_button() ) {
					$buy_links_html .= $link . ' class="' . $classname .'-button mbdb_retailer_button" style="background-color:' . esc_attr( $retailer->get_button_color()) . ';color:' . esc_attr( $retailer->get_button_color_text()) . ';">' .  esc_html($retailer->name) . '</a>';

				} else {
					// if all else fails just put a link
					$buy_links_html .= '<span class="' . $classname . '-text">' . $link . ' class="' . $classname . '-link">' . esc_html( $retailer->name ) . '</a></span>';
				}
			}



		}
		return apply_filters( 'mbdb_shortcode_buylinks', '<div class="' . $classname . '"><span class="' . $classname . '-label">' . esc_html( $attr['label'] ) . '</span>' . $buy_links_html . '<span class="' . $classname . '-after">' . esc_html( $attr['after'] ) . '</span></div>' );
	}

	public function shortcode_buylinks( $attr, $content ) {
		$attr = shortcode_atts( array(
			'width'  => '',
			'height' => '',
			'size'   => '',
			'align'  => 'vertical',
			'label'  => '',
			'after'  => '',
			'blank'  => '',
			'book'   => '',
		), $attr );

		$this->set_book( $attr['book'] );
		$book = $this->data_object;
		if ( ! $book->has_buy_links() ) {
			return $this->output_blank_data( 'buy-links', $attr['blank'] );
		}

		return $this->output_buylinks( $book->buy_links, $attr, $book->id );

	}

	public function output_downloadlinks( $downloadlinks, $attr ) {
		$classname = 'mbm-book-download-links';

		$download_links_html = '<UL class="' . $classname . '-list" style="list-style-type:none;">';
		if ( $attr['align'] == 'vertical' ) {
			$li_style = "margin: 1em 0 1em 0;";
		} else {
			$li_style = "display:inline;margin: 0 3% 0 0;";
		}

		foreach ( $downloadlinks as $mbdb_downloadlink ) {

			$format = $mbdb_downloadlink->download_format;

			$download_links_html .= '<li class="' . $classname . '-listitem" style="' . $li_style . '">';


			// 3.5.6
			if ( $mbdb_downloadlink->uniqueID == '2' ) {
				$download_links_html .= '<span style="float:right;">' . __( 'Available on', 'mooberry-book-manager' ) . '<br/> ';
			}

			$download_links_html .= '<A class="' . $classname . '-link" HREF="' . esc_url( $mbdb_downloadlink->link ) . '">';


			if ( $format->has_logo_image() ) {
				$alt = __( 'Download Now:', 'mooberry-book-manager' ) . ' ' . $format->name;

				$download_links_html .= '<img class="' . $classname . '-image" src="' . esc_url( $format->logo ) . '"' . $alt . '/>';
			} else {
				$download_links_html .= '<p class="' . $classname . '-text">' . esc_html( $format->name ) . '</p>';
			}
			$download_links_html .= '</a></li>';

		}

		$download_links_html .= "</ul>";

		return apply_filters( 'mbdb_shortcode_downloadlinks', '<div class="' . $classname . '"><span class="' . $classname . '-label">' . esc_html( $attr['label'] ) . '</span>' . $download_links_html . '<span class="' . $classname . '-after">' . esc_html( $attr['after'] ) . '</span></div>' );
	}

	public function shortcode_downloadlinks( $attr, $content ) {
		$attr = shortcode_atts( array(
			'align' => 'vertical',
			'label' => '',
			'after' => '',
			'blank' => '',
			'book'  => '',
		), $attr );

		$this->set_book( $attr['book'] );
		$book = $this->data_object;

		if ( ! $book->has_download_links() ) {
			return $this->output_blank_data( 'download-links', $attr['blank'] );
		}

		return $this->output_downloadlinks( $book->download_links, $attr );
	}

	/**
	 *  Outputs list of books in series with links to individual books
	 *  except for the current book
	 *
	 *
	 * @param  [string] $delim  list or comma
	 * @param  [string] $series series to print out (term)
	 * @param  [int] $bookID current bookid
	 *
	 * @return html output
	 *
	 * @access public
	 * @since  1.0
	 *
	 */
	protected function series_list( $delim, $series, $bookID ) {
		$classname = 'mbm-book-serieslist';


		//$books = MBDB()->books->get_books_by_taxonomy( null, 'series', $series, 'series_order', 'ASC');
		//error_log('before book list');
		$books = new MBDB_Book_List( MBDB_Book_List_Enum::all, 'series_order', 'ASC', null, array( 'series' => $series ) );
		//error_log('after book list');
		//$books = $list->get_books_by_taxonomy( null, 'mbdb_series', $series, 'series_order', 'ASC');

		if ( $delim == 'list' ) {
			$list = '<ul class="' . $classname . '-list">';
		} else {
			$list = '';
		}
		foreach ( $books as $book ) {
			if ( $delim == 'list' ) {
				$list .= '<li class="' . $classname . '-listitem">';
			}
			if ( $book->id != $bookID ) {
				$list .= '<A class="' . $classname . '-listitem-link" HREF="' . get_permalink( $book->id ) . '">';
			}
			$list .= '<span class="' . $classname . '-listitem-text">' . esc_html( $book->title ) . '</span>';
			if ( $book->id != $bookID ) {
				$list .= '</a>';
			}
			if ( $delim == 'list' ) {
				$list .= '</li>';
			} else {
				$list .= ', ';
			}
		}
		if ( $delim == 'list' ) {
			$list .= '</ul>';
		} else {
			// trim off the last space and comma
			$list = substr( $list, 0, - 2 );
		}

		return $list;
	}


	public function shortcode_serieslist( $attr, $content ) {
		$attr = shortcode_atts( array(
			'blank'  => '',
			'before' => __( 'Part of the ', 'mooberry-book-manager' ),
			'after'  => __( ' series:', 'mooberry-book-manager' ),
			'delim'  => 'list',
			'book'   => '',
		), $attr );
		//error_log('start series list');
		$this->set_book( $attr['book'] );

		if ( ! $this->data_object->has_series() ) {
			return $this->output_blank_data( 'serieslist', $attr['blank'] );
		}

		$bookID      = $this->data_object->id;
		$classname   = 'mbm-book-serieslist';
		$series_name = '';

		foreach ( $this->data_object->series as $series ) {
			//error_log('next series');
			$series_name .= '<div class="' . $classname . '-seriesblock"><span class="' . $classname . '-before">' . esc_html( $attr['before'] ) . '</span>';
			$series_name .= '<a class="' . $classname . '-link" href="';

			if ( get_option( 'permalink_structure' ) != '' ) {
				// v3.0 get permalink from options
				$permalink = MBDB()->options->tax_grid_slugs['mbdb_series']; //mbdb_get_tax_grid_slug( 'mbdb_series' );
				/* $mbdb_options['mbdb_book_grid_mbdb_series_slug'];
				if ($permalink == '') {
					$permalink = 'series';
				}
				*/

				$series_name .= home_url( $permalink . '/' . $series->slug );
			} else {
				$series_name .= home_url( '?the-taxonomy=mbdb_series&the-term=' . $series->slug . '&post_type=mbdb_tax_grid' );
			}
			$series_name .= '"><span class="' . $classname . '-text">' . $series->name . '</span></a>';
			$series_name .= '<span class="' . $classname . '-after">' . esc_html( $attr['after'] ) . '</span>';
			$series_name .= $this->series_list( $attr['delim'], $series->term_id, $bookID );
			$series_name .= '</div>';
		}

		//error_log('end series list');
		return apply_filters( 'mbdb_shortcode_serieslist', '<div class="' . $classname . '">' . $series_name . '</div>' );
	}

	public function shortcode_series( $attr, $content ) {
		return $this->shortcode_taxonomy( $attr, 'mbdb_series', 'series', 'series' );
	}

	public function shortcode_tags( $attr, $content ) {
		return $this->shortcode_taxonomy( $attr, 'mbdb_tag', 'book-tag', 'tags' );
	}

	public function shortcode_illustrator( $attr, $content ) {
		return $this->shortcode_taxonomy( $attr, 'mbdb_illustrator', 'illustrator', 'illustrators' );
	}

	public function shortcode_editor( $attr, $content ) {
		return $this->shortcode_taxonomy( $attr, 'mbdb_editor', 'editor', 'editors' );
	}

	public function shortcode_cover_artist( $attr, $content ) {
		return $this->shortcode_taxonomy( $attr, 'mbdb_cover_artist', 'cover-artist', 'cover_artists' );
	}

	public function shortcode_narrator( $attr, $content ) {
		return $this->shortcode_taxonomy( $attr, 'mbdb_narrator', 'cover-artist', 'narrators' );
	}

	public function shortcode_translator( $attr, $content ) {
		return $this->shortcode_taxonomy( $attr, 'mbdb_translator', 'cover-artist', 'translators' );
	}

	public function shortcode_links( $attr, $content ) {
		$attr           = shortcode_atts( array(
			'width'         => '',
			'height'        => '',
			'size'          => '',
			'align'         => 'vertical',
			'downloadlabel' => '',
			'buylabel'      => '',
			'after'         => '',
			'blank'         => '',
			'blanklabel'    => '',
			'book'          => '',
		), $attr );
		$attr2          = $attr;
		$attr2['blank'] = '';

		$this->set_book( $attr['book'] );
		$book = $this->data_object;
		if ( ! $book->has_buy_links() && ! $book->has_download_links() ) {
			$classname = 'mbm-book-buy-links';

			return apply_filters( 'mbdb_shortcode_links', '<span class="' . $classname . '"><span class="' . $classname . '-label">' . esc_html( $attr['blanklabel'] ) . '</span><span class="' . $classname . '-blank">' . esc_html( $attr['blank'] ) . '</span></span>' );
		}
		$output_html = '<div class="mbm-book-links">';
		if ( $book->has_buy_links() ) {
			$attr2['label'] = $attr['buylabel'];
			$output_html    .= $this->output_buylinks( $book->buy_links, $attr2, $book->id );
		}

		if ( $book->has_download_links() ) {
			$attr2['label'] = $attr['downloadlabel'];
			$output_html    .= $this->output_downloadlinks( $book->download_links, $attr2 );
		}
		$output_html .= '</div>';

		return apply_filters( 'mbdb_shortcode_links', $output_html );
	}

	public function shortcode_editions( $attr, $content ) {
		$attr = shortcode_atts( array(
			'label' => '',
			'after' => '',
			'blank' => '',
			'book'  => '',
		), $attr );
		$this->set_book( $attr['book'] );
		$book = $this->data_object;

		if ( ! $book->has_editions() ) {
			return $this->output_blank_data( 'editions', $attr['blank'] );
		}
		//error_log('start editions');
		$output_html      = '';
		$counter          = 0;
		$default_language = MBDB()->options->default_language;
		$languages        = MBDB()->options->languages;
		$currency_symbols = MBDB()->options->currency_symbols;
		foreach ( $book->editions as $edition ) {

			$is_isbn     = ( $edition->isbn != '' );
			$is_doi     = ( $edition->doi != '' );
			$is_sku     = ( $edition->sku != '' );
			$is_height   = ( $edition->height != '' );
			$is_width    = ( $edition->width != '' );
			$is_pages    = ( $edition->length != '' );
			$is_price    = ( $edition->retail_price != '' );
			$is_language = ( $edition->language != '' );
			$is_title    = ( $edition->edition_title != '' );

			$output_html .= '<span class="mbm-book-editions-format" id="mbm_book_editions_format_' . $counter . '" name="mbm_book_editions_format[' . $counter . ']">';
			if ( $is_isbn || $is_sku || $is_doi || $is_pages || ( $is_height && $is_width ) ) {
				$output_html .= '<a class="mbm-book-editions-toggle" id="mbm_book_editions_toggle_' . $counter . '" name="mbm_book_editions_toggle[' . $counter . ']"></a>';
			}
			$format_name = $edition->format->name;
			$output_html .= '<span class="mbm-book-editions-format-name">' . $format_name . '</span>';


			if ( $is_language && $edition->language != $default_language ) {
				if ( array_key_exists( $edition->language, $languages ) ) {
					$language_name = $languages[ $edition->language ];
				} else {
					$language_name = $edition->language;
				}
				$output_html .= ' <span class="mbm-book-editions-language">(' . $language_name . ')</span>';
			}

			if ( $is_title ) {
				$output_html .= ' - <span class="mbm-book-editions-title">' . $edition->edition_title . '</span>';
			}
			if ( $is_price && $edition->retail_price != '0.00' && $edition->retail_price != '0,00' ) {
				$edition->retail_price = str_replace( ',', '.', $edition->retail_price );
				$price                 = number_format_i18n( $edition->retail_price, 2 );
				// TODO get currency symbol

				if ( array_key_exists( $edition->currency, $currency_symbols ) ) {
					$symbol = $currency_symbols[ $edition->currency ];
				} else {
					$symbol = $edition->currency;
				}
				/* translators: This colon (:) is displayed between the edition name and price. It's translatable so that you may change the spacing as desired. */
				$output_html .= __( ':', 'mooberry-book-manager' ) . ' <span class="mbm-book-editions-srp"><span class="mbm-book-editions-price">';
				/* translators: %1$s is the currency symbol. %2$s is the price. To put currency after price, enter %2$s %1$s */
				$output_html .= sprintf( _x( '%1$s %2$s', '%1$s is the currency symbol. %2$s is the price. To put currency after price, enter %2$s %1$s', 'mooberry-book-manager' ), $symbol, $price );
				if ( MBDB()->options->show_currency == 'yes' ||  $edition->language != $default_language ) {
					$output_html .= '<span class="mbm-book-edition-currency"> ' . $edition->currency . '</span>';
				}
				$output_html .= '</span></span>';
			}
			if ( $is_isbn || $is_sku || $is_doi || ( $is_height && $is_width ) || $is_pages ) {
				$output_html .= '<div name="mbm_book_editions_subinfo[' . $counter . ']" id="mbm_book_editions_subinfo_' . $counter . '" class="mbm-book-editions-subinfo">';

				if ( $is_isbn ) {
					$output_html .= '<strong>' . __( 'ISBN:', 'mooberry-book-manager' ) . '</strong> <span class="mbm-book-editions-isbn">' . $edition->isbn . '</span><br/>';
				}
				if ( $is_doi ) {
					$output_html .= '<strong>' . __( 'DOI:', 'mooberry-book-manager' ) . '</strong> <span class="mbm-book-editions-doi">' . $edition->doi . '</span><br/>';
				}
				if ( $is_sku ) {
					$output_html .= '<strong>' . __( 'SKU:', 'mooberry-book-manager' ) . '</strong> <span class="mbm-book-editions-sku">' . $edition->sku . '</span><br/>';
				}
				if ( $is_height && $is_width ) {
					$output_html .= '<strong>' . __( 'Size:', 'mooberry-book-manager' ) . '</strong> <span class="mbm-book-editions-size"><span class="mbm-book-editions-height">' . number_format_i18n( $edition->width, 2 ) . '</span> x <span class="mbm-book-editions-width">' . number_format_i18n( $edition->height, 2 ) . '</span> <span class="mbm-book-editions-unit">' . $edition->unit . '</span></span><br/>';
				}
				if ( $is_pages ) {
					$output_html .= '<strong>' . __( 'Pages:', 'mooberry-book-manager' ) . '</strong> <span class="mbm-book-editions-length">' . number_format_i18n( $edition->length ) . '</span>';
				}
				$output_html = apply_filters('mbdb_edition_fields_output', $output_html, $edition, $book);
				$output_html .= '</div>';
			}
			$output_html .= '</span>';
			$counter ++;
		}

		//error_log('end editions');
		return apply_filters( 'mbdb_shortcode_editions', '<div class="mbm-book-editions"><span class="mbm-book-editions-label">' . esc_html( $attr['label'] ) . '</span>' . $output_html . '<span class="mbm-book-editions-after">' . esc_html( $attr['after'] ) . '</span></div>' );

	}

	public function shortcode_back_to_grid( $attr, $content ) {
		$attr = shortcode_atts( array(
			'grid' => 0,
			'book' => '',
		), $attr );
		$book = 0;
		if ( $attr['grid'] != 0 ) {
			if ( $attr['book'] == '' ) {
				global $post;
				$book_id = $post->ID;
			} else {
				$book = get_page_by_path( $attr['book'], OBJECT, 'mbdb_book' );
				if ( $book ) {
					$book_id = $book->ID;
				}
			}
			if ( $book_id != 0 ) {

				if ( $attr['grid'] == MBDB()->options->tax_grid_page ) {
					$tax = 'mbdb_' . $_GET['taxonomy'];
					$term_id = intval($_GET['term']);
					$term = get_term($term_id, $tax);
					error_log(print_r($term, true));
					$link = $this->get_tax_grid_link($term, $tax);
				} else {
					$link = get_permalink( $attr['grid'] );
				}

				if ( $link != '' ) {
					$link = apply_filters('mbdb_book_back_to_grid_link', $link, $book_id, $attr['grid']);
					$content = '<a class="mbdb_back_to_grid_link" href="' . $link . '#book_' . $book_id . '">&lt; ' . __('Back to grid', 'mooberry-book-manager') . '</a>';

				}
			}
		}
		return apply_filters('mbdb_shortcode_back_to_grid', $content);
	}


	public function shortcode_book( $attr, $content ) {

		global $post;
		if ( $post ) {
			$book_id = $post->ID;
			$title   = $post->post_title;
			$slug    = $post->post_name;
		} else {
			$book_id = 0;
			$title   = '';
			$slug    = '';
		}
		$attr = shortcode_atts( array(
			'book' => $slug,
		), $attr );


		$this->set_book( $attr['book'] );
		$book = $attr['book'];


		$book_page_layout = apply_filters( 'mbdb_before_book_page', '', $this->data_object, $attr );

		$book_page_layout .= '<div id="mbm-book-page" itemscope itemtype="http://schema.org/Book"><meta itemprop="name" content="' . esc_attr( strip_tags( $title ) ) . '" >';
		//error_log('subtitile');
		$book_page_layout = apply_filters( 'mbdb_book_page_before_subtitle', $book_page_layout, $this->data_object, $attr );
		if ( $this->data_object->subtitle != '' ) {
			$book_page_layout .= '<h3>[book_subtitle blank="" book="' . $book . '"]</h3>';
		}
		if ( MBDB()->options->show_back_to_grid_link == 'yes' ) {
			if ( isset( $_GET['grid_referrer'] ) && intval( $_GET['grid_referrer'] ) != 0 ) {
				$book_page_layout .= '<div id="mbdb_book_page_back_to_grid_top">[book_back_to_grid grid="' . intval( $_GET['grid_referrer'] ) . '"]</div>';
			}
		}
		// v 3.0 for customizer
		$book_page_layout = apply_filters( 'mbdb_book_page_after_subtitle', $book_page_layout, $this->data_object, $attr );
		$book_page_layout .= '<div id="mbm-first-column">';
		//error_log('book cover');
		$book_page_layout = apply_filters( 'mbdb_book_page_before_book_cover', $book_page_layout, $this->data_object, $attr );
		$book_page_layout .= '[book_cover book="' . $book . '"]';
		$book_page_layout = apply_filters( 'mbdb_book_page_after_cook_cover', $book_page_layout, $this->data_object, $attr );
		//error_log('start links');
		$book_page_layout .= '<div id="mbm-book-links1">';
		//$is_links_data = mbdb_get_links_data();
		$book_page_layout = apply_filters( 'mbdb_book_page_before_buy_links', $book_page_layout, $this->data_object, $attr );
		if ( $this->data_object->has_buy_links() ) {
			$book_page_layout .= '[book_buylinks  align="horizontal" book="' . $book . '"]';
		}
		$book_page_layout = apply_filters( 'mbdb_book_page_before_download_links', $book_page_layout, $this->data_object, $attr );
		if ( $this->data_object->has_download_links() ) {
			$book_page_layout .= '[book_downloadlinks align="horizontal" label="' . __( 'Download Now:', 'mooberry-book-manager' ) . '" book="' . $book . '"]';
		}
		$book_page_layout = apply_filters( 'mbdb_book_page_after_download_links', $book_page_layout, $this->data_object, $attr );
		$book_page_layout .= '</div>';
		//error_log('end links');
		$book_page_layout = apply_filters( 'mbdb_book_page_before_series', $book_page_layout, $this->data_object, $attr );
		if ( ! $this->data_object->is_standalone() ) {
			$book_page_layout .= '[book_serieslist before="' . __( 'Part of the', 'mooberry-book-manager' ) . ' " after=" ' . __( 'series:', 'mooberry-book-manager' ) . ' " delim="list"  book="' . $book . '"]';
		}
		/*	TO DO */
		//error_log('editions');
		$book_page_layout = apply_filters( 'mbdb_book_page_before_editions', $book_page_layout, $this->data_object, $attr );
		if ( $this->data_object->has_editions() ) {
			$book_page_layout .= '[book_editions blank="" label="' . __( 'Editions:', 'mooberry-book-manager' ) . '" book="' . $book . '"]';
		}
		//error_log('goodreads');
		$book_page_layout = apply_filters( 'mbdb_book_page_before_goodreads', $book_page_layout, $this->data_object, $attr );
		if ( $this->data_object->goodreads != '' ) {
			$book_page_layout .= '[book_goodreads book="' . $book . '"]';
		}
		//error_log('summary');
		// v 3.0 for customizer
		$book_page_layout = apply_filters( 'mbdb_book_page_after_goodreads', $book_page_layout, $this->data_object, $attr );

		$book_page_layout = apply_filters( 'mbdb_book_page_before_reedsy', $book_page_layout, $this->data_object, $attr );
		if ( $this->data_object->reedsy != '' ) {
			$book_page_layout .= '[book_reedsy book="' . $book . '"]';
		}
		$book_page_layout = apply_filters( 'mbdb_book_page_after_reedsy', $book_page_layout, $this->data_object, $attr );


		$book_page_layout = apply_filters( 'mbdb_book_page_before_google_books', $book_page_layout, $this->data_object, $attr );
		if ( $this->data_object->google_books != '' ) {
			$book_page_layout .= '[book_google_books book="' . $book . '"]';
		}
		//error_log('summary');
		// v 3.0 for customizer
		$book_page_layout = apply_filters( 'mbdb_book_page_after_google_books', $book_page_layout, $this->data_object, $attr );

		$book_page_layout .= '</div><div id="mbm-second-column">';
		$book_page_layout = apply_filters( 'mbdb_book_page_before_summary', $book_page_layout, $this->data_object, $attr );
		//	if ( $this->data_object->summary != '' ) {
		$book_page_layout .= '[book_summary blank=""  book="' . $book . '"]';
		//	}
		$book_page_layout = apply_filters( 'mbdb_book_page_after_summary', $book_page_layout, $this->data_object, $attr );


		// v3.0 convert to simple true/false so that the if statement with ||
		// below actually works. Otherwise, valid data could be "0" and still
		// evaulate to false in the if statement
		// also simplifies the following if statments
		$has_published_date = $this->data_object->has_published_date();
		$is_publisher       = $this->data_object->has_publisher();
		$is_genre           = $this->data_object->has_genres();
		$is_tag             = $this->data_object->has_tags();
		$is_editor          = $this->data_object->has_editors();
		$is_illustrator     = $this->data_object->has_illustrators();
		$is_cover_artist    = $this->data_object->has_cover_artists();
		$is_narrator = $this->data_object->has_narrators();
		$is_translator = $this->data_object->has_translators();

		$display_details  = apply_filters( 'mbdb_display_book_details', $has_published_date || $is_publisher || $is_genre || $is_tag || $is_editor || $is_illustrator || $is_cover_artist || $is_translator || $is_narrator );
		$book_page_layout = apply_filters( 'mbdb_book_page_before_details_section', $book_page_layout, $this->data_object, $attr );
		//error_log('start details');
		if ( $display_details ) {
			$book_page_layout .= '<div class="mbm-book-details-outer">';
			$book_page_layout .= '  <div class="mbm-book-details">';

			$book_page_layout = apply_filters( 'mbdb_book_page_before_pubdate', $book_page_layout, $this->data_object, $attr );
			if ( $has_published_date ) {
				//$book_page_layout .= '<span class="mbm-book-details-published-label">' . __('Published:', 'mooberry-book-manager') . '</span> <span class="mbm-book-details-published-data">[book_published format="default" blank="" book="' . $book . '"]</span><br/>';

				$book_page_layout .= '<span class="mbm-book-details-published-data">[book_published format="default" blank="" book="' . $book . '"]</span><br/>';
			}
			$book_page_layout = apply_filters( 'mbdb_book_page_before_publisher', $book_page_layout, $this->data_object, $attr );
			if ( $is_publisher ) {

				$book_page_layout .= '<span class="mbm-book-details-publisher-label">' . __( 'Publisher:', 'mooberry-book-manager' ) . '</span> <span class="mbm-book-details-publisher-data">[book_publisher  blank="" book="' . $book . '"]</span><br/>';
			}
			$book_page_layout = apply_filters( 'mbdb_book_page_before_imprint', $book_page_layout, $this->data_object, $attr );
			if ( $this->data_object->has_imprint() ) {

				$book_page_layout .= '<span class="mbm-book-details-imprint-label">' . __( 'Imprint:', 'mooberry-book-manager' ) . '</span> <span class="mbm-book-details-imprint-data">[book_imprint  blank="" book="' . $book . '"]</span><br/>';
			}
			$book_page_layout = apply_filters( 'mbdb_book_page_before_editor', $book_page_layout, $this->data_object, $attr );
			if ( $is_editor ) {

				$book_page_layout .= '<span class="mbm-book-details-editors-label">' . __( 'Editors:', 'mooberry-book-manager' ) . '</span> <span class="mbm-book-details-editors-data">[book_editor delim="comma" blank="" book="' . $book . '"]</span><br/>';
			}
			$book_page_layout = apply_filters( 'mbdb_book_page_before_illustrators', $book_page_layout, $this->data_object, $attr );
			if ( $is_illustrator ) {

				$book_page_layout .= '<span class="mbm-book-details-illustrators-label">' . __( 'Illustrators:', 'mooberry-book-manager' ) . '</span> <span class="mbm-book-details-illustrators-data">[book_illustrator delim="comma" blank="" book="' . $book . '"]</span><br/>';
			}
			$book_page_layout = apply_filters( 'mbdb_book_page_before_cover_artist', $book_page_layout, $this->data_object, $attr );
			if ( $is_cover_artist ) {

				$book_page_layout .= '<span class="mbm-book-details-cover-artists-label">' . __( 'Cover Artists:', 'mooberry-book-manager' ) . '</span> <span class="mbm-book-details-cover-artists-data">[book_cover_artist delim="comma" blank="" book="' . $book . '"]</span><br/>';
			}

			$book_page_layout = apply_filters( 'mbdb_book_page_before_narrator', $book_page_layout, $this->data_object, $attr );
			if ( $is_narrator ) {
				$book_page_layout .= '<span class="mbm-book-details-narrators-label">' . __( 'Narrators:', 'mooberry-book-manager' ) . '</span> <span class="mbm-book-details-narrators-data">[book_narrator delim="comma" blank="" book="' . $book . '"]</span><br/>';
			}

			$book_page_layout = apply_filters( 'mbdb_book_page_before_translator', $book_page_layout, $this->data_object, $attr );
			if ( $is_translator ) {
				$book_page_layout .= '<span class="mbm-book-details-translators-label">' . __( 'Translators:', 'mooberry-book-manager' ) . '</span> <span class="mbm-book-details-translators-data">[book_translator delim="comma" blank="" book="' . $book . '"]</span><br/>';
			}


			$book_page_layout = apply_filters( 'mbdb_book_page_before_genre', $book_page_layout, $this->data_object, $attr );
			if ( $is_genre ) {

				$book_page_layout .= '<span class="mbm-book-details-genres-label">' . __( 'Genres:', 'mooberry-book-manager' ) . '</span> <span class="mbm-book-details-genres-data">[book_genre delim="comma" blank="" book="' . $book . '"]</span><br/>';
			}
			$book_page_layout = apply_filters( 'mbdb_book_page_before_tag', $book_page_layout, $this->data_object, $attr );
			if ( $is_tag ) {
				$book_page_layout .= '<span class="mbm-book-details-tags-label">' . __( 'Tags:', 'mooberry-book-manager' ) . '</span> <span class="mbm-book-details-tags-data">[book_tags delim="comma" blank="" book="' . $book . '"]</span><br/>';
			}


			$book_page_layout = apply_filters( 'mbdb_extra_book_details', $book_page_layout, $this->data_object, $attr );


			$book_page_layout .= '</div></div> <!-- mbm-book-details -->';
		}
		$book_page_layout = apply_filters( 'mbdb_book_page_before_excerpt', $book_page_layout, $this->data_object, $attr );

		if ( $this->data_object->has_excerpt() ) {
			$book_page_layout .= '[book_excerpt label="' . __( 'Excerpt:', 'mooberry-book-manager' ) . '" length="1000"  blank=""  book="' . $book . '"]';
		}
		$book_page_layout = apply_filters( 'mbdb_book_page_after_excerpt', $book_page_layout, $this->data_object, $attr );
		// v 3.0 for customizer
		$book_page_layout .= '</div><div id="mbm-third-column">';
		//error_log('reviews');
		$book_page_layout = apply_filters( 'mbdb_book_page_before_reviews', $book_page_layout, $this->data_object, $attr );
		if ( $this->data_object->has_reviews() ) {
			$book_page_layout .= '<span>[book_reviews  blank="" label="' . __( 'Reviews:', 'mooberry-book-manager' ) . '" book="' . $book . '"]</span><br/>';
		}
		//error_log('additonal info');
		$book_page_layout = apply_filters( 'mbdb_book_page_before_additional_info', $book_page_layout, $this->data_object, $attr );
		if ( $this->data_object->additional_info != '' ) {
			$book_page_layout .= '[book_additional_info book="' . $book . '"]';
		}
		//error_log('links 2');
		// only show 2nd set of links if exceprt is more than 1500 characters long

		$has_links = $this->data_object->has_buy_links() || $this->data_object->has_download_links();
		if ( $this->data_object->excerpt_type == 'text' && strlen( $this->data_object->excerpt ) > 1500 && $has_links ) {
			$book_page_layout .= '<div id="mbm-book-links2">';
			$book_page_layout = apply_filters( 'mbdb_book_page_before_bottom_links', $book_page_layout, $this->data_object, $attr );
			if ( $this->data_object->has_buy_links() ) {
				$book_page_layout .= '[book_buylinks  align="horizontal" book="' . $book . '"]';
			}

			if ( $this->data_object->has_download_links() ) {
				$book_page_layout .= '[book_downloadlinks align="horizontal" label="' . __( 'Download Now:', 'mooberry-book-manager' ) . '" book="' . $book . '"]';
			}
			$book_page_layout = apply_filters( 'mbdb_book_page_after_bottom_links', $book_page_layout, $this->data_object, $attr );
			$book_page_layout .= '</div>'; // book links
			//$book_page_layout .= '<div id="mbm-book-links2">[book_links buylabel="" downloadlabel="' . __('Download Now:', 'mooberry-book-manager') . '" align="horizontal"  blank="" blanklabel="" book="' . $book . '"]</div>';
		}
		//error_log('end links 2');
		$book_page_layout .= '</div> <!-- third column -->';
		if ( MBDB()->options->show_back_to_grid_link == 'yes' ) {
			if ( isset( $_GET['grid_referrer'] ) && intval( $_GET['grid_referrer'] ) != 0 ) {
				$book_page_layout .= '<div id="mbdb_book_page_back_to_grid_bottom">[book_back_to_grid grid="' . intval( $_GET['grid_referrer'] ) . '"]</div>';
			}
		}
		$book_page_layout .= '</div> <!-- mbm-book-page -->';


		$content .= stripslashes( $book_page_layout );
		$content = preg_replace( '/\\n/', '<br/>', $content );

		$content = apply_filters( 'mbdb_book_content', $content, $book_id );
		$content = do_shortcode( $content );

		//return do_shortcode($content);
		return $content;

	}


}
