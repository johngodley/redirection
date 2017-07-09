/**
 * Internal dependencies
 */

import { STATUS_IN_PROGRESS } from 'state/settings/type';
import { getDefaultTable } from 'lib/table';

export function getInitialModule() {
	return {
		rows: [],
		status: STATUS_IN_PROGRESS,
		total: 3,
		table: getDefaultTable( [], [], '', [ 'module' ] ),
		error: false,
	};
}
