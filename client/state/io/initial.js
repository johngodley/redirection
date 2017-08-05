/**
 * Internal dependencies
 */

import { STATUS_IN_PROGRESS } from 'state/settings/type';

export function getInitialIO() {
	return {
		status: STATUS_IN_PROGRESS,
		file: false,
		lastImport: false,
		exportData: false,
		importingStatus: false,
		exportStatus: false,
	};
}
