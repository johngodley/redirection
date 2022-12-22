/**
 * External dependencies
 */

import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */

import { ExternalLink, createInterpolateElement } from 'wp-plugin-components';
import RestApiStatus from 'component/rest-api-status';
import { getErrorLinks } from 'lib/error-links';

function ErrorDetails() {
	return (
		<>
			<RestApiStatus />

			<h3>{ __( 'What do I do next?', 'redirection' ) }</h3>

			<ol>
				<li>
					{ createInterpolateElement(
						__(
							'Take a look at the {{link}}plugin status{{/link}}. It may be able to identify and "magic fix" the problem.',
							'redirection'
						),
						{
							link: <a href="?page=redirection.php&sub=support" />,
						}
					) }
				</li>
				<li>
					{ createInterpolateElement(
						__(
							'{{link}}Caching software{{/link}}, in particular Cloudflare, can cache the wrong thing. Try clearing all your caches.',
							'redirection'
						),
						{
							link: <ExternalLink url="https://redirection.me/support/problems/cloudflare/" />,
						}
					) }
				</li>
				<li>
					{ createInterpolateElement(
						__( '{{link}}Please temporarily disable other plugins!{{/link}} This fixes so many problems.', 'redirection' ),
						{
							link: <ExternalLink url="https://redirection.me/support/problems/plugins/" />,
						}
					) }
				</li>
				<li>
					{ createInterpolateElement(
						__(
							'If you are using WordPress 5.2 or newer then look at your {{link}}Site Health{{/link}} and resolve any issues.',
							'redirection'
						),
						{
							link: <ExternalLink url={ getErrorLinks().siteHealth } />,
						}
					) }
				</li>
			</ol>
		</>
	);
}

export default ErrorDetails;
