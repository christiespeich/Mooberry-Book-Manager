<?php

function mbdb_upgrade_versions() {
		
	
		
		$current_version = get_option(MBDB_PLUGIN_VERSION_KEY);
		
		if ($current_version == '') {
			$current_version = MBDB_PLUGIN_VERSION;
		}
		
		if (version_compare($current_version, '1.3.1', '<')) {
			// upgrade to 1.3 script
			// add new retailers
			mbdb_upgrade_to_1_3_1();
		} 
		
		if (version_compare($current_version, '2.0', '<')) {
			mbdb_upgrade_to_2_0();
						
		}
		
		if (version_compare($current_version, '2.0.1', '<')) {
			//flush the rules
			global $wp_rewrite;
			$wp_rewrite->flush_rules();
		}
		
		if (version_compare($current_version, '2.1', '<')) {
			mbdb_migrate_to_book_grid_height_defaults();
		}
	
		if (version_compare($current_version, '2.2', '<')) {
			// re-run the new retailer images to fix them
			mbdb_update_retailer_images();
			
		}	
		
		if (version_compare($current_version, '2.3', '<')) {
				//flush the rules
			global $wp_rewrite;
			$wp_rewrite->flush_rules();
		}
		
		if (version_compare($current_version, '2.4.3', '<')) {
			mbdb_upgrade_to_2_4_3();
		}
		
		if (version_compare($current_version, '3.0', '<')) {
			mbdb_upgrade_to_3_0();
		
		}
		
		if (version_compare($current_version, '3.1', '<')) {
			mbdb_upgrade_to_3_1($current_version);
		}
		
		if ($current_version != MBDB_PLUGIN_VERSION && version_compare(MBDB_PLUGIN_VERSION, '3.1.1', '=')) {
			mbdb_upgrade_to_3_1_1();
		}
		
		/*
		$m1 = __('You may choose to re-migrate your data from version 2 if you\'ve noticed issues with your books\' information.', 'mooberry-book-manager');
		$m4 = __('Changes you\'ve made since migrating may be lost.', 'mooberry-book-manager');
		$m2 = __('Migrate Data Now', 'mooberry-book-manager');
		$m3 = __('Dismiss Notice', 'mooberry-book-manager');
		$key = '3_1_remigrate';
		
		$message = $m1 . '<b>' . $m4 . '</b><p><a href="#" id="mbdb_3_1_remigrate" class="button">' . $m2 . '</a> <a href="#" class="button mbdb_admin_notice_dismiss" data-admin-notice="' . $key . '">' . $m3 . '</a></p>';
		mbdb_set_admin_notice($message, 'error', $key );
	*/
	
	update_option(MBDB_PLUGIN_VERSION_KEY, MBDB_PLUGIN_VERSION);
}


function mbdb_upgrade_to_1_3_1() {
	$default_retailers = array();
	$default_retailers[] = array('name' => 'Audible', 'uniqueID' => 6, 'image' => 'audible.png' );
	$default_retailers[] = array('name' => 'Book Baby', 'uniqueID' => 7, 'image' => 'bookbaby.gif' );
	$default_retailers[] = array('name' => 'Books A Million', 'uniqueID' => 8, 'image' => 'bam.png' );
	$default_retailers[] = array('name' => 'Create Space', 'uniqueID' => 9, 'image' => 'createspace.jpg' );
	$default_retailers[] = array('name' => 'Indie Bound', 'uniqueID' => 10, 'image' => 'indiebound.gif' );
	$default_retailers[] = array('name' => 'Powells', 'uniqueID' => 11, 'image' => 'powells.jpg' );
	$default_retailers[] = array('name' => 'Scribd', 'uniqueID' => 12, 'image' => 'scribd.jpg' );
	$default_retailers[] = array('name' => 'Amazon Kindle', 'uniqueID' => 13, 'image' => 'kindle.jpg' );
	$default_retailers[] = array('name' => 'Barnes and Noble Nook', 'uniqueID' => 14, 'image' => 'nook.png' );
	$mbdb_options = get_option( 'mbdb_options' );
	mbdb_insert_defaults( $default_retailers, 'retailers', $mbdb_options);
	update_option( 'mbdb_options',  $mbdb_options );
}
	


