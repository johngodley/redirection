/**
 * Internal dependencies
 */

import * as qs from 'qs';
import equal from 'deep-equal';

/**
 * Add query params to the page URL
 *
 * @param {object} query Query object
 * @param {object} defaults Query object of defaults that are removed
 */
export function setPageUrl( query, defaults ) {
	const url = getWordPressUrl( query, defaults, '?' );

	if ( document.location.search !== url ) {
		history.pushState( {}, '', url );
	}
}

/**
 * Remove a query param from the page URL
 * @param {string} queryToRemove Query param name to remove
 */
export function removeFromPageUrl( queryToRemove ) {
	const existing = getPageUrl();

	delete existing[ queryToRemove ];

	const newUrl = Object.keys( existing ).length === 0 ? '' : '?' + qs.stringify( existing );

	if ( document.location.search !== newUrl ) {
		history.pushState( {}, '', newUrl );
	}
}

/**
 * Get the current page query parameters
 *
 * @param {String|null} [query] Query string, or null to use the browser
 * @returns {Object.<String,String>} Query params
 */
export function getPageUrl( query ) {
	return qs.parse( query ? query.slice( 1 ) : document.location.search.slice( 1 ) );
}

/**
 * Get a WordPress admin URL
 *
 * @param {Object.<String,String>} query Query params to add
 * @param {Object.<String,String>} defaults Default query params to ignore
 * @param {String} [url] Current query params
 * @returns {String} Query string
 */
export function getWordPressUrl( query, defaults, url ) {
	const existing = getPageUrl( url );

	for ( const param in query ) {
		const isEqual = equal( defaults[ param ], query[ param ] );

		if ( query[ param ] && ! isEqual || param === 'page' ) {
			existing[ param.toLowerCase() ] = query[ param ];
		} else if ( isEqual ) {
			delete existing[ param.toLowerCase() ];
		}
	}

	return '?' + qs.stringify( existing, { arrayFormat: 'brackets' } );
}
