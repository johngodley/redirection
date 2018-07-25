/**
 * Internal dependencies
 */

import { getInitialSettings } from 'state/settings/initial';
import { getInitialLog } from 'state/log/initial';
import { getInitialError } from 'state/error/initial';
import { getInitialIO } from 'state/io/initial';
import { getInitialGroup } from 'state/group/initial';
import { getInitialRedirect } from 'state/redirect/initial';
import { getInitialMessage } from 'state/message/initial';
import { getInitialInfo } from 'state/info/initial';

export function initialActions( store ) {
	return store;
}

export function getInitialState() {
	return {
		settings: getInitialSettings(),
		log: getInitialLog(),
		error: getInitialError(),
		io: getInitialIO(),
		group: getInitialGroup(),
		redirect: getInitialRedirect(),
		message: getInitialMessage(),
		info: getInitialInfo(),
	};
}
