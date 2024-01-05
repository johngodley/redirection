=== Plugin Name ===
Contributors: johnny5
Donate link: https://redirection.me/donation/
Tags: redirect, htaccess, 301, 404, seo, permalink, apache, nginx, post, admin
Requires at least: 5.9
Tested up to: 6.4.2
Stable tag: 5.4.1
Requires PHP: 5.6
License: GPLv3

Manage 301 redirects, track 404 errors, and improve your site. No knowledge of Apache or Nginx required.

== Description ==

Redirection is the most popular redirect manager for WordPress. With it you can easily manage 301 redirections, keep track of 404 errors, and generally tidy up any loose ends your site may have. This can help reduce errors and improve your site ranking.

Redirection is designed to be used on sites with a few redirects to sites with thousands of redirects.

It has been a WordPress plugin for over 10 years and has been recommended countless times. And it's free!

Full documentation can be found at [https://redirection.me](https://redirection.me)

Redirection is compatible with PHP from 5.6 to 8.1.

= Redirect manager =

Create and manage redirects quickly and easily without needing Apache or Nginx knowledge. If your WordPress supports permalinks then you can use Redirection to redirect any URL.

There is full support for regular expressions so you can create redirect patterns to match any number of URLs. You can match query parameters and even pass them through to the target URL.

The plugin can also be configured to monitor when post or page permalinks are changed and automatically create a redirect to the new URL.

= Conditional redirects =

In addition to straightforward URL matching you can redirect based on other conditions:

- Login status - redirect only if the user is logged in or logged out
- WordPress capability - redirect if the user is able to perform a certain capability
- Browser - redirect if the user is using a certain browser
- Referrer - redirect if the user visited the link from another page
- Cookies - redirect if a particular cookie is set
- HTTP headers - redirect based on a HTTP header
- Custom filter - redirect based on your own WordPress filter
- IP address - redirect if the client IP address matches
- Server - redirect another domain if also hosted on this server
- Page type - redirect if the current page is a 404

= Full logging =

A configurable logging option allows to view all redirects occurring on your site, including information about the visitor, the browser used, and the referrer. A 'hit' count is maintained for each redirect so you can see if a URL is being used.

Logs can be exported for external viewing, and can be searched and filtered for more detailed investigation.

Display geographic information about an IP address, as well as a full user agent information, to try and understand who the visitor is.

You are able to disable or reduce IP collection to meet the legal requirements of your geographic region, and can change the amount of information captured from the bare minimum to HTTP headers.

You can also log any redirect happening on your site, including those performed outside of Redirection.

= Add HTTP headers =

HTTP headers can be added to redirects or your entire site that help reduce the impact of redirects or help increase security. You can also add your own custom headers.

= Track 404 errors =

Redirection will keep track of all 404 errors that occur on your site, allowing you to track down and fix problems.

Errors can be grouped to show where you should focus your attention, and can be redirected in bulk.

= Query parameter handling =

You can match query parameters exactly, ignore them, and even pass them through to your target.

= Migrate Permalinks =

Changed your permalink structure? You can migrate old permalinks simply by entering the old permalink structure. Multiple migrations are supported.

= Apache & Nginx support =

By default Redirection will manage all redirects using WordPress. However you can configure it so redirects are automatically saved to a .htaccess file and handled by Apache itself.

If you use Nginx then you can export redirects to an Nginx rewrite rules file.

= Fine-grained permissions =

Fine-grained permissions are available so you can customise the plugin for different users. This makes it particularly suitable for client sites where you may want to prevent certain actions, and remove functionality.

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
- Rank Math
- WordPress old slug redirects
- Quick Post/Pages redirects

= Search Regex compatible =

Redirection is compatible with [Search Regex](https://searchregex.com), allowing you to bulk update your redirects.

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
1. Configure the options from the `Tools/Redirection` page

You can find full details of installing a plugin on the [plugin installation page](https://redirection.me/support/installation/).

Full documentation can be found on the [Redirection](https://redirection.me/support/) site.

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

= 5.4 =
* You may need to configure the IP header option if using a proxy

= 3.0 =
* Upgrades the database to support IPv6. Please backup your data and visit the Redirection settings to perform the upgrade
* Switches to the WordPress REST API
* Permissions changed from 'administrator' role to 'manage_options' capability

= 3.6.1 =
* Note Redirection will not work with PHP < 5.4 after 3.6 - please upgrade your PHP

= 3.7 =
* Requires minimum PHP 5.4. Do not upgrade if you are still using PHP < 5.4

= 4.0 =
* Alters database to support case insensitivity, trailing slashes, and query params. Please backup your data

= 4.7 =
* Requires minimum PHP 5.6+. Do not upgrade if you are still using PHP < 5.6

= 4.9 =
* Alters database to support enhanced logging. Please backup your data

== Changelog ==

A x.1 version increase introduces new or updated features and can be considered to contain 'breaking' changes. A x.x.1 increase is purely a bug fix and introduces no new features, and can be considered as containing no breaking changes.

= 5.4.1 - 5th January 2024 =
* Fix problem with some international URLs not appearing in the 404 log

= 5.4 - 1st January 2024 =
* Don't encode negative lookaheads
* Remove port from server name
* Importing into a disabled group now creates disabled items
* Add option to pick IP header
* Fix save of x-content-type-options: sniff
* Fix save of multiple spaces

= 5.3.10 - 2nd April 2023 =
* Fix associated redirect setting not saving properly

= 5.3.9 - 25th January 2023 =
* Fix incorrect sanitization applied to target URLs

= 5.3.8 - 22nd January 2023 =
* Fix app rendering twice causing problems with upgrades
* Fix CSV header being detected as an error

= 5.3.7 - 8th January 2023 =
* Fix problem with locales in certain directories
* Fix incorrect import of empty CSV lines
* Don't encode regex for Nginx

= 5.3.6 - 12th November 2022 =
* Fix for sites with a version of +OK
* Another fix for CZ locale

= 5.3.5 - 6th November 2022 =
* Fix crash on options page for Czech language

= 5.3.4 - 14th September 2022 =
* Fix query parameter name with a + not matching

= 5.3.3 - 7th September 2022 =
* Fix default HTTP header not being set when first used
* Fix incorrect column heading in CSV
* Fix passing of mixed case parameters

= 5.3.2 - 6th August 2022 =
* Fix missing props error
* Fix missing value for .htaccess location display

= 5.3.1 - 29th July 2022 =
* Fix crash caused by bad translations in locale files
* Fix query match not working when it contained mixed case
* Fix missing flag in .htaccess export

= 5.3.0 - 21st July 2022 =
* Improve installation process
* Improve permalink migration so it works with more permalinks
* Prevent ordering columns by HTTP code
* Better encode URLs in Nginx export
* Allow escaped characters to work in the redirect checker
* Reduce CSV import time

= 5.2.3 - 6th February 2022 =
* Fix error when grouping by URL, adding redirect, and then adding another redirect
* Add a warning for unescaped ? regex

= 5.2.2 - 22nd January 2022 =
* Further improve URL checker response to clarify responsibility
* Fix WordPress and pagetype match preventing the logging of 404s
* Fix title field being inactive
* Fix CSV export having duplicate column

= 5.2.1 - 16th January 2022 =
* Include path with inline URL checker

= 5.2 - 15th January 2022 =
* Improve URL checker and show more details
* Retain query parameter case when passing to target URL
* Remove unnecessary database stage option check
* PHP 8.1 compatibility

= 5.1.3 - 24th July 2021 =
* Fix geo IP on log pages showing an API redirected error
* Fix crash when changing match type in edit dialog

= 5.1.2 - 17th July 2021 =
* Fix random redirect not working
* Fix [userid] shortcode returning 1

= 5.1.1 - 11th April 2021 =
* Revert the permalink migration improvement from 5.1 as it's causing problems on some sites

= 5.1 - 10th April 2021 =
* Add importer for PrettyLinks
* Fix crash converting a 'do nothing' to 'redirect to URL'
* Improve warning messages
* Improve permalink migration when is_404 is not set
* Fix 'delete log entries' returning blank data
* Fix missing .htaccess location
* Fix hits & date not imported with JSON format

= 5.0.1 - 26th Jan 2021 =
* Fix incorrect warning when creating a regular expression with captured data
* Fix JS error when upgrading a database with a broken REST API
* Increase regular expression redirect limit
* PHP8 support

= 5.0 - 16th Jan 2021 =
* Add caching support
* Add support for migrated permalink structures
* Add dynamic URL variables
* Add fully automatic database upgrade option
* Add a new version release information prompt
* Improve performance when many redirects have the same path
* Move bulk all action to a separate button after selecting all
* Fix error in display with restricted capabilities
* Avoid problems with 7G Firewall
* Improve handling of invalid encoded characters

= 4.9.2 - 30th October =
* Fix warning with PHP 5.6
* Improve display of long URLs

= 4.9.1 - 26th October 2020 =
* Restore missing time and referrer URL from log pages
* Restore missing client information from debug reports
* Fix order by count when grouping by URL
* Check for duplicate columns in DB upgrade

= 4.9 - 24th October 2020 =
* Expand log information to capture HTTP headers, domain, HTTP code, and HTTP method
* Allow non-Redirection redirects to be logged - allows tracking of all redirects on a site
* Expand log and 404 pages with greatly improved filters
* Bulk delete logs and 404s by selected filter
* Logging is now optional per redirect rule
* Fix random action on a site with non-root URL
* Fix group and search being reset when searching
* Fix canonical alias not using request server name

= 4.8 - 23rd May 2020 =
* Add importer for Quick Post/Page Redirects plugin
* Add plugin imports to WP CLI
* Fix install wizard using wrong relative API
* Fix sub menu outputting invalid HTML

= 4.7.2 - 8th May 2020 =
* Fix PHP warning decoding an encoded question mark
* Fix site adding an extra period in a domain name
* Fix protocol appearing in .htaccess file server redirect

= 4.7.1 - 14th March 2020 =
* Fix HTTP header over-sanitizing the value
* Fix inability to remove .htaccess location
* Fix 404 group by 'delete all'
* Fix import of empty 'old slugs'

= 4.7 - 15th February 2020 =
* Relocate entire site to another domain, with exceptions
* Site aliases to map another site to current site
* Canonical settings for www/no-www
* Change content-type for API requests to help with mod_security

= 4.6.2 - 6th January 2020 =
* Fix 404 log export button
* Fix HTTPS option not appearing enabled
* Fix another PHP compat issue

= 4.6.1 - 30th December 2019 =
* Back-compatibility fix for old PHP versions

= 4.6 - 27th December 2019 =
* Add fine-grained permissions allowing greater customisation of the plugin, and removal of functionality
* Add an import step to the install wizard
* Remove overriding of default WordPress 'old slugs'

= 4.5.1 - 23rd November 2019 =
* Fix broken canonical redirects

= 4.5 - 23rd November 2019 =
* Add HTTP header feature, with x-robots-tag support
* Move HTTPS setting to new Site page
* Add filter to disable redirect hits
* Add 'Disable Redirection' option to stop Redirection, in case you break your site
* Fill out API documentation
* Fix style with WordPress 5.4
* Fix encoding of # in .htaccess

= 4.4.2 - 29th September 2019 =
* Fix missing options for monitor group
* Fix check redirect not appearing if position column not shown

= 4.4.1 - 28th September 2019 =
* Fix search highlighter causing problems with regex characters
* Fix 'show all' link not working
* Fix 'Request URI Too Long' error when switching pages after creating redirects

= 4.4 - 22nd September 2019 =
* Add 'URL and language' match
* Add page display type for configurable information
* Add 'search by' to search by different information
* Add filter dropdown to filter data
* Add warning about relative absolute URLs
* Add 451, 500, 501, 502, 503, 504 error codes
* Fix multiple 'URL and page type' redirects
* Improve invalid nonce warning
* Encode replaced values in regular expression targets

= 4.3.3 - 8th August 2019 ==
* Add back compatibility fix for URL sanitization

= 4.3.2 - 4th August 2019 ==
* Fix problem with UTF8 characters in a regex URL
* Fix invalid characters causing an error message
* Fix regex not disabled when removed

= 4.3.1 - 8th June 2019 =
* Fix + character being removed from source URL

= 4.3 - 2nd June 2019 =
* Add support for UTF8 URLs without manual encoding
* Add manual database install option
* Add check for pipe character in target URL
* Add warning when problems saving .htaccess file
* Switch from 'x-redirect-agent' to 'x-redirect-by', for WP 5+
* Improve handling of invalid query parameters
* Fix query param name is a number
* Fix redirect with blank target and auto target settings
* Fix monitor trash option applying when deleting a draft
* Fix case insensitivity not applying to query params
* Disable IP grouping when IP option is disabled
* Allow multisite database updates to run when more than 100 sites

= 4.2.3 - 16th Apr 2019 =
* Fix bug with old API routes breaking test

= 4.2.2 - 13th Apr 2019 =
* Improve API checking logic
* Fix '1' being logged for pass-through redirects

= 4.2.1 - 8th Apr 2019 =
* Fix incorrect CSV download link

= 4.2 - 6th Apr 2019 =
* Add auto-complete for target URLs
* Add manual database upgrade
* Add support for semi-colon separated import files
* Add user agent to 404 export
* Add workaround for qTranslate breaking REST API
* Improve API problem detection
* Fix JSON import ignoring group status

= 4.1.1 - 23rd Mar 2019 =
* Remove deprecated PHP
* Fix REST API warning
* Improve WP CLI database output

= 4.1 - 16th Mar 2019 =
* Move 404 export option to import/export page
* Add additional redirect suggestions
* Add import from Rank Math
* Fix 'force https' causing WP to redirect to admin URL when accessing www subdomain
* Fix .htaccess import adding ^ to the source
* Fix handling of double-slashed URLs
* Fix WP CLI on single site
* Add DB upgrade to catch URLs with double-slash URLs
* Remove unnecessary escaped slashes from JSON output

= 4.0.1 - 2nd Mar 2019 =
* Improve styling of query flags
* Match DB upgrade for new match_url to creation script
* Fix upgrade on some hosts where plugin is auto-updated
* Fix pagination button style in WP 5.1
* Fix IP match when action is 'error'
* Fix database upgrade on multisite WP CLI

= 4.0 - 23rd Feb 2019 =
* Add option for case insensitive redirects
* Add option to ignore trailing slashes
* Add option to copy query parameters to target URL
* Add option to ignore query parameters
* Add option to set defaults for case, trailing, and query settings
* Improve upgrade for sites with missing tables

= 3.7.3 - 2nd Feb 2019 =
* Add PHP < 5.4 message on plugins page
* Prevent upgrade message being hidden by other plugins
* Fix warning with regex and no leading slash
* Fix missing display of disabled redirects with a title
* Improve upgrade for sites with a missing IP column
* Improve API detection with plugins that use sessions
* Improve compatibility with ModSecurity
* Improve compatibility with custom API prefix
* Detect site where Redirection was once installed and has settings but no database tables

= 3.7.2 - 16th Jan 2019 =
* Add further partial upgrade detection
* Add fallback for sites with no REST API value

= 3.7.1 - 13th Jan 2019 =
* Clarify database upgrade text
* Fix Firefox problem with multiple URLs
* Fix 3.7 built against wrong dropzone module
* Add DB upgrade detection for people with partial 2.4 sites

= 3.7 - 12th Jan 2019 =
* Add redirect warning for known problem redirects
* Add new database install and upgrade process
* Add database functions to WP CLI
* Add introduction message when first installed
* Drop PHP < 5.4 support. Please use version 3.6.3 if your PHP is too old
* Improve export filename
* Fix IPs appearing for bulk redirect
* Fix disabled redirects appearing in htaccess

= 3.6.3 - 14th November 2018 =
* Remove potential CSRF

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

= 2.3.0 and earlier =
* Remove 404 module and move 404 logs into a separate option
* Clean up log code, using WP_List_Table to power it
* Fix some broken links in admin pages
* Fix order of redirects, thanks to Nicolas Hatier
* Fix XSS in admin menu & referrers log
* Better database compatibility
* Remove warning from VaultPress
* Remove debug from htaccess module
* Fix encoding of JS strings
* Use fgetcsv for CSV importer - better handling
* Allow http as URL parameter
* Props to Ben Noordhuis for a patch
* WordPress 2.9+ only - cleaned up all the old cruft
* Better new-install process
* Upgrades from 1.0 of Redirection no longer supported
* Optimized DB tables
* Change to jQuery
* Nonce protection
* Disable category monitor in 2.7
* Refix log delete
* get_home_path seems not be available for some people
* Update plugin.php to better handle odd directories
* Correct DB install
* Install defaults when no existing redirection setup
* Fix problem with custom post types auto-redirecting (click on 'groups' and then 'modified posts' and clear any entries for '/' from your list)
* Database optimization
* Add patch to disable logs (thanks to Simon Wheatley!)
* Fix for some users with problems deleting redirections
* Fix group edit and log add entry
* Disable category monitoring
* Fix 'you do not permissions' error on some non-English sites
* Fix category change 'quick edit'
* RSS feed token
