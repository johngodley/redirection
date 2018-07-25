/**
 * External dependencies
 */
import { combineReducers } from 'redux';

import settings from 'state/settings/reducer';
import log from 'state/log/reducer';
import error from 'state/error/reducer';
import io from 'state/io/reducer';
import group from 'state/group/reducer';
import redirect from 'state/redirect/reducer';
import message from 'state/message/reducer';
import info from 'state/info/reducer';

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
