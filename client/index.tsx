/**
 * External dependencies
 */
import ReactDOM from 'react-dom';

/**
 * Internal dependencies
 */

import App from './app';

function show( dom ) {
	ReactDOM.render( <App />, document.getElementById( dom ) );
}

if ( document.querySelector( '#react-ui' ) && window.Redirectioni10n ) {
	show( 'react-ui' );
	window.redirection = window.Redirectioni10n.version;
}
