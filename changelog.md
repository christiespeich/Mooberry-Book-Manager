#### 4.0 
* Fixed: Edition size (width/height) no longer rounds to nearest whole number
* Added: Ability to import and export books (for backup and site migration purposes)
* Added: Open-Graph meta tags compatible with FB for sharing. Adds book cover, title,  summary, and ISBN. 
* Added: Twitter Card meta tags to enhance sharing on Twitter. Adds book cover, title, and summary.
* Added: Meta description tag with series, genre, purchase links, and summary info added to single book page.
* Added: Ability to override open-graph, twitter card, and meta description tags set by Yoast's WP SEO on single book pages
* Added: Schema meta tags for type Book to enhance SEO on single book pages and book grids. Adds title, cover image, publication date (single book page only), and genre (single book page only).
* Updated: Taxonomy Grids changed to a shortcode on a specific page to increase compatibility with themes
* Updated: On book grids, uses smallest image size needed to accomodate size chosen for grid to retrieve cover image
* Updated: re-written code base, improved cachin

#### 3.5.16 
* Fixed: Adds page class to tax grid pages to better fit with themes

#### 3.5.15 
* Fixed: handle splitting excerpts even if no paragraph tags exists
* Updated: Change label to "Available on" for books that aren't published yet
* Updated: Use scrolling boxes for all multichecks

#### 3.5.14  
* Fixed: Width/Height in Editions no longer rounds to nearest whole number
* Updated: Kindle Live Preview now uses ASIN instead of iframe code

#### 3.5.13 
* Fixed: Publisher, Illustrator, Cover Artist, Editor, and Review website links open new browser tab
* Fixed: Illustrator permissions

#### 3.5.12 
* Updated: Put a note to not use currency symbol in price
* Fixed: Do an extra check on edition's format before printing out

#### 3.5.11  
* Fixed: Bug about missing argument in Advanced Widgets
* Fixed: Bugs wth Add Book Grid button
* Updated: Added Add Book Grid button to summaries, excerpts, additional info on book pages
* Fixed: Don't cache random widgets

#### 3.5.10 
* Changed: Excerpt removed from search

#### 3.5.9 
* Updated: compatibility for upcoming version
* Added: finer tuning of taxonomy capabilties
* Added: Jr Librarian role, correlates to Contributor WP role
* Added: MBM Admin role

#### 3.5.8 
* Fixed: Problem with GeneratePress theme and Cover Artist/Illustrator/Editor websites
* Updated: Updated book grid filters

#### 3.5.7 
* Fixed: Improved styling around "Available on" verbage on Kindle image

#### 3.5.6 
* Updated: New Amazon and Kindle images to comply with Amazon's trademark usage policies
* Updated: added "Available on" verbage above Kindle image to comply with Amazon's trademakr usage policies
* Updated: License checker for extensions (per Easy Digital Downloads update)
* Fixed: errors with Add Book Grid popup when there are multiple on the page (ie the book edit page)
* Fixed: translation issues on book grids groupe dby taxonomy
* Fixed: Ensure "Download Now", "Editions", and "Reviews" don't display if there is no data

#### 3.5.5 
* Updated: Users in Author Wordpress role can now add new genres, tags, series, editors, cover artists, and illustrators

#### 3.5.4 
* Fixed: Book Grid can now be used on book pages
* Fixed: When a search is done, the proper template is used when a book is on of the results
* Updated: Wordpress 4.7 compatibility

#### 3.5.3 
* Fixed: bug when series order was not a whole number

#### 3.5.2 
* Fixed: Subtitles can now be 255 characters
* Fixed: Compatible with earlier versions of PHP
* Fixed: translations added for book grid headings for genre, series, tags, etc.
* Updated: changed book grid headings for genre, series, tags, etc. for easier translation

#### 3.5.1 
* Fixed: translations not loading

#### 3.5 
* Improved: Better affiliate handling -- now set affiliate codes on the Retailers page
* Improved: Added publisher column to searches
* Added: Ability to use Kindle Live Preview for excerpt
* Fixed: process shortcodes in excerpts too

#### 3.4.13 
* Fixed: Error with Bulk Edit that was overwriting Publisher
* Fixed: Select/Deselect All Books button on Book Grids was not working with custom sort
* Fixed: Select/Deselect All buttons on Book Grids were not enabling/disabling Preview button

#### 3.4.12 
* Fixed: error with genres, series, tags, editors, illustrators, and cover artsts appearing as numbers (load any page in the Dashboard to see a notice about running a fix)

