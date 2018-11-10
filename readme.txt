=== Plugin Name ===
Contributors: johnny5
Donate link: https://redirection.me/donation/
Tags: redirect, htaccess, 301, 404, seo, permalink, apache, nginx, post, admin
Requires at least: 4.5
Tested up to: 5.0
Stable tag: 3.6.2
Requires PHP: 5.4
License: GPLv3

Manage 301 redirections, keep track of 404 errors, and improve your site, with no knowledge of Apache or Nginx needed.

== Description ==

Redirection is the most popular redirect manager for WordPress. With it you can easily manage 301 redirections, keep track of 404 errors, and generally tidy up any loose ends your site may have. This can help reduce errors and improve your site ranking.

Redirection is designed to be used on sites with a few redirects to sites with thousands of redirects.

It has been a WordPress plugin for over 10 years and has been recommended countless times. And it's free!

Full documentation can be found at [https://redirection.me](https://redirection.me)

= Redirect manager =

Create and manage redirects quickly and easily without needing Apache or Nginx knowledge. If your WordPress supports permalinks then you can use Redirection to redirect any URL.

There is full support for regular expressions so you can create redirect patterns to match any number of URLs.

The plugin can also be configured to monitor when post or page permalinks are changed and automatically create a redirect to the new URL.

= Conditional redirects =

In addition to straightforward URL matching you can redirect based on other conditions:

- Login status - redirect only if the user is logged in or logged out
- Browser - redirect if the user is using a certain browser
- Referrer - redirect if the user visited the link from another page
- Cookies - redirect if a particular cookie is set
- HTTP headers - redirect based on a HTTP header
- Custom filter - redirect based on your own WordPress filter

= Full logging =

A configurable logging option allows to view all redirects occurring on your site, including information about the visitor, the browser used, and the referrer. A 'hit' count is maintained for each redirect so you can see if a URL is being used.

Logs can be exported for external viewing, and can be searched and filtered for more detailed investigation.

Display geographic information about an IP address, as well as a full user agent information, to try and understand who the visitor is.

= Track 404 errors =

Redirection will keep track of all 404 errors that occur on your site, allowing you to track down and fix problems.

= Apache & Nginx support =

By default Redirection will manage all redirects using WordPress. However you can configure it so redirects are automatically saved to a .htaccess file and handled by Apache itself.

If you use Nginx then you can export redirects to an Nginx rewrite rules file.

= Import & Export =

The plugin has a fully-featured import and export system and you can:

- Import and export to Apache .htaccess
- Export to Nginx rewrite rules
- Copy redirects between sites using JSON
- Import and export to CSV for viewing in a spreadsheet
- Use WP CLI to automate import and export

You can also import from the following plugins:

- Simple 301 Redirects
- SEO Redirection
- Safe Redirect Manager
- WordPress old slug redirects

= Wait, it's free? =

Yes, it's really free. There's no premium version and no need to pay money to get access to features. This is a dedicated redirect management plugin.

== Support ==

Please submit bugs, patches, and feature requests to:

[https://github.com/johngodley/redirection](https://github.com/johngodley/redirection)

Please submit translations to:

[https://translate.wordpress.org/projects/wp-plugins/redirection](https://translate.wordpress.org/projects/wp-plugins/redirection)

== Installation ==

The plugin is simple to install:

1. Download `redirection.zip`
1. Unzip
1. Upload `redirection` directory to your `/wp-content/plugins` directory
1. Go to the plugin management page and enable the plugin
1. Configure the options from the `Manage/Redirection` page

You can find full details of installing a plugin on the [plugin installation page](https://redirection.me/support/installation/).

Full documentation can be found on the [Redirection](https://redirection.me/support/) page.

== Screenshots ==

1. Redirection management interface
2. Adding a redirection
3. Redirect logs
4. Import/Export
5. Options
6. Support

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

= 3.0 =
* Upgrades the database to support IPv6. Please backup your data and visit the Redirection settings to perform the upgrade
* Switches to the WordPress REST API
* Permissions changed from 'administrator' role to 'manage_options' capability

= 3.6.1 =
* Note Redirection will not work with PHP < 5.4 after 3.6 - please upgrade your PHP

== Changelog ==

= 3.6.2 - 10th November 2018 =
* Add another PHP < 5.4 compat fix
* Fix 'delete all from 404 log' when ungrouped deleting all 404s
* Fix IDs shown in bulk add redirect

= 3.6.1 - 3rd November 2018 =
* Add another PHP < 5.4 fix. Sigh

= 3.6 - 3rd November 2018 =
* Add option to ignore 404s
* Add option to block 404s by IP
* Add grouping of 404s by IP and URL
* Add bulk block or redirect a group of 404s
* Add option to redirect on a 404
* Better page navigation change monitoring
* Add URL & IP match
* Add 303 and 304 redirect codes
* Add 400, 403, and 418 (I'm a teapot!) error codes
* Fix server match not supporting regex properly
* Deprecated file pass through removed
* 'Do nothing' now stops processing further rules

= 3.5 - 23rd September 2018 =
* Add redirect checker on redirects page
* Fix missing translations
* Restore 4.7 backwards compatibility
* Fix unable to delete server name in server match
* Fix error shown when source URL is blank

= 3.4.1 - 9th September 2018 =
* Fix import of WordPress redirects
* Fix incorrect parsing of URLs with 'http' in the path
* Fix 'force ssl' not including path

= 3.4 - 17th July 2018 =
* Add a redirect checker
* Fix incorrect host parsing with server match
* Fix PHP warning with CSV import
* Fix old capability check that was missed from 3.0

= 3.3.1 - 24th June 2018 =
* Add a minimum PHP check for people < 5.4

= 3.3 - 24th June 2018 =
* Add user role/capability match
* Add fix for IP blocking plugins
* Add server match to redirect other domains (beta)
* Add a force http to https option (beta)
* Use users locale setting, not site
* Check for mismatched site/home URLs
* Fix WP CLI not clearing logs
* Fix old capability check
* Detect BOM marker in response
* Improve detection of servers that block content-type json
* Fix incorrect encoding of entities in some locale files
* Fix table navigation parameters not affecting subsequent pages
* Fix .htaccess saving after WordPress redirects
* Fix get_plugin_data error
* Fix canonical redirect problem caused by change in WordPress
* Fix situation that prevented rules cascading

= 3.2 - 11th February 2018 =
* Add cookie match - redirect based on a cookie
* Add HTTP header match - redirect based on an HTTP header
* Add custom filter match - redirect based on a custom WordPress filter
* Add detection of REST API redirect, causing 'fetch error' on some sites
* Update table responsiveness
* Allow redirects for canonical WordPress URLs
* Fix double include error on some sites
* Fix delete action on some sites
* Fix trailing slash redirect of API on some sites

= 3.1.1 - 29th January 2018 =
* Fix problem fetching data on sites without https

= 3.1 - 27th January 2018 =
* Add alternative REST API routes to help servers that block the API
* Move DELETE API calls to POST, to help servers that block DELETE
* Move API nonce to query param, to help servers that don't pass HTTP headers
* Improve error messaging
* Preload support page so it can be used when REST API isn't working
* Fix bug editing Nginx redirects
* Fix import from JSON not setting status

= 3.0.1 - 21st Jan 2018 =
* Don't show warning if per page setting is greater than max
* Don't allow WP REST API to be redirected

= 3.0 - 20th Jan 2018 =
* Add support for IPv6
* Add support for disabling or anonymising IP collection
* Add support for monitoring custom post types
* Add support for monitoring from quick edit mode
* Default to last group used when editing
* Permissions changed from 'administrator' role to 'manage_options' capability
* Swap to WP REST API
* Add new IP map service
* Add new useragent service
* Add 'add new' button to redirect page
* Increase 'title' length
* Fix position not saving on creation
* Fix log pages not remembering table settings
* Fix incorrect column used for HTTP code when importing CSV
* Add support links from inside the plugin

= 2.10.1 - 26th November 2017 =
* Fix incorrect HTTP code reported in errors
* Improve management page hook usage

= 2.10 - 18th November 2017 =
* Add support for WordPress multisite
* Add new Redirection documentation
* Add extra actions when creating redirects
* Fix user agent dropdown not setting agent

= 2.9.2 - 11th November 2017 =
* Fix regex breaking .htaccess export
* Fix error when saving Error or No action
* Restore sortable table headers

= 2.9.1 - 4th November 2017 =
* Fix const issues with PHP 5

= 2.9 - 4th November 2017 =
* Add option to set redirect cache expiry, default 1 hour
* Add a check for unsupported versions of WordPress
* Add check for database tables before starting the plugin
* Improve JSON import memory usage
* Add importers for: Simple 301 Redirects, SEO Redirection, Safe Redirect Manager, and WordPress old post slugs
* Add responsive admin UI

= 2.8.1 - 22nd October 2017 =
* Fix redirect edit not closing after save
* Fix user agent dropdown not auto-selecting regex
* Fix focus to bottom of page on load
* Improve error message when failing to start
* Fix associated redirect appearing at start of URL, not end

= 2.8 - 18th October 2017 =
* Add a fixer to the support page
* Ignore case for imported files
* Fixes for Safari
* Fix WP CLI importing CSV
* Fix monitor not setting HTTP code
* Improve error, random, and pass-through actions
* Fix bug when saving long title
* Add user agent dropdown to user agent match
* Add pages and trashed posts to monitoring
* Add 'associated redirect' option to monitoring, for AMP
* Remove 404 after adding
* Allow search term to apply to deleting logs and 404s
* Deprecate file pass-through, needs to be enabled with REDIRECTION_SUPPORT_PASS_FILE and will be replaced with WP actions
* Further sanitize match data against bad serialization

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

= < 2.2 =
* Props to Ben Noordhuis for a patch
* WordPress 2.9+ only - cleaned up all the old cruft
* Better new-install process
* Upgrades from 1.0 of Redirection no longer supported
* Optimized DB tables
* Change to jQuery
* Nonce protection
* Disable category monitor in 2.7
* Fix small issues in display with WP 2.7
* Fix delete redirects
* Refix log delete
* Fix incorrect automatic redirection with static home pages
* Support for wp-load.php
* get_home_path seems not be available for some people
* Update plugin.php to better handle odd directories
* Correct DB install
* Fix IIS problem
* Install defaults when no existing redirection setup
* Fix problem with custom post types auto-redirecting (click on 'groups' and then 'modified posts' and clear any entries for '/' from your list)
* Brazilian Portuguese translation
* WP 3.0 compatibility
* Fix deep slashes
* Database optimization
* Add patch to disable logs (thanks to Simon Wheatley!)
* Pre WP2.8 compatibility fix
* Fix for some users with problems deleting redirections
* Fix some ajax
* Fix module deletion
* Log JS fixes
* Fix group edit and log add entry
* Use WP Ajax
* WP2.8 compatibility
* Add icons
* Disable category monitoring
* Errors on some sites
* Fix 'you do not permissions' error on some non-English sites
* Fix category change 'quick edit'
* Redirection loops
* RSS feed token
* Re-enable import feature
* Force JS cache
* Fix log deletion
