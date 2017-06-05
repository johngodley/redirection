/**
 * External dependencies
 */
import { combineReducers } from 'redux';

import settings from 'state/settings/reducer';

const reducer = combineReducers( {
	settings,
} );

export default reducer;
