<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Loads the plugin language files
 *
 * @access public
 * @since 1.0
 * @return void
 */
add_action( 'plugins_loaded', 'mbdb_load_textdomain' );
function mbdb_load_textdomain() {

	load_plugin_textdomain( 'mooberry-book-manager', FALSE, basename( MBDB_PLUGIN_DIR ) . '/languages/' );
	
	// load the settings
	$mbdb_admin_settings = mbdb_admin();
	
	// check if SuperCache is installed
	define('MBDB_SUPERCACHE', function_exists('wp_cache_manager'));
	
}

/**
 * Init
 *
 * Registers Custom Post Types and Taxonomies
 * Verifies Tax Grid is installed correctly
 * Does upgrade routines
 *
 * @access public
 * @since 1.0
 * @return void
 */
add_action( 'init', 'mbdb_init' );	
function mbdb_init() {
	
	
	mbdb_register_cpts();
	mbdb_register_taxonomies();
	
	
	mbdb_add_tax_grid();
	
	mbdb_upgrade_versions();
	
}

/**
 * Widget Init
 *
 * Registers Book Widget
 *
 * @access public
 * @since 1.0
 * @return widget
 */
add_action( 'widgets_init', 'mbdb_register_widgets' );
function mbdb_register_widgets() {
	return register_widget( 'mbdb_book_widget2' );
}


/**
 * Displays book grid if necessary
 *
 *
 * @access public
 * @since 1.0
 * @since 1.1 Added tc_post_list_content filter for Customizr theme
 * @since 2.3 Added check for !in_the_loop or !is_main_query
 * @since 3.0 Removed book pages and tax grids into shortcodes
 * @since 3.0 Added priority 50 to run after PageBuilder because PB overwrites $content
 *
 * @param string $content 
 * @return string content to display
 */
// because the Customizr theme doesn't use the standard WP set up and
// is automatically considering the tax grids a post list type (archive),
// add an additional filter handler for the content of the Customizr theme
// tc_post_list_content should be unique enough to the Customizr theme
// that it doesn't affect anything else?

//add_filter( 'tc_post_list_content', 'mbdb_content' );
//add_filter( 'the_content', 'mbdb_content', 50, 1 );
function mbdb_content( $content ) {
	
	global $post;
	
	// this weeds out content in the sidebar and other odd places
	// thanks joeytwiddle for this update
	// added in version 2.3
	if ( !in_the_loop() || !is_main_query() ) {
		return $content;
	}
	
	if ( get_post_type() == 'page' && is_main_query() && !is_admin() ) {
			
		$display_grid = get_post_meta( $post->ID, '_mbdb_book_grid_display', true );
		
		if ( $display_grid != 'yes' ) {
			return apply_filters( 'mbdb_book_grid_display_grid_no', $content );
		} else {
			$content .= mbdb_bookgrid_content();
			
			return apply_filters( 'mbdb_book_grid_content', $content );
		}
	}
	
	// return what we got in
	return $content;
}
	
	
/**
 * Displays tax grid if necessary. Truncates excerpt if necessary
 *
 *
 * Forces the display of the whole content, not the excerpt, in the case of 
 * users set to use except on archives, for the tax query. This was found
 * by the Generate theme
 * 
 * On the admin page for the Books CPT truncates the excerpt to 50 characters
 * 
 * @access public
 * @since 2.0
 * @since 2.3 Added check for !in_the_loop or !is_main_query
 * @since 3.0 Returns post_content because tax grids now use a short code
 *
 * @param string $content
 *
 * @return string content to display
 */	
add_filter( 'the_excerpt', 'mbdb_excerpt' );
function mbdb_excerpt( $content ) {
	
	// if on a tax grid and there's query vars set, display the special grid
	if ( get_post_type() == 'mbdb_tax_grid' && is_main_query() && !is_admin() ) {
		
		// this weeds out content in the sidebar and other odd places
		// thanks joeytwiddle for this update
		// added in version 2.3
		if ( !in_the_loop() || !is_main_query() ) {
			return $content;
		}
	
		global $post;
		return do_shortcode($post->post_content);
	}

	// if we're in the admin side and the post type is mbdb_book then we're showign the list of books
	// truncate the excerpt
	if ( is_admin() && get_post_type() == 'mbdb_book' ) {
		$content = trim( substr( $content, 0, 50 ) );
		if ( strlen( $content ) > 0 ) {
			$content .= '...';
		}
	}
	
	// v3.1
	// if we're not on the admin side and it's a book post and main query
	// don't display the excerpt
	if ( get_post_type() == 'mbdb_book' && is_main_query() && !is_admin() ) {
		
		// this weeds out content in the sidebar and other odd places
		// thanks joeytwiddle for this update
		
		if ( !in_the_loop() || !is_main_query() ) {
			return $content;
		}
		return '';
	}	
	
	return $content;
}	
	
/**
 * Grab the template set in the options for the book page and tax grid
 *
 *
 * Attempts to pull the template from the options 
 *
 * In the case that the options aren't set or the template selected
 * doesn't exist, default to the theme's single template
 * 
 * 
 * @access public
 * @since 2.1
 * @since 3.0 Added support for tax grid template as well. Changed from single_template to template_include filter
 * @since 3.5.4 Checks if this is a search and bails if so
 *
 * @param string $template
 * @return string $template
 */
 // NOTE Eventually split this out so it can be used in MA
