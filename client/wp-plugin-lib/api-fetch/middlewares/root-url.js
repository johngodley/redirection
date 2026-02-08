function removeWP( url ) {
	return url.replace( 'wp-json/wp-json', 'wp-json' ).replace( '=/wp-json', '=' );
}

function removeTrailingSlash( url ) {
	return url.replace( /\/$/, '' );
}

function removeLeadingSlash( url ) {
	return url.replace( /^\//, '' );
}

function convertParams( root, url ) {
	if ( root.indexOf( '?' ) !== -1 ) {
		return url.replace( '?', '&' );
	}

	return url;
}

function addRoute( base, route ) {
	return base + '/' + route;
}

function mergeWithRoot( root, url ) {
	return removeWP( addRoute( removeTrailingSlash( root ), convertParams( root, removeLeadingSlash( url ) ) ) );
}

function createRootURLMiddleware( rootURL ) {
	function middleware( options, next ) {
		// Absolute URL - do nothing
		if ( options.url.substr( 0, 4 ) === 'http' ) {
			return next( options );
		}

		return next( {
			...options,
			url: mergeWithRoot( rootURL, options.url ),
		} );
	}

	middleware.rootURL = rootURL;

	return middleware;
}

export default createRootURLMiddleware;
