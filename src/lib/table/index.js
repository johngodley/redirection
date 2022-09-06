/* global Redirectioni10n */
/**
 * Internal dependencies
 */

import { getPageUrl } from '../../lib/wordpress-url';

const tableParams = [
	'orderby',
	'direction',
	'page',
	'per_page',
	'filterBy',
	'groupBy',
	'group',
	'displayType',
	'displaySelected',
];

const removeIfExists = ( current, newItems ) => {
	const newArray = [];

	for ( let x = 0; x < current.length; x++ ) {
		if ( newItems.indexOf( current[ x ] ) === -1 ) {
			newArray.push( current[ x ] );
		}
	}

	return newArray;
};

const strOrInt = ( value ) => ( parseInt( value, 10 ) > 0 || value === '0' ? parseInt( value, 10 ) : value );

function filterFilters( query, filters ) {
	const filteredQuery = {};

	Object.keys( query ).map( ( key ) => {
		if (
			filters[ key ] &&
			Array.isArray( filters[ key ] ) &&
			filters[ key ].indexOf( strOrInt( query[ key ] ) ) !== -1
		) {
			filteredQuery[ key ] = strOrInt( query[ key ] );
		} else if ( filters[ key ] && ! Array.isArray( filters[ key ] ) ) {
			filteredQuery[ key ] = query[ key ];
		}
	} );

	return filteredQuery;
}

/**
 * Get default table parameters
 *
 * @param {*} allowedOrder Allowed orders
 * @param {*} allowedFilter Allowed filters
 * @param {*} allowedGroup Allowed group by
 * @param {*} defaultOrder Default order
 * @param {*} subParams
 * @param {*} displayName
 * @param {*} displayGroups
 */
export const getDefaultTable = (
	allowedOrder = [],
	allowedFilter = [],
	allowedGroup = [],
	defaultOrder = '',
	subParams = [],
	displayName = '',
	displayGroups = []
) => {
	const query = getPageUrl();
	const defaults = {
		orderby: defaultOrder,
		direction: 'desc',
		page: 0,
		per_page: parseInt( Redirectioni10n.per_page, 10 ),
		selected: [],
		filterBy: {},
		groupBy: '',
		displayType: 'standard',
		displaySelected: [],
		selectAll: false,
	};
	const sub = query.sub === undefined ? '' : query.sub;

	let displayType = 'standard';
	let displaySelected = displayGroups.length > 0 ? displayGroups[ 0 ].grouping : [];

	if ( localStorage.getItem( displayName + '_displayType' ) ) {
		displayType = localStorage.getItem( displayName + '_displayType' ) || '';
	}

	if ( displayType === 'custom' && localStorage.getItem( displayName + '_displaySelected' ) ) {
		displaySelected = localStorage.getItem( displayName + '_displaySelected' ).split( ',' );
	} else if ( displayGroups.find( ( item ) => item.value === displayType ) ) {
		displaySelected = displayGroups.find( ( item ) => item.value === displayType ).grouping;
	}

	if ( subParams.indexOf( sub ) === -1 && sub !== displayName ) {
		return {
			...defaults,
			displayType,
			displaySelected,
		};
	}

	return {
		...defaults,
		orderby: query.orderby && allowedOrder.indexOf( query.orderby ) !== -1 ? query.orderby : defaults.orderby,
		direction: query.direction && query.direction === 'asc' ? 'asc' : defaults.direction,
		page: query.offset && parseInt( query.offset, 10 ) > 0 ? parseInt( query.offset, 10 ) : defaults.page,
		per_page: Redirectioni10n.per_page ? parseInt( Redirectioni10n.per_page, 10 ) : defaults.per_page,
		filterBy: query.filterby ? filterFilters( query.filterby, allowedFilter ) : defaults.filterBy,
		groupBy: query.groupby && allowedGroup.indexOf( query.groupby ) !== -1 ? query.groupby : defaults.groupBy,
		displayType,
		displaySelected,
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

export function removeDefaults( table, defaultOrder ) {
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

	if ( table.groupBy === '' ) {
		delete table.groupBy;
		delete table.group;
	}

	if ( parseInt( Redirectioni10n.per_page, 10 ) !== 25 ) {
		table.per_page = parseInt( Redirectioni10n.per_page, 10 );
	}

	delete table.selected;
	delete table.displaySelected;
	delete table.displayType;
	delete table.selectAll;

	return table;
};

export const clearSelected = ( state ) => {
	return Object.assign( {}, state, { selected: [], selectAll: false } );
};

export function setTableSelected( table, items, selectAll, rows ) {
	if ( items === true ) {
		return {
			...table,
			selected: rows.map( ( item ) => `${ item.id }` ),
			selectAll,
		};
	}

	if ( items === false ) {
		return {
			...table,
			selected: [],
			selectAll,
		};
	}

	return {
		...table,
		selected: removeIfExists( table.selected, items ).concat( removeIfExists( items, table.selected ) ),
		selectAll,
	};
}

export const tableKey = ( { filterBy, filter } ) => [ filterBy, filter ].join( '-' );

export const toFilter = ( filter, extra ) => {
	const filtered = {};

	filter.map( ( { value, options } ) => {
		filtered[ value ] = Array.isArray( options ) ? options.map( ( item ) => item.value ) : value;
	} );

	return { ...filtered, ...extra };
};
