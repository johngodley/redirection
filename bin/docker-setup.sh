echo "Waiting for MySQL"
cd /var/www/html
sleep 20

if ! $(wp core is-installed); then
	echo "Setting up Redirection e2e tests"

	wp core install --url=http://redirection-e2e.local --title="Redirection e2e tests" --admin_user=admin --admin_email=root@redirection-e2e.local --admin_password=password
	wp rewrite structure '/%postname%'

	# Setup plugin
	wp plugin activate redirection
	wp redirection database install
	wp redirection import /opt/redirection/redirects.json

	echo "CLI: Setup finished"
else
	echo "CLI: Already installed, exiting"
fi
