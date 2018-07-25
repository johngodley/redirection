/**
 * Internal dependencies
 */

import { STATUS_IN_PROGRESS } from 'state/settings/type';
import { LOGS_TYPE_REDIRECT } from 'state/log/type';
import { getDefaultTable } from 'lib/table';

export function getInitialLog() {
	return {
		rows: [],
		saving: [],
		logType: LOGS_TYPE_REDIRECT,
		total: 0,
		status: STATUS_IN_PROGRESS,
		table: getDefaultTable( [ 'ip', 'url' ], [ 'ip' ], 'date', [ 'log' ] ),
		requestCount: 0,
	};
}
