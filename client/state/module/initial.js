/**
 * Internal dependencies
 */

import { STATUS_IN_PROGRESS } from 'state/settings/type';
import { getDefaultTable } from 'lib/table';

export function getInitialModule() {
	return {
		rows: [
			{
				name: 'wordpress',
				redirects: null,
				data: null,
			},
			{
				name: 'nginx',
				redirects: null,
				data: null,
			},
			{
				name: 'apache',
				redirects: null,
				data: null,
			},
		],
		status: STATUS_IN_PROGRESS,
		total: 3,
		table: getDefaultTable(),
	};
}
