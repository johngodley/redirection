/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import * as parseUrl from 'url';

const isRegex = ( text ) => {
	if ( text.match( /[\*\\\(\)\[\]\^\$]/ ) !== null ) {
		return true;
	}

	if ( text.indexOf( '.?' ) !== -1 ) {
		return true;
	}

	return false;
};

const getWarningFromState = ( state ) => {
	const warnings = [];

	// Anchor value
	if ( state.url.indexOf( '#' ) !== -1 ) {
		warnings.push(
			__( 'Anchor values are not sent to the server and cannot be redirected.' ),
		);
	}

	// Server redirect
	if ( state.url.substr( 0, 4 ) === 'http' && state.url.indexOf( document.location.origin ) === -1 ) {
		warnings.push(
			__( 'This will be converted to a server redirect for the domain {{code}}%(server)s{{/code}}.', {
				components: {
					code: <code />,
				},
				args: {
					server: parseUrl.parse( state.url ).hostname,
				},
			} )
		);
	}

	// Regex without checkbox
	if ( isRegex( state.url ) && state.regex === false ) {
		warnings.push(
			__( 'Remember to enable the "regex" checkbox if this is a regular expression.' ),
		);
	}

	// Anchor
	if ( isRegex( state.url ) && state.url.indexOf( '^' ) === -1 && state.url.indexOf( '$' ) === -1 ) {
		warnings.push(
			__( 'To prevent a greedy regular expression you can use a {{code}}^{{/code}} to anchor it to the start of the URL. For example: {{code}}%(example)s{{/code}}', {
				components: {
					code: <code />,
				},
				args: {
					example: '^' + state.url,
				},
			} ),
		);
	}

	// Redirect everything
	if ( state.url === '/(.*)' || state.url === '^/(.*)' ) {
		warnings.push( __( 'This will redirect everything, including the login pages. Please be sure you want to do this.' ) );
	}

	return warnings;
};

export default getWarningFromState;
