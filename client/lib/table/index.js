/* global Redirectioni10n */
/**
 * Internal dependencies
 */

import { getPageUrl, setPageUrl } from 'lib/wordpress-url';

const filterObject = obj => {
	const newObj = {};

	Object.keys( obj ).forEach( key => {
		if ( obj[ key ] !== undefined ) {
			newObj[ key ] = obj[ key ];
		}
	} );

	return newObj;
};

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
		orderBy: defaultOrder,
		direction: 'desc',
		page: 0,
		perPage: parseInt( Redirectioni10n.per_page, 10 ),
		selected: [],
		filterBy: '',
		filter: '',
		error: false,
	};

	if ( query.sub && subParams.indexOf( query.sub ) === -1 ) {
		return defaults;
	}

	return {
		... defaults,
		orderBy: query.orderby && allowedOrder.indexOf( query.orderby ) !== -1 ? query.orderby : defaults.orderBy,
		direction: query.direction && query.direction === 'asc' ? 'asc' : defaults.direction,
		page: query.offset && parseInt( query.offset, 10 ) > 0 ? parseInt( query.offset, 10 ) : defaults.page,
		perPage: Redirectioni10n.per_page ? parseInt( Redirectioni10n.per_page, 10 ) : defaults.perPage,
		filterBy: query.filterby && allowedFilter.indexOf( query.filterby ) ? query.filterby : defaults.filterBy,
		filter: query.filter ? query.filter : defaults.filter,
	};
};

export const mergeWithTable = ( params, state ) => {
	const { orderBy, direction, page, perPage, filter, filterBy } = state;

	return Object.assign( {}, { orderBy, direction, page, perPage, filter, filterBy }, params );
};

export const setTableParams = ( data, defaultOrder = '' ) => {
	const { orderBy = defaultOrder, direction = 'desc', page = 0, perPage, filter = '', filterBy = '', selected = [], error = false } = data;

	setPageUrl( { orderBy, direction, offset: page, filter, filterBy }, { orderBy: defaultOrder, direction: 'desc', offset: 0, filter: '', filterBy: '' } );

	return filterObject( { orderBy, direction, page, perPage, filter, filterBy, selected, error } );
};

export const setTableSelected = ( table, newItems ) => ( { ... table, selected: removeIfExists( table.selected, newItems ).concat( removeIfExists( newItems, table.selected ) ) } );
export const setTableAllSelected = ( table, rows, onoff ) => ( { ... table, selected: onoff ? rows.map( item => item.id ) : [] } );
