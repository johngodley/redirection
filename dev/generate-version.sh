#!/usr/bin/env bash

MD5=`md5sum redirection.js | cut -d ' ' -f 1`

echo "<?php" > redirection-version.php
echo "" >> redirection-version.php
echo "define( 'REDIRECTION_VERSION', '$1' );" >> redirection-version.php
echo "define( 'REDIRECTION_MIN_WP', '$2' );" >> redirection-version.php
echo -n "define( 'REDIRECTION_BUILD', '"$MD5"' );" >> redirection-version.php
echo "" >> redirection-version.php