add_filter( 'template_include', 'mbdb_single_template' );
function mbdb_single_template( $template ) {

	// if a search, return what we got in
	global $wp_query;
	if ( $wp_query->is_search() ) {
		return $template;
	}
	
	// if not a book or tax grid, return what we got in
	if ( get_post_type() != 'mbdb_book' && get_post_type() != 'mbdb_tax_grid' ) {
		return $template;
	}
	
	// make sure it's the main query and not on the admin
	if ( is_main_query() && !is_admin() ) {
		$mbdb_options = get_option( 'mbdb_options' );
		
		// if it's a book, get the default template for book pages
		if ( get_post_type() == 'mbdb_book' ) {
			if ( array_key_exists( 'mbdb_default_template', $mbdb_options ) ) {
				$default_template = $mbdb_options['mbdb_default_template'];
			} 	else {
				$default_template = 'default';
			}
		} else {
			// otherwise it's a tax grid so get the default template for tax grids
			if ( array_key_exists( 'mbdb_tax_grid_template', $mbdb_options ) ) {
				$default_template = $mbdb_options['mbdb_tax_grid_template'];
			} else {
				$default_template = 'default';
			}
		}
	} else {
		return $template;
	}
	
	// if it's the default template, use the single.php template
	if ( $default_template == 'default' ) {
		$default_template = 'single.php';
	}
	
	// now get the file
	if ( isset($default_template) && $default_template != '' && $default_template != 'default' ) {
		
		// first check if there's one in the child theme
		$child_theme = get_stylesheet_directory();
	
		if ( file_exists( $child_theme . '/' . $default_template ) ) {
			return $child_theme . '/' . $default_template;
		} else {
			// if not get the parent theme
			$parent_theme = get_template_directory();

			if ( file_exists( $parent_theme . '/' . $default_template ) ) {
				return $parent_theme . '/' . $default_template;
			}
		}
	}
	
	// if everything fails, just return whatever came in
	return $template;
}


add_action( 'admin_notices', 'mbdb_admin_import_notice', 0 );
function mbdb_admin_import_notice(){
	
	// original 3.0 upgrade migration notice
	$current_version = get_option(MBDB_PLUGIN_VERSION_KEY);
	if (version_compare($current_version, '2.4.4', '>') && version_compare($current_version, '3.1', '<')) {
		$import_books = get_option('mbdb_import_books');
		if (!$import_books || $import_books == null) {
			// only need to migrate if there are books
			$args = array('posts_per_page' => -1,
						'post_type' => 'mbdb_book',
			);
			
			$posts = get_posts( $args  );
			
			if (count($posts) > 0) {
				
				$m = __('Upgrading to Mooberry Book Manager version 3.0 requires some data migration before Mooberry Book Manager will operate properly.', 'mooberry-book-manager');
				$m2 = __('Migrate Data Now', 'mooberry-book-manager');
				echo '<div id="message" class="error"><p>' . $m . '</p><p><a href="admin.php?page=mbdb_migrate" class="button">' . $m2 . '</a></p></div>';
			} else {
				update_option('mbdb_import_books', true);
			}
			wp_reset_postdata();
		}
	}		
	
	// v3.1 added amdin notice option
	$notices  = get_option('mbdb_admin_notices');
	if (is_array($notices)) {
		foreach ($notices as $key => $notice) {
		  echo "<div class='notice {$notice['type']}' id='{$key}'><p>{$notice['message']}</p></div>";
		}
	}	
}




add_action( 'wp_ajax_mbdb_admin_notice_dismiss', 'mbdb_admin_notice_dismiss' );
function mbdb_admin_notice_dismiss() {
	check_ajax_referer( 'mbdb_admin_notice_dismiss_ajax_nonce', 'security' );
	
	$key = $_POST['admin_notice_key'];
	
	mbdb_remove_admin_notice($key);
}


add_action( 'wp_ajax_mbdb_admin_3_1_remigrate', 'mbdb_admin_3_1_remigrate' );
function mbdb_admin_3_1_remigrate() {
	
		
		check_ajax_referer( 'mbdb_admin_notice_3_1_remigrate_ajax_nonce', 'security' );
	
		update_option('mbdb_import_books', false);
		mbdb_remove_admin_notice( '3_1_remigrate');
	
	//	wp_redirect(admin_url('admin.php?page=mbdb_migrate'));
	//exit;
}
  
  



//****************************** term meta *************************************/

		
function mbdb_new_series_grid_description_field() {
	mbdb_taxonomy_grid_description_field( 'mbdb_series' );
}

function mbdb_new_genre_grid_description_field() {
	mbdb_taxonomy_grid_description_field( 'mbdb_genre' );
}

function mbdb_new_tag_grid_description_field() {
	mbdb_taxonomy_grid_description_field( 'mbdb_tag' );
}

function mbdb_new_editor_grid_description_field() {
	mbdb_taxonomy_grid_description_field( 'mbdb_editor' );
}

function mbdb_new_cover_artist_grid_description_field() {
	mbdb_taxonomy_grid_description_field( 'mbdb_cover_artist' );
}

function mbdb_new_illustrator_grid_description_field() {
	mbdb_taxonomy_grid_description_field( 'mbdb_illustrator' );
}


function mbdb_save_series_book_grid_description( $termid ) {
	mbdb_save_taxonomy_book_grid_description( $termid, 'mbdb_series' );
}

function mbdb_save_genre_book_grid_description( $termid ) {
	mbdb_save_taxonomy_book_grid_description( $termid, 'mbdb_genre' );
}

function mbdb_save_tag_book_grid_description( $termid ) {
	mbdb_save_taxonomy_book_grid_description( $termid, 'mbdb_tag' );
}

