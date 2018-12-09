/* global Redirectioni10n */
/**
 * Internal dependencies
 */

import { STATUS_IN_PROGRESS } from './type';

function getPreload() {
	if ( Redirectioni10n && Redirectioni10n.preload && Redirectioni10n.preload.pluginStatus ) {
		return Redirectioni10n.preload.pluginStatus;
	}

	return [];
}

export function getInitialSettings() {
	const pluginStatus = getPreload();

	return {
		loadStatus: STATUS_IN_PROGRESS,
		saveStatus: false,
		error: false,
		installed: '',
		settings: {},
		postTypes: [],
		pluginStatus,
		canDelete: false,
		showDatabase: false,
		apiTest: {},
		database: Redirectioni10n.database ? Redirectioni10n.database : {},
	};
}
