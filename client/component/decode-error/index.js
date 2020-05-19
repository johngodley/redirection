/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';

/**
 * Internal dependencies
 */

import ExternalLink from 'component/external-link';

const isSecurityPlugin = ( status, code ) => ( [ 400, 401, 403, 405 ].indexOf( status ) !== -1 || code === 'rest_no_route' ) && parseInt( code, 10 ) === 0;
const isServerError = status => [ 500, 502, 503 ].indexOf( status ) !== -1;
const extractPhpError = ( { raw } ) => {
	const parts = raw.split( '<br />' ).filter( item => item );
	const last = raw.lastIndexOf( '}' );

	if ( last !== raw.length ) {
		return raw.substr( last + 1 ).trim();
	}

	return parts.slice( 0, parts.length - 1 ).join( ' ' ).trim();
};

function getErrorDetails( error ) {
	if ( error.code === 0 ) {
		return error.message;
	}

	if ( error.data && error.data.wpdb ) {
		return <span>{ `${ error.message } (${ error.code })` }: <code>{ error.data.wpdb }</code></span>;
	}

	if ( error.code ) {
		return (
			<>
				{ error.message } (<code>{ error.code }</code>)
			</>
		);
	}

	return error.message;
}

const DecodeError = ( { error } ) => {
	if ( error.code === 0 ) {
		return <p>{ __( 'WordPress did not return a response. This could mean an error occurred or that the request was blocked. Please check your server error_log.' ) }</p>;
	}

	if ( error.code === 'rest_cookie_invalid_nonce' ) {
		return (
			<>
				<p>{ getErrorDetails( error ) }</p>
				<p>{ __( 'Your REST API is being cached. Please clear any caching plugin and any server cache, logout, clear your browser cache, and try again.' ) }</p>
				<p><ExternalLink url="https://redirection.me/support/problems/cloudflare/">{ __( 'Read this REST API guide for more information.' ) }</ExternalLink></p>
			</>
		);
	}

	if ( error.request && isSecurityPlugin( error.request.status, error.code ) ) {
		return (
			<>
				<p>{ getErrorDetails( error ) }</p>
				<p>{ __( 'Your REST API is probably being blocked by a security plugin. Please disable this, or configure it to allow REST API requests.' ) }</p>
				<p><ExternalLink url="https://redirection.me/support/problems/rest-api/">{ __( 'Read this REST API guide for more information.' ) }</ExternalLink></p>
			</>
		);
	}

	if ( error.request && error.request.status === 404 ) {
		return (
			<>
				<p>{ getErrorDetails( error ) }</p>
				<p>{ __( 'Your REST API is returning a 404 page. This may be caused by a security plugin, or your server may be misconfigured' ) }</p>
				<p><ExternalLink url="https://redirection.me/support/problems/rest-api/">{ __( 'Read this REST API guide for more information.' ) }</ExternalLink></p>
			</>
		);
	}

	if ( error.request && error.request.status === 413 ) {
		return <p>{ __( 'Your server has rejected the request for being too big. You will need to change it to continue.' ) }</p>;
	}

	if ( error.request && isServerError( error.request.status ) ) {
		return (
			<>
				<p>{ getErrorDetails( error ) }</p>
				<p>{ __( 'This could be a security plugin, or your server is out of memory or has an external error. Please check your server error log' ) }</p>
				<p><ExternalLink url="https://redirection.me/support/problems/rest-api/#http">{ __( 'Read this REST API guide for more information.' ) }</ExternalLink></p>
			</>
		);
	}

	if ( error.code === 'disabled' || error.code === 'rest_disabled' ) {
		return <p>{ __( 'Your WordPress REST API has been disabled. You will need to enable it for Redirection to continue working' ) }</p>;
	}

	if ( error.message.indexOf( 'Unexpected token' ) !== -1 || error.message.indexOf( 'JSON parse error' ) !== -1 ) {
		const php = extractPhpError( error.request );

		return (
			<>
				<p>{ getErrorDetails( error ) }</p>
				<p>{ __( 'WordPress returned an unexpected message. This is probably a PHP error from another plugin.' ) }</p>
				{ php.length > 1 && <p><strong>{ __( 'Possible cause' ) }:</strong> <code>{ php.substr( 0, 1000 ) }</code></p>}
			</>
		);
	}

	const message = error.message.toLowerCase();
	if ( message === 'failed to fetch' || message === 'not allowed to request resource' || message.indexOf( 'networkerror' ) !== -1 ) {
		return (
			<>
				<p>{ getErrorDetails( error ) }</p>
				<p>{ __( 'Unable to make request due to browser security. This is typically because your WordPress and Site URL settings are inconsistent, or the request was blocked by your site CORS policy.' ) }</p>
				<p><ExternalLink url="https://redirection.me/support/problems/rest-api/#url">{ __( 'Read this REST API guide for more information.' ) }</ExternalLink></p>
			</>
		);
	}

	return <p>{ getErrorDetails( error ) }</p>;
};

export default DecodeError;
