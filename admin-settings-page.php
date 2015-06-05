<?php
do_action('mbdb_settings_before_instructions');
?>
<h2>Mooberry Book Manager <?php _e('Settings', 'mooberry-book-manager'); ?></h2>
<p><b><?php _e('NOTE:', 'mooberry-book-manager'); ?></b> <?php _e('You must click the SAVE button to save your changes before switching tabs.', 'mooberry-book-manager'); ?></p>


<?php
do_action('mbdb_settings_after_instructions');

global $pagenow;
if ( $pagenow == 'options-general.php' && $_GET['page'] == 'mbdb_settings' ) {
    if ( isset ( $_GET['tab'] ) ) {
        $tab = $_GET['tab'];
    } else {
        $tab = 'general';
    }
	mbdb_admin_tabs($tab);
	do_action('mbdb_settings_before_tab_display', $tab);
    switch ( $tab ) {
		case 'general':
			$fields = mbdb_general_settings();
			mbdb_meta_fields($fields);
			break;
		case 'publishers':
			$fields = mbdb_publishers();
			mbdb_meta_fields($fields);
			break;
		case 'retailers' :
           $fields =  mbdb_retailers();
		   mbdb_meta_fields($fields);
            break;
        case 'formats' :
            $fields = mbdb_formats();
			mbdb_meta_fields($fields);
            break;
		// case 'output':
		// mbdb_print_book_list();
			// break;
		case 'editions' :
			$fields = mbdb_editions();
			mbdb_meta_fields($fields);
			break;
	}
	do_action('mbdb_settings_after_tab_display', $tab);
}

function mbdb_publishers() {
	return apply_filters('mbdb_settings_publishers_settings', array(
				array(
					'id'          => 'publishers',
					'type'        => 'group',
					'desc'			=>	__('Add your publishers.', 'mooberry-book-manager'),
					'options'     => array(
						'group_title'   => __('Publisher', 'mooberry-book-manager') . ' {#}',  // since version 1.1.4, {#} gets replaced by row number
						'add_button'    =>  __('Add New Publisher', 'mooberry-book-manager'),
						'remove_button' =>  __('Remove Publisher', 'mooberry-book-manager') ,
						'sortable'      => false, // beta
					),
					// Fields array works the same, except id's only need to be unique for this group. Prefix is not needed.
					'fields'      => array(
							array(
								'name' => __('Publisher', 'mooberry-book-manager'),
								'id'   => 'name',
								'type' => 'text_medium',
								'attributes' => array(
									'required' => 'required',
								),
							),
							array(
								'name' 	=> __('Publisher Website', 'mooberry-book-manager'),
								'id'	=> 'website',
								'type'	=> 'text_url',
								'desc' => 'http://www.someWebsite.com/',
								'attributes' =>  array(
									//'pattern' => '^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})\/?([\/\w \.=\?&\-%]*)*\/?',
									'pattern' => '^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6}).*',
									
								),
							),
							array(
							'id' => 'uniqueID',
							'type' => 'text',
							'show_names' => false,
							'sanitization_cb' => 'mbdb_uniqueID_generator',
							'attributes' => array(
								'type' => 'hidden',
								),
							),
						),
					),
				)
			);
}

function mbdb_editions() {
	return apply_filters('mbdb_settings_editions_settings', array(
				array(
					'id'          => 'editions',
					'type'        => 'group',
					'desc'			=>	__('Add any additional formats your books are available in.', 'mooberry-book-manager'),
					'options'     => array(
						'group_title'   => __('Format', 'mooberry-book-manager') . ' {#}',  // since version 1.1.4, {#} gets replaced by row number
						'add_button'    =>  __('Add New Format', 'mooberry-book-manager'),
						'remove_button' =>  __('Remove Format', 'mooberry-book-manager') ,
						'sortable'      => false, // beta
					),
					// Fields array works the same, except id's only need to be unique for this group. Prefix is not needed.
					'fields'      => array(
						array(
							'name' => __('Format Name', 'mooberry-book-manager'),
							'id'   => 'name',
							'type' => 'text_medium',
							'attributes' => array(
								'required' => 'required',
							),
							
						),
						array(
							'id' => 'uniqueID',
							'type' => 'text',
							'show_names' => false,
							'sanitization_cb' => 'mbdb_uniqueID_generator',
							'attributes' => array(
								'type' => 'hidden',
								),
						),
					),
				),
			)
		);
}

