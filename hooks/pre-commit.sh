#!/bin/sh

# Check we don't commit the plugin debug mode
git diff --cached --name-only | while read FILE; do
if [[ "$FILE" =~ ^.+(php|inc|module|install|test)$ ]]; then
    RESULT=$(grep "REDIRECTION_DEV_MODE', true" "$FILE")
    if [[ -n "$RESULT" ]]; then
      echo "Warning, the commit enables REDIRECTION_DEV_MODE." >&2
	  exit 1
    fi
fi
done
