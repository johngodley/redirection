/* global document, Redirectioni10n */
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

const show = ( dom ) => {
	i18n.setLocale( { '': Redirectioni10n.locale } );
	i18n.addTranslations( Redirectioni10n.locale.translations );

	ReactDOM.render( <App />, document.getElementById( dom ) );
};

if ( document.querySelector( '#react-ui' ) && Redirectioni10n ) {
	show( 'react-ui' );
	window.redirection = Redirectioni10n.version;
}
