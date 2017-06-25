/* global document, Redirectioni10n */
/**
 * External dependencies
 */

import React from 'react';
import ReactDOM from 'react-dom';
import { AppContainer } from 'react-hot-loader';
import i18n from 'lib/locale';

/**
 * Internal dependencies
 */

import App from './app';

const render = ( Component, dom ) => {
	ReactDOM.render(
		<AppContainer>
			<Component />
		</AppContainer>,

		document.getElementById( dom )
	);
};

const show = dom => {
	i18n.setLocale( {
		'': { localeSlug: Redirectioni10n.localeSlug }
	} );

	if ( module.hot ) {
		module.hot.accept( './app', () => {
			render( App, dom );
		} );
	}

	render( App, dom );
};

window.redirection = {
	show,
};
