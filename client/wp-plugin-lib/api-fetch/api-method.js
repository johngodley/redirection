/**
 * External dependencies
 */
import querystring from 'qs';

/**
 * Internal dependencies
 */

const getRequestString = ( path, params = {} ) => {
	const base = path + '/';

	if ( Object.keys( params ).length > 0 && querystring.stringify( params ).length > 0 ) {
		return base + ( base.indexOf( '?' ) === -1 ? '?' : '&' ) + querystring.stringify( params );
	}

	return base;
};

const getApiHeaders = () => {
	return {
		Accept: 'application/json, */*;q=0.1',
	};
}

const postApiheaders = () => {
	return {
		'Content-Type': 'application/json; charset=utf-8',
		Accept: 'application/json, */*;q=0.1',
	};
};

/**
 * Return a request GET object suitable for `fetch`
 * @param {String} path URL path
 * @param {Object} query Query parameters
 */
export const getApiRequest = ( path, query = {} ) => ( {
	headers: getApiHeaders(),
	url: getRequestString( path, query ),
	credentials: 'include',
	method: 'get',
	redirect: 'error',
} );

/**
 * Return a request POST object suitable for `fetch`
 * @param {String} path URL path
 * @param {Object} params Body parameters
 * @param {Object} query Query parameters
 * @return {Object} Request object
 */
export const postApiRequest = ( path, params = {}, query = {} ) => {
	const request = {
		headers: postApiheaders(),
		url: getRequestString( path, query ),
		credentials: 'include',
		method: 'post',
		body: '{}',
	};

	if ( Object.keys( params ).length > 0 ) {
		request.body = JSON.stringify( params );
	}

	return request;
};

/**
 * Upload a file to an API endpoint
 *
 * @param {String} path URL path
 * @param {Object} params Body parameters
 * @param {File} file File to upload
 */
export const uploadApiRequest = ( path, params, file ) => {
	const request = postApiRequest( path, params );

	delete request.headers['Content-Type'];
	request.body = new FormData();
	request.body.append( 'file', file );

	return request;
};

export const deleteApiRequest = ( path, params = {}, query = {} ) => postApiRequest( path, params, query );