function mbdb_save_illustrator_book_grid_description( $termid ) {
	mbdb_save_taxonomy_book_grid_description( $termid, 'mbdb_illustrator' );
}

function mbdb_save_cover_artist_book_grid_description( $termid ) {
	mbdb_save_taxonomy_book_grid_description( $termid, 'mbdb_cover_artist' );
}

function mbdb_save_editor_book_grid_description( $termid ) {
	mbdb_save_taxonomy_book_grid_description( $termid, 'mbdb_editor' );
}

function mbdb_edit_series_grid_description_field( $term ) {
	mbdb_edit_taxonomy_grid_description_field( $term, 'mbdb_series' );
}

function mbdb_edit_genre_grid_description_field( $term ) {
	mbdb_edit_taxonomy_grid_description_field( $term, 'mbdb_genre' );
}

function mbdb_edit_tag_grid_description_field( $term ) {
	mbdb_edit_taxonomy_grid_description_field( $term, 'mbdb_tag' );
}

function mbdb_edit_editor_grid_description_field( $term ) {
	mbdb_edit_taxonomy_grid_description_field( $term, 'mbdb_editor' );
}

function mbdb_edit_illustrator_grid_description_field( $term ) {
	mbdb_edit_taxonomy_grid_description_field( $term, 'mbdb_illustrator' );
}

function mbdb_edit_cover_artist_grid_description_field( $term ) {
	mbdb_edit_taxonomy_grid_description_field( $term, 'mbdb_cover_artist' );
}

function mbdb_taxonomy_grid_description_field( $taxonomy ) {
	 wp_nonce_field( basename( __FILE__ ), $taxonomy .'_grid_description_nonce' ); 
	 $slug = mbdb_get_tax_grid_slug( $taxonomy);
	 
	 ?>
	 
<?php
if ( in_array( $taxonomy, mbdb_taxonomies_with_websites() ) ) {
?>
	<div class="form-field">
        <label for="<?php echo $taxonomy; ?>_website"><?php _e( 'Website', 'mooberry-book-manager' ); ?></label>
		<input type="text" name="<?php echo $taxonomy . '_website'; ?>" id="<?php echo $taxonomy . '_website'; ?>" />
    </div>
<?php
}
?>
    <div class="form-field">
        <label for="<?php echo $taxonomy; ?>_book_grid_description"><?php _e( 'Book Grid Description', 'mooberry-book-manager' ); ?></label>
		<?php wp_editor( '',  $taxonomy . '_book_grid_description', array('textarea_rows'=>5)); ?>
		<p><?php _e('The Book Grid Description is displayed above the auto-generated grid for this page, ex. ', 'mooberry-book-manager'); ?><?php echo home_url($slug . '/' . $taxonomy . '-slug'); ?></p>
        
    </div>
	
	<div class="form-field">
        <label for="<?php echo $taxonomy; ?>_book_grid_description_bottom"><?php _e( 'Book Grid Description (Bottom)', 'mooberry-book-manager' ); ?></label>
		<?php wp_editor( '', $taxonomy . '_book_grid_description_bottom', array('textarea_rows'=>5)); ?>
		<p><?php _e('The bottom Book Grid Description is displayed below the auto-generated grid for this page, ex. ', 'mooberry-book-manager'); ?><?php echo home_url($slug . '/' . $taxonomy . '-slug'); ?></p>
        
    </div>
<?php 
}



function mbdb_edit_taxonomy_grid_description_field( $term, $taxonomy ) {

	
		$description = get_term_meta( $term->term_id,  $taxonomy .'_book_grid_description', true );
		$description_bottom = get_term_meta( $term->term_id,  $taxonomy .'_book_grid_description_bottom', true );
		
		$slug = mbdb_get_tax_grid_slug( $taxonomy);
	
		wp_nonce_field( basename( __FILE__ ),  $taxonomy . '_grid_description_nonce' ); 
	  ?>

<?php
if ( in_array( $taxonomy, mbdb_taxonomies_with_websites() ) ) {
	$website = get_term_meta( $term->term_id, $taxonomy . '_website', true);
		
?>
	<tr class="form-field">
			<th scope="row"><label for="<?php echo $taxonomy; ?>_website"><?php _e( 'Website', 'mooberry-book-manager' ); ?></label></th>
		<td><input type="text" id="<?php echo $taxonomy . '_website'; ?>" name="<?php echo $taxonomy . '_website'; ?>" value="<?php echo $website; ?>" />
		<p class="description"></p>
		</td>
	</tr>
<?php
}
?>	  
	  
		<tr class="form-field">
			<th scope="row"><label for="<?php echo $taxonomy; ?>_book_grid_description"><?php _e( 'Book Grid Description', 'mooberry-book-manager' ); ?></label></th>
			<td>
				<?php 	
				wp_editor( $description,  $taxonomy . '_book_grid_description', array('textarea_rows' => 5));
				?>
				<p class="description"><?php _e('The Book Grid Description is displayed above the auto-generated grid for this page, ', 'mooberry-book-manager'); ?><A target="_new" href="<?php echo home_url($slug . '/' . $term->slug); ?>"><?php echo home_url($slug . '/' . $term->slug); ?></a></p>
			</td>
		</tr>
		
		<tr class="form-field">
			<th scope="row"><label for="<?php echo $taxonomy; ?>_book_grid_description_bottom"><?php _e( 'Book Grid Description (Bottom)', 'mooberry-book-manager' ); ?></label></th>
			<td>
				<?php 
			
				wp_editor( $description_bottom,  $taxonomy . '_book_grid_description_bottom', array('textarea_rows' => 5));
				?>
				<p class="description"><?php _e('The bottom Book Grid Description is displayed below the auto-generated grid for this page, ', 'mooberry-book-manager' ); ?><A target="_new" href="<?php echo home_url($slug . '/' . $term->slug); ?>"><?php echo home_url($slug . '/' . $term->slug); ?></a></p>
			</td>
		</tr>
<?php }



