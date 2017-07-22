/* global document */
/**
 * Internal dependencies
 */

import * as qs from 'querystring';

const ALLOWED_PAGES = [ 'groups', '404s', 'log', 'modules', 'options', 'support' ];

export function setPageUrl( query, defaults ) {
	history.pushState( {}, null, getWordPressUrl( query, defaults ) );
}

export function getPageUrl( query ) {
	return qs.parse( query ? query.slice( 1 ) : document.location.search.slice( 1 ) );
}

export function getWordPressUrl( query, defaults, url ) {
	const existing = getPageUrl( url );

	for ( const param in query ) {
		if ( query[ param ] && defaults[ param ] !== query[ param ] ) {
			existing[ param.toLowerCase() ] = query[ param ];
		} else if ( defaults[ param ] === query[ param ] ) {
			delete existing[ param.toLowerCase() ];
		}
	}

	if ( existing.filterby && ! existing.filter ) {
		delete existing.filterby;
	}

	return '?' + qs.stringify( existing );
}

export function getPluginPage( url ) {
	const params = getPageUrl( url );

	if ( ALLOWED_PAGES.indexOf( params.sub ) !== -1 ) {
		return params.sub;
	}

	return 'redirect';
}
