/**
 * Internal dependencies
 */

import { getInitialSettings } from './settings/initial';
import { getInitialLog } from './log/initial';
import { getInitialError } from './error/initial';
import { getInitialIO } from './io/initial';
import { getInitialGroup } from './group/initial';
import { getInitialRedirect } from './redirect/initial';
import { getInitialMessage } from './message/initial';
import { getInitialInfo } from './info/initial';

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
