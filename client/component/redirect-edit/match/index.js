/**
 * External dependencies
 */

import React from 'react';

/**
 * Internal dependencies
 */
import MatchAgent from './agent';
import MatchReferrer from './referrer';
import MatchHeader from './header';
import MatchCustom from './custom';
import MatchCookie from './cookie';
import MatchRole from './role';
import MatchServer from './server';
import MatchIp from './ip';
import MatchPage from './page';
import {
	MATCH_REFERRER,
	MATCH_AGENT,
	MATCH_COOKIE,
	MATCH_HEADER,
	MATCH_CUSTOM,
	MATCH_ROLE,
	MATCH_SERVER,
	MATCH_IP,
	MATCH_PAGE,
} from 'state/redirect/selector';

const Match = ( { matchType, actionData, onChange } ) => {
	const map = {
		[ MATCH_REFERRER ]: MatchReferrer,
		[ MATCH_AGENT ]: MatchAgent,
		[ MATCH_COOKIE ]: MatchCookie,
		[ MATCH_HEADER ]: MatchHeader,
		[ MATCH_CUSTOM ]: MatchCustom,
		[ MATCH_ROLE ]: MatchRole,
		[ MATCH_SERVER ]: MatchServer,
		[ MATCH_IP ]: MatchIp,
		[ MATCH_PAGE ]: MatchPage,
	};

	if ( map[ matchType ] ) {
		const Component = map[ matchType ];

		return <Component data={ actionData } onChange={ onChange } />;
	}

	return null;
};

export default Match;
