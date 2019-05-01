/* global document, Redirectioni10n */
import 'lib/polyfill';

/**
 * External dependencies
 */

import React from 'react';
import ReactDOM from 'react-dom';
import i18n from 'lib/locale';

/**
 * Internal dependencies
 */

import App from './app';

const show = dom => {
	i18n.setLocale( { '': { localeSlug: Redirectioni10n.localeSlug } } );
	i18n.addTranslations( Redirectioni10n.locale );

	ReactDOM.render( <App />, document.getElementById( dom ) );
};

if ( document.querySelector( '#react-ui' ) ) {
	show( 'react-ui' );
}

window.redirection = Redirectioni10n.version;
