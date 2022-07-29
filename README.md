# Redirection

PHP/JavaScript [![Build Status](https://travis-ci.org/johngodley/redirection.svg?branch=master)](https://travis-ci.org/johngodley/redirection)

Redirection is a WordPress plugin to manage 301 redirections, keep track of 404 errors, and generally tidy up any loose ends your site may have. This is particularly useful if you are migrating pages from an old website, or are changing the directory of your WordPress installation.

Note: this is the current 'trunk' version of Redirection. It may be newer than what is in the WordPress.org plugin repository, and should be considered experimental.

## Installation
Redirection can be installed by visiting the WordPress.org plugin page:

https://wordpress.org/plugins/redirection/

## Customisation

Redirection provides a large set of WordPress action and filter hooks that can be used to extend or customise the plugin. These can found on the [Redirection Hooks](https://redirection.me/developer/wordpress-hooks/) page.

### Permissions

Access to the Redirection admin interface is given to users who can `manage_options`. You can modify this with permission filters, described on the [Redirection permissions](https://redirection.me/developer/permissions/) page.

## Building

Redirection is mostly a PHP plugin, but does contain files that need to be built. For this you'll need Gulp, Node, and Yarn installed. Install required modules with:

`yarn install`

### Language files

`gulp pot` - Updates language files

### React

Some parts of the UI are React and can be built with:

`yarn build`

To use in development mode then run:

`yarn start`

### PHPUnit

To setup PHPUnit:

`./bin/install-wp-tests.sh test root root <mysql socket file>`

Make a note of the WP download location (for example, /var/folders/l8/something) and set the `WP_TESTS_DIR` environement variable to the `wordpress-tests-lib` directory in that download location.

`export WP_TEST_DIR=/var/folders/l8/somthing/wordpress-tests-lib`

Then edit `wordpress-tests-lib/wp-tests-config.php` in that directory and configure the database.

Then:

`yarn test:php`

### Releasing

Finally, to produce a release copy:

`gulp plugin`

## Support

Please raise any bug reports or enhancement requests here. Pull requests are always welcome.

You can find a more detailed description of the plugin on the [Redirection home page](https://redirection.me)

Translations can be added here:

https://translate.wordpress.org/projects/wp-plugins/redirection
