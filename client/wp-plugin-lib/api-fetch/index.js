/**
 * Internal dependencies
 */

import { getErrorCode, getErrorMessage } from './api-error';
import createNonceMiddleware from './middlewares/nonce';
import createRootURLMiddleware from './middlewares/root-url';

let middlewares = [];

function createApiError( code, message, request ) {
	return {
		code: getErrorCode( code ),
		message: getErrorMessage( message ),
		request,
		data: request.apiFetch.data ? request.apiFetch.data : null,
	};
}

function registerMiddleware( middleware ) {
	middlewares.unshift( middleware );
}

const checkStatus = ( response ) => {
	if ( response.status >= 200 && response.status < 300 ) {
		return response;
	}

	throw response;
};

const recordResponse = ( response, request ) => {
	request.apiFetch = {
		action: request.url.replace( /[\?&]_wpnonce=[a-f0-9]*/, '' ) + ' ' + request.method.toUpperCase(),
		body: typeof request.body === 'object' ? JSON.stringify( request.body ) : request.body,
	};

	if ( response.status && response.statusText !== undefined ) {
		request.apiFetch.status = response.status;
		request.apiFetch.statusText = response.statusText;
	}

	if ( response.redirected ) {
		request.apiFetch.redirected = response.redirected;
	}

	return response;
};

const recordData = ( response, request ) => {
	request.apiFetch.data = response;
	return response;
};

const checkResponse = ( response, request ) => {
	if ( response.code || response.error_code ) {
		throw createApiError( response, response.message, request );
	}

	if ( request.apiFetch ) {
		const { status, redirected, statusText } = request.apiFetch;

		if ( status < 200 || status >= 300 ) {
			throw createApiError( status, statusText, request );
		}

		if ( redirected ) {
			throw createApiError( 'rest_api_redirected', 'REST API redirected', request );
		}
	}

	return response;
};

function setNonce( response ) {
	if ( response.headers.get( 'x-wp-nonce' ) && apiFetch.nonceMiddleware ) {
		apiFetch.nonceMiddleware.nonce = response.headers.get( 'x-wp-nonce' );
	}

	return response;
}

const getResponseData = ( response ) => response.text();

const parseResponse = ( response, request ) => {
	try {
		const json = JSON.parse( response.replace( /\ufeff/, '' ) );

		if ( json === 0 ) {
			throw createApiError( 'json-zero', 'Failed to get data', request );
		}

		return json;
	} catch ( error ) {
		throw createApiError( error, error.message, request );
	}
};

function fetchHandler( request ) {
	return fetch( request.url, request )
		.then( setNonce )
		.then( ( response ) => recordResponse( response, request ) )
		.then( getResponseData )
		.then( ( response ) => recordData( response, request ) )
		.then( ( response ) => parseResponse( response, request ) )
		.then( ( response ) => checkResponse( response, request ) );
}

/**
 * Perform an API request. Based on Gutenberg apiFetch
 *
 * @param {*} request
 * @returns {Promise.<object>} Fetch promise
 */
function apiFetch( request ) {
	const steps = [ ...middlewares, fetchHandler ];

	const createRunStep = ( index ) => ( workingOptions ) => {
		const step = steps[ index ];
		if ( index === steps.length - 1 ) {
			return step( workingOptions );
		}

		const next = createRunStep( index + 1 );
		return step( workingOptions, next );
	};

	return new Promise( ( resolve, reject ) => {
		createRunStep( 0 )( request )
			.then( resolve )
			.catch( ( error ) => {
				if ( error.code !== 'rest_cookie_invalid_nonce' ) {
					return reject( error );
				}

				// If the nonce is invalid, refresh it and try again.
				window
					.fetch( 'admin-ajax.php?action=rest-nonce' )
					.then( checkStatus )
					.then( getResponseData )
					.then( ( text ) => {
						apiFetch.nonceMiddleware.nonce = text;

						// Try again
						apiFetch( request )
							.then( resolve )
							.catch( reject );
					} )
					.catch( reject );
			} );
	} );
}

apiFetch.getUrl = ( url ) => apiFetch.rootURLMiddleware( { url }, ( options ) => apiFetch.nonceMiddleware( options, ( item ) => item.url ) );
apiFetch.use = registerMiddleware;
apiFetch.createNonceMiddleware = ( nonce ) => {
	const middle = createNonceMiddleware( nonce );
	apiFetch.nonceMiddleware = middle;
	return middle;
};
apiFetch.createRootURLMiddleware = ( rootURL ) => {
	const middle = createRootURLMiddleware( rootURL );
	apiFetch.rootURLMiddleware = middle;
	return middle;
};
apiFetch.resetMiddlewares = () => {
	middlewares = [];
};

export default apiFetch;
