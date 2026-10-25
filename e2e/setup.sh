#!/usr/bin/env bash
#
# Prepare the wp-env tests environment (http://localhost:8889) for the e2e suite.
# Safe to re-run - Redirection's tables are dropped and the fixtures re-imported each time.
#
# Usage: pnpm wp-env:start && pnpm test:e2e:setup

set -euo pipefail

wp() {
	wp-env run tests-cli wp "$@"
}

PLUGIN_DIR=wp-content/plugins/redirection

wp rewrite structure '/%postname%/' --hard

# Reset Redirection to a clean install
wp plugin activate redirection
wp redirection database remove
wp redirection database install

wp redirection import "$PLUGIN_DIR/e2e/redirects.json" --use-groups-in-file

# Settings the fixtures depend on
wp redirection setting ip_headers --set='["HTTP_CF_CONNECTING_IP"]'
wp redirection setting permalinks --set='["/%year%/%monthnum%/%day%/%postname%/"]'
wp redirection setting headers --set='[{"type":"custom","headerName":"X-Custom-Header","headerValue":"custom","location":"site"},{"type":"custom","headerName":"X-Robots-Tag","headerValue":"nofollow","location":"site"},{"type":"custom","headerName":"X-Robots-Tag","headerValue":"nofollow,noindex","location":"redirect"}]'

# Post used by the permalink migration tests. Owned by admin so the ?author=1 canonical redirect works
wp post delete $( wp post list --post_type=post --name=migrated-permalink --format=ids ) --force 2>/dev/null || true
wp post create --post_type=post --post_status=publish --post_author=1 --post_title='Migrated permalink' --post_name=migrated-permalink --post_date='2022-07-06 12:00:00'
