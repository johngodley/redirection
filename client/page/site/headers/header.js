/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';

/**
 * Internal dependencies
 */
import Select from 'wp-plugin-components/select';
import ExternalLink from 'wp-plugin-components/external-link';
import {
	HeaderSimpleChoice,
	HeaderReplaceSimpleChoice,
	HeaderCustom,
	HeaderPlainValue,
	HeaderMultiChoice,
} from './types';

const arrayToSelect = array => array.map( header => ( { label: header, value: header } ) );

const getLocations = () => [
	{
		label: __( 'Site' ),
		value: 'site',
	},
	{
		label: __( 'Redirect' ),
		value: 'redirect',
	},
];

const knownHeaders = {
	'X-UA-Compatible': {
		component: HeaderSimpleChoice,
		info: 'https://frankcode.net/2013/10/17/a-guide-to-ie-compatibility-view-and-x-ua-compatible/',
		default: 'Chrome=1',
		options: arrayToSelect( [
			'IE=EmulateIE7',
			'IE=edge',
			'Chrome=1',
		] ),
	},
	'X-Frame-Options': {
		component: HeaderReplaceSimpleChoice,
		info: '',
		options: {
			choices: arrayToSelect( [
				'deny',
				'sameorigin',
				'allow-from <URI>',
			] ),
			replace: 'URI',
			replaceType: 'uri',
		},
	},
	'Strict-Transport-Security': {
		component: HeaderReplaceSimpleChoice,
		default: 'max-age=<expire-time>',
		info: 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Strict-Transport-Security',
		options: {
			choices: arrayToSelect( [
				'max-age=<expire-time>',
				'max-age=<expire-time>; includeSubDomains',
				'max-age=<expire-time>; preload',
			] ),
			replace: 'expire-time',
			replaceType: 'integer',
		},
	},
	'X-XSS-Protection': {
		component: HeaderReplaceSimpleChoice,
		default: '1; mode=block',
		info: 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-XSS-Protection',
		options: {
			choices: arrayToSelect( [
				'0',
				'1',
				'1; mode=block',
				'1; report=<URI>',
			] ),
			replace: 'URI',
			replaceType: 'uri',
		},
	},
	'X-Content-Type-Options': {
		component: HeaderSimpleChoice,
		default: 'nosniff',
		info: 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Content-Type-Options',
		options: arrayToSelect( [
			'nosniff',
		] ),
	},
	'Feature-Policy': {
		component: HeaderPlainValue,
		info: 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Feature-Policy',
	},
	'Clear-Site-Data': {
		component: HeaderMultiChoice,
		info: 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Clear-Site-Data',
		options: {
			choices: arrayToSelect( [
				'cache',
				'cookies',
				'storage',
				'executionContexts',
			] ),
			implode: ',',
			wildCard: '*',
		},
	},
	'Referrer-Policy': {
		component: HeaderSimpleChoice,
		default: 'no-referrer-when-downgrade',
		info: 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Referrer-Policy',
		options: arrayToSelect( [
			'no-referrer',
			'no-referrer-when-downgrade',
			'origin',
			'origin-when-cross-origin',
			'same-origin',
			'strict-origin',
			'strict-origin-when-cross-origin',
			'unsafe-url',
		] ),
	},
	'Content-Security-Policy-Report-Only': {
		component: HeaderPlainValue,
		info: 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy-Report-Only',
	},
	Custom: {
		component: HeaderCustom,
	},
	'Access-Control-Allow-Methods': {
		component: HeaderMultiChoice,
		info: 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Methods',
		options: {
			choices: arrayToSelect( [
				'GET',
				'POST',
				'PUT',
				'HEAD',
				'DELETE',
				'OPTIONS',
			] ),
			implode: ',',
			wildCard: '*',
		},
	},
	'Access-Control-Allow-Credentials': {
		component: HeaderSimpleChoice,
		default: 'true',
		info: 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Credentials',
		options: arrayToSelect( [
			'true',
		] ),
	},
	'Access-Control-Allow-Origin': {
		component: HeaderReplaceSimpleChoice,
		default: '<origin>',
		info: 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Origin',
		options: {
			choices: arrayToSelect( [
				'<origin>',
				'*',
			] ),
			replace: 'origin',
			replaceType: 'uri',
		},
	},
	'X-Robots-Tag': {
		component: HeaderMultiChoice,
		default: 'noindex',
		info: 'https://developers.google.com/search/reference/robots_meta_tag#xrobotstag',
		options: {
			choices: arrayToSelect( [
				'noindex',
				'nofollow',
				'none',
				'noarchive',
				'nosnippet',
				'notranslate',
				'noimageindex',
			] ),
			implode: ',',
			wildCard: 'all',
		},
	},
	'Access-Control-Allow-Headers': {
		component: HeaderPlainValue,
		info: 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Headers',
	},
	'Access-Control-Max-Age': {
		component: HeaderPlainValue,
		info: 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Max-Age',
	},
	'Access-Control-Expose-Headers': {
		component: HeaderPlainValue,
		info: 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Expose-Headers',
	},
};

