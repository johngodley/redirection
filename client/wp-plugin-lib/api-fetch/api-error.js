/**
 * Return an error message from a JSON object
 * @param {Object} json JSON error
 * @return {String} Error message
 */
export const getErrorMessage = json => {
	if ( json === 0 ) {
		return 'Admin AJAX returned 0';
	}

	if ( typeof json ===  'string' ) {
		return json;
	}

	if ( json.message ) {
		return json.message;
	}

	console.error( json );
	return 'Unknown error ' + ( typeof json === 'object' ? Object.keys( json ) : json );
};

/**
 * Return an error code from a JSON object
 * @param {Object} json JSON error
 * @return {String} Error code
 */
export const getErrorCode = json => {
	if ( typeof json === 'number' ) {
		return `${ json }`;
	}

	if ( json.error_code ) {
		return json.error_code;
	}

	if ( json.data && json.data.error_code ) {
		return json.data.error_code;
	}

	if ( json === 0 ) {
		return 'admin-ajax';
	}

	if ( json.code ) {
		return json.code;
	}

	if ( json.name ) {
		return json.name;
	}

	return json;
};
