echo "Waiting for MySQL"
cd /var/www/html
sleep 20

if ! $(wp core is-installed); then
	echo "Setting up Redirection e2e tests"

	wp core install --url=http://redirection-e2e.local --title="Redirection e2e tests" --admin_user=admin --admin_email=root@redirection-e2e.local --admin_password=password
	wp core update
	wp rewrite structure '/%postname%'

	# Setup plugin
	wp plugin activate redirection
	wp redirection database install
	wp redirection import /opt/redirection/redirects.json
	wp redirection setting headers --set='[{"type":"custom","location":"site","headerName":"X-Custom-Header","headerValue":"custom"},{"type":"X-Robots-Tag","location":"site","headerName":"X-Robots-Tag","headerValue":"nofollow"},{"type":"X-Robots-Tag","location":"redirect","headerName":"X-Robots-Tag","headerValue":"nofollow,noindex"}]'
	wp redirection setting permalinks --set='["/%year%/%monthnum%/%day%/%postname%/"]'
	wp post create --post_date='2020-01-01 07:00:00' --post_title='A post' --post_content='Just a small post.' --post_name='migrated-permalink' --post_status='publish' --post_author=1

	echo "CLI: Setup finished"
else
	echo "CLI: Already installed, exiting"
fi