const getComponent = name => knownHeaders[ name ] ? knownHeaders[ name ].component : HeaderPlainValue;
const getOptions = name => knownHeaders[ name ] && knownHeaders[ name ].options ? knownHeaders[ name ].options : null;
const getInfo = name => knownHeaders[ name ] && knownHeaders[ name ].info ? knownHeaders[ name ].info : null;
const getDefault = name => knownHeaders[ name ] && knownHeaders[ name ].default ? knownHeaders[ name ].default : '';

const getHeaderComponent = ( type, headerName, headerValue, headerSettings, onChange ) => {
	const Component = getComponent( type );

	return (
		<Component
			headerName={ headerName }
			headerValue={ headerValue === '' ? getDefault( type ) : headerValue }
			headerSettings={ headerSettings }
			options={ getOptions( type ) }
			onChange={ onChange }
		/>
	);
};

const getHeaders = () => {
	return [
		{
			label: __( 'General' ),
			value: arrayToSelect( [
				'X-UA-Compatible',
				'X-Robots-Tag',
			] ),
		},
		{
			label: 'CORS',
			value: arrayToSelect( [
				'Access-Control-Allow-Headers',
				'Access-Control-Allow-Methods',
				'Access-Control-Max-Age',
				'Access-Control-Allow-Credentials',
				'Access-Control-Allow-Origin',
				'Access-Control-Expose-Headers',
			] ),
		},
		{
			label: 'Security',
			value: arrayToSelect( [
				'X-Frame-Options',
				'X-XSS-Protection',
				'X-Content-Type-Options',
				'Strict-Transport-Security',
				'Feature-Policy',
				'Clear-Site-Data',
				'Referrer-Policy',
				'Content-Security-Policy',
				'Content-Security-Policy-Report-Only',
				'P3P',
			] ),
		},
		{
			label: __( 'Custom Header' ),
			value: 'Custom',
		},
	];
};

const Header = ( { header, onChange, onDelete } ) => {
	const { location, headerName, headerValue, headerSettings, type } = header;
	const saveHeader = attrs => {
		onChange( { ...header, ...attrs } );
	};
	const changeHeader = ( ev ) => {
		const { name, value } = ev.target;
		let hName = headerName;

		if ( name === 'type' && value === 'Custom' ) {
			hName = '';
		} else if ( name === 'type' ) {
			hName = value;
		}

		saveHeader( {
			headerValue: name === 'type' ? '' : header.headerValue,
			headerSettings: name === 'type' ? getDefault( value ) : header.headerSettings,
			headerName: hName,
			[ name ]: value,
		} );
	};
	const deleteIt = ev => {
		ev.preventDefault();
		onDelete();
	};
	const options = getHeaderComponent( type, headerName, headerValue, headerSettings, saveHeader );
	const info = getInfo( headerName );

	return (
		<tr className="redirect-headers__item">
			<td className="redirect-headers__type">
				<Select items={ getLocations() } name="location" value={ location } onChange={ changeHeader } />
			</td>

			<td className="redirect-headers__name">
				<div className="redirect-headers__name__content">
					<Select items={ getHeaders() } name="type" value={ type } onChange={ changeHeader } />

					{ options }
				</div>

				{ info && <ExternalLink url={ info }><span className="dashicons dashicons-editor-help"></span></ExternalLink> }
			</td>

			<td className="redirect-headers__delete">
				<button onClick={ deleteIt }><span className="dashicons dashicons-trash"></span></button>
			</td>
		</tr>
	);
};

export default Header;
