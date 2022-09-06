/* global Redirectioni10n */
/**
 * Internal dependencies
 */

import { STATUS_IN_PROGRESS } from '../settings/type';

function getPreload() {
	if ( Redirectioni10n && Redirectioni10n.preload && Redirectioni10n.preload.importers ) {
		return Redirectioni10n.preload.importers;
	}

	return [];
}

export function getInitialIO() {
	return {
		status: STATUS_IN_PROGRESS,
		file: false,
		lastImport: false,
		exportData: false,
		importingStatus: false,
		exportStatus: false,
		importers: getPreload(),
	};
}
