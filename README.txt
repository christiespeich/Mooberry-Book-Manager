=== Mooberry Book Manager ===
Contributors: mooberrydreams
Donate link: https://www.paypal.me/mooberrydreams/
Tags: book, author, writer, writing, author website management
Requires at least: 3.8.0
Tested up to: 6.7
Stable tag: 4.16.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Sell books via Amazon and other retailers directly from your author website with this easy-to-use system. Creates book pages, widgets, and book grids.

== Description ==

Mooberry Book Manager is an easy-to-use system for authors to add books to their Wordpress websites.

**NOTE:** [Please read this information about updating to version 5 of Mooberry Book Manager and consider installing version 5 now.](https://www.mooberrybookmanager.com/upgrading-to-mooberry-book-manager-5/)

**No coding** is necessary to use Mooberry Book Manager. Adding books is as easy as filling out a form. Include as much or as little information as you want, including the book cover, links to purchase the book, reviews of the book, an excerpt, and more.

Each book can be linked to as many book store retailers to you want. You can even use your affiliate links!

Mooberry Book Manager will create a page for each book, ensuring a consistent look on all of your pages.

Organize your book into grids with just a few clicks. Grids can include all of your books or a selection of books, and you choose how they are ordered. You can create multiple grids; for example, an "Available Now" page and a "Coming Soon" page. Grids update automatically when you edit or add books. Set it and forget it!

Feature books on your sidebar with four options:

* A random book
* The newest book
* A book that's coming soon
* A specific book

Mooberry Book Manager works with your chosen theme to provide a consistent look throughout your website.

Requires Wordpress 3.8+ and Javascript.


**Getting Started with Mooberry Book Manager**

After installing and activating Mooberry Book Manager, you'll now have a Books menu.  Use that to add your books to your website.

In order for your books to appear on your website, you need to add a Book Grid and/or a Widget.

*To Add a Book Grid:*

* Go to Book Grids -> Add New
* Give the Book Grid a name to help you remember what kind of grid it is
* Choose the books, grouping, and sort order
* Save the grid. You will be shown a shortcode.  Copy the entire shortcode.
* On any page, blog post, etc. paste the shortcode into the text editor.
* Save your page and view it. Click on any book to see the details of the book.


*To Add a Widget:*

* Go to Appearance -> Widget
* Drag the Mooberry Book Manager Book Widget to the Widget Area of your choice
* Choose the book to display
* Save the widget and view your website. Click on the book cover to get the details of the book.

*Need more?*

Premium features are available, from adding multiple authors to creating your own fields to advanced grid filtering and pagination, and more! [See the details here.](https://www.mooberrybookmanager.com/pricing/)

*Additonal questions?*

Check out the [documentation and support page.](http://mooberry-book-manager.helpscoutdocs.com/)

**Want regular updates?**

* [Subscribe to Mooberry Dreams' mailing list](http://eepurl.com/bwXBPH)
* [Like Mooberry Dreams on Facebook](https://www.facebook.com/MooberryDreams)
* [Join the Mooberry Book Manager Users Group on Facebook](https://www.facebook.com/groups/mooberrybookmanager/)


== Installation ==

1. Upload the entire `mooberry-book-manager` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Use the Books menu to add new books.
4. Use the Book Grids menu to add book gridds.
5. Add Widgets.

== Frequently Asked Questions ==

= Will Mooberry Book Manager work with my theme? =

Mooberry Book Manager has been designed to be theme-independent and should work for most themes. However, as there are infinite possibilities when developing themes and little required standardization, so some themes may not work as expected with the plugin. If Mooberry Book Manager isn’t working for your theme, [contact us](http://www.mooberrydreams.com/contact/) and we’ll see if we can help.

We have found issues when using Mooberry Book Manager with the Customizr theme. These issues should be resolved with v1.1.

Many theme incompatibilities should be solved with v3.0. If you continue to find problems with your theme, please [contact us](http://www.mooberrydreams.com/contact/).

= What’s the difference between Download Links and Retailer Links? =

Download Links should be used when you are allowing readers to download the book for free.

Retailer Links are used when you are linking to website for readers to purchase your book.

A single book most likely won’t have both Download Links and Retailer Links, unless your book is listed for free at retailers and you are also allowing readers to download the ebooks directly from your website.

= What link do I use for download links? How will my readers download the files? =

Your ebook files will need to be uploaded somewhere. You have a few options:
1. Upload to a public storage website such as Dropbox.
2. Upload directly to your website via FTP.
3. Upload to your website via Wordpress' Media Libary. **Note:** Wordpress does not allow you to upload .epub and .mobi files. You'll need to compress them into .zip files before uploading.

The link you enter on the book page should point directly to the file to download.

= Can I use affiliate links? =
Of course! Mooberry Book Manager doesn’t change your link, so you can set your Retailer Links to use your Amazon, Barnes and Noble, or other affiliate code.

On the Moobery Book Manager Settings -> Retailers page you can list an affiliate code for each retailer. For example, Amazon’s code would look something like this:

?tag=<your-affiliate-id>

Different retailers and affiliate programs use different ways of formatting the links for tracking affiliates. Choose the method your program uses.

If your affiliate links look like this, choose After Book Link:

http://www.retailerwebsite.com/link/to/book/someAffiliateCode

If your affiliate links look like this, choose Before Book Link:

http://www.affiliatewebsite.com/someAffiliateCode/http://www.retailerwebsite.com/link/to/book

(Hint: For Amazon, use After Book Link.)

Once you enter your affiliate codes into the Retailers Settings screen, all of your book links will automatically use any affiliate codes that apply. So, if you entered an affiliate code for Amazon, every book that has an Amazon buy link will use that code in the link.

= Why is there no Author field? =
Mooberry Book Manager is designed to be used by the author on the author's website. In this case, the author name would always be the same, and it's already written all over the website.

There are cases though when you might want to have an Author field. You might be an author who publishes under multiple pen names and has a single website. You might be a small publisher who works with multiple authors. Or you might not be an author at all, and using Mooberry Book Manager for a different purpose, such as for a book club. In those cases you have two options:

1. To simply display the author name, you could use another field, such as Subtitle, to display that information.

2. For more advanced handling of author names, use the [Mooberry Book Manager Multi-Author plugin] (http://www.mooberrybookmanager.com/downloads/multi-author/). This will allow you to have a bio and photo of each author as well as advanced filtering and sorting on Book Grids based on author.

= Does Mooberry Book Manager work with Wordpress Multi-Site? =

Versions 2.4.4 and earlier work with Wordpress Multi-Site if you **don't** use Network Activation. If each blog activates Mooberry Book Manager themselves, it should work.

Versions 3.0 - 3.0.4 does **not** work with Wordpress Multi-Site in any way.

Versions 3.1 and above work with Wordpress Multi-Site both with and without Network Activation.

= Does Mooberry Book Manager work with caching plugins? =

See the **Other Notes** section for information about using caching plugins with Mooberry Book Manager.

= Additional questions? =

Check out the [documentation and support page.](https://mooberry-book-manager.helpscoutdocs.com/)


== Screenshots ==

1. Adding your books to your website is as easy as filling out a form.
2. Mooberry Book Manager creates pages for all of your books, ensuring a consistent design.
3. Manage the books on your website the same way you manage your blog posts and pages.
4. Create custom grids of all your books with just a few clicks. They update automatically when you add a new book.
5. Choose from four types of widgets to feature books on your sidebar.

== Changelog ==
= 4.15.2 =
* Updated: version 5 notice only for administrators
* Updated: version 5 notice can be snoozed for 30 days

= 4.16.1 =
* Fixed: error with PHP 8 Attributes used on servers with older PHP versions

= 4.16 =
* Added: Publisher and taxonomy list shortcodes
* Added: version 5 notice

= 4.15.15 =
* Fixed: suppress an error if a book grid is placed not on a page

= 4.15.14 =
* Fixed: updated link from _new to _blank

= 4.15.13 =
* Fixed bug

= 4.15.12 =
* Fixed: bug where retailer buttons/images not working properly when adding retailers

= 4.15.11 =
* Fixed: error on installation/activation

= 4.15.10 =
* Fixed: bug causing coming soon grids to not return books

= 4.15.9 =
* Fixed: update DateTime constructors to not throw deprecated errors in php8

= 4.15.8 =
* Fixed: Bug with WP 6.4

= 4.15.7 =
* Fixed: Typo

= 4.15.6 =
* Fixed: Use selected template for book pages

= 4.15.5 =
* Fixed: Google Books changed their link format

= 4.15.4 =
* Fixed: bulk edit

= 4.15.3 =
* Added: Tell search engines not to index the MBM Tax Grid page

= 4.15.2 =
* Added: support for random sorting in Advanced Grids

= 4.15.1 =
* Fixed: Added parentheses to prevent casting errors on book grid descriptions

= 4.15 =
* Added: Ability to display Book Grid Descriptions (set for Genre, Series, etc.) in book grids when grouped

= 4.14.16 =
* Fixed: retro-fix books added since 4.14.13 update

= 4.14.15 =
* Fixed: Actually fixed the bug from 4.14.13 for books, not just publishers and book grids

= 4.14.14 =
* Fixed: bug introduced in 4.14.13 causing book pages to be blank

= 4.14.13 =
* Fixed: some words not being translated
* Updated: language files

= 4.14.12 =
* Added: Chinese translation files

= 4.14.11 =
* Fixed: Better handling of detecting last line in import file

= 4.14.10 =
* Updated: added support for future options

= 4.14.9 =
* Updated: added support for future options
* Updated: WP Version

= 4.14.8 =
* Updated: support more options for widgets

= 4.14.7 =
* Updated: started 8.1 compatibility (more will be needed)

= 4.14.6 =
* Updated: book pop ups alway show in lower right corner

= 4.14.5 =
* Added: narrator and translator taxonomies

= 4.14.4. =
* Fixed: bug with custom sorting in book grid
* Updated: translation .pot file
* Fixed: duplicate publishers caused by 4.14 update

= 4.14.3 =
* Fixed: bug with book grids selecting by publisher
* Fixed: all places of old publisher code
* Fixed: export with UTF-8 formating

= 4.14.2 =
* Fixed: bug displaying Publisher: on book when there was no publisher set

= 4.14.1 =
* Fixed: bug adding \ into summaries before ' and "

= 4.14 =
* Updated: Publishers are now CPTs instead of in settings
* Added: Importing and Exporting via CSV
* Added: SKU field to editions

= 4.13.2 =
* Updated: Updated CMB2 library

= 4.13.1 =
* Fixed: use site's time zone for figuring out coming soon vs new ribbon
* Fixed: accommodate square book covers in grid with ribbons and popup cards
* Fixed: properly save show title in widget

= 4.13 =
* Updated: Fixed widgets for WP 5.8
* Added: ability to add a NEW or COMING SOON ribbon to book covers
* Added: ability to add a popup card with book informaiton in grids and widgets
* Updated: "Editions" changed to "Formats" on book edit screen to be more clear

= 4.12.1 =
* Fixed typo

= 4.12 =
* Added Retailer Buttons

= 4.11 =
* Added Google Books Link field
* Added DOI field in formats

= 4.10 =
* Added shortcode to book grid list

= 4.9.12 =
* Updated links to documentation and extensions
* Updated quick/bulk edit javascript

= 4.9.11 =
* Fixed: Book Grid Preview bug

= 4.9.10 =
* Fixed: Book Grid Preview bug

= 4.9.9 =
* Fixed: Back to Grid link for tax grids

= 4.9.8 =
* Fixed: bug in widget with PHP7+ where array functions using not an array
* Added: grid ID added to book grid div

= 4.9.7 =
* Fixed: remove itunes link notice if needed

= 4.9.6 =
* Added: necessary code for advanced grids bug fixes

= 4.9.5 =
* Fixed: CSS issue

= 4.9.4 =
* Fixed: support plain permalinks in grids

= 4.9.3 =
* Fixed: bug introduced in 4.9.2

= 4.9.2 =
* Fixed: Back to Grid link not working for tax grids
* Fixed: translation/internationalization updated

= 4.9.1 =
* Fixed: missing text for translations in .pot file

= 4.9 =
* Added: add book summary and additional info to indexes for searchWP and relevanssi

= 4.8.2 =
* Added: more prep for custom fields importing/exporting
* Fixed: missing text for translations in .pot file

= 4.8.1 =
* Fixed: error while activating
* Added: prepare for custom fields importing

= 4.8 =
* Added: Reedsy Discovery link added to books

= 4.7.6 =
* Fixed: warning if menu_object isn't created

= 4.7.5 =
* Added translation tag for Back to Grid

= 4.7.4 =
* Added: permissions for demo site

= 4.7.3 =
* Fixed: code inadvertantly included in 4.7.2

= 4.7.2 =
* Fixed: don't add grid referral to url if back to grid link is set to no

= 4.7.1 =
* Fixed: Allow tax grid urls to be updated in database elsewhere

= 4.7 =
* Fixed: removed mbdb_{taxonomy} url that was causing duplicate pages
* Fixed: Don't let /book/ be a 404
* Updated: updated CMB2 to be PHP7+ compatible

= 4.6.2 =
* wpseo_opengraph_* filters are *not* deprecated, so add them back in

= 4.6.1 =
* Added option to make Back to Grid link optional

= 4.6 =
* Updated for Wordpress 5.7
* Added Back to Grid link on books if reached via grid

= 4.5 =
* Added: Imprints -- add a list of imprints and assign books to an imprint
* Added: Ability to choose whether to hide or show the currency if the default language matches the edition

= 4.4.6 =
* Updated URL for EDD licenses

= 4.4.5 =
* Added: Bosnian language
* Fixed: only display currency if it doesn't match the default language

= 4.4.4 =
* Added: Bosnian currency
* Added: filter on tax grid headers

= 4.4.3 =
* Fixed: bug with default language and currency

= 4.4.2 =
* Fixed incomplete copy of CMB2 files

= 4.4.1 =
* Fixed capitalization of CMB2 folder

= 4.4 =
* Updates for Wordpress 5.6 compatibility

= 4.3.17 =
* Fixed: remove references to wpseo_opengraph because it's deprecated

= 4.3.16 =
* Fixed: send affiliate code to kindle preview properly

= 4.3.15 =
* Fixed: Return to using medium size image for widget. Everything else was causing a problem.

= 4.3.14 =
* Fixed: Remove code handing image size in widget that wasn't supposed to go to production

= 4.3.13 =
* Fixed: Errors when importing from Novelist

= 4.3.12 =
* Fixed: Handle edge case of medium being too small for widget image size

= 4.3.11 =
* Fixed: Don't use Thumbnail size for widget even if it's the closest size

= 4.3.10 =
* Fixed: Choose best size image for widget, don't default to medium

= 4.3.9 =
* Fixed: Wordpress 5.5 compatibility

= 4.3.8 =
* Fixed: Update ALL itunes.apple.com links not just 10. (third time's the charm?)

= 4.3.7 =
* Fixed: Adding a book to the selection on a book grid with custom sort no longer resets the custom sort. New books are added to the bottom.

= 4.3.6 =
* Fixed: Update ALL itunes.apple.com links not just the first 5. (oops)

= 4.3.5 =
* Added: Will update Apple books buy links from itunes.apple.com to books.apple.com

= 4.3.3 =
* Fixed: Bug with book grids when grouping by series when other group by selections may have been made previously

= 4.3.2 =
* Fixed: checks to verify WP roles exist before adding to them on activation

= 4.3.1 =
* Added: Ability to import from Novelist


For revision history of older versions, please see changelog.md


== Upgrade Notice ==

= 2.2 =
Updated for Wordpress 4.3!

= 2.1 =
Responsive Book Grids and more!

= 2.0 =
Redesigned, responsive book page and much more!

== Other Notes ==
= Translations =
* English
* German
* French
* Serbo-Croatian
* Spanish
* Russian
* Italian
* Greek
* Brazilian Portuguese
* Chinese

**NOTE:** [Mooberry Book Manager is translatable.](https://translate.wordpress.org/projects/wp-plugins/mooberry-book-manager/)

* Thanks to [Kathrin Hamann](https://profiles.wordpress.org/thrakonia) for providing the German translation!
* Thanks to [Cyrille Sanson](https://100son.net/) for providing the French translation!
* Thanks to [Web Hosting Geeks](http://www.webhostinggeeks.com/) for the Serbo-Croatian translation!
* Thanks to Ana Gomez for the Spanish translation!
* Thanks to Sergey Kryukov for the Russian translation!
* Thanks to Fabrizio Guidicini for the Italian translation!
* Thanks to Eleni Linaki for the Greek translation!
* Thanks to Vinicius Cubas Brand for the Brazilian Portuguese translation!
* Thanks to Jin Gu for the Chinese translation!
