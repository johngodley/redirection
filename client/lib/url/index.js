import * as parseUrl from 'url';

function parseUrlParts( url ) {
	if ( url.substr( 0, 5 ) !== 'http:' && url.substr( 0, 6 ) !== 'https:' ) {
		return parseUrl.parse( document.location.protocol + '//' + url );
	}

	return parseUrl.parse( url );
}

export function getParts( url, buildUrl ) {
	if ( url === false ) {
		return false;
	}

	const parts = parseUrlParts( url );
	if ( parts.host && parts.host.length > 0 ) {
		return buildUrl( parts );
	}

	return '';
}

export function getDomainAndPathOnly( url ) {
	return getParts( url, ( parts ) => {
		return parts.protocol + '//' + ( parts.host || '' ) + ( parts.pathname || '' );
	} );
}

export function getDomainOnly( url ) {
	return getParts( url, ( parts ) => {
		return parts.protocol + '//' + ( parts.host || '' )
	} );
}
