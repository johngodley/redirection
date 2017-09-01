/**
 * External dependencies
 */

import React from 'react';
import { Provider } from 'react-redux';

/**
 * Internal dependencies
 */

import createReduxStore from 'state';
import { getInitialState } from 'state/initial';
import './index.scss';
import Home from './component/home';

const App = () => (
	<Provider store={ createReduxStore( getInitialState() ) }>
		<Home />
	</Provider>
);

export default App;
