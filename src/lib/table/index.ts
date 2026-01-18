import { getPageUrl } from 'lib/wordpress-url';

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

interface FilterMap {
	[ key: string ]: string[] | boolean;
}

type RowId = number | string;

interface TableState {
	orderby: string;
	direction: string;
	page: number;
	per_page: number;
	selected: RowId[];
	filterBy: { [ key: string ]: string };
	groupBy: string;
	displayType: string;
	displaySelected: string[];
	selectAll: boolean;
	[ key: string ]: any;
}

interface DisplayGroup {
	value: string;
	grouping: string[];
}

interface TableRow {
	id: number;
	[ key: string ]: any;
}

const strOrInt = ( value: string ): string | number =>
	parseInt( value, 10 ) > 0 || value === '0' ? parseInt( value, 10 ) : value;

function filterFilters( query: { [ key: string ]: string }, filters: FilterMap ): { [ key: string ]: string | number } {
	const filteredQuery: { [ key: string ]: string | number } = {};

	Object.keys( query ).forEach( ( key ) => {
		const queryValue = query[ key ];
		if ( ! queryValue ) {
			return;
		}

		if (
			filters[ key ] &&
			Array.isArray( filters[ key ] ) &&
			( filters[ key ] as string[] ).includes( strOrInt( queryValue ) as string )
		) {
			filteredQuery[ key ] = strOrInt( queryValue );
		} else if ( filters[ key ] && ! Array.isArray( filters[ key ] ) ) {
			filteredQuery[ key ] = queryValue;
		}
	} );

	return filteredQuery;
}

export const getDefaultTable = (
	allowedOrder: string[] = [],
	allowedFilter: FilterMap = {},
	allowedGroup: string[] = [],
	defaultOrder = '',
	subParams: string[] = [],
	displayName = '',
	displayGroups: DisplayGroup[] = []
): TableState => {
	const query: any = getPageUrl();
	const defaults: TableState = {
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
	let displaySelected = displayGroups.length > 0 && displayGroups[ 0 ] ? displayGroups[ 0 ].grouping : [];

	if ( localStorage.getItem( displayName + '_displayType' ) ) {
		displayType = localStorage.getItem( displayName + '_displayType' ) || '';
	}

	if ( displayType === 'custom' && localStorage.getItem( displayName + '_displaySelected' ) ) {
		const stored = localStorage.getItem( displayName + '_displaySelected' );
		displaySelected = stored ? stored.split( ',' ) : [];
	} else if ( displayGroups.find( ( item ) => item.value === displayType ) ) {
		const found = displayGroups.find( ( item ) => item.value === displayType );
		displaySelected = found ? found.grouping : [];
	}

	if ( ! subParams.includes( sub ) && sub !== displayName ) {
		return {
			...defaults,
			displayType,
			displaySelected,
		};
	}

	return {
		...defaults,
		orderby: query.orderby && allowedOrder.includes( query.orderby ) ? query.orderby : defaults.orderby,
		direction: query.direction && query.direction === 'asc' ? 'asc' : defaults.direction,
		page: query.offset && parseInt( query.offset, 10 ) > 0 ? parseInt( query.offset, 10 ) : defaults.page,
		per_page: Redirectioni10n.per_page ? parseInt( Redirectioni10n.per_page, 10 ) : defaults.per_page,
		filterBy: query.filterby
			? ( filterFilters( query.filterby, allowedFilter ) as { [ key: string ]: string } )
			: defaults.filterBy,
		groupBy: query.groupby && allowedGroup.includes( query.groupby ) ? query.groupby : defaults.groupBy,
		displayType,
		displaySelected,
	};
};

export const mergeWithTable = ( state: TableState, params: Partial< TableState > ): TableState => {
	const newState = Object.assign( {}, state );

	for ( let x = 0; x < tableParams.length; x++ ) {
		const param = tableParams[ x ];
		if ( param && params[ param as keyof TableState ] !== undefined ) {
			newState[ param ] = params[ param as keyof TableState ];
		}
	}

	return newState;
};

export function removeDefaults( table: Partial< TableState >, defaultOrder: string ): Partial< TableState > {
	const newTable = { ...table };

	if ( newTable.direction === 'desc' ) {
		delete newTable.direction;
	}

	if ( newTable.orderby === defaultOrder ) {
		delete newTable.orderby;
	}

	if ( newTable.page === 0 ) {
		delete newTable.page;
	}

	if ( newTable.per_page === parseInt( Redirectioni10n.per_page, 10 ) ) {
		delete newTable.per_page;
	}

	if ( typeof newTable.filterBy === 'object' && Object.keys( newTable.filterBy || {} ).length === 0 ) {
		delete newTable.filterBy;
		delete ( newTable as any ).filter;
	}

	if ( newTable.groupBy === '' ) {
		delete newTable.groupBy;
		delete ( newTable as any ).group;
	}

	if ( parseInt( Redirectioni10n.per_page, 10 ) !== 25 ) {
		newTable.per_page = parseInt( Redirectioni10n.per_page, 10 );
	}

	delete newTable.selected;
	delete newTable.displaySelected;
	delete newTable.displayType;
	delete newTable.selectAll;

	return newTable;
}

export const clearSelected = ( state: TableState ): TableState => {
	return Object.assign( {}, state, { selected: [], selectAll: false } );
};

export function setTableSelected(
	table: TableState,
	items: boolean | RowId[],
	selectAll: boolean,
	rows: TableRow[]
): TableState {
	if ( items === true ) {
		return {
			...table,
			selected: rows.map( ( item ) => item.id ),
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

	// Convert to strings for comparison
	const currentStrings = table.selected.map( ( id ) => String( id ) );
	const itemStrings = items.map( ( id ) => String( id ) );
	const newSelected = currentStrings
		.filter( ( id ) => ! itemStrings.includes( id ) )
		.concat( itemStrings.filter( ( id ) => ! currentStrings.includes( id ) ) );

	return {
		...table,
		selected: newSelected,
		selectAll,
	};
}

export const tableKey = ( { filterBy, filter }: { filterBy?: any; filter?: any } ): string =>
	[ filterBy, filter ].join( '-' );

interface FilterOption {
	value: string;
	options?: { value: string }[];
}

export const toFilter = ( filter: FilterOption[], extra: { [ key: string ]: any } ): { [ key: string ]: any } => {
	const filtered: { [ key: string ]: any } = {};

	filter.forEach( ( { value, options } ) => {
		filtered[ value ] = Array.isArray( options ) ? options.map( ( item ) => item.value ) : value;
	} );

	return { ...filtered, ...extra };
};