#### 3.4.11 
* Fixed: Custom Sort fixed
* Improved: Genres, series, tags, editors, illustrators, and cover artist interface improved: now uses checkboxes

#### 3.4.10 
* Fixed: Displays price with comma if locale specifies to do so

#### 3.4.9 
* Updated: Updates for Wordpress 4.6
* Fixed: Allow price to be entered with commas (ie 1,99 vs 1.99)
* Updated: New retailer logo images
* Fixed: Standalones => Stand-Alone Books

#### 3.4.8 
* Fixed: error with caching that would eventually cause an out of memory error

#### 3.4.7 
* Fixed: Ignore all object caching with Supercache is installed. (supercache still considers memcache use experimental)
* Fixed: Tags no longer displayed as bold on book page

#### 3.4.6 
* Fixed: Price no longer rounds to nearest whole number
* Updated: language files

#### 3.4.5 =
* Fixed: No more double-outputting the Additional Content under book grids
* Fixed: broken styling on editing pages

#### 3.4.4 =
* Fixed: Uses smaller image sizes when displaying book covers
* Fixed: Fixed bug when adding License Keys menu page in multisite

#### 3.4.3 =
* Fixed: table prefix

#### 3.4.2 =
* Fixed: Moved book grid migration to a separate page instead of auto-running on update

#### 3.4.1 =
* Fixed: AJAX needed for updating... but prevent updating from running more than once

