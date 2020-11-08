/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'i18n-calypso';

/**
 * Internal dependencies
 */
import { TableRow } from 'component/form-table';
import { Select } from 'wp-plugin-components';

export const restApi = () => [
	{ value: 0, label: __( 'Default REST API' ) },
	{ value: 1, label: __( 'Raw REST API' ) },
	{ value: 3, label: __( 'Relative REST API' ) },
];

function OtherOptions( props ) {
	const { settings, onChange, getLink, installed, warning } = props;
	const {
		location,
		token,
		rest_api,
	} = settings;

	return (
		<>
			<tr className="redirect-option__row">
				<td colSpan={ 2 }>
					<h2 className="title">{ __( 'Other' ) }</h2>
				</td>
			</tr>
			<TableRow title={ __( 'RSS Token' ) + ':' } url={ getLink( 'options', 'rsstoken' ) }>
				<input className="regular-text" type="text" value={ token } name="token" onChange={ onChange } />
				<br />
				<span className="sub">
					{ __(
						'A unique token allowing feed readers access to Redirection log RSS (leave blank to auto-generate)'
					) }
				</span>
			</TableRow>
			<TableRow title={ __( 'Apache .htaccess' ) } url={ getLink( 'options', 'apache' ) }>
				<label>
					<p>
						<input
							type="text"
							className="regular-text"
							name="location"
							value={ location }
							onChange={ onChange }
						/>
					</p>

					<p className="sub">
						{ __(
							'Redirects added to an Apache group can be saved to an {{code}}.htaccess{{/code}} file by adding the full path here. For reference, your WordPress is installed to {{code}}%(installed)s{{/code}}.',
							{
								components: {
									code: <code />,
								},
								args: {
									installed,
								},
							}
						) }
					</p>

					{ warning && (
						<p className="inline-notice">
							{ __( 'Unable to save .htaccess file' ) } <code>{ warning }</code>
						</p>
					) }
				</label>
			</TableRow>
			<TableRow title={ __( 'REST API' ) } url={ getLink( 'options', 'restapi' ) }>
				<Select
					items={ restApi() }
					name="rest_api"
					value={ parseInt( rest_api, 10 ) }
					onChange={ onChange }
				/>{' '}
				&nbsp;
				<span className="sub">
					{ __( "How Redirection uses the REST API - don't change unless necessary" ) }
				</span>
			</TableRow>
		</>
	);
}

export default OtherOptions;
