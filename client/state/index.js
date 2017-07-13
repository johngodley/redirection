/**
 * External dependencies
 */

import {
	applyMiddleware,
	createStore,
} from 'redux';
import { composeWithDevTools } from 'redux-devtools-extension/developmentOnly';
import thunk from 'redux-thunk';
import reducers from './reducers';

/**
 * Internal dependencies
 */

const composeEnhancers = composeWithDevTools( {
	name: 'Redirection'
} );

const middlewares = [
	thunk,
];

export default function createReduxStore( initialState = {} ) {
	const store = createStore(
		reducers,
		initialState,
		composeEnhancers( applyMiddleware( ...middlewares ) )
	);

	if ( module.hot ) {
		module.hot.accept( './reducers', () => {
			const nextRootReducer = require( './reducers' );
			store.replaceReducer( nextRootReducer );
		} );
	}

	return store;
}
