import 'wp-plugin-lib/polyfill';

/**
 * External dependencies
 */
import React from 'react';
import ReactDOM from 'react-dom';
import i18n from 'i18n-calypso';

/**
 * Internal dependencies
 */

import App from './app';

function show( dom ) {
	i18n.setLocale( { '': window.Redirectioni10n.locale } );
	i18n.addTranslations( window.Redirectioni10n.locale.translations );

	ReactDOM.render( <App />, document.getElementById( dom ) );
}

if ( document.querySelector( '#react-ui' ) && window.Redirectioni10n ) {
	show( 'react-ui' );
	window.redirection = window.Redirectioni10n.version;
}
