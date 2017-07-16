/* global Redirectioni10n */
/**
 * Internal dependencies
 */

import { REDIRECT_LOADING } from 'state/redirect/type';
import { GROUP_LOADING } from 'state/group/type';
import { MODULE_LOADING } from 'state/module/type';
import { LOG_LOADING } from 'state/log/type';
import { getPluginPage, setPageUrl } from 'lib/wordpress-url';

const setUrlForPage = ( action, table ) => {
	const pluginPage = getPluginPage();
	const currentPage = {
		redirect: [
			REDIRECT_LOADING,
			'name',
		],
		groups: [
			GROUP_LOADING,
			'name',
		],
		log: [
			LOG_LOADING,
			'date',
		],
		'404s': [
			LOG_LOADING,
			'date',
		],
	};

	if ( currentPage[ pluginPage ] && action === currentPage[ pluginPage ][ 0 ] ) {
		const { orderBy, direction, page, perPage, filter, filterBy } = table;

		setPageUrl( { orderBy, direction, offset: page, perPage, filter, filterBy }, { orderBy: currentPage[ pluginPage ][ 1 ], direction: 'desc', offset: 0, filter: '', filterBy: '', perPage: parseInt( Redirectioni10n.per_page, 10 ) } );
	}
};

export const urlMiddleware = () => next => action => {
	switch ( action.type ) {
		case REDIRECT_LOADING:
		case GROUP_LOADING:
		case MODULE_LOADING:
		case LOG_LOADING:
			setUrlForPage( action.type, action );
			break;
	}

	return next( action );
};
