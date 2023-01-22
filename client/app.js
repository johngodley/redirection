/**
 * External dependencies
 */

import { Provider } from 'react-redux';

/**
 * Internal dependencies
 */

import createReduxStore from 'state';
import { getInitialState } from 'state/initial';
import Home from './page/home';
import apiFetch from 'wp-plugin-lib/api-fetch';

// Validate the locale works with the browser
try {
	new Intl.NumberFormat( window.Redirectioni10n.locale );
} catch {
	window.Redirectioni10n.locale = 'en-US';
}

// Set API nonce and root URL
apiFetch.resetMiddlewares();
apiFetch.use( apiFetch.createRootURLMiddleware( window.Redirectioni10n?.api?.WP_API_root ?? '/wp-json/' ) );
apiFetch.use( apiFetch.createNonceMiddleware( window.Redirectioni10n?.api?.WP_API_nonce ?? '' ) );

export default function App() {
	return (
		<Provider store={ createReduxStore( getInitialState() ) }>
			<Home />
		</Provider>
	);
};
