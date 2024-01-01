/**
 * External dependencies
 */

import { __ } from '@wordpress/i18n';
import TextareaAutosize from 'react-textarea-autosize';

/**
 * Internal dependencies
 */
import { TableRow } from 'component/form-table';
import { ExternalLink, MultiOptionDropdown, Notice, Select, createInterpolateElement } from 'wp-plugin-components';

const timeToKeep = () => [
	{ value: -1, label: __( 'No logs', 'redirection' ) },
	{ value: 1, label: __( 'A day', 'redirection' ) },
	{ value: 7, label: __( 'A week', 'redirection' ) },
	{ value: 30, label: __( 'A month', 'redirection' ) },
	{ value: 60, label: __( 'Two months', 'redirection' ) },
	{ value: 0, label: __( 'Forever', 'redirection' ) },
];
const ipLogging = () => [
	{ value: 0, label: __( 'No IP logging', 'redirection' ) },
	{ value: 1, label: __( 'Full IP logging', 'redirection' ) },
	{ value: 2, label: __( 'Anonymize IP (mask last part)', 'redirection' ) },
];
const ipAddress = () => [
	{ value: 'HTTP_CF_CONNECTING_IP', label: 'HTTP_CF_CONNECTING_IP' },
	{ value: 'HTTP_CLIENT_IP', label: 'HTTP_CLIENT_IP' },
	{ value: 'HTTP_X_FORWARDED_FOR', label: 'HTTP_X_FORWARDED_FOR' },
	{ value: 'HTTP_X_FORWARDED', label: 'HTTP_X_FORWARDED' },
	{ value: 'HTTP_X_CLUSTER_CLIENT_IP', label: 'HTTP_X_CLUSTER_CLIENT_IP' },
	{ value: 'HTTP_FORWARDED_FOR', label: 'HTTP_FORWARDED_FOR' },
	{ value: 'HTTP_FORWARDED', label: 'HTTP_FORWARDED' },
	{ value: 'HTTP_VIA', label: 'HTTP_VIA' },
];

function LogOptions( props ) {
	const { settings, onChange, getLink } = props;
	const { expire_redirect, expire_404, ip_logging, log_external, track_hits, log_header, ip_proxy, ip_headers } = settings;

	return (
		<>
			<tr className="redirect-option__row">
				<td colSpan={ 2 }>
					<h2 className="title">{ __( 'Logs', 'redirection' ) }</h2>
				</td>
			</tr>
			<TableRow title={ __( 'Redirect Logs', 'redirection' ) + ':' } url={ getLink( 'logs' ) }>
				<Select
					items={ timeToKeep() }
					name="expire_redirect"
					value={ parseInt( expire_redirect, 10 ) }
					onChange={ onChange }
				/>{ ' ' }
				{ __( '(time to keep logs for)', 'redirection' ) }
			</TableRow>
			<TableRow title={ __( '404 Logs', 'redirection' ) + ':' } url={ getLink( 'tracking-404-errors' ) }>
				<Select
					items={ timeToKeep() }
					name="expire_404"
					value={ parseInt( expire_404, 10 ) }
					onChange={ onChange }
				/>{ ' ' }
				{ __( '(time to keep logs for)', 'redirection' ) }
			</TableRow>
			<TableRow title={ __( 'IP Logging', 'redirection' ) + ':' } url={ getLink( 'options', 'iplogging' ) }>
				<Select
					items={ ipLogging() }
					name="ip_logging"
					value={ parseInt( ip_logging, 10 ) }
					onChange={ onChange }
				/>{ ' ' }
				{ __( '(IP logging level)', 'redirection' ) }
			</TableRow>
			<TableRow title={ __( 'IP Address', 'redirection' ) + ':' } url={ getLink( 'options', 'ipaddress' ) }>
				<MultiOptionDropdown
					options={ ipAddress() }
					selected={ ip_headers }
					multiple
					badges={ ip_headers.length > 0 }
					hideTitle={ ip_headers.length > 0 }
					onApply={ ( options ) => onChange( { ip_headers: options } ) }
					title={ ip_headers.length === 0 ? __( 'REMOTE_ADDR', 'redirection' ) : '' }
				/>

				<p>{ createInterpolateElement( __( 'Only set custom IP headers if your server does not use <code>REMOTE_ADDR</code> to store the client IP address.', 'redirection' ), { code: <code /> } ) }</p>
				{ ip_headers.length > 0 && <Notice status="warning"><p>{ createInterpolateElement( __( 'Please ensure you trust the data in these headers. If using a proxy then set its address below.', 'redirection' ), { code: <code /> } ) }</p></Notice> }
			</TableRow>
			{
				ip_headers.length > 0 &&
				<TableRow title={ __( 'Proxy Address', 'redirection' ) + ':' } url={ getLink( 'options', 'proxy' ) }>
					<TextareaAutosize minRows={ 3 } value={ ip_proxy.join( '\n' ) } onChange={ ( ev ) => onChange( { ip_proxy: ev.target.value.split( '\n' ) } ) } rows={ 5 } />
					<p>{ __( 'If using a proxy then add any IP addresses here to only trust IP headers from those addresses.', 'redirection' ) }</p>
				</TableRow>
			}
			<TableRow title={ __( 'Logging', 'redirection' ) + ':' } url={ getLink( 'options', 'iplogging' ) }>
				<p>
					<label>
						<input
							type="checkbox"
							disabled={ parseInt( expire_redirect, 10 ) === -1 }
							name="log_external"
							onChange={ onChange }
							checked={ parseInt( expire_redirect, 10 ) === -1 ? false : log_external }
						/>{ ' ' }
						{ __(
							'Log "external" redirects - those not from Redirection. This can increase your log size and contains no user information.',
							'redirection'
						) }
					</label>
				</p>
				<p>
					<label>
						<input type="checkbox" name="track_hits" onChange={ onChange } checked={ track_hits } />{ ' ' }
						{ __( 'Track redirect hits and date of last access. Contains no user information.', 'redirection' ) }
					</label>
				</p>
				<p>
					<label>
						<input type="checkbox" name="log_header" onChange={ onChange } checked={ log_header } />{ ' ' }
						{ __(
							'Capture HTTP header information with logs (except cookies). It may include user information, and could increase your log size.',
							'redirection'
						) }
					</label>
				</p>
			</TableRow>
			<tr>
				<th />
				<td>
					{ createInterpolateElement(
						__(
							'Redirection stores no user identifiable information other than what is configured above. It is your responsibility to ensure your site meets any applicable {{link}}privacy requirements{{/link}}.',
							'redirection'
						),
						{
							link: <ExternalLink url={ getLink( 'privacy-gdpr' ) } />,
						}
					) }
				</td>
			</tr>
		</>
	);
}

export default LogOptions;