#### 3.4 = 
* Added: Book Grid Shortcodes. See the [documentation](http://www.bookmanager.mooberrydreams.com/docs/category/mooberry-book-manager/book-grids/) for details
* Fixed: translation errors
* Fixed: ignore AJAX on some init functions

#### 3.3.8 =
* Fixed: translation errors

#### 3.3.7 =
* Fixed: translation errors
* Fixed: Bug adding slashes to quotes/apostrophes to summary, excerpt, and additional info after adding iframe support

#### 3.3.6 =
* Fixed: Allow iframes in summary, excerpt, and additional info

#### 3.3.5 =
* Fixed: version number

#### 3.3.4 =
* Fixed: Translation error on editions pricing
* Fixed: always show Activate button on extensions page unless deactivate button is displayed

#### 3.3.3 =
* Fixed: Added shortcode support to Additional Information

#### 3.3.2 =
* Fixed: Translation fixes

#### 3.3.1 =
* Fixed: Bug with custom sort

#### 3.3 =
* Added: Ability to custom sort book grids (choose Select Books and Group By None and Sort By Custom) See the [documentation](http://www.bookmanager.mooberrydreams.com/docs/category/mooberry-book-manager/book-grids/) for details
* Added: Quick/Bulk Edit fields to update publisher, release date, series order, subtitle, and Goodreads link
* Added: Ability to add website for Cover Artists, Editors, and Illustrators (WordPress 4.4+ required)

#### 3.2.1 =
* Fixed: Bugs in extension licensing support

#### 3.2 =
* Fixed: Internationalization errors
* Fixed: Added spans and CSS classes around book details
* Fixed: Sort by series order if on series tax grid
* Fixed: Uses SSL for Goodreads and Coming Soon images if necessary
* Improved: Added color to better separate sections of admin screens
* Added: Support for extension licensing 
* Added: Support for Advanced Widgets


#### 3.1.2 =
* Added: Nigerian Naira currency
* Fixed: Translations for Editions and Publication Date
* Fixed: Search error


#### 3.1 =
* Fixed: Now compatible with Multisite, including Network Activation
* Fixed: Migration problems from version 3.0
* Fixed: Problems with W3 Total Cache (see **Other Notes** section for more info about using caching plugins with MBM)
* Fixed: Not pulling grid default cover height from settings, always defaulting to 200
* Improved: For new insallations, activation process simplified and sped up (images no longer uploaded to Media Library)
* Added: Russian Ruble currency
* Removed: unused images
* Wordpress 4.5 Compatibility


#### 3.0 =
* Added: Book Grids can now be filtered and grouped by Editors, Illustrators, Cover Artists, and Publishers
* Added: Book Grids can now be grouped by several levels
* Added: Additional pretty links, eg /book/tag/{some-tag} which lists all matching books but display the full book page not a grid
* Added: Ability to update/change "Add to Goodreads" and "Coming Soon" book cover images (on settings page)
* Added: Custom text above and below auto-generated Book Grids (Wordpress 4.4+ only)
* Added: Tip Jar
* Added: Greek Translation
* Added: Indian Rupees (INR/₹) to currency dropdown options
* Improved: Book Cover metabox moved above taxonomies, book info, and buy/download links metaboxes for greater visibility
* Improved: Book Pages and auto-generated Book Grids now created using a shortcode, which should allow them to play nicely with more themes
* Improved: Most book data is now stored in a custom table, allowing for faster and more complex grids
* Improved: URLs for auto-generated Book Grids can now be customized in settings
* Improved: Settings Page moved to main Admin menu for greater visibility and tabs are now sub-pages of the Settings menu
* Improved: New Page Settings sub-page to better categorize settings
* Improved: 2nd set of retailer/download links only displays if excerpt is more than 1500 characters AND excerpt is expanded
* Improved: User interface and instructions/descriptions of fields updated/added
* Improved: "Published on" date now uses the format set in Wordpress General settings
* Improved: Browser tab title is now the title of grid when viewing an auto-generated grid
* Improved: Accessibility improved by all images now using ALT tags set by user OR a default ALT tag in case the user has not set one.
* Improved: Reordered metaboxes on Book Edit page
* Improved: Added filter to move Yoast SEO metabox lower on the Edit Book page
* Improved: Formats & Editions box defaults to closed on Book Edit page. Entering all this info looks intimidating and probably 95% of authors don't want/need this info on the website anyway.
* Improved: Added up/down buttons on repeated fields on Edit Book page to allow re-ordering (reviews, retailer links, download links, and editions)



#### 2.4.2 
* Fixed: Switched from site_url to home_url to create series grid link on book page (caused a problem when Wordpress is installed in a different directory than web URL)
* Improved: Reduced size of included images. Hopefully will help 500 errors on activation.


#### 2.4.1 
* Fixed: Error about non-object sometimes happening (grid styles)


= 2.4 
* Added: South African Rand (ZAR/R) to currency dropdown options
* Added: Ability to update the user who created the book (may need to enable "author" in the Screen Options)
* Updated: Compliant with Wordpress 4.4 update


#### 2.3 
* New: Added Russian translation
* New: Added Italian translation
* New: Buy Links on the bottom of the book page are now hidden if the excerpt is collapsed
* New: Added Editors, Illustrators, and Cover Artists
* Fixed: Remove roles when uninstalled


#### 2.2 
* New: Added Spanish translation
* Fixed: Compliant with Wordpress 4.3 (and works for < Wordpress 4.3)
* Fixed: Retailer images missing since version 2.0 restored
* Fixed: Improved translation string for Series and Genre grids for more flexibility


#### 2.1 
* New: Book Grids are now responsive
* New: Added Serbo–Croatian translation
* New: Choose which theme template to use for your book pages
* Improved: Filter Book Grids by tag
* Improved: Group Book Grids by tag
* Changed: Book Grids cover height configurable, instead of number of book per row


#### 2.0.1 
* Fixed: Flushes rewrite rules

#### 2.0 
* New: Added General Settings, Publisher Settings, and Edition Formats Settings
* New: Added Editions to book pages 
* New: Added Librarian and Master Librarian Roles
* New: Added French translation
* Improved: Redesigned layout of Book Page
* Improved: Blank data defaults and labels no longer display on Book Page
* Improved: Added Comments support to books
* Fixed: Moved tags to new custom taxonomy instead of using post tags.

#### 1.3.2   
* Added 9 new retailers   
* Escapes add_query_vars for safety   

#### 1.2 
* Fixes bugs introduced in 1.1 (oops!)

#### 1.1 
* Added support for language translations   
* Added German translation  
* Fixed issues resulting from Customizr theme  
* Fixed bug with book drop down in widget  

#### 1.0 
* Initial Release

#### 0.2.4 
* Final update before first public release
* Removed update checker
* Misc bugs
* Upgraded CMB2 to 2.0.5
* Fixed issue with taxonomy grids and non-permalinks
* removed wysiwyg on reviews
* fixed buy links/download links to only show the appropriate ones
* Added About Mooberry Dreams box

#### 0.2.3

* Small fixes/typos

#### 0.2.2

* Shows appropriate label for Download vs Buy links

#### 0.2.1 

* Added Download Links to page layout

#### 0.2 

* Book Grid interface updated to accomodate Mutli-Author add-on
* NOT backwards-compatible. All pages/posts with a Book Grid will need to be updated.

#### 0.1.2

* Fixed genre spacing issue
* Added subtitle to page layout
* Changed "Order Now" to "Buy Now"
* Updated grid screenshot

#### 0.1.1
* Fixes bug with excerpt not showing up

#### 0.1 
* Initial Release
