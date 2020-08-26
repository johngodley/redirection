/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'i18n-calypso';

/**
 * Internal dependencies
 */
import { ExternalLink, Error } from 'wp-plugin-components';

function CacheDetect() {
	return (
		<Error
			errors={ [
				Redirectioni10n.versions + '\nServer: ' + Redirectioni10n.version + ' !== ' + REDIRECTION_VERSION,
			] }
			versions={ Redirectioni10n.versions }
			type="fixed"
			title={ __( 'Cached Redirection detected' ) }
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
