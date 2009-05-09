=== Plugin Name ===
Contributors: johnny5
Donate link: http://urbangiraffe.com/about/support/
Tags: post, admin, seo, pages, manage, 301, 404, redirect, permalink
Requires at least: 2.3
Tested up to: 2.8
Stable tag: trunk

Redirection is a WordPress plugin to manage 301 redirections and keep track of 404 errors without requiring knowledge of Apache .htaccess files.

== Description ==

Redirection is a WordPress plugin to manage 301 redirections, keep track of 404 errors, and generally tidy up any loose ends your site may have. This is particularly useful if you are migrating pages from an old website, or are changing the directory of your WordPress installation.

New features include:

* 404 error monitoring - captures a log of 404 errors and allows you to easily map these to 301 redirects
* Custom 'pass-through' redirections allowing you to pass a URL through to another page, file, or website.
* Full logs for all redirected URLs
* All URLs can be redirected, not just ones that don't exist
* Redirection methods - redirect based upon login status, redirect to random pages, redirect based upon the referrer!
* WordPress 2.7+ only

Existing features include:

* Automatically add a 301 redirection when a post's URL changes
* Manually add 301, 302, and 307 redirections for a WordPress post, or for any other file
* Full regular expression support
* Apache .htaccess is not required - works entirely inside WordPress
* Strip or add www to all your WordPress pages
* Redirect index.php, index.html, and index.htm access
* Redirection statistics telling you how many times a redirection has occurred, when it last happened, who tried to do it, and where they found your URL
* Fully localized

Redirection is available in:

* English
* French (thanks to [Oncle Tom](http://oncle-tom.net))
* Hebrew (thanks to [Rami](http://www.bdihot.co.il/))
* Spanish (thanks to [Juan](http://unahormiga.com>))
* Simplified Chinese (thanks to [Sha Miao](http://shamiao.com))
* Catalan (thanks to Robert Bu)

== Installation ==

The plugin is simple to install:

1. Download `redirection.zip`
1. Unzip
1. Upload `redirection` directory to your `/wp-content/plugins` directory
1. Go to the plugin management page and enable the plugin
1. Configure the options from the `Manage/Redirection` page

You can find full details of installing a plugin on the [plugin installation page](http://urbangiraffe.com/articles/how-to-install-a-wordpress-plugin/).

== Frequently Asked Questions ==

= Why would I want to use this instead of .htaccess? =

Ease of use.  Redirections are automatically created when a post URL changes, and it is a lot easier to manually add redirections than to hack around a .htaccess.  You also get the added benefit of being able to keep track of 404 errors.

= What is the performance of this plugin? =

The plugin works in a similar manner to how WordPress handles permalinks and should not result in any noticeable slowdown to your site.

== Screenshots ==

1. Simple interface to add a redirection
2. A graphical interface to manage all your redirections

== Documentation ==

Full documentation can be found on the [Redirection](http://urbangiraffe.com/plugins/redirection/) page.

