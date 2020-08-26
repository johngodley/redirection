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
	i18n.setLocale( {
		'': { localeSlug: Redirectioni10n.locale.localeSlug, 'Plural-Forms': Redirectioni10n.locale.plurals },
	} );
	i18n.addTranslations( Redirectioni10n.locale.translations );

	ReactDOM.render( <App />, document.getElementById( dom ) );
};

if ( document.querySelector( '#react-ui' ) ) {
	show( 'react-ui' );
}

window.redirection = Redirectioni10n.version;