function mbdb_save_taxonomy_book_grid_description( $term_id, $taxonomy ) {



    if ( ! isset( $_POST[ $taxonomy . '_grid_description_nonce'] ) || ! wp_verify_nonce( $_POST[ $taxonomy . '_grid_description_nonce'], basename( __FILE__ ) ) ) {
        return;
	}

	
	$old_description = get_term_meta( $term_id,  $taxonomy . '_book_grid_description', true );
    
	$new_description = isset( $_POST[ $taxonomy . '_book_grid_description'] ) ?  $_POST[$taxonomy . '_book_grid_description']  : '';

   if ( $old_description && '' === $new_description ) {
       delete_term_meta( $term_id,  $taxonomy . '_book_grid_description' );
   } else {
	   if ( $old_description !== $new_description ) {
        update_term_meta( $term_id,  $taxonomy . '_book_grid_description', $new_description );
	   }
   }
	
	$old_description_bottom = get_term_meta( $term_id,  $taxonomy . '_book_grid_description_bottom', true );
    $new_description_bottom = isset( $_POST[ $taxonomy . '_book_grid_description_bottom'] ) ?  $_POST[ $taxonomy . '_book_grid_description_bottom']  : '';

   if ( $old_description_bottom && '' === $new_description_bottom ) {
       delete_term_meta( $term_id,  $taxonomy . '_book_grid_description_bottom' );
   } else {
	   if ( $old_description_bottom !== $new_description_bottom ) {
        update_term_meta( $term_id,  $taxonomy . '_book_grid_description_bottom', $new_description_bottom );
	   }
   }

   if ( in_array( $taxonomy, mbdb_taxonomies_with_websites()) ) {
	   $old_website = get_term_meta( $term_id, $taxonomy . '_website', true);
	   $new_website = isset( $_POST[ $taxonomy . '_website'] ) ? $_POST[ $taxonomy . '_website'] : '';
	   
	   if ($old_website && $new_website == '') {
		   delete_term_meta( $term_id, $taxonomy . '_website' );
	   } else {
		   if ( $old_website !== $new_website ) {
			   update_term_meta( $term_id, $taxonomy . '_website', $new_website );
		   }
	   }
   }
}

//********************* end term meta ****************************************/


/**
 * Register Custom Post Types
 *
 * @access public
 * @since 1.0
 * @since 2.0 Added comments support to mbdb_book, capabilities for new roles
 * @since 2.4 Added author support to mbdb_book, Added filter and item_list labels
 * @since 3.0 moved to separate function, added editor, illustrator, cover artist taxonomies
 *
 */
