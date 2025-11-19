/**
 * External dependencies
 */

import React from 'react';

/**
 * Internal dependencies
 */

import ActionLogin from './login';
import ActionUrl from './url';
import ActionUrlFrom from './url-from';
import {
	MATCH_URL,
	MATCH_LOGIN,
	MATCH_PAGE,

	hasUrlTarget,
	getMatchState,
} from 'state/redirect/selector';

function getComponentForType( type ) {
	if ( type === MATCH_LOGIN ) {
		return ActionLogin;
	}

	if ( type === MATCH_URL || type === MATCH_PAGE ) {
		return ActionUrl;
	}

	return ActionUrlFrom;
}

const ActionTarget = ( { actionType, matchType, actionData, onChange } ) => {
	if ( hasUrlTarget( actionType ) ) {
		const Component = getComponentForType( matchType );
		const state = getMatchState( matchType, actionData );

		return <Component data={ state === null ? {} : state } onChange={ onChange } />;
	}

	return null;
};

export default ActionTarget;
