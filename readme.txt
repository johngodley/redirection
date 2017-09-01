=== Plugin Name ===
Contributors: johnny5
Donate link: http://urbangiraffe.com/about/
Tags: post, admin, seo, pages, manage, 301, 404, redirect, permalink, apache, nginx
Requires at least: 4.4
Tested up to: 4.8.1
Stable tag: 2.7.3

Redirection is a WordPress plugin to manage 301 redirections and keep track of 404 errors without requiring knowledge of Apache .htaccess files.

== Description ==

Redirection is a WordPress plugin to manage 301 redirections, keep track of 404 errors, and generally tidy up any loose ends your site may have.
This is particularly useful if you are migrating pages from an old website, or are changing the directory of your WordPress installation.

And it's 100% free!

Features include:

* 404 error monitoring - captures a log of 404 errors and allows you to easily map these to 301 redirects
* Custom 'pass-through' redirections allowing you to pass a URL through to another page, file, or website.
* Full logs for all redirected URLs
* All URLs can be redirected, not just ones that don't exist
* WP CLI support
* Redirect based upon login status, user agent, or referrer
* Automatically add a 301 redirection when a post's URL changes
* Manually add 301, 302, and 307 redirections for a WordPress post, or for any other file
* Full import/export to JSON, CSV, .htaccess, and Nginx rewrite.rules
* Full regular expression support
* Apache .htaccess is not required - works entirely inside WordPress
* Support for Apache and Nginx
* Redirection statistics telling you how many times a redirection has occurred, when it last happened, who tried to do it, and where they found your URL
* Fully localized & available in many languages

Please submit bugs and patches to https://github.com/johngodley/redirection
Please submit translations to https://translate.wordpress.org/projects/wp-plugins/redirection

== Installation ==

The plugin is simple to install:

1. Download `redirection.zip`
1. Unzip
1. Upload `redirection` directory to your `/wp-content/plugins` directory
1. Go to the plugin management page and enable the plugin
1. Configure the options from the `Manage/Redirection` page

