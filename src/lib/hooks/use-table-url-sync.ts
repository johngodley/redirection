import { useEffect, useRef } from 'react';
import { getPageUrl, getWordPressUrl } from 'lib/wordpress-url';
import { removeDefaults } from 'lib/table';
import type { TableState } from 'types';

interface TableUrlSyncOptions {
	/** The current table state */
	table: TableState;
	/** Function to update the table state */
	setTable: ( table: Partial< TableState > ) => void;
	/** Allowed values for orderby parameter */
	allowedOrder?: string[];
	/** Allowed values for groupBy parameter */
	allowedGroup?: string[];
	/** Allowed filter keys */
	allowedFilters?: string[];
	/** Default orderby value */
	defaultOrder?: string;
	/** Page name for URL sub parameter (e.g., 'log', '404s') */
	pageName: string;
}

/**
 * Hook to sync table state with URL query parameters.
 * - On mount: reads URL params and initializes table state
 * - On table change: updates URL to reflect current state
 */
export function useTableUrlSync( options: TableUrlSyncOptions ): void {
	const {
		table,
		setTable,
		allowedOrder = [],
		allowedGroup = [],
		allowedFilters = [],
		defaultOrder = '',
		pageName,
	} = options;

	const isInitialized = useRef( false );
	const previousTable = useRef< TableState | null >( null );

	// Initialize table state from URL on mount
	useEffect( () => {
		if ( isInitialized.current ) {
			return;
		}

		const query = getPageUrl();

		// Only initialize if we're on the right page
		if ( query.sub !== pageName && pageName !== 'redirect' ) {
			isInitialized.current = true;
			return;
		}

		const updates: Partial< TableState > = {};

		// Parse orderby
		if ( query.orderby && allowedOrder.includes( query.orderby ) ) {
			updates.orderby = query.orderby;
		}

		// Parse direction
		if ( query.direction === 'asc' || query.direction === 'desc' ) {
			updates.direction = query.direction;
		}

		// Parse page offset
		if ( query.offset && parseInt( query.offset, 10 ) > 0 ) {
			updates.page = parseInt( query.offset, 10 );
		}

		// Parse groupBy
		if ( query.groupby && allowedGroup.includes( query.groupby ) ) {
			updates.groupBy = query.groupby;
		}

		// Parse filterBy - handle both nested object and bracket notation
		const filterBy: { [ key: string ]: string } = {};

		// Check for bracket notation filterby[key]=value
		for ( const [ queryKey, queryValue ] of Object.entries( query ) ) {
			const match = queryKey.match( /^filterby\[(\w+)\]$/ );
			if ( match && match[ 1 ] ) {
				const filterKey = match[ 1 ];
				if ( allowedFilters.length === 0 || allowedFilters.includes( filterKey ) ) {
					filterBy[ filterKey ] = String( queryValue );
				}
			}
		}

		// Also check for nested object format (legacy)
		if ( query.filterby && typeof query.filterby === 'object' ) {
			for ( const key of Object.keys( query.filterby ) ) {
				if ( allowedFilters.length === 0 || allowedFilters.includes( key ) ) {
					filterBy[ key ] = query.filterby[ key ];
				}
			}
		}

		if ( Object.keys( filterBy ).length > 0 ) {
			updates.filterBy = filterBy;
		}

		if ( Object.keys( updates ).length > 0 ) {
			setTable( updates );
		}

		isInitialized.current = true;
		previousTable.current = { ...table, ...updates };
	}, [ table, setTable, allowedOrder, allowedGroup, allowedFilters, pageName ] );

	// Sync table changes to URL
	useEffect( () => {
		if ( ! isInitialized.current ) {
			return;
		}

		// Check if table has actually changed
		const prev = previousTable.current;
		if (
			prev &&
			prev.groupBy === table.groupBy &&
			prev.orderby === table.orderby &&
			prev.direction === table.direction &&
			prev.page === table.page &&
			JSON.stringify( prev.filterBy ) === JSON.stringify( table.filterBy )
		) {
			return;
		}

		previousTable.current = table;

		// Build URL using URLSearchParams for proper encoding
		const searchParams = new URLSearchParams( document.location.search );

		// Add non-default values
		const cleaned = removeDefaults( table, defaultOrder );

		// Handle sub parameter
		if ( pageName !== 'redirect' ) {
			searchParams.set( 'sub', pageName );
		}

		// Handle orderby
		if ( cleaned.orderby ) {
			searchParams.set( 'orderby', cleaned.orderby );
		} else {
			searchParams.delete( 'orderby' );
		}

		// Handle direction
		if ( cleaned.direction ) {
			searchParams.set( 'direction', cleaned.direction );
		} else {
			searchParams.delete( 'direction' );
		}

		// Handle page offset
		if ( cleaned.page && cleaned.page > 0 ) {
			searchParams.set( 'offset', String( cleaned.page ) );
		} else {
			searchParams.delete( 'offset' );
		}

		// Handle groupBy
		if ( cleaned.groupBy ) {
			searchParams.set( 'groupby', cleaned.groupBy );
		} else {
			searchParams.delete( 'groupby' );
		}

		// Clear existing filterby params
		const keysToDelete: string[] = [];
		searchParams.forEach( ( _, key ) => {
			if ( key.startsWith( 'filterby[' ) ) {
				keysToDelete.push( key );
			}
		} );
		keysToDelete.forEach( ( key ) => searchParams.delete( key ) );

		// Add filterBy params
		if ( table.filterBy && typeof table.filterBy === 'object' ) {
			for ( const [ key, value ] of Object.entries( table.filterBy ) ) {
				if ( value ) {
					searchParams.set( `filterby[${ key }]`, String( value ) );
				}
			}
		}

		// Update URL without adding to history
		const newUrl = '?' + searchParams.toString();
		history.replaceState( {}, '', newUrl );
	}, [ table, defaultOrder, pageName ] );
}
