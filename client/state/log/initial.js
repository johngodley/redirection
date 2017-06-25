/* global Redirectioni10n */
/**
 * Internal dependencies
 */

import { STATUS_IN_PROGRESS } from 'state/settings/type';
import { LOGS_TYPE_REDIRECT } from 'state/log/type';
import { getPageUrl } from 'lib/wordpress-url';

export function getInitialLog() {
	const query = getPageUrl();

	return {
		orderBy: query.orderby && [ 'ip', 'url', ].indexOf( query.orderby ) !== -1 ? query.orderby : 'date',
		direction: query.direction && query.direction === 'asc' ? 'asc' : 'desc',
		logs: [],
		status: STATUS_IN_PROGRESS,
		page: query.offset && parseInt( query.offset, 10 ) > 0 ? parseInt( query.offset, 10 ) : 0,
		perPage: Redirectioni10n.per_page ? parseInt( Redirectioni10n.per_page, 10 ) : 25,
		total: 0,
		selected: [],
		logType: LOGS_TYPE_REDIRECT,
		filterBy: query.filterby && query.filterby === 'ip' ? query.filterby : '',
		filter: query.filter ? query.filter : '',
		error: false,
	};
}
