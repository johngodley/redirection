/**
 * Internal dependencies
 */

import * as qs from 'querystring';

export function setPageUrl( query, defaults ) {
	history.pushState( {}, null, getWordPressUrl( query, defaults ) );
}

export function getPageUrl() {
	return qs.parse( document.location.search.slice( 1 ) );
}

export function getWordPressUrl( query, defaults ) {
	const existing = qs.parse( document.location.search.slice( 1 ) );

	for ( const param in query ) {
		if ( query[ param ] && defaults[ param ] !== query[ param ] ) {
			existing[ param.toLowerCase() ] = query[ param ];
		} else if ( defaults[ param ] === query[ param ] ) {
			delete existing[ param.toLowerCase() ];
		}
	}

	return '?' + qs.stringify( existing );
}