function mbdb_upgrade_to_2_0() {
	// set all pages with a book grid to NOT use the default values
	mbdb_migrate_to_book_grid_defaults();
	
	// set up roles
	mbdb_set_up_roles();
	
	// migrate post_tags to mbdb_tags
	mbdb_migrate_post_tags();
	
	// insert default edition formats
	$mbdb_options = get_option('mbdb_options');
	mbdb_insert_default_edition_formats($mbdb_options);
	update_option( 'mbdb_options',  $mbdb_options);
	
	//fix retailer array imageID = image_id
	//mbdb_fix_retailer_array();

		
	// update buy link images
	mbdb_update_retailer_images();
	
	// update format images
	mbdb_update_format_images();

	// update the excerpts
	$mbdb_books = mbdb_get_books_list('all', null, 'title', 'ASC', null, null, null);
	foreach($mbdb_books as $book) {
		mbdb_save_excerpt($book->ID, $book);
	}
	
	// migrate publishers to settings
	mbdb_migrate_publishers();
		
	// rewrite rules because new redirects added
	global $wp_rewrite;
	$wp_rewrite->flush_rules();
			
}

function mbdb_update_format_images() {
	$new_images = array();
	$new_images[] = array(	'uniqueID' => 1, 'image' => 'epub.png');
	$new_images[] = array('uniqueID' => 2, 'image' => 'amazon-kindle.jpg');
	$new_images[] = array('uniqueID' => 3, 'image' => 'pdficon.png');
					
	mbdb_update_images($new_images, 'formats');
}

function mbdb_update_retailer_images() {
	$new_images = array();
	$new_images[] = array('uniqueID' => 1, 'image' => 'amazon.png');
	$new_images[] = array('uniqueID' => 7, 'image' => 'bookbaby.gif');
	$new_images[] = array('uniqueID' => 2, 'image' => 'bn.jpg');
	$new_images[] = array('uniqueID' => 13, 'image' => 'kindle.png');
	$new_images[] = array('uniqueID' => 12, 'image' => 'scribd.png');
	$new_images[] = array('uniqueID' => 9, 'image' => 'createspace.png');
	$new_images[] = array('uniqueID' => 14, 'image' => 'nook.png');
	$new_images[] = array('uniqueID' => 6, 'image' => 'audible.png');
	mbdb_update_images($new_images, 'retailers');
}

function mbdb_update_images($new_images, $options_name) {
		
	
	$mbdb_options = get_option('mbdb_options');
	$options = $mbdb_options[$options_name];
	foreach ($new_images as $image) {
		// find the retailer that matches the uniqueID
		for($x=0; $x<count($options); $x++) {
			if ($options[$x]['uniqueID'] == $image['uniqueID']) {
			
				// save the original attachID
				$old_attachID = $options[$x]['imageID'];
				// delete the original image
				wp_delete_attachment($old_attachID, true);
				// upload the new image
				$new_attachID = mbdb_upload_image($image['image']);

				if ($new_attachID != 0) {
					// if the upload succeeded
					// update the attach id
					$options[$x]['imageID'] = $new_attachID;
					// update the image
					$img = wp_get_attachment_url( $new_attachID );
					
					$options[$x]['image'] = $img;
					
				} else {
					// error message?
					
				}
				// item has been found, break out of loop
				break;
			}
		}
	}
	
	$mbdb_options[$options_name] = $options;
	// update the options with the new retailers
	update_option('mbdb_options', $mbdb_options);
}

function mbdb_migrate_post_tags() {
		
	//loop through all terms in post_tags
	$post_tags = get_terms('post_tag');
	foreach($post_tags as $tag) {
		// get all objects in each term
		$tagged_posts = get_objects_in_term((int) $tag->term_id, 'post_tag');
		// loop through the objects
		foreach($tagged_posts as $tagged_post) {
			// if one is a book
			if (get_post_type($tagged_post) == 'mbdb_book') {
				// add the term to mbdb_tags
				// if term has already been added, get the ID
				$new_term = term_exists($tag->name, 'mbdb_tag');
				// otherwise insert it
				if ($new_term == 0 || $new_term == null) {					
					$new_term = wp_insert_term($tag->name, 'mbdb_tag', array(
									'description' => $tag->description,
									'slug'	=>	$tag->slug)	);
				}
			
				// add the object to mbdb_tags term
				wp_set_object_terms($tagged_post, (int) $new_term['term_id'], 'mbdb_tag', true);
			}
		}
	}

	// remove post_tag terms from books
	// do this outside of the above loop because it will remove ALL tags from the books
	// and the above loop handles one tag at a time
	 $mbdb_books = mbdb_get_books_list( 'all', null, 'title', 'ASC', null, null, null );
	foreach($mbdb_books as $mbdb_book) {
		$bookID = $mbdb_book->ID;
		wp_delete_object_term_relationships( $bookID, 'post_tag' );
	}
}