function mbdb_register_cpts() {
	// create Book Post Type
	register_post_type( 'mbdb_book', apply_filters( 'mbdb_book_cpt', array(	
			'label' => _x( 'Books', 'noun', 'mooberry-book-manager' ),
			'public' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'menu_icon' => 'dashicons-book-alt',
			'menu_position' => 20,
			'show_in_nav_menus' => true,
			'has_archive' => false,
			'capability_type' => array( 'mbdb_book', 'mbdb_books' ),
			'map_meta_cap' => true,
			'hierarchical' => false,
			'rewrite' => array( 'slug' => 'book' ),
			'query_var' => true,
			'supports' => array( 'title', 'comments', 'author' ),
			'taxonomies' => array( 'mbdb_tag', 'mbdb_genre', 'mbdb_series', 'mbdb_editor', 'mbdb_illustator', 'mbdb_cover_artist' ),
			'labels' => array (
				'name' => _x( 'Books', 'noun', 'mooberry-book-manager' ),
				'singular_name' => _x( 'Book', 'noun', 'mooberry-book-manager' ),
				'menu_name' => _x( 'Books', 'noun', 'mooberry-book-manager' ),
				'all_items' => __( 'All Books', 'mooberry-book-manager' ),
				'add_new' => __( 'Add New', 'mooberry-book-manager' ),
				'add_new_item' => __( 'Add New Book', 'mooberry-book-manager' ),
				'edit' => __( 'Edit', 'mooberry-book-manager' ),
				'edit_item' => __( 'Edit Book', 'mooberry-book-manager' ),
				'new_item' => __( 'New Book', 'mooberry-book-manager' ),
				'view' => __( 'View Book', 'mooberry-book-manager' ),
				'view_item' => __( 'View Book', 'mooberry-book-manager' ),
				'search_items' => __( 'Search Books', 'mooberry-book-manager' ),
				'not_found' => __( 'No Books Found', 'mooberry-book-manager' ),
				'not_found_in_trash' => __( 'No Books Found in Trash', 'mooberry-book-manager' ),
				'parent' => __( 'Parent Book', 'mooberry-book-manager' ),
				'filter_items_list'     => __( 'Filter Book List', 'mooberry-book-manager' ),
				'items_list_navigation' => __( 'Book List Navigation', 'mooberry-book-manager' ),
				'items_list'            => __( 'Book List', 'mooberry-book-manager' ),
				'view items'	=>	__('View Books', 'mooberry-book-manager'),
				'attributes'	=>	__('Book Attributes', 'mooberry-book-manager'),
				),
			) )
		);
		
		register_post_type( 'mbdb_tax_grid', apply_filters( 'mbdb_tax_grid_cpt', array(	
				'label' => 'Tax Grid',
				'public' => true,
				'show_in_menu' => false,
				'show_ui' => false,
				'exclude_from_search' => true,
				'publicly_queryable' => true,
				'show_in_nav_menus' => false,
				'show_in_admin_bar'	=> false,
				'has_archive' => false,
				'capability_type' => 'post',
				'hierarchical' => false,
				'rewrite' => array( 'slug' => 'mbdb_tax_grid' ),
				'query_var' => true,
				'supports' => array( 'title' ),
				) 
			)
		);
		
		// added 3.4 ?
		register_post_type( 'mbdb_book_grid', apply_filters( 'mbdb_book_grid_cpt', array(
			'label' => __( 'Book Grids',  'mooberry-book-manager' ),
			'show_ui' => true,
			'show_in_menu' => true, //'edit.php?post_type=mbdb_book', 
			'show_in_admin_bar'	=> true,
			'menu_icon' => 'dashicons-screenoptions',
			'menu_position' => 20,
			'show_in_nav_menus' => false,
			'publicly_queryable' => false,
			'exclude_from_search'	=> true,
			'has_archive' => false,
			'capability_type' => array( 'mbdb_book_grid', 'mbdb_book_grids' ),
			'map_meta_cap' => true,
			'hierarchical' => false,
			'rewrite' => false, //array( 'slug' => 'book' ),
			'query_var' => false,
			'can_export'	=> true,
			'supports' => array( 'title' ),
			'labels' => array (
				'name' => __( 'Book Grids', 'mooberry-book-manager' ),
				'singular_name' => __( 'Book Grid', 'mooberry-book-manager' ),
				'all_items' => __( 'All Book Grids', 'mooberry-book-manager' ),
				'add_new' => __( 'Add New', 'mooberry-book-manager' ),
				'add_new_item' => __( 'Add New Book Grid', 'mooberry-book-manager' ),
				'edit' => __( 'Edit', 'mooberry-book-manager' ),
				'edit_item' => __( 'Edit Book Grid', 'mooberry-book-manager' ),
				'new_item' => __( 'New Book Grid', 'mooberry-book-manager' ),
				'view' => __( 'View Book Grid', 'mooberry-book-manager' ),
				'view_item' => __( 'View Book Grid', 'mooberry-book-manager' ),
				'search_items' => __( 'Search Book Grids', 'mooberry-book-manager' ),
				'not_found' => __( 'No Book Grids Found', 'mooberry-book-manager' ),
				'not_found_in_trash' => __( 'No Book Grids Found in Trash', 'mooberry-book-manager' ),
				'parent' => __( 'Parent Book Grid', 'mooberry-book-manager' ),
				'filter_items_list'     => __( 'Filter Book Grid List', 'mooberry-book-manager' ),
				'items_list_navigation' => __( 'Book Grid List Navigation', 'mooberry-book-manager' ),
				'items_list'            => __( 'Book Grid List', 'mooberry-book-manager' ),
				'view items'	=>	__('View Book Grids', 'mooberry-book-manager'),
				'attributes'	=>	__('Book Grid Attributes', 'mooberry-book-manager'),
				),
			) )
		);
}

/**
 * Register Custom Taxonomies
 *
 * @access public
 * @since 1.0
 * @since 2.0 Added capabilities for new roles, moved tags to mbdb_tags
 * @since 2.4 Added filter and item_list labels
 * @since 3.0 moved to separate function, added editor, illustrator, cover artist taxonomies
 *
 */
