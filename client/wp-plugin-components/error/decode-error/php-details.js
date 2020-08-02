/**
 * Try and extract a PHP error message
 *
 * @param {string} raw - Raw error details
 * @returns {string}
 */
export default function extractPhpError( raw ) {
	const parts = raw.split( '<br />' ).filter( ( item ) => item );
	const last = raw.lastIndexOf( '}' );

	if ( last !== raw.length ) {
		return raw.substr( last + 1 ).trim();
	}

	return parts
		.slice( 0, parts.length - 1 )
		.join( ' ' )
		.trim();
};