function mbdb_migrate_to_book_grid_defaults() {
	$grid_pages = get_posts(array(
								'posts_per_page' => -1,
								'post_type' => 'page',
								'meta_query'	=>	array(
										array(
											'key'	=>	'_mbdb_book_grid_display',
											'value'	=>	'yes',
											'compare'	=>	'=',
										),
									),	
							)
					);
	foreach($grid_pages as $page) {
		//update_post_meta($page->ID, '_mbdb_book_grid_cover_height_default', 'no');
		update_post_meta($page->ID, '_mbdb_book_grid_books_across_default', 'no');
	}
	wp_reset_postdata();
					
	// set the default values
	$mbdb_options = get_option('mbdb_options');
	
	//if (!isset($mbdb_options['mbdb_default_cover_height'])) {
	//	$mbdb_options['mbdb_default_cover_height'] = 200;
	//}
	if (!isset($mbdb_options['mbdb_default_books_across'])) {
		$mbdb_options['mbdb_default_books_across'] = 3;
	}
	
	update_option('mbdb_options', $mbdb_options);
	
}

/* function mbdb_fix_retailer_array() {
	// at some point there was a typo in the upload_image function
	// and some retailers have image_id in the array and others
	// have imageID
	// this function adds image_id to any ones that have imageID
	// going forward, image_id will be used, not imageID
	$mbdb_options = get_option('mbdb_options');
	$retailers = $mbdb_options['retailers'];
	for($x=0; $x<count($retailers); $x++) {
		if (array_key_exists('imageID', $retailers[$x])) {
			$retailers[$x]['image_id'] = $retailers[$x]['imageID'];
		}
	}
	$mbdb_options['retailers'] = $retailers;
	update_option('mbdb_options', $mbdb_options);
}
 */
function mbdb_migrate_publishers() {
	$mbdb_options = get_option('mbdb_options');
	if (array_key_exists('publishers', $mbdb_options)) {
		$publishers = $mbdb_options['publishers'];
	} else {
		$publishers = array();
	}
	
	$mbdb_books = mbdb_get_books_list('all', null, 'title', 'ASC', null, null, null);
	foreach ($mbdb_books as $book) {
		$book_publisher = get_post_meta($book->ID, '_mbdb_publisher', true);
		$book_website = get_post_meta($book->ID, '_mbdb_publisherwebsite', true);
		if ($book_publisher != '') {
			// see if publisher is already in options
			$flag = '';
			foreach ($publishers as $publisher) {
				if ($publisher['name'] == $book_publisher) {
					$flag = $publisher['uniqueID'];
					break;		
				}
			}
			
			// if not found, add it to the options
			// save publisherID to book
			if ($flag == '') {
				$flag = mbdb_uniqueID_generator('');
				$publishers[] = array ('name' => $book_publisher, 'website' => $book_website, 'uniqueID' => $flag );	
			}
			update_post_meta($book->ID, '_mbdb_publisherID', $flag);
		}
	}
	// update options		
	$mbdb_options['publishers'] = $publishers;
	update_option('mbdb_options', $mbdb_options);
}

function mbdb_migrate_to_book_grid_height_defaults() {
	
	// set the default values
	$mbdb_options = get_option('mbdb_options');
	
	if (!isset($mbdb_options['mbdb_default_cover_height'])) {
		$mbdb_options['mbdb_default_cover_height'] = 200;
	}
	
	update_option('mbdb_options', $mbdb_options);
	
	$grid_pages = get_posts(array(
								'posts_per_page' => -1,
								'post_type' => 'page',
								'meta_query'	=>	array(
										array(
											'key'	=>	'_mbdb_book_grid_display',
											'value'	=>	'yes',
											'compare'	=>	'=',
										),
									),	
							)
					);
	foreach($grid_pages as $page) {
		update_post_meta($page->ID, '_mbdb_book_grid_cover_height_default', 'no');
		$current_height = get_post_meta($page->ID, '_mbdb_book_grid_cover_height', true);
		if ($current_height == '') {
			update_post_meta($page->ID, '_mbdb_book_grid_cover_height', $mbdb_options['mbdb_default_cover_height']);
		}
	}
	wp_reset_postdata();	
}

