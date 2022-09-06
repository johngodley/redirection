/**
 * External dependencies
 */
import { combineReducers } from 'redux';

import settings from './settings/reducer';
import log from './log/reducer';
import error from './error/reducer';
import io from './io/reducer';
import group from './group/reducer';
import redirect from './redirect/reducer';
import message from './message/reducer';
import info from './info/reducer';

const reducer = combineReducers( {
	settings,
	log,
	error,
	io,
	group,
	redirect,
	message,
	info,
} );

export default reducer;
