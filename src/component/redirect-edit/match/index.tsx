import MatchAgent from './agent';
import MatchReferrer from './referrer';
import MatchHeader from './header';
import MatchCustom from './custom';
import MatchCookie from './cookie';
import MatchRole from './role';
import MatchServer from './server';
import MatchIp from './ip';
import MatchPage from './page';
import MatchLanguage from './language';
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
	MATCH_LANGUAGE,
} from 'lib/redirect-constants';

interface MatchProps {
	matchType: string;
	actionData: any;
	onChange: (
		ev:
			| React.ChangeEvent< HTMLInputElement | HTMLTextAreaElement >
			| { target: { name: string; value: string | string[] } }
	) => void;
}

type MatchComponentMap = {
	[ key: string ]: React.ComponentType< any >;
};

const Match = ( { matchType, actionData, onChange }: MatchProps ) => {
	const map: MatchComponentMap = {
		[ MATCH_REFERRER ]: MatchReferrer,
		[ MATCH_AGENT ]: MatchAgent,
		[ MATCH_COOKIE ]: MatchCookie,
		[ MATCH_HEADER ]: MatchHeader,
		[ MATCH_CUSTOM ]: MatchCustom,
		[ MATCH_ROLE ]: MatchRole,
		[ MATCH_SERVER ]: MatchServer,
		[ MATCH_IP ]: MatchIp,
		[ MATCH_PAGE ]: MatchPage,
		[ MATCH_LANGUAGE ]: MatchLanguage,
	};

	if ( map[ matchType ] ) {
		const Component = map[ matchType ];
		return <Component data={ actionData === null ? {} : actionData } onChange={ onChange } />;
	}

	return null;
};

export default Match;