function mbdb_upgrade_to_2_4_3() {
// 4. update all books to remove book short code
	
	// get all posts of type mbdb_book
	$books = get_posts(array('posts_per_page' => -1, 'post_type' => 'mbdb_book'));
	
	// unhook this function because wp_update_post will call it
	remove_action( 'save_post_mbdb_book', 'mbdb_save_book' );
	
	foreach($books as $book) {
		// update the post, which calls save_post again
		wp_update_post( array( 'ID' => $book->ID, 'post_content' => '') );
	}
	
	// re-hook this function
	add_action( 'save_post_mbdb_book', 'mbdb_save_book' );		
}


function mbdb_upgrade_to_3_0() {
	// 0. CREATE TABLE 
	MBDB()->books->create_table();
	
	/*
	// 1. IMPORT BOOK DATA
	// this needs to run just one time
	$import_books = get_option('mbdb_import_books');
	if (!$import_books || $import_books == null) {
		$success = MBDB()->books->import();
		if ($success === true) {
			update_option('mbdb_import_books', true);
		} else {
			global $wpdb;
			update_option('mbdb_error', $wpdb->last_error);
			add_action( 'admin_notices', 'mbdb_admin_notice_db_error' );
			
		}
	}
	*/
	
	// 2. UPDATE GRID OPTIONS
	// loop through all the pages with a book grid
	$grid_pages = get_posts(array(
								'posts_per_page' => -1,
								'post_type' => 'page',
								'meta_query'	=>	array(
										array(
											'key'	=>	'_mbdb_book_grid_display',
											'value'	=>	'yes',
											'compare'	=>	'=',
										),
									),	
							)
					);
	foreach($grid_pages as $page) {
	
		// group_by => level 1
		$level1 = get_post_meta($page->ID, '_mbdb_book_grid_group_by', true);
		if (!$level1) {
			update_post_meta($page->ID, '_mbdb_book_grid_group_by_level_1', 'none');
		} else {
			update_post_meta($page->ID, '_mbdb_book_grid_group_by_level_1', $level1 );
		}
		
		// genre_group_by => level 2
		// tag_group_by => level 2
		// else level 2 => none
		if ($level1 == 'tag' || $level1 == 'genre') {
			$level2 = get_post_meta($page->ID, '_mbdb_book_grid_' . $level1 . '_group_by', true);
			update_post_meta($page->ID, '_mbdb_book_grid_group_by_level_2', $level2);
		} else {
			update_post_meta($page->ID, '_mbdb_book_grid_group_by_level_2', 'none' );
			$level2 = null;
		}
			
		// genre_tag_group_by => level 3
		// tag_genre_group_by => level 3
		// level 4 => none
		// else level 3 => none
		if ($level2 == 'tag' || $level2 == 'genre') {
			$level3 = get_post_meta($page->ID, '_mbdb_book_grid_' . $level1 . '_' . $level2 . '_group_by', true);
			update_post_meta($page->ID, '_mbdb_book_grid_group_by_level_3', $level3);
			update_post_meta($page->ID, '_mbdb_book_grid_group_by_level_4', 'none');
		} else {
			update_post_meta($page->ID, '_mbdb_book_grid_group_by_level_3', 'none');
		}
	}
	
	// 3. SET DEFAULT OPTIONS FOR GRID SLUGS
	mbdb_set_default_tax_grid_slugs();
	
	

	// 4. update all books to have book short code
	
	// get all posts of type mbdb_book
	$books = get_posts(array('posts_per_page' => -1, 'post_type' => 'mbdb_book'));
	
	// unhook this function because wp_update_post will call it
	remove_action( 'save_post_mbdb_book', 'mbdb_save_book' );
	
	foreach($books as $book) {
		// update the post, which calls save_post again
		wp_update_post( array( 'ID' => $book->ID, 'post_content' => '[mbdb_book]') );
	}
	
	// re-hook this function
	add_action( 'save_post_mbdb_book', 'mbdb_save_book' );	
	
	// 5. INSERT DEFAULT SOCIAL MEDIA SITES
	$mbdb_options = get_option('mbdb_options');
	mbdb_insert_default_social_media( $mbdb_options );
	update_option('mbdb_options', $mbdb_options );

	flush_rewrite_rules();
	wp_reset_postdata();
}

