/* global Redirectioni10n */
/**
 * Internal dependencies
 */

import { STATUS_IN_PROGRESS } from './type';

export function getInitialSettings() {
	return {
		loadStatus: STATUS_IN_PROGRESS,
		saveStatus: false,
		error: false,
		installed: '',
		settings: {},
		postTypes: [],
		pluginStatus: Redirectioni10n.preload.pluginStatus ? Redirectioni10n.preload.pluginStatus : [],
		canDelete: false,
	};
}
