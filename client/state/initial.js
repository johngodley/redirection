/**
 * Internal dependencies
 */

import { getInitialSettings } from 'state/settings/initial';
import { getInitialLog } from 'state/log/initial';
import { getInitialIO } from 'state/io/initial';
import { getInitialGroup } from 'state/group/initial';
import { getInitialRedirect } from 'state/redirect/initial';
import { getInitialMessage } from 'state/message/initial';

export function initialActions( store ) {
	return store;
}

export function getInitialState() {
	return {
		settings: getInitialSettings(),
		log: getInitialLog(),
		io: getInitialIO(),
		group: getInitialGroup(),
		redirect: getInitialRedirect(),
		message: getInitialMessage(),
	};
}
