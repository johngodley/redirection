/**
 * External dependencies
 */

import {
	applyMiddleware,
	createStore,
} from 'redux';
import { composeWithDevTools } from '@redux-devtools/extension';
import thunk from 'redux-thunk';
import reducers from './reducers';
import { urlMiddleware } from 'state/middleware';

/**
 * Internal dependencies
 */

const middlewares = [
	thunk,
	urlMiddleware,
];

export default function createReduxStore( initialState = {} ) {
	const store = createStore(
		reducers,
		initialState,
		composeWithDevTools( applyMiddleware( ...middlewares ) )
	);

	return store;
}
