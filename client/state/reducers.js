/**
 * External dependencies
 */
import { combineReducers } from 'redux';

import settings from 'state/settings/reducer';
import log from 'state/log/reducer';
import module from 'state/module/reducer';
import group from 'state/group/reducer';

const reducer = combineReducers( {
	settings,
	log,
	module,
	group,
} );

export default reducer;
