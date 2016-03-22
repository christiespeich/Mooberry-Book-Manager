# Mooberry Book Manager
**Contributors:** mooberrydreams  
**Tags:** book, author, publisher, writer, books, writing, publishing, authors   
**Requires at least:** 3.8.0  
**Tested up to:** 4.3.1   
**Stable tag:** 2.4.2  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

## Description
An easy-to-use system for authors. Add your new book to your site in less than 5 minutes.


No coding is necessary to use Mooberry Book Manager. Adding books is as easy as filling out a form. Include as much or as little information as you want, including the book cover, links to purchase the book, reviews of the book, an excerpt, and more.

Mooberry Book Manager will create a page for each book, ensuring a consistent look on all of your pages.

Organize your book into grids with just a few clicks. Grids can include all of your books or a selection of books, and you choose how they are ordered. You can create multiple grids; for example, an "Available Now" page and a "Coming Soon" page. Grids update automatically when you edit or add books. Set it and forget it!

Feature books on your sidebar with four options:
* A random book  
* The newest book  
* A book that's coming soon  
* A specific book  
	
Mooberry Book Manager works with your chosen theme to provide a consistent look throughout your website.

Requires Wordpress 3.8. The admin screens (for creating books, etc.) require Javascript, but the public pages do not.

##Getting Started with Mooberry Book Manager  
After installing and activating Mooberry Book Manager, you'll now have a Books menu.  Use that to add your books to your website.

In order for your books to appear on your website, you need to add a Book Grid and/or a Widget.

To Add a Book Grid:  
* Create a new Page or edit an existing one  
* Scroll down to the Book Grid Settings section  
* Choose the books, grouping, and sort order  
* Save the page and view it. Click on any book cover to get details of the book  

To Add a Widget:  
* Go to Appearance -> Widget  
* Drag the Mooberry Book Manager Book Widget to the Widget Area of your choice  
* Choose the book to display  
* Save the widget and view your website. Click on the book cover to get the details of the book.  

Additonal questions?  
Download the [User Manual](http://www.mooberrydreams.com/support/mooberry-book-manager-support/) (PDF format)

Want regular updates? 
* Like Mooberry Dreams on Facebook: https://www.facebook.com/MooberryDreams
* Follow Mooberry Dreams on Twitter: https://twitter.com/MooberryDreams
* Check out the blog: http://www.mooberrydreams.com/blog
* Subscribe to Mooberry Dreams' mailing list: http://www.mooberrydreams.com/products/mooberry-book-manager/




## Changelog
#### 3.0 
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

#### 2.4  
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
* Flushes rewrite rules  

#### 2.0 
* New: Added General Settings, Publisher Settings, and Edition Formats Settings
* New: Added Editions to book pages 
* New: Added Librarian and Master Librarian Roles
* New: Added French translation
* Improved: Redesigned layout of Book Page
* Improved: Blank data defaults and labels no longer display on Book Page
* Improved: Added Comments support to books
* Fixed: Moved tags to new custom taxonomy instead of using post tags.

For more revision history, see changelog.md

