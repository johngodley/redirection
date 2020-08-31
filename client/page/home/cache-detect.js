/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'i18n-calypso';

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
			title={ __( 'Cached Redirection detected' ) }
			links={ getErrorLinks() }
		>
			<p>{ __( 'Please clear your browser cache and reload this page.' ) }</p>
			<p>
				{ __( 'If you are using a caching system such as Cloudflare then please read this: ' ) }
				<ExternalLink url="https://redirection.me/support/problems/cloudflare/">
					{ __( 'clearing your cache.' ) }
				</ExternalLink>
			</p>
		</Error>
	);
}

export default CacheDetect;
