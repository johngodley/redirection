/**
 * External dependencies
 */
import { combineReducers } from 'redux';

import settings from 'state/settings/reducer';
import log from 'state/log/reducer';

const reducer = combineReducers( {
	settings,
	log,
} );

export default reducer;
