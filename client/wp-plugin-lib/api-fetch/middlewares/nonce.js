function addNonceToUrl( url, nonce ) {
	return url + ( url.indexOf( '?' ) === -1 ? '?' : '&' ) + '_wpnonce=' + encodeURIComponent( nonce );
}

function createNonceMiddleware( nonce ) {
	function middleware( options, next ) {
		return next( {
			...options,
			url: addNonceToUrl( options.url, middleware.nonce ),
		} );
	}

	middleware.nonce = nonce;

	return middleware;
}

export default createNonceMiddleware;
