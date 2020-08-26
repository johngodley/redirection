/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'i18n-calypso';

/**
 * Internal dependencies
 */
import { ExternalLink, Error } from 'wp-plugin-components';
import getErrorLinks from 'lib/error-links';
import DebugReport from './debug';

function CrashHandler( stack, errorInfo, extra ) {
	const debug = [
		Redirectioni10n.versions,
		'Query: ' + document.location.search,
		'Buster: ' + REDIRECTION_VERSION + ' === ' + Redirectioni10n.version,
		'',
		stack ? stack : '',
	];

	if ( errorInfo ) {
		debug.push( errorInfo.componentStack );
	}

	return (
		<Error
			errors={ [ debug.join( '\n' ) ] }
			renderDebug={ DebugReport }
			type="fixed"
			links={ getErrorLinks() }
		>
			<p>
				{ __( 'Redirection is not working. Try clearing your browser cache and reloading this page.' ) } &nbsp;
				{ __(
					'If you are using a page caching plugin or service (CloudFlare, OVH, etc) then you can also try clearing that cache.'
				) }
			</p>

			<p>
				{ __(
					"If that doesn't help, open your browser's error console and create a {{link}}new issue{{/link}} with the details.",
					{
						components: {
							link: <ExternalLink url="https://github.com/johngodley/redirection/issues" />,
						},
					}
				) }
			</p>
		</Error>
	);
}

export default CrashHandler;
