/* global Redirectioni10n */
/**
 * Internal dependencies
 */

import { REDIRECT_LOADING, REDIRECT_ITEM_SAVING } from 'state/redirect/type';
import { GROUP_LOADING, GROUP_ITEM_SAVING } from 'state/group/type';
import { LOG_LOADING } from 'state/log/type';
import { ERROR_LOADING } from 'state/error/type';
import { getPluginPage, setPageUrl } from 'lib/wordpress-url';

const setUrlForPage = ( action, table ) => {
	const pluginPage = getPluginPage();
	const currentPage = {
		redirect: [
			[ REDIRECT_LOADING, REDIRECT_ITEM_SAVING ],
			'id',
		],
		groups: [
			[ GROUP_LOADING, GROUP_ITEM_SAVING ],
			'name',
		],
		log: [
			[ LOG_LOADING ],
			'date',
		],
		'404s': [
			[ ERROR_LOADING ],
			'date',
		],
	};

	if ( currentPage[ pluginPage ] && action === currentPage[ pluginPage ][ 0 ].find( item => item === action ) ) {
		const { orderby, direction, page, per_page, filter, filterBy } = table;

		setPageUrl( { orderby, direction, offset: page, per_page, filter, filterBy }, { orderby: currentPage[ pluginPage ][ 1 ], direction: 'desc', offset: 0, filter: '', filterBy: '', per_page: parseInt( Redirectioni10n.per_page, 10 ) } );
	}
};

export const urlMiddleware = () => next => action => {
	switch ( action.type ) {
		case REDIRECT_ITEM_SAVING:
		case GROUP_ITEM_SAVING:
		case REDIRECT_LOADING:
		case GROUP_LOADING:
		case LOG_LOADING:
		case ERROR_LOADING:
			setUrlForPage( action.type, action.table ? action.table : action );
			break;
	}

	return next( action );
};
