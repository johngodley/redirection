/**
 * Has a security plugin caused a problem?
 *
 * @param {Error|ApiError} error - Error
 * @returns {boolean}
 */
export function isSecurityPlugin( error ) {
	const { request, code } = error;

	if ( request && request.status && code ) {
		return ( [ 400, 401, 403, 405 ].indexOf( request.status ) !== -1 || code === 'rest_no_route' ) && parseInt( code, 10 ) === 0;
	}

	return false;
}

/**
 * Has a security plugin caused a problem?
 *
 * @param {Error|ApiError} error - Error
 * @returns {boolean}
 */
export function isServerError( error ) {
	return [ 500, 502, 503 ].indexOf( error?.request?.apiFetch?.status ) !== -1;
}

/**
 * Was the request rejected with a nonce error? Usually the session timed out, or the user's site is cached
 *
 * @param {Error|ApiError} error - Error
 * @returns {boolean}
 */
export function isNonceError( error ) {
	return error.code && error.code === 'rest_cookie_invalid_nonce';
}

/**
 * Is there any response at all?
 *
 * @param {Error|ApiError} error - Error
 * @returns {boolean}
 */
export function isEmptyResponse( error ) {
	return typeof error.code !== 'undefined' && error.code === 0;
}

/**
 * Is the REST API returning a 404? Usually a security plugin
 *
 * @param {Error|ApiError} error - Error
 * @returns {boolean}
 */
export function is404( error ) {
	return error?.request?.apiFetch?.status === 404;
}

/**
 * Was a server 'too big' response returned, usually found for uploads
 *
 * @param {Error|ApiError} error - Error
 * @returns {boolean}
 */
export function isTooBig( error ) {
	return error?.request?.apiFetch && error?.request?.apiFetch?.status === 413;
}

/**
 * Is the WordPress REST API disabled?
 *
 * @param {Error|ApiError} error - Error
 * @returns {boolean}
 */
export function isRESTDisabled( error ) {
	return error?.code === 'disabled' || error?.code === 'rest_disabled';
}

/**
 * The wonderful unknown error
 *
 * @param {Error|ApiError} error - Error
 * @returns {boolean}
 */
export function isUnknownError( error ) {
	return typeof error.message === 'undefined';
}

/**
 * The API is redirected
 *
 * @param {Error|ApiError} error - Error
 * @returns {boolean}
 */
export function isRedirectedAPI( error ) {
	return error?.code === 'rest_api_redirected';
}

/**
 * Was there an error parsing the response? Typically caused by a PHP error, or some other non-JSON data being output
 *
 * @param {Error|ApiError} error - Error
 * @returns {boolean}
 */
export function isParseError( error ) {
	const { message } = error;

	if ( error.code === 'SyntaxError' ) {
		return true;
	}

	return message.indexOf( 'Unexpected token' ) !== -1 || message.indexOf( 'JSON parse error' ) !== -1;
}

/**
 * Has the fetch request failed? Usually this is because of CORS, site settings, or an API redirect
 *
 * @param {Error|ApiError} error - Error
 * @returns {boolean}
 */
export function isFailedFetch( error ) {
	const message = error?.message?.toLowerCase();

	if ( message ) {
		return message === 'failed to fetch' || message === 'not allowed to request resource' || message.indexOf( 'networkerror' ) !== -1;
	}

	return false;
}
