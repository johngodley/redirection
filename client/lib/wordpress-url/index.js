/* global document */
/**
 * Internal dependencies
 */

import * as qs from 'querystring';

const ALLOWED_PAGES = [ 'groups', '404s', 'log', 'modules', 'options', 'support' ];

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

	if ( existing.filterby && ! existing.filter ) {
		delete existing.filterby;
	}

	return '?' + qs.stringify( existing );
}

export function getPluginPage( url ) {
	const params = url ? url.split( '&' ) : document.location.search.split( '&' );

	if ( params.length > 1 ) {
		const page = params[ 1 ].split( '=' )[ 1 ];

		if ( ALLOWED_PAGES.indexOf( page ) !== -1 ) {
			return page;
		}
	}

	return 'redirect';
}