You can find full details of installing a plugin on the [plugin installation page](http://urbangiraffe.com/articles/how-to-install-a-wordpress-plugin/).

Full documentation can be found on the [Redirection](http://urbangiraffe.com/plugins/redirection/) page.

== Frequently Asked Questions ==

= Why would I want to use this instead of .htaccess? =

Ease of use.  Redirections are automatically created when a post URL changes, and it is a lot easier to manually add redirections than to hack around a .htaccess.  You also get the added benefit of being able to keep track of 404 errors.

= What is the performance of this plugin? =

The plugin works in a similar manner to how WordPress handles permalinks and should not result in any noticeable slowdown to your site.

== Upgrade Notice ==

= 2.3.3 =
* Full WordPress 3.5+ compatibility! Note that this contains database changes so please backup your data.

= 2.4 =
* Another database change. Please backup your data

== Changelog ==

= 2.7.3 - 26th August 2017 =
* Fix an import regression bug

= 2.7.2 - 25th August 2017 =
* Better IE11 support
* Fix Apache importer
* Show more detailed error messages
* Refactor match code and fix a problem saving referrer & user agent matches
* Fix save button not enabling for certain redirect types

= 2.7.1 - 14th August 2017 =
* Improve display of errors
* Improve handling of CSV
* Reset tables when changing menus
* Change how the page is displayed to reduce change of interference from other plugins

= 2.7 - 6th August 2017 =
* Finish conversion to React
* Add WP CLI support for import/export
* Add a JSON import/export that exports all data
* Edit redirect position
* Apache config moved to options page
* Fix 410 error code
* Fix page limits
* Fix problems with IE/Safari

= 2.6.6 =
* Use React on redirects page
* Use translate.wordpress.org for language files

= 2.6.5 =
* Use React on groups page

= 2.6.4 =
* Add a limit to per page screen options
* Fix warning in referrer match when referrer doesn't exist
* Fix 404 page showing options
* Fix RSS token not regenerating
* 404 and log filters can now avoid logging
* Use React on modules page

= 2.6.3 =
* Use React on log and 404 pages
* Fix log option not saving 'never'
* Additional check for auto-redirect from root
* Fix delete plugin button
* Improve IP detection for Cloudflare

= 2.6.2 =
* Set auto_detect_line_endings when importing CSV
* Replace options page with a fancy React version that looks exactly the same

= 2.6.1 =
* Fix CSV export merging everything into one line
* Fix bug with HTTP codes not being imported from CSV
* Add filters for source and target URLs
* Add filters for log and 404s
* Add filters for request data
* Add filter for monitoring post permalinks
* Fix export of 404 and logs

= 2.6 =
* Show example CSV
* Allow regex and redirect code to be set on import
* Fix a bunch of database installation problems

= 2.5 =
* Fix no group created on install
* Fix missing export key on install
* Add 308 HTTP code, props to radenui
* Fix imported URLs set to regex, props to alpipego
* Fix sorting of URLs, props to JordanReiter
* Don't cache 307s, props to rmarchant
* Abort redirect exit if no redirection happened, props to junc

= 2.4.5 =
* Ensure cleanup code runs even if plugin was updated
* Extra sanitization of Apache & Nginx files, props to Ed Shirey
* Fix regex bug, props to romulodl
* Fix bug in correct group not being shown in dropdown

= 2.4.4 =
* Fix large advanced settings icon
* Add text domain to plugin file, props Bernhard Kau
* Better PHP7 compatibility, props to Ohad Raz
* Better Polylang compatibility, props to imrehg

= 2.4.3 =
* Bump minimum WP to 4.0.0
* Updated German translation, props to Konrad Tadesse
* Additional check when creating redirections in case of bad data

= 2.4.2 =
* Add Gulp task to generate POT file
* Fix a problem with duplicate positions in the redirect table, props to Jon Jensen
* Fix URL monitor not triggering
* Fix CSV export

= 2.4.1 =
* Fix error for people with an unknown module in a group

= 2.4 =
* Reworked modules now no longer stored in database
* Nginx module (experimental)
* View .htaccess/Nginx inline
* Beginnings of some unit tests!
* Fix DB creation on activation, props syntax53
* Updated Japanese locale, props to Naoko
* Remove deprecated like escaping

= 2.3.16 =
* Fix export options not showing for some people

= 2.3.15 =
* Fix error on admin page for WP 4.2

= 2.3.14 =
* Remove error_log statements
* Fix incorrect table name when exporting 404 errors, props to brazenest/synchronos-t

= 2.3.13 =
* Split admin and front-end code out to streamline the loading a bit
* Fix bad groups link when viewing redirects in a group, props to Patrick Fabre
* Improved plugin activation/deactivation and cleanup
* Improved log clearing

= 2.3.12 =
* Persian translation by Danial Hatami
* Fix saving a redirection with login status, referrer, and user agent
* Fix problem where deleting your last group would cause Redirection to only show an error
* Add limits to referrer and destination in the logs
* Redirect title now shows in the main list again. The field is hidden when editing until toggled
* Fix 'bad nonce' error, props to Jonathan Harrell
* Remove old WP code

= 2.3.11 =
* Fix log cleanup options
* More space when editing redirects
* Better detection of regex when importing
* Restore export options
* Fix unnecessary protected

= 2.3.10 =
* Another compatibility fix for PHP < 5.3
* Fix incorrect module ID used when creating a group
* Fix .htaccess duplication, props to Jörg Liwa

= 2.3.9 =
* Compatibility fix for PHP < 5.3

= 2.3.8 =
* Fix plugin activation error
* Fix fatal error in table nav, props to spacedmonkey

= 2.3.7 =
* New redirect page to match WP style
* New module page to match WP style
* Configurable permissions via redirection_role filter, props to RodGer-GR
* Fix saving 2 month log period
* Fix importer
* Fix DB creation to check for existing tables

= 2.3.6 =
* Updated Italian translation, props to Raffaello Tesi
* Updated Romanian translation, props to Flo Bejgu
* Simplify logging options
* Fix log deletion by search term
* Export logs and 404s to CSV

= 2.3.5 =
* Default log settings to 7 days, props to Maura
* Updated Danish translation thanks to Mikael Rieck
* Add per-page screen option for log pages
* Remove all the corners

= 2.3.4 =
* Fix escaping of URL in admin page

= 2.3.3 =
* Fix PHP strict, props to Juliette Folmer
* Fix RSS entry date, props to Juliette
* Fix pagination

= 2.3.2 =
* WP 3.5 compatibility
* Fix export

= 2.3.0 =
* Remove 404 module and move 404 logs into a separate option
* Add Danish translation, thanks to Rasmus Himmelstrup

= 2.2.14 =
* Clean up log code, using WP_List_Table to power it
* Update Hungarian translation

= 2.2.13 =
* Fix some broken links in admin pages

= 2.2.12 =
* Cleanup some XSS issues

= 2.2.11 =
* Add Lithuanian
* Add Belarusian
* Add Czech
* Fix order of redirects, thanks to Nicolas Hatier

= 2.2.10 =
* Fix XSS in referrers log

= 2.2.9 =
* Fix XSS in admin menu
* Update Russian translation, thanks to Alexey Pazdnikov

= 2.2.8 =
* Add Romanian translation, thanks to Alina
* Add Greek, thanks to Stefanos Kofopoulos

= 2.2.7 =
* Better database compatibility

= 2.2.6 =
* Remove warning from VaultPress

= 2.2.5 =
* Add Turkish translation, thanks to Fatih Cevik
* Fix search box
* Fix 410 error code
* Fix DB errors when MySQL doesn't auto-convert data types

= 2.2.4 =
* Add Hungarian translation, thanks to daSSad

= 2.2.3 =
* Remove debug from htaccess module

= 2.2.2 =
* Fix encoding of JS strings

= 2.2.1 =
* More Dutch translation
* Use fgetcsv for CSV importer - better handling
* Allow http as URL parameter

= 2.2 =
* Add Dutch translation
* Props to Ben Noordhuis for a patch
* WordPress 2.9+ only - cleaned up all the old cruft
* Better new-install process
* Upgrades from 1.0 of Redirection no longer supported
* Optimized DB tables

= 2.1.29 =
* Fix problem with custom post types auto-redirecting (click on 'groups' and then 'modified posts' and clear any entries for '/' from your list)

= 2.1.28 =
* Brazilian Portuguese translation

= 2.1.27 =
* Arabic translation

= 2.1.26 =
* WP 3.0 compatibility

= 2.1.25 =
* Fix deep slashes

= 2.1.24 =
* Add Ukrainian translation
* Add Polish translation
* Database optimization

= 2.1.23 =
* Add Bahasa Indonesian translation
* Add German translation
* Add patch to disable logs (thanks to Simon Wheatley!)

= 2.1.22 =
* Pre WP2.8 compatibility fix

= 2.1.21 =
* Fix #620
* Add Russian translation

= 2.1.20 =
* Fix for some users with problems deleting redirections

= 2.1.19 =
* Add Hindi translation
* Fix some ajax

= 2.1.18 =
* Fix module deletion

= 2.1.17 =
* Log JS fixes

= 2.1.16 =
* Fix group edit and log add entry

= 2.1.15 =
* Use WP Ajax
* Add Japanese

= 2.1.14 =
* Fix #457
* Add #475, #427
* Add Catalan translation.
* WP2.8 compatibility

= 2.1.13 =
* Add Spanish and Chinese translation

= 2.1.12 =
* Add icons
* Disable category monitoring

= 2.1.11 =
* Errors on some sites

= 2.1.10 =
* Missing localizations

= 2.1.9 =
* Fix 'you do not permissions' error on some non-English sites

= 2.1.8 =
* Fix category change 'quick edit'

= 2.1.7 =
* Fix #422, #426

= 2.1.6 =
* Redirection loops

= 2.1.5 =
* Fix #366, #371, #378, #390, #400.
* Add #370, #357

= 2.1.4 =
* RSS feed token

= 2.1.3 =
* Re-enable import feature

= 2.1.2 =
* Minor button changes

= 2.1.1 =
* Force JS cache
* Fix log deletion

= 2.1 =
* Change to jQuery
* Nonce protection
* Fix #352, #353, #339, #351
* Add #358, #316.

= 2.0.12 =
* Disable category monitor in 2.7

= 2.0.11 =
* Hebrew translation

= 2.0.10 =
* Fix small issues in display with WP 2.7

= 2.0.9 =
* Fix delete redirects

= 2.0.8 =
* Refix log delete

= 2.0.7 =
* Fix incorrect automatic redirection with static home pages

= 2.0.6 =
* Support for wp-load.php

= 2.0.5 =
* Fix #264

= 2.0.4 =
* get_home_path seems not be available for some people

= 2.0.3 =
* Fix #248
* Update plugin.php to better handle odd directories

= 2.0.2 =
* Correct DB install
* Fix IIS problem

= 2.0.1 =
* Install defaults when no existing redirection setup

= 2.0 =
* New version