function mbdb_general_settings() {
	return apply_filters('mbdb_settings_general_settings', array(
				array(
					'id'	=> 'mbdb_book_default_settings_title',
					'name'	=>	__('BOOK PAGE DEFAULT SETTINGS', 'mooberry-book-manager'),
					'type'	=>	'title',
				),array(
					'id'	=>	'mbdb_default_unit',
					'name'	=>	__('Default Unit of Measurement', 'mooberry-book-manager'),
					'type'	=> 'select',
					'default'	=> 'in',
					'options'	=> mbdb_get_units_array(),
				),
				array(
					'id'	=>	'mbdb_default_currency',
					'name'	=>	__('Default Currency', 'mooberry-book-manager'),
					'type'	=> 'select',
					'default'	=> 'USD',
					'options'	=> mbdb_get_currency_array(),
				),
				array(
					'id'	=>	'mbdb_default_language',
					'name'	=>	__('Default Language', 'mooberry-book-manager'),
					'type'	=> 'select',
					'default'	=> 'EN',
					'options'	=> mbdb_get_language_array(),
				),
				array(
					'id'	=> 'mbdb_grid_default_settings_title',
					'name'	=>	__('BOOK GRID DEFAULT SETTINGS', 'mooberry-book-manager'),
					'type'	=>	'title',
				),
			/*	array(
					'id'	=>	'mbdb_default_cover_height',
					'name'	=> __('Cover Height (px)', 'mooberry-book-manager'),
					'type'	=> 'text_small',
					'default'	=> 200,
					'attributes' => array(
							'type' => 'number',
							'pattern' => '\d*',
							'min' => 50,
					),
				),
			*/
				array(
					'name'	=> __('Number of Books Across', 'mooberry-book-manager'),
					'id'	=> 'mbdb_default_books_across',
					'type'	=> 'text_small',
					'default'	=>	3,
					'attributes' => array(
							'type' => 'number',
							'pattern' => '\d*',
							'min' => 1,
					),
				),
			)
		);
}

function mbdb_print_book_list() {
	$book_query = mbdb_get_books_list( 'all', null, 'title', 'ASC', null, null );
	$output = '<table border="1"><tr><th>ID</th><th>Title</th><th>Cover</th><th>Genre</th><th>Series</th><th>Pub Date</th><th>Author</th><th>Series Order</th></tr>';
	foreach($book_query as $book) {
		$output .= '<tr><td>' . $book->ID . '</td><td>' . $book->post_title . '</td><td>';
		$img_src = get_post_meta($book->ID, '_mbdb_cover', true);
		if ($img_src!='') {
			$output .= '<IMG SRC="' . esc_url($img_src) . '" width="100"/>';
		}
		$output .= '</td><td>' . get_the_term_list( $book->ID, 'mbdb_genre', '' , ', ' ) . '</td>';
		$output .= '<td>' . get_the_term_list( $book->ID, 'mbdb_series', '' , ', ' ) . '</td>';
		$output .= '<td>' . get_post_meta(  $book->ID, '_mbdb_published', true) . '</td>';
		$output .= '<td></td>';
		$output .= '<td>' . get_post_meta(  $book->ID, '_mbdb_series_order', true) . '</td></tr>' ;
	}
	$output .= '</table>';
	echo $output;
}

function mbdb_meta_fields( $fields) {
	$metabox = apply_filters('mbdb_settings_options_meta_box', array(
		'id'         => 'mbdb_option_metabox',
		'show_on'    => array( 'key' => 'options-page', 'value' => 'mbdb_options', ),
		'show_names' => true,
		'fields'     => $fields,
	));
	do_action('mbdb_settings_before_metabox');
	cmb2_metabox_form( $metabox, 'mbdb_options' );	
	do_action('mbdb_settings_after_metabox');
	echo '<div id="mbdb_about_mooberry"><div id="mbdb_box">			<h3>Need help with Mooberry Book Manager?</h3>';
		include('views/admin-about-mooberry.php');
	echo '</div></div>';
}

