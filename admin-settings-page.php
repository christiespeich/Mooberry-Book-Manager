<?php
do_action('mbdb_settings_before_instructions');
?>
<h2>Mooberry Book Manager Settings</h2>
<p><b>NOTE:</b> You must click the SAVE button to save your changes before switching tabs.</p>


<?php
do_action('mbdb_settings_after_instructions');

global $pagenow;
if ( $pagenow == 'options-general.php' && $_GET['page'] == 'mbdb_settings' ) {
    if ( isset ( $_GET['tab'] ) ) {
        $tab = $_GET['tab'];
    } else {
        $tab = 'retailers';
    }
	mbdb_admin_tabs($tab);
	do_action('mbdb_settings_before_tab_display', $tab);
    switch ( $tab ) {
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
	}
	do_action('mbdb_settings_after_tab_display', $tab);
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
	
	include('views/admin-about-mooberry.php');
}

function mbdb_retailers() {
	return apply_filters('mbdb_settings_retailer_fields', array(
		array(
			'id'          => 'retailers',
			'type'        => 'group',
			'desc'			=>	'Add any additional retailers that sell your books.',
			'options'     => array(
				'group_title'   => 'Retailer {#}',  // since version 1.1.4, {#} gets replaced by row number
				'add_button'    =>  'Add Retailer', 
				'remove_button' =>  'Remove Retailer', 
				'sortable'      => false, // beta
			),
			// Fields array works the same, except id's only need to be unique for this group. Prefix is not needed.
			'fields'      => array(
				array(
					'name' => 'Retailer Name',
					'id'   => 'name',
					'type' => 'text_medium',
					'attributes' => array(
						'required' => 'required',
//						'class' => 'mbdb_retailers',
						),
					// 'repeatable' => true, // Repeatable fields are supported w/in repeatable groups (for most types)
					
				),
				array(
					'name' => 'Retailer Logo Image',
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
			'desc'			=> 'If you have free books for download, add any additional formats your books are available in.',
			'options'     => array(
				'group_title'   => 'Format {#}',  // since version 1.1.4, {#} gets replaced by row number
				'add_button'    => 'Add Format', 
				'remove_button' => 'Remove Format',
				'sortable'      => false, // beta
			),
			// Fields array works the same, except id's only need to be unique for this group. Prefix is not needed.
			'fields'      => array(
				array(
					'name' => 'Format',
					'id'   => 'name',
					'type' => 'text',
					'attributes' => array(
						'required' => 'required',
					),
					// 'repeatable' => true, // Repeatable fields are supported w/in repeatable groups (for most types)
				),
				array(
					'name' => 'Format Image',
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
	
	
function mbdb_uniqueID_generator( $value ) {
	if ($value=='') {
		$value =  uniqid();
	}
	return apply_filters('mbdb_settings_uniqid', $value);
}

function mbdb_admin_tabs( $current = 'book-page' ) {
	$tabs = apply_filters('mbdb_settings_tabs', array( 'retailers' => 'Retailers', 'formats' => 'Formats')); //, 'output' => 'Print Book list'));
	do_action('mbdb_settings_before_tabs');
	echo '<div id="icon-themes" class="icon32"><br></div>';
	echo '<h2 class="nav-tab-wrapper">';
	foreach( $tabs as $tab => $name ){
		$class = ( $tab == $current ) ? ' nav-tab-active' : '';
		echo "<a class='nav-tab$class' href='?page=mbdb_settings&tab=$tab'>$name</a>";
	}
	echo '</h2>';
	do_action('mbdb_settings_after_tabs');
}

	
	