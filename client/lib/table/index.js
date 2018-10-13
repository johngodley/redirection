/* global Redirectioni10n */
/**
 * Internal dependencies
 */

import { getPageUrl } from 'lib/wordpress-url';

const tableParams = [ 'orderby', 'direction', 'page', 'per_page', 'filter', 'filterBy' ];

const removeIfExists = ( current, newItems ) => {
	const newArray = [];

	for ( let x = 0; x < current.length; x++ ) {
		if ( newItems.indexOf( current[ x ] ) === -1 ) {
			newArray.push( current[ x ] );
		}
	}

	return newArray;
};

export const getDefaultTable = ( allowedOrder = [], allowedFilter = [], defaultOrder = '', subParams = [] ) => {
	const query = getPageUrl();
	const defaults = {
		orderby: defaultOrder,
		direction: 'desc',
		page: 0,
		per_page: parseInt( Redirectioni10n.per_page, 10 ),
		selected: [],
		filterBy: '',
		filter: '',
	};
	const sub = query.sub === undefined ? '' : query.sub;

	if ( subParams.indexOf( sub ) === -1 ) {
		return defaults;
	}

	return {
		... defaults,
		orderby: query.orderby && allowedOrder.indexOf( query.orderby ) !== -1 ? query.orderby : defaults.orderby,
		direction: query.direction && query.direction === 'asc' ? 'asc' : defaults.direction,
		page: query.offset && parseInt( query.offset, 10 ) > 0 ? parseInt( query.offset, 10 ) : defaults.page,
		per_page: Redirectioni10n.per_page ? parseInt( Redirectioni10n.per_page, 10 ) : defaults.per_page,
		filterBy: query.filterby && allowedFilter.indexOf( query.filterby ) !== -1 ? query.filterby : defaults.filterBy,
		filter: query.filter ? query.filter : defaults.filter,
	};
};

export const mergeWithTable = ( state, params ) => {
	const newState = Object.assign( {}, state );

	for ( let x = 0; x < tableParams.length; x++ ) {
		if ( params[ tableParams[ x ] ] !== undefined ) {
			newState[ tableParams[ x ] ] = params[ tableParams[ x ] ];
		}
	}

	return newState;
};

export const removeDefaults = ( table, defaultOrder ) => {
	if ( table.direction === 'desc' ) {
		delete table.direction;
	}

	if ( table.orderby === defaultOrder ) {
		delete table.orderby;
	}

	if ( table.page === 0 ) {
		delete table.page;
	}

	if ( table.per_page === parseInt( Redirectioni10n.per_page, 10 ) ) {
		delete table.per_page;
	}

	if ( table.filterBy === '' && table.filter === '' ) {
		delete table.filterBy;
		delete table.filter;
	}

	if ( parseInt( Redirectioni10n.per_page, 10 ) !== 25 ) {
		table.per_page = parseInt( Redirectioni10n.per_page, 10 );
	}

	delete table.selected;

	return table;
};

export const clearSelected = state => {
	return Object.assign( {}, state, { selected: [] } );
};

export const setTableSelected = ( table, newItems ) => ( { ... table, selected: removeIfExists( table.selected, newItems ).concat( removeIfExists( newItems, table.selected ) ) } );
export const setTableAllSelected = ( table, rows, onoff ) => ( { ... table, selected: onoff ? rows.map( item => item.id ) : [] } );
export const tableKey = ( { filterBy, filter } ) => [ filterBy, filter ].join( '-' );
