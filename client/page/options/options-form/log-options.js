/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'i18n-calypso';

/**
 * Internal dependencies
 */
import { TableRow } from 'component/form-table';
import { ExternalLink, Select } from 'wp-plugin-components';

const timeToKeep = () => [
	{ value: -1, label: __( 'No logs' ) },
	{ value: 1, label: __( 'A day' ) },
	{ value: 7, label: __( 'A week' ) },
	{ value: 30, label: __( 'A month' ) },
	{ value: 60, label: __( 'Two months' ) },
	{ value: 0, label: __( 'Forever' ) },
];
const ipLogging = () => [
	{ value: 0, label: __( 'No IP logging' ) },
	{ value: 1, label: __( 'Full IP logging' ) },
	{ value: 2, label: __( 'Anonymize IP (mask last part)' ) },
];

function LogOptions( props ) {
	const { settings, onChange, getLink } = props;
	const { expire_redirect, expire_404, ip_logging, log_external, track_hits, log_header, support } = settings;

	return (
		<>
			<tr className="redirect-option__row">
				<td colSpan={ 2 }>
					<h2 className="title">{ __( 'Logs' ) }</h2>
				</td>
			</tr>
			<TableRow title={ __( 'Redirect Logs' ) + ':' } url={ getLink( 'logs' ) }>
				<Select
					items={ timeToKeep() }
					name="expire_redirect"
					value={ parseInt( expire_redirect, 10 ) }
					onChange={ onChange }
				/>{' '}
				{ __( '(time to keep logs for)' ) }
			</TableRow>
			<TableRow title={ __( '404 Logs' ) + ':' } url={ getLink( 'tracking-404-errors' ) }>
				<Select
					items={ timeToKeep() }
					name="expire_404"
					value={ parseInt( expire_404, 10 ) }
					onChange={ onChange }
				/>{' '}
				{ __( '(time to keep logs for)' ) }
			</TableRow>
			<TableRow title={ __( 'IP Logging' ) + ':' } url={ getLink( 'options', 'iplogging' ) }>
				<Select
					items={ ipLogging() }
					name="ip_logging"
					value={ parseInt( ip_logging, 10 ) }
					onChange={ onChange }
				/>{' '}
				{ __( '(IP logging level)' ) }
			</TableRow>
			<TableRow title={ __( 'Logging' ) + ':' } url={ getLink( 'options', 'iplogging' ) }>
				<p>
					<label>
						<input
							type="checkbox"
							disabled={ parseInt( expire_redirect, 10 ) === -1 }
							name="log_external"
							onChange={ onChange }
							checked={ parseInt( expire_redirect, 10 ) === -1 ? false : log_external }
						/>{' '}
						{ __(
							'Log "external" redirects - those not from Redirection. This can increase your log size and contains no user information.'
						) }
					</label>
				</p>
				<p>
					<label>
						<input type="checkbox" name="track_hits" onChange={ onChange } checked={ track_hits } />{' '}
						{ __( 'Track redirect hits and date of last access. Contains no user information.' ) }
					</label>
				</p>
				<p>
					<label>
						<input type="checkbox" name="log_header" onChange={ onChange } checked={ log_header } />{' '}
						{ __(
							'Capture HTTP header information with logs (except cookies). It may include user information, and could increase your log size.'
						) }
					</label>
				</p>
			</TableRow>
			<tr>
				<th />
				<td>
					{ __(
						'Redirection stores no user identifiable information other than what is configured above. It is your responsibility to ensure your site meets any applicable {{link}}privacy requirements{{/link}}.',
						{
							components: {
								link: <ExternalLink url={ getLink( 'privacy-gdpr' ) } />,
							},
						}
					) }
				</td>
			</tr>
		</>
	);
}

export default LogOptions;