function mbdb_upgrade_to_3_1($current_version) {
	
	// force re-importing if on multisite OR if coming from a version < 3.0
	if ( is_multisite()  ||  version_compare($current_version, '3.0', '<') ) {
		
		// truncate the table 
		MBDB()->books->empty_table();

		// re-import the data
		update_option('mbdb_import_books', false);
		
		// only need to migrate if there are books
			$args = array('posts_per_page' => -1,
						'post_type' => 'mbdb_book',
			);
			
			$posts = get_posts( $args  );
			
			if (count($posts) > 0) {
				
				
				$m = __('Upgrading to Mooberry Book Manager version 3.1 requires some data migration before Mooberry Book Manager will operate properly.', 'mooberry-book-manager');
				$m3 = __('Even if you have previously migrated the data (if you upgraded to version 3.0 and then rolled back to version 2.4.x), you must migrate again to ensure Mooberry Book Manaer will operate properly.');
				$m4 = __('Changes made since migrating the first time may be lost.', 'mooberry-book-manager');
				$m2 = __('Migrate Data Now', 'mooberry-book-manager');
				$message = $m . '<p>' . $m3 . '<i>' . $m4 . '</i></p><p><a href="#" id="mbdb_3_1_remigrate" class="button">' . $m2 . '</a></p>';
				
				
				mbdb_set_admin_notice($message, 'error', '3_1_migrate');
				
				
			} else {
				update_option('mbdb_import_books', true);
			}
			wp_reset_postdata();
			
	}	else {
		// user is NOT on multisite AND is NOT coming from a pre-3.0 version
		
		// if fresh 3.1 install, no need to import
		if ($current_version == MBDB_PLUGIN_VERSION) {
			update_option('mbdb_import_books', true);
		} else {
			// if coming from 3.0.x, give option to re-import
			$m1 = __('You may choose to re-migrate your data from version 2 if you\'ve noticed issues with your books\' information.', 'mooberry-book-manager');
			$m4 = __('Changes you\'ve made since migrating may be lost.', 'mooberry-book-manager');
			$m2 = __('Migrate Data Now', 'mooberry-book-manager');
			$m3 = __('Dismiss Notice', 'mooberry-book-manager');
			$key = '3_1_remigrate';
			
			$message = $m1 . '<br><b>' . $m4 . '</b><p><a href="#" id="mbdb_3_1_remigrate" class="button">' . $m2 . '</a> <a href="#" class="button mbdb_admin_notice_dismiss" data-admin-notice="' . $key . '">' . $m3 . '</a></p>';
			mbdb_set_admin_notice($message, 'error', $key );
			
		}
		
	}
	
	
	/*
	// how will the blog id column get populated if the data isn't remigrated?!
	// the table will only be not remigrated if not multisite, so just add the blog id?
	if (!is_multisite()) {
		global $blog_id;
		$wpdb->query( "UPDATE {$wpdb->base_prefix}mbdb_books SET blog_id = {$blog_id}" );
		
	}
*/

	// make sure short code is set
	// get all posts of type mbdb_book
	$books = get_posts(array('posts_per_page' => -1, 'post_type' => 'mbdb_book'));
	
	// unhook this function because wp_update_post will call it
	remove_action( 'save_post_mbdb_book', 'mbdb_save_book' );
	
	foreach($books as $post) {
		
		wp_update_post( array( 'ID' => $post->ID, 'post_content' => '[mbdb_book]') );
	}
	
	// re-hook this function
	add_action( 'save_post_mbdb_book', 'mbdb_save_book' );	
	
	
	// update book grid setting _mbdb_book_grid_custom_select =>
	// _mbdb_book_grid_custom
	$pages = get_posts(array('posts_per_page' => -1, 'post_type' => 'page', 'meta_key' => '_mbdb_book_grid_custom_select'));
	foreach ($pages as $page) {
		$value = get_post_meta($page->ID, '_mbdb_book_grid_custom_select', true);
		update_post_meta($page->ID, '_mbdb_book_grid_custom', $value);
	}
	wp_reset_postdata();
}


// only local and Tyler Tork need this update. TT does not use multisite.
function mbdb_upgrade_to_3_1_1() {
	
	global $wpdb;
	// drop the original primary key if it exsists
	$results = $wpdb->query("SHOW TABLES LIKE '{$wpdb->prefix}mbdb_books'");
	if ($results == 1) {
		$wpdb->query( "ALTER TABLE {$wpdb->prefix}mbdb_books DROP PRIMARY KEY" );
	}
	// alter/create the table
	MBDB()->books->create_table();
	
	
}