function mbdb_register_taxonomies() {
	register_taxonomy( 'mbdb_genre', 'mbdb_book', apply_filters( 'mdbd_genre_taxonomy', array(
				//'rewrite' => false, 
				'rewrite' => array(	'slug' => 'mbdb_genres' ),
				'public' => true, //false,
				'show_admin_column' => true,
				'update_count_callback' => '_update_post_term_count',
					'meta_box_cb' => 'post_categories_meta_box',
				'capabilities'	=> array(
					'manage_terms' => 'manage_categories',
					'edit_terms'   => 'manage_categories',
					'delete_terms' => 'manage_categories',
					'assign_terms' => 'manage_mbdb_books',				
				),
				'labels' => array(
					'name' => __( 'Genres', 'mooberry-book-manager' ),
					'singular_name' => __( 'Genre', 'mooberry-book-manager' ),
					'search_items' => __( 'Search Genres' , 'mooberry-book-manager' ),
					'all_items' =>  __( 'All Genres' , 'mooberry-book-manager' ),
					'parent_item' =>  __( 'Parent Genre' , 'mooberry-book-manager' ),
					'parent_item_colon' =>  __( 'Parent Genre:' , 'mooberry-book-manager' ),
					'edit_item' =>  __( 'Edit Genre' , 'mooberry-book-manager' ),
					'update_item' =>  __( 'Update Genre' , 'mooberry-book-manager' ),
					'add_new_item' =>  __( 'Add New Genre' , 'mooberry-book-manager' ),
					'new_item_name' =>  __( 'New Genre Name' , 'mooberry-book-manager' ),
					'menu_name' =>  __( 'Genres' , 'mooberry-book-manager' ),
					'popular_items' => __( 'Popular Genres', 'mooberry-book-manager' ),
					'separate_items_with_commas' => __( 'Separate genres with commas', 'mooberry-book-manager' ),
					'add_or_remove_items' => __( 'Add or remove genres', 'mooberry-book-manager' ),
					'choose_from_most_used' => __( 'Choose from the most used genres', 'mooberry-book-manager' ),
					'not_found' => __( 'No genres found', 'mooberry-book-manager' ),
					'items_list_navigation' => __( 'Genre navigation', 'mooberry-book-manager' ),
					'items_list'            => __( 'Genre list', 'mooberry-book-manager' ),

				)
			)
		)
	);

	register_taxonomy( 'mbdb_tag', 'mbdb_book', apply_filters( 'mdbd_tag_taxonomy', array(
				'rewrite' => array(	'slug' => 'mbdb_tags' ),
			//	'rewrite'	=>	false,
				'public'	=> true, //false,
				'show_admin_column' => true,
				'update_count_callback' => '_update_post_term_count',
					'meta_box_cb' => 'post_categories_meta_box',
				'capabilities'	=> array(
					'manage_terms' => 'manage_categories',
					'edit_terms'   => 'manage_categories',
					'delete_terms' => 'manage_categories',
					'assign_terms' => 'manage_mbdb_books',				
				),
				'labels' => array(
					'name' => __( 'Tags', 'mooberry-book-manager' ),
					'singular_name' => __( 'Tag', 'mooberry-book-manager' ),
					'search_items' => __( 'Search Tags' , 'mooberry-book-manager' ),
					'all_items' =>  __( 'All Tags' , 'mooberry-book-manager' ),
					'parent_item' =>  __( 'Parent Tag' , 'mooberry-book-manager' ),
					'parent_item_colon' =>  __( 'Parent Tag:' , 'mooberry-book-manager' ),
					'edit_item' =>  __( 'Edit Tag' , 'mooberry-book-manager' ),
					'update_item' =>  __( 'Update Tag' , 'mooberry-book-manager' ),
					'add_new_item' =>  __( 'Add New Tag' , 'mooberry-book-manager' ),
					'new_item_name' =>  __( 'New Tag Name' , 'mooberry-book-manager' ),
					'menu_name' =>  __( 'Tags' , 'mooberry-book-manager' ),
					'popular_items' => __( 'Popular Tags', 'mooberry-book-manager' ),
					'separate_items_with_commas' => __( 'Separate tags with commas', 'mooberry-book-manager' ),
					'add_or_remove_items' => __( 'Add or remove tags', 'mooberry-book-manager' ),
					'choose_from_most_used' => __( 'Choose from the most used tags', 'mooberry-book-manager' ),
					'not_found' => __( 'No tags found', 'mooberry-book-manager' ),
					'items_list_navigation' => __( 'Tag navigation', 'mooberry-book-manager' ),
					'items_list'            => __( 'Tag list', 'mooberry-book-manager' ),
				)
			)
		)
	);  


	register_taxonomy( 'mbdb_series', 'mbdb_book', apply_filters( 'mbdb_series_taxonomy', array( 
				'rewrite' =>  array( 'slug' => 'mbdb_series' ),
				'public' => true, // false,
				'show_admin_column' => true,
				'update_count_callback' => '_update_post_term_count',
					'meta_box_cb' => 'post_categories_meta_box',
				'capabilities'	=> array(
					'manage_terms' => 'manage_categories',
					'edit_terms'   => 'manage_categories',
					'delete_terms' => 'manage_categories',
					'assign_terms' => 'manage_mbdb_books',				
				),
				'labels' => array(
					'name' => __( 'Series', 'mooberry-book-manager' ),
					'singular_name' => __( 'Series', 'mooberry-book-manager' ),
					'search_items' => __( 'Search Series' , 'mooberry-book-manager' ),
					'all_items' =>  __( 'All Series' , 'mooberry-book-manager' ),
					'parent_item' =>  __( 'Parent Series' , 'mooberry-book-manager' ),
					'parent_item_colon' =>  __( 'Parent Series:' , 'mooberry-book-manager' ),
					'edit_item' =>  __( 'Edit Series' , 'mooberry-book-manager' ),
					'update_item' =>  __( 'Update Series' , 'mooberry-book-manager' ),
					'add_new_item' =>  __( 'Add New Series' , 'mooberry-book-manager' ),
					'new_item_name' =>  __( 'New Series Name' , 'mooberry-book-manager' ),
					'menu_name' =>  __( 'Series' , 'mooberry-book-manager' ),
					'popular_items' => __( 'Popular Series', 'mooberry-book-manager' ),
					'separate_items_with_commas' => __( 'Separate series with commas', 'mooberry-book-manager' ),
					'add_or_remove_items' => __( 'Add or remove series', 'mooberry-book-manager' ),
					'choose_from_most_used' => __( 'Choose from the most used series', 'mooberry-book-manager' ),
					'not_found' => __( 'No Series found', 'mooberry-book-manager' ),
					'items_list_navigation' => __( 'Series navigation', 'mooberry-book-manager' ),
					'items_list'            => __( 'Series list', 'mooberry-book-manager' ),
				)
			)
		)
	);
		
	register_taxonomy( 'mbdb_editor', 'mbdb_book', apply_filters( 'mbdb_editor_taxonomy', array(
				//'rewrite' => false, 
				'rewrite' => array(	'slug' => 'mbdb_editors' ),
				'public' => true, //false,
				'show_admin_column' => true,
									'show_in_quick_edit'	=> 	false,
				'update_count_callback' => '_update_post_term_count',
					'meta_box_cb' => 'post_categories_meta_box',
				'capabilities'	=> array(
					'manage_terms' => 'manage_categories',
					'edit_terms'   => 'manage_categories',
					'delete_terms' => 'manage_categories',
					'assign_terms' => 'manage_mbdb_books',				
				),
				'labels' => array(
					'name' => __( 'Editors', 'mooberry-book-manager' ),
					'singular_name' => __( 'Editor', 'mooberry-book-manager' ),
					'search_items' => __( 'Search Editors' , 'mooberry-book-manager' ),
					'all_items' =>  __( 'All Editors' , 'mooberry-book-manager' ),
					'parent_item' =>  __( 'Parent Editor' , 'mooberry-book-manager' ),
					'parent_item_colon' =>  __( 'Parent Editor:' , 'mooberry-book-manager' ),
					'edit_item' =>  __( 'Edit Editor' , 'mooberry-book-manager' ),
					'update_item' =>  __( 'Update Editor' , 'mooberry-book-manager' ),
					'add_new_item' =>  __( 'Add New Editor' , 'mooberry-book-manager' ),
					'new_item_name' =>  __( 'New Editor Name' , 'mooberry-book-manager' ),
					'menu_name' =>  __( 'Editors' , 'mooberry-book-manager' ),
					'popular_items' => __( 'Popular Editors', 'mooberry-book-manager' ),
					'separate_items_with_commas' => __( 'Separate Editors with commas', 'mooberry-book-manager' ),
					'add_or_remove_items' => __( 'Add or remove Editors', 'mooberry-book-manager' ),
					'choose_from_most_used' => __( 'Choose from the most used Editors', 'mooberry-book-manager' ),
					'not_found' => __( 'No Editors found', 'mooberry-book-manager' ),
					'items_list_navigation' => __( 'Edtior navigation', 'mooberry-book-manager' ),
					'items_list'            => __( 'Editor list', 'mooberry-book-manager' ),
				)
			)
		)
	);
		
	register_taxonomy( 'mbdb_illustrator', 'mbdb_book', apply_filters( 'mbdb_illustrator_taxonomy', array(
				//'rewrite' => false, 
				'rewrite' => array(	'slug' => 'mbdb_illustrators' ),
				'public' => true, //false,
				'show_admin_column' => true,
									'show_in_quick_edit'	=> 	false,
				'update_count_callback' => '_update_post_term_count',
					'meta_box_cb' => 'post_categories_meta_box',
				'capabilities'	=> array(
					'manage_terms' => 'manage_categories',
					'edit_terms'   => 'manage_categories',
					'delete_terms' => 'manage_categories',
					'assign_terms' => 'manage_mbdb_books',				
				),
				'labels' => array(
					'name' => __( 'Illustrators', 'mooberry-book-manager' ),
					'singular_name' => __( 'Illustrator', 'mooberry-book-manager' ),
					'search_items' => __( 'Search Illustrators' , 'mooberry-book-manager' ),
					'all_items' =>  __( 'All Illustrators' , 'mooberry-book-manager' ),
					'parent_item' =>  __( 'Parent Illustrator' , 'mooberry-book-manager' ),
					'parent_item_colon' =>  __( 'Parent Illustrator:' , 'mooberry-book-manager' ),
					'edit_item' =>  __( 'Edit Illustrator' , 'mooberry-book-manager' ),
					'update_item' =>  __( 'Update Illustrator' , 'mooberry-book-manager' ),
					'add_new_item' =>  __( 'Add New Illustrator' , 'mooberry-book-manager' ),
					'new_item_name' =>  __( 'New Illustrator Name' , 'mooberry-book-manager' ),
					'menu_name' =>  __( 'Illustrators' , 'mooberry-book-manager' ),
					'popular_items' => __( 'Popular Illustrators', 'mooberry-book-manager' ),
					'separate_items_with_commas' => __( 'Separate Illustrators with commas', 'mooberry-book-manager' ),
					'add_or_remove_items' => __( 'Add or remove Illustrators', 'mooberry-book-manager' ),
					'choose_from_most_used' => __( 'Choose from the most used Illustrators', 'mooberry-book-manager' ),
					'not_found' => __( 'No Illustrators found', 'mooberry-book-manager' ),
					'items_list_navigation' => __( 'Illustrator navigation', 'mooberry-book-manager' ),
					'items_list'            => __( 'Illustrator list', 'mooberry-book-manager' ),
				)
			)
		)
	);
		
	register_taxonomy( 'mbdb_cover_artist', 'mbdb_book', apply_filters( 'mbdb_cover_artist_taxonomy', array(
				//'rewrite' => false, 
				'rewrite' => array(	'slug' => 'mbdb_cover_artists' ),
				'public' => true, //false,
				'show_admin_column' => true,
									'show_in_quick_edit'	=> 	false,
				'update_count_callback' => '_update_post_term_count',
					'meta_box_cb' => 'post_categories_meta_box',
				'capabilities'	=> array(
					'manage_terms' => 'manage_categories',
					'edit_terms'   => 'manage_categories',
					'delete_terms' => 'manage_categories',
					'assign_terms' => 'manage_mbdb_books',				
				),
				'labels' => array(
					'name' => __( 'Cover Artists', 'mooberry-book-manager' ),
					'singular_name' => __( 'Cover Artist', 'mooberry-book-manager' ),
					'search_items' => __( 'Search Cover Artists' , 'mooberry-book-manager' ),
					'all_items' =>  __( 'All Cover Artists' , 'mooberry-book-manager' ),
					'parent_item' =>  __( 'Parent Cover Artist' , 'mooberry-book-manager' ),
					'parent_item_colon' =>  __( 'Parent Cover Artist:' , 'mooberry-book-manager' ),
					'edit_item' =>  __( 'Edit Cover Artist' , 'mooberry-book-manager' ),
					'update_item' =>  __( 'Update Cover Artist' , 'mooberry-book-manager' ),
					'add_new_item' =>  __( 'Add New Cover Artist' , 'mooberry-book-manager' ),
					'new_item_name' =>  __( 'New Cover Artist Name' , 'mooberry-book-manager' ),
					'menu_name' =>  __( 'Cover Artists' , 'mooberry-book-manager' ),
					'popular_items' => __( 'Popular Cover Artists', 'mooberry-book-manager' ),
					'separate_items_with_commas' => __( 'Separate Cover Artists with commas', 'mooberry-book-manager' ),
					'add_or_remove_items' => __( 'Add or remove Cover Artists', 'mooberry-book-manager' ),
					'choose_from_most_used' => __( 'Choose from the most used Cover Artists', 'mooberry-book-manager' ),
					'not_found' => __( 'No Cover Artists found', 'mooberry-book-manager' ),
					'items_list_navigation' => __( 'Cover Artist navigation', 'mooberry-book-manager' ),
					'items_list'            => __( 'Cover Artist list', 'mooberry-book-manager' ),
				)
			)
		)
	);
	
	
	// ************ term meta *********************************/
	if (  function_exists( 'get_term_meta' ) ) {
		$taxonomies = get_object_taxonomies( 'mbdb_book', 'objects' );
		foreach($taxonomies as $name => $taxonomy) {
		
			$pretty_name = str_replace('mbdb_', '', $name);
			
			$args = array(
				'sanitize_callback' => 'mbdb_sanitize_book_grid_description',
				'type' => 'string',
				'description' => 'This text will be displayed above the auto-generated book grid.',
				'single' => true,
				'show_in_rest' => true,
			);
		
			
			register_meta( 'term',  $name . '_book_grid_description', $args );
			// Pre-WordPress 4.6 compatibility
			if ( ! has_filter( 'sanitize_term_meta_' . $name . '_book_grid_description' ) ) {
				add_filter( 'sanitize_term_meta_' . $name . '_book_grid_description', 'mbdb_sanitize_book_grid_description', 10, 4 );
			}
			 
			 
			
			$args['description'] = 'This text will be displayed below the auto-generated book grid.';
			register_meta( 'term', $name . '_book_grid_description_bottom', $args );
			// Pre-WordPress 4.6 compatibility
			if ( ! has_filter( 'sanitize_term_meta_' . $name . '_book_grid_description_bottom' ) ) {
				add_filter( 'sanitize_term_meta_' . $name . '_book_grid_description_bottom', 'mbdb_sanitize_book_grid_description', 10, 4 );
			}
			    
			   
			if (in_array($name, mbdb_taxonomies_with_websites() ) ) {
				$args = array(
					'sanitize_callback' => 'mbdb_sanitize_url',
					'type' => 'string',
					'description' => 'The website for this ' . $pretty_name,
					'single' => true,
					'show_in_rest' => true,
				);
				register_meta( 'term', $name . '_website', $args);
				// Pre-WordPress 4.6 compatibility
				if ( ! has_filter( 'sanitize_term_meta_' . $name . '_website' ) ) {
					add_filter( 'sanitize_term_meta_' . $name . '_website', 'mbdb_sanitize_url', 10, 4 );
				}
					
			   
			}
		
			add_action(  $name . '_add_form_fields', 'mbdb_new_' . $pretty_name . '_grid_description_field' );
			add_action( 'edit_' . $name,   'mbdb_save_' . $pretty_name . '_book_grid_description' );
			add_action( 'create_' . $name, 'mbdb_save_' . $pretty_name . '_book_grid_description' );
			add_action( $name . '_edit_form_fields', 'mbdb_edit_' . $pretty_name . '_grid_description_field' );

			
			
			
		}
	}
}

