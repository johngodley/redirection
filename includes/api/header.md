Redirection is a WordPress plugin to manage redirects. It exposes a set of REST API endpoints. These are used by the Redirection plugin itself, and can also be used by anyone else.

Note: this documentation is incomplete and the API should not be considered stable and could change without notice.

## REST API Endpoints

The API endpoints are available on the WordPress site at `https://yoursite.com/wp-json/redirection/v1`.

The examples below reference `https://redirection.me` and you should substitute this for your own site.

Body parameters are supplied as JSON.

## Authentication

All requests must be authenticated by someone with `manage_options` capabilities. See the [REST API authentication guide](https://developer.wordpress.org/rest-api/using-the-rest-api/authentication/).
