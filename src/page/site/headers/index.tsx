import { useState } from 'react';
import { __ } from '@wordpress/i18n';
import DropdownButton from '@wp-plugin-components/dropdown-button';
import Header from './header';

const getPresets = () => [
	{
		label: __( 'Add Header', 'redirection' ),
		value: 'header',
	},
	{
		label: __( 'Add Security Presets', 'redirection' ),
		value: 'security',
	},
	{
		label: __( 'Add CORS Presets', 'redirection' ),
		value: 'cors',
	},
];

const createHeader = ( headerName = 'X-Robots-Tag', headerValue = 'nofollow', headerSettings = {} ) => ( {
	location: 'site',
	type: headerName,
	headerName,
	headerValue,
	headerSettings,
} );

const createCorsHeaders = () => [
	createHeader( 'Access-Control-Allow-Origin', '*' ),
	createHeader( 'Access-Control-Allow-Credentials', 'true' ),
	createHeader( 'Access-Control-Allow-Methods', 'POST,GET,OPTIONS' ),
	createHeader( 'Access-Control-Allow-Headers', 'origin' ),
	createHeader( 'Referrer-Policy', 'no-referrer-when-downgrade' ),
	createHeader( 'P3P', 'CP="CAO PSA OUR"' ),
];

const createSecurityHeaders = () =>
	[
		createHeader( 'X-Frame-Options', 'deny' ),
		createHeader( 'X-XSS-Protection', '1; mode=block' ),
		createHeader( 'X-Content-Type-Options', 'nosniff' ),
		createHeader(
			'Content-Security-Policy',
			"default-src 'none'; script-src 'self'; connect-src 'self'; img-src 'self'; style-src 'self';base-uri 'self';form-action 'self'"
		),
		document.location.protocol === 'https'
			? createHeader( 'Strict-Transport-Security', 'max-age: 31536000; includeSubDomains' )
			: null,
		createHeader( 'Referrer-Policy', 'no-referrer-when-downgrade' ),
	].filter( ( item ) => item );

const onChangeHeader = (
	pos: number,
	attrs: any,
	existing: any[],
	onChange: ( settings: { headers: any[] } ) => void
) => {
	const headers = existing.slice();

	headers[ pos ] = attrs;
	onChange( { headers } );
};

const onDeleteHeader = ( pos: number, existing: any[], onChange: ( settings: { headers: any[] } ) => void ) => {
	const headers = [ ...existing.slice( 0, pos ), ...existing.slice( pos + 1 ) ];

	onChange( { headers } );
};

const onPreset = ( preset: string, headers: any[], onChange: ( settings: { headers: any[] } ) => void ) => {
	if ( preset === 'header' ) {
		onChange( { headers: headers.concat( [ createHeader() ] ) } );
	} else if ( preset === 'security' ) {
		onChange( { headers: headers.concat( createSecurityHeaders() ) } );
	} else if ( preset === 'cors' ) {
		onChange( { headers: headers.concat( createCorsHeaders() ) } );
	}
};

interface HttpHeadersProps {
	headers: any[];
	onChange: ( settings: { headers: any[] } ) => void;
}

const HttpHeaders = ( { headers, onChange }: HttpHeadersProps ) => {
	const [ preset, setPreset ] = useState( 'header' );
	const presets = getPresets();
	const selectedPreset = presets.find( ( p ) => p.value === preset );

	return (
		<>
			<h3>{ __( 'HTTP Headers', 'redirection' ) }</h3>
			<p>
				{ __(
					'Site headers are added across your site, including redirects. Redirect headers are only added to redirects.',
					'redirection'
				) }
			</p>

			<table className="wp-list-table widefat fixed striped items redirect-headers table-auto inline-edit-row">
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
							onChange={ ( attrs ) => onChangeHeader( pos, attrs, headers, onChange ) }
							onDelete={ () => onDeleteHeader( pos, headers, onChange ) }
						/>
					) ) }

					{ headers.length === 0 && (
						<tr>
							<td colSpan={ 3 }>{ __( 'No headers', 'redirection' ) }</td>
						</tr>
					) }
				</tbody>
			</table>

			<DropdownButton
				options={ presets }
				selected={ preset }
				title={ selectedPreset ? selectedPreset.label : presets[ 0 ]?.label || '' }
				onSelect={ ( value ) => {
					setPreset( value );
					onPreset( value, headers, onChange );
				} }
			/>

			<p>{ __( 'Note that some HTTP headers are set by your server and cannot be changed.', 'redirection' ) }</p>
		</>
	);
};

export default HttpHeaders;
