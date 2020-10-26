/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'i18n-calypso';
import { ExternalLink } from 'wp-plugin-components';

function DebugReport( debug ) {
	const email =
		'mailto:john@redirection.me?subject=Redirection%20Error&body=' + encodeURIComponent( debug );
	const github =
		'https://github.com/johngodley/redirection/issues/new?title=Redirection%20Error&body=' +
		encodeURIComponent( '```\n' + debug.trim() + '\n```\n\n' );

	return (
		<>
			<p className="wpl-error__highlight">
				{ __( 'Please check the {{link}}support site{{/link}} before proceeding further.', {
					components: {
						link: <ExternalLink url="https://redirection.me/support/" />,
					},
				} ) }
			</p>
			<p>
				{ __(
					'If that did not help then {{strong}}create an issue{{/strong}} or send it in an {{strong}}email{{/strong}}.',
					{
						components: {
							strong: <strong />,
						},
					}
				) }
			</p>
			<p>
				<a href={ github } className="button-primary">
					{ __( 'Create An Issue' ) }
				</a>{' '}
				<a href={ email } className="button-secondary">
					{ __( 'Email' ) }
				</a>
			</p>
			<p>
				{ __(
					'Include these details in your report along with a description of what you were doing and a screenshot.'
				) }
			</p>
		</>
	);
}

export default DebugReport;
