/**
 * External dependencies
 */

import React, { useState } from 'react';
import { __ } from '@wordpress/i18n';
import PropTypes from 'prop-types';

/**
 * Local dependencies
 */
import { DropdownButton } from '@wp-plugin-components';
import Header from './header';

const getPresets = () => [
	{
		title: __( 'Add Header', 'redirection' ),
		name: 'header',
	},
	{
		title: __( 'Add Security Presets', 'redirection' ),
		name: 'security',
	},
	{
		title: __( 'Add CORS Presets', 'redirection' ),
		name: 'cors',
	},
];

const createHeader = ( headerName = 'X-Robots-Tag', headerValue = 'nofollow', headerSettings = {} ) => ( {
	location: 'site',
	type: headerName,
	headerName,
	headerValue,
	headerSettings,
} );

const createCorsHeaders = () => ( [
	createHeader( 'Access-Control-Allow-Origin', '*' ),
	createHeader( 'Access-Control-Allow-Credentials', 'true' ),
	createHeader( 'Access-Control-Allow-Methods', 'POST,GET,OPTIONS' ),
	createHeader( 'Access-Control-Allow-Headers', 'origin' ),
	createHeader( 'Referrer-Policy', 'no-referrer-when-downgrade' ),
	createHeader( 'P3P', 'CP="CAO PSA OUR"' ),
] );

const createSecurityHeaders = () => ( [
	createHeader( 'X-Frame-Options', 'deny' ),
	createHeader( 'X-XSS-Protection', '1; mode=block' ),
	createHeader( 'X-Content-Type-Options', 'nosniff' ),
	createHeader( 'Content-Security-Policy', "default-src 'none'; script-src 'self'; connect-src 'self'; img-src 'self'; style-src 'self';base-uri 'self';form-action 'self'" ),
	document.location.protocol === 'https' ? createHeader( 'Strict-Transport-Security', 'max-age: 31536000; includeSubDomains' ) : null,
	createHeader( 'Referrer-Policy', 'no-referrer-when-downgrade' ),
].filter( item => item ) );

const onChangeHeader = ( pos, attrs, existing, onChange ) => {
	const headers = existing.slice();

	headers[ pos ] = attrs;
	onChange( { headers } );
};

const onDeleteHeader = ( pos, existing, onChange ) => {
	const headers = [ ...existing.slice( 0, pos ), ...existing.slice( pos + 1 ) ];

	onChange( { headers } );
};

const onPreset = ( preset, headers, onChange ) => {
	if ( preset === 'header' ) {
		onChange( { headers: headers.concat( [ createHeader() ] ) } );
	} else if ( preset === 'security' ) {
		onChange( { headers: headers.concat( createSecurityHeaders() ) } );
	} else if ( preset === 'cors' ) {
		onChange( { headers: headers.concat( createCorsHeaders() ) } );
	}
}

const HttpHeaders = ( { headers, onChange } ) => {
	const [ preset, setPreset ] = useState( 'header' );

	return (
		<>
			<h3>{ __( 'HTTP Headers', 'redirection' ) }</h3>
			<p>{ __( 'Site headers are added across your site, including redirects. Redirect headers are only added to redirects.', 'redirection' ) }</p>

			<table className="wp-list-table widefat fixed striped items redirect-headers table-auto">
				<thead>
					<tr>
						<th>{ __( 'Location', 'redirection' ) }</th>
						<th>{ __( 'Header', 'redirection' ) }</th>
						<th></th>
					</tr>
				</thead>

				<tbody>
					{ headers.map( ( header, pos ) => (
						<Header
							key={ pos }
							header={ header }
							onChange={ attrs => onChangeHeader( pos, attrs, headers, onChange ) }
							onDelete={ () => onDeleteHeader( pos, headers, onChange ) }
						/>
					) ) }

					{ headers.length === 0 && <tr><td colSpan="3">{ __( 'No headers', 'redirection' ) }</td></tr> }
				</tbody>
			</table>

			<DropdownButton
				options={ getPresets() }
				selected={ preset }
				onChange={ setPreset }
				onSelect={ () => onPreset( preset, headers, onChange ) }
			/>

			<p>{ __( 'Note that some HTTP headers are set by your server and cannot be changed.', 'redirection' ) }</p>
		</>
	);
};

HttpHeaders.propTypes = {
	headers: PropTypes.array.isRequired,
	onChange: PropTypes.func.isRequired,
};

export default HttpHeaders;
