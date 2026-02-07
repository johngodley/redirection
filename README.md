# Redirection

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

Redirection is mostly a PHP plugin, but does contain files that need to be built. For this you'll need Node and pnpm installed.

### Requirements

- Node.js >= 12.14.0
- pnpm
- Composer (for PHP development)

### Setup

Install JavaScript dependencies:

```bash
pnpm install
```

Install PHP dependencies (for linting and testing):

```bash
composer install
```

### Development

The UI is built with React using `@wordpress/scripts`. To start development with hot-reload:

```bash
pnpm start
```

To create a production build:

```bash
pnpm build
```

### Linting

Run all linters:

```bash
pnpm lint
```

Or run individually:

- `pnpm lint:php` - PHP linting (PHPCS and PHPStan)
- `pnpm lint:js` - JavaScript linting
- `pnpm lint:css` - CSS linting
- `pnpm lint:ts` - TypeScript type checking

PHP code can be auto-formatted with:

```bash
composer format
```

### Testing

**PHP Unit tests:**

```bash
pnpm test:unit
```

Or directly via Composer:

```bash
composer test-unit
```

**PHP Integration tests (requires WordPress):**

First, start the WordPress environment:

```bash
pnpm wp-env:start
```

Then run the integration tests:

```bash
pnpm test:integration
```

To stop the environment:

```bash
pnpm wp-env:destroy
```

**JavaScript tests:**

```bash
pnpm test:js
```

**End-to-end tests:**

```bash
pnpm test:e2e
```

**Run all tests:**

```bash
pnpm test
```

### Releasing

Create a release build (builds assets and creates a zip file):

```bash
pnpm release
```

Create just the plugin zip without releasing:

```bash
pnpm plugin:zip
```

Release to WordPress.org SVN:

```bash
pnpm release:svn
```

### API Documentation

Generate API documentation:

```bash
pnpm doc
```

## Support

Please raise any bug reports or enhancement requests here. Pull requests are always welcome.

You can find a more detailed description of the plugin on the [Redirection home page](https://redirection.me)

Translations can be added here:

https://translate.wordpress.org/projects/wp-plugins/redirection
