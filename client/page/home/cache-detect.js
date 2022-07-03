/**
 * External dependencies
 */

import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { ExternalLink, Error } from 'wp-plugin-components';
import { getErrorLinks, getErrorDetails, getCacheBuster } from 'lib/error-links';

function CacheDetect() {
	return (
		<Error
			errors={ '' }
			details={ getErrorDetails().concat( [ getCacheBuster() ] ) }
			type="fixed"
			title={ __( 'Cached Redirection detected', 'redirection' ) }
			links={ getErrorLinks() }
			locale="redirection"
		>
			<p>{ __( 'Please clear your browser cache and reload this page.', 'redirection' ) }</p>
			<p>
				{ __( 'If you are using a caching system such as Cloudflare then please read this: ', 'redirection' ) }
				<ExternalLink url="https://redirection.me/support/problems/cloudflare/">
					{ __( 'clearing your cache.', 'redirection' ) }
				</ExternalLink>
			</p>
		</Error>
	);
}

export default CacheDetect;
