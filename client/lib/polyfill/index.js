/* global window, fetch, System */

/**
 * External dependencies
 */

import Plite from 'plite';

! window.Promise && ( window.Promise = Plite );

if ( ! Array.from ) {
	// $FlowFixMe
	Array.from = function( object ) {
		return [].slice.call( object );
	};
}

if ( typeof Object.assign !== 'function' ) {
	( function() {
		// $FlowFixMe
		Object.assign = function( target ) {
			// We must check against these specific cases.
			if ( target === undefined || target === null ) {
				throw new TypeError( 'Cannot convert undefined or null to object' );
			}

			const output = Object( target );
			for ( let index = 1; index < arguments.length; index++ ) {
				const source = arguments[ index ];

				if ( source !== undefined && source !== null ) {
					for ( const nextKey in source ) {
						if ( source.hasOwnProperty( nextKey ) ) {
							output[ nextKey ] = source[ nextKey ];
						}
					}
				}
			}

			return output;
		};
	} )();
}

function hotFetch( params ) {
	if ( window.fetch && window.fetch !== hotFetch ) {
		return window.fetch( params );
	}

	delete window.fetch;

	// $FlowFixMe
	return import( /* webpackChunkName: "compat" */ './compat' ).then( () => {
		return fetch( params );
	} );
}

if ( ! window.fetch ) {
	window.fetch = hotFetch;
}
