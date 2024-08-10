#!/bin/sh
VERSION=$1

# Make sure we have the repo
svn co https://plugins.svn.wordpress.org/redirection/trunk svn

cd svn

# Make sure we have the latest version
svn up

# Sync the files
rsync --filter='P .svn' -avz --delete ../release/ .

svn st

# Force user to enter the details
echo ""
echo "From the ./svn directory run 'svn ci' to commit the changes. You may need to 'svn rm' any files that are no longer needed, or 'svn add' ones that are new."

# Tag the release
echo "Then run: svn cp https://plugins.svn.wordpress.org/redirection/trunk https://plugins.svn.wordpress.org/redirection/tags/$VERSION -m \"Tagging version $VERSION\""