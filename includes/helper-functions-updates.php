<?php

function mbdb_upgrade_versions() {
		
	
		
		$current_version = get_option(MBDB_PLUGIN_VERSION_KEY);
		
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
	
		
		
		// update database to the new version
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
	$mbdb_books = mbdb_get_books_list('all', null, 'title', 'ASC', null, null);
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
				// upload the new image
				$new_attachID = mbdb_upload_image($image['image']);
				if ($new_attachID != 0) {
					// if the upload succeeded
					// update the attach id
					$options[$x]['imageID'] = $new_attachID;
					// update the image
					$img = wp_get_attachment_url( $new_attachID );
					$options[$x]['image'] = $img;
					// delete the original image
					wp_delete_attachment($old_attachID, true);
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
	 $mbdb_books = mbdb_get_books_list( 'all', null, 'title', 'ASC', null, null );
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
	
	$mbdb_books = mbdb_get_books_list('all', null, 'title', 'ASC', null, null);
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
		//update_post_meta($page->ID, '_mbdb_book_grid_books_across_default', 'no');
	}
	wp_reset_postdata();
					
	// set the default values
	$mbdb_options = get_option('mbdb_options');
	
	if (!isset($mbdb_options['mbdb_default_cover_height'])) {
		$mbdb_options['mbdb_default_cover_height'] = 200;
	}
	// if (!isset($mbdb_options['mbdb_default_books_across'])) {
		// $mbdb_options['mbdb_default_books_across'] = 3;
	// }
	
	update_option('mbdb_options', $mbdb_options);
	
}