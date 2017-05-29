# Redirection

[![Build Status](https://travis-ci.org/johngodley/redirection.svg?branch=master)](https://travis-ci.org/johngodley/redirection)

Redirection is a WordPress plugin to manage 301 redirections, keep track of 404 errors, and generally tidy up any loose ends your site may have. This is particularly useful if you are migrating pages from an old website, or are changing the directory of your WordPress installation.

Note: this is the current 'trunk' version of Redirection. It may be newer than what is in the WordPress.org plugin repository, and should be considered experimental.

## Installation
Redirection can be installed by visiting the WordPress.org plugin page:

https://wordpress.org/plugins/redirection/

## Customisation

### Request Information

The following WordPress filters are available for customisation of a server requests:

- `redirection_request_url` - The request URL
- `redirection_request_agent` - The request user agent
- `redirection_request_referrer` - The request referrer
- `redirection_request_ip` - The request IP address

### Logging

The following WordPress filters are available for customisation of logged data:

- `redirection_404_data` - Data to be inserted into the 404 table
- `redirection_log_data` - Data to be inserted into the redirect log table

### Redirect source and target

- `redirection_url_source` - The original URL used before matching a request. Return false to stop any redirection
- `redirection_url_target` - The target URL after a request has been matched (and after any regular expression captures have been replaced). Return false to stop any redirection

### Dynamic URL data

The following special words can be inserted into a target URL:

- `%userid%` - Insert user's ID
- `%userlogin%` - Insert user's login name
- `%userurl%` - Insert user's custom URL

### Management

- `redirection_permalink_changed` - return boolean if a post's permalink has changed
- `redirection_remove_existing` - fired when a post changes permalink and we need to clear existing redirects that might affect it

Additionally, if the target URL is a number without any slashes then Redirection will treat it as a post ID and redirect to the full URL for that post.

## Support

Please raise any bug reports or enhancement requests here. Pull requests are always welcome.

You can find a more detailed description of the plugin on the [Redirection home page](http://urbangiraffe.com/plugins/redirection/)

Translations can be added here:

https://translate.wordpress.org/projects/wp-plugins/redirection