function mbdb_sanitize_book_grid_description( $description) {
	return balanceTags(wp_kses_post($description), true);
}

function mbdb_sanitize_url( $url ) {
	//return esc_url($url);
	return $url;

}

/******************* end term meta ******************************************/

/**
 * Add Tax Grid Post
 *
 * add tax grid post if necessary
 * this is never seen and just needed for the series/tag/genre shortcut URLS (hack)
 *	
 * @access public
 * @since 1.0
 * @since 3.0 added [mbdb_tax_grid] shortcode
 *
 */
function mbdb_add_tax_grid() {

	$tax_grids = get_posts( array(
				'posts_per_page' => -1,
				'post_type' => 'mbdb_tax_grid',
				'post_status' => 'publish' 
				)
			);

	// if there's more than one already in the database, delete them all but one
	if ( count( $tax_grids > 1 ) ) {
		for ( $x=1; $x < count( $tax_grids ); $x++ ) {
			wp_delete_post( $tax_grids[ $x ]->ID, true );
		}
	}
	
	// if there aren't any, add one
	if ( count( $tax_grids ) == 0 ) { 
				$tax_grid_id = wp_insert_post( apply_filters( 'mbdb_insert_tax_grid_args', array(
						'post_title' => wp_title(),
						'post_type' => 'mbdb_tax_grid',
						'post_status' => 'publish',
						'name' => 'test',
						'comment_status' => 'closed',
						'post_content' => '[mbdb_tax_grid]',
						)
					)
				);
	} else {
		$tax_grid_id = $tax_grids[0]->ID;
	}
	
	// check that the tax grid has the short code
	$tax_grid = get_post( $tax_grid_id );
	if ( $tax_grid->post_content != '[mbdb_tax_grid]' ) {
		wp_update_post( array( 'ID' => $tax_grid->ID, 
								'post_content' => '[mbdb_tax_grid]' ) 
						);
	}
}
	