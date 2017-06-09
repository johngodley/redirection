/**
 * Internal dependencies
 */

import { getInitialSettings } from 'state/settings/initial';

export function initialActions( store ) {
	return store;
}

export function getInitialState() {
	return {
		settings: getInitialSettings(),
	};
}
