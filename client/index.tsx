/**
 * External dependencies
 */
import { createRoot } from 'react-dom/client';

/**
 * Internal dependencies
 */

import App from './app';

function show( dom: string ) {
	const element = document.getElementById( dom );
	if ( element ) {
		const root = createRoot( element );

		root.render( <App /> );
	}
}

if ( document.querySelector( '#react-ui' ) && window.Redirectioni10n ) {
	show( 'react-ui' );
	window.redirection = window.Redirectioni10n.version;
}