function mbdb_retailers() {
	return apply_filters('mbdb_settings_retailer_fields', array(
		array(
			'id'          => 'retailers',
			'type'        => 'group',
			'desc'			=>	__('Add any additional retailers that sell your books.', 'mooberry-book-manager'),
			'options'     => array(
				'group_title'   => __('Retailer', 'mooberry-book-manager') . ' {#}',  // since version 1.1.4, {#} gets replaced by row number
				'add_button'    =>  __('Add Retailer', 'mooberry-book-manager'),
				'remove_button' =>  __('Remove Retailer', 'mooberry-book-manager') ,
				'sortable'      => false, // beta
			),
			// Fields array works the same, except id's only need to be unique for this group. Prefix is not needed.
			'fields'      => array(
				array(
					'name' => __('Retailer Name', 'mooberry-book-manager'),
					'id'   => 'name',
					'type' => 'text_medium',
					'attributes' => array(
						'required' => 'required',
//						'class' => 'mbdb_retailers',
						),
					// 'repeatable' => true, // Repeatable fields are supported w/in repeatable groups (for most types)
					
				),
				array(
					'name' => __('Retailer Logo Image', 'mooberry-book-manager'),
					'id'   => 'image',
					'type' => 'file',
					'attributes' => array(
						'size' => 10,
					),
				),
				array(
					'id' => 'uniqueID',
					'type' => 'text',
					'show_names' => false,
					'sanitization_cb' => 'mbdb_uniqueID_generator',
					'attributes' => array(
						'type' => 'hidden',
						),
				),
			),
		),
	));
}

function  mbdb_formats() {
  return apply_filters('mbdb_settings_format_fields', array(
		array(
			'id'          => 'formats',
			'type'        => 'group',
			'desc'			=> __('If you have free books for download, add any additional formats your books are available in.', 'mooberry-book-manager'),
			'options'     => array(
				'group_title'   => _x('Format', 'noun', 'mooberry-book-manager') . ' {#}',  // since version 1.1.4, {#} gets replaced by row number
				'add_button'    => __('Add Format', 'mooberry-book-manager'),
				'remove_button' => __('Remove Format', 'mooberry-book-manager'),
				'sortable'      => false, // beta
			),
			// Fields array works the same, except id's only need to be unique for this group. Prefix is not needed.
			'fields'      => array(
				array(
					'name' => _x('Format', 'noun', 'mooberry-book-manager'),
					'id'   => 'name',
					'type' => 'text',
					'attributes' => array(
						'required' => 'required',
					),
					// 'repeatable' => true, // Repeatable fields are supported w/in repeatable groups (for most types)
				),
				array(
					'name' => _x('Format Image', 'noun', 'mooberry-book-manager'),
					'id'   => 'image',
					'type' => 'file',
				),
				array(
					'id' => 'uniqueID',
					'type' => 'text',
					'show_names' => false,
					'sanitization_cb' => 'mbdb_uniqueID_generator',
					'attributes' => array(
						'type' => 'hidden',
						),
				),
			),
		),
	));
}
	


// make the tabs for the admin screen
function mbdb_admin_tabs( $current = 'book-page' ) {
	$tabs = apply_filters('mbdb_settings_tabs', array( 'general' => __('General Settings', 'mooberry-book-manager'), 'publishers' => __('Publishers', 'mooberry-book-manager'), 'editions' => __('Edition Formats', 'mooberry-book-manager'), 'retailers' => __('Retailers', 'mooberry-book-manager'), 'formats' => _x('E-book Formats', 'noun', 'mooberry-book-manager'))); //, 'output' => 'Print Book list'));
	do_action('mbdb_settings_before_tabs');
	echo '<div id="icon-options-general" class="icon32"></div>';
	echo '<h2 class="nav-tab-wrapper">';
	foreach( $tabs as $tab => $name ){
		$class = ( $tab == $current ) ? ' nav-tab-active' : '';
		echo "<a class='nav-tab$class' href='?page=mbdb_settings&tab=$tab'>$name</a>";
	}
	echo '</h2>';
	do_action('mbdb_settings_after_tabs');
}

	
	