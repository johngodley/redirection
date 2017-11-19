/**
 * Internal dependencies
 */

import { STATUS_IN_PROGRESS } from 'state/settings/type';
import { getDefaultTable } from 'lib/table';

export function getInitialGroup() {
	return {
		rows: [],
		saving: [],
		total: 0,
		status: STATUS_IN_PROGRESS,
		table: getDefaultTable( [ 'name' ], [ 'name', 'module' ], 'name', [ 'groups' ] ),
	};
}
