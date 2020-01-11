/**
 * External dependencies
 */

import React from 'react';
import { Provider } from 'react-redux';
import { hot } from 'react-hot-loader/root';

/**
 * Internal dependencies
 */

import createReduxStore from 'state';
import { getInitialState } from 'state/initial';
import Home from './page/home';

const App = () => (
	<Provider store={ createReduxStore( getInitialState() ) }>
		<React.StrictMode>
			<Home />
		</React.StrictMode>
	</Provider>
);

export default hot( App );
