import { __ } from '@wordpress/i18n';
import { useRedirectList, useGroupList, useRedirectDelete, useRedirectBulkAction, useExport } from 'lib/api/hooks';
import type { ExportRequestVariables } from 'lib/api/hooks';
import { useTableStore, useSettingsStore } from 'stores';
import {
	getDisplayGroups,
	getDisplayOptions,
	getBulk,
	getHeaders,
	getFilterOptions,
	getSearchOptions,
} from './constants';
import { cleanApiParams } from 'lib/api/utils';
import { useTableUrlSync } from 'lib/hooks';
import { has_capability, CAP_REDIRECT_ADD } from 'lib/capabilities';
import { STATUS_IDLE, STATUS_LOADING, STATUS_COMPLETE } from 'lib/constants';
import { nestedGroups } from 'lib/wordpress-url';
import { getExportFilename } from 'page/export/export-helpers';
import LogPage, {
	type LogPageTable,
	type LogOptions,
	type LogActions,
	type FilterBy,
	type RowId,
} from 'component/log-page';
import CreateRedirect from './create';
import RedirectRowActions from './row-actions';
import getColumns from './columns';
import './style.scss';

function alwaysHasSource( selected: string[] ) {
	if ( ! selected.includes( 'source' ) ) {
		return selected.concat( [ 'source' ] );
	}

	return selected;
}

function validateDisplay( selected: string[] ) {
	// Ensure we have at least source or title
	if ( ! selected.includes( 'title' ) && ! selected.includes( 'source' ) ) {
		return alwaysHasSource( selected.concat( [ 'title' ] ) );
	}

	return alwaysHasSource( selected );
}

function getGroups( groups: any[] ) {
	return [
		{
			value: '0',
			label: __( 'All groups', 'redirection' ),
		},
	].concat( nestedGroups( groups ) as any );
}

function isAvailable( item: string, table: any ) {
	return table.displaySelected.includes( item );
}

function Redirects() {
	// Get table UI state from table store
	const table = useTableStore( ( state ) => state.redirects );
	const addTop = useTableStore( ( state ) => state.redirectsAddTop );
	const { setRedirectsTable, setRedirectsSelected } = useTableStore();

	const settings = useSettingsStore( ( state ) => state.values );

	// Sync table state with URL query parameters
	useTableUrlSync( {
		table,
		setTable: setRedirectsTable,
		allowedOrder: [ 'id', 'url', 'last_access', 'last_count', 'position' ],
		allowedFilters: [ 'url', 'url-exact', 'target', 'title', 'group', 'status', 'match', 'action' ],
		defaultOrder: 'id',
		pageName: 'redirect',
	} );

	// Fetch groups for dropdown - read directly from Query
	const { data: groupData, isSuccess: groupSuccess } = useGroupList( {} );
	const groupRows = groupData?.items ?? [];

	// Bulk action mutations
	const deleteMutation = useRedirectDelete();
	const enableMutation = useRedirectBulkAction( 'enable' );
	const disableMutation = useRedirectBulkAction( 'disable' );
	const resetMutation = useRedirectBulkAction( 'reset' );
	const exportMutation = useExport();

	// Fetch redirects with current table params - read directly from Query
	const { data: redirectData, isLoading, isSuccess } = useRedirectList( table );
	const rows = redirectData?.items ?? [];
	const total = redirectData?.total ?? 0;

	// Derive status from Query states
	let status = STATUS_IDLE;
	if ( isLoading && ! redirectData ) {
		status = STATUS_LOADING;
	} else if ( isSuccess || redirectData ) {
		status = STATUS_COMPLETE;
	}

	// Get default flags from settings
	const defaultFlags = {
		flag_query: settings?.flag_query ?? 'ignore',
		flag_case: settings?.flag_case ?? false,
		flag_trailing: settings?.flag_trailing ?? false,
		flag_regex: settings?.flag_regex ?? false,
	};

	const canAdd = status === STATUS_COMPLETE && groupSuccess && has_capability( CAP_REDIRECT_ADD );

	const handleChangePage = ( page: number ) => {
		setRedirectsTable( { page, selected: [], selectAll: false } );
	};

	const handleBulk = ( action: string ) => {
		// Redirect IDs are always numbers
		const items = table.selected as number[];
		if ( items.length === 0 && ! table.selectAll ) {
			return;
		}

		if ( action === 'delete' ) {
			const message = table.selectAll
				? __( 'Are you sure you want to delete all items?', 'redirection' )
				: __( 'Are you sure you want to delete the selected items?', 'redirection' );

			if ( ! window.confirm( message ) ) {
				return;
			}
		}

		if ( [ 'export-csv', 'export-json', 'copy-csv', 'copy-json' ].includes( action ) ) {
			const params = cleanApiParams(
				table.selectAll
					? {
							global: true,
							filterBy: table.filterBy,
					  }
					: {
							items,
					  }
			);
			const format = action.endsWith( 'csv' ) ? 'csv' : 'json';
			const isCopy = action.startsWith( 'copy-' );

			const variables: ExportRequestVariables = {
				exportType: 'redirect',
				format,
				download: ! isCopy,
				copy: isCopy,
				completionNotice: {
					message: isCopy ? __( 'Export copied', 'redirection' ) : __( 'Export downloaded', 'redirection' ),
				},
				params,
			};

			if ( ! isCopy ) {
				variables.filename = getExportFilename( 'redirect', format );
			}

			exportMutation.mutate( variables );

			return;
		}

		if ( table.selectAll ) {
			// Apply action to all items matching current filters
			const params = { global: true, filterBy: table.filterBy };
			switch ( action ) {
				case 'delete':
					deleteMutation.mutate( { items: [], params } );
					break;
				// Enable, disable, and reset don't support global yet
				default:
					break;
			}
		} else {
			// Apply action to selected items only
			switch ( action ) {
				case 'delete':
					deleteMutation.mutate( { items } );
					break;
				case 'enable':
					enableMutation.mutate( { items } );
					break;
				case 'disable':
					disableMutation.mutate( { items } );
					break;
				case 'reset':
					resetMutation.mutate( { items } );
					break;
			}
		}
	};

	const handleSelect = ( items: RowId[] | boolean | RowId ) => {
		if ( typeof items === 'boolean' ) {
			setRedirectsSelected( items ? rows.map( ( r ) => r.id ) : [] );
		} else if ( typeof items === 'number' || typeof items === 'string' ) {
			// Toggle single item selection - clear selectAll if active
			const newSelected = table.selected.includes( items )
				? table.selected.filter( ( id ) => id !== items )
				: [ ...table.selected, items ];
			setRedirectsTable( { selected: newSelected, selectAll: false } );
		} else {
			setRedirectsSelected( items );
		}
	};

	const handleSetOrder = ( column: string, direction: string ) => {
		setRedirectsTable( { orderby: column, direction: direction as 'asc' | 'desc' } );
	};

	const handleFilter = ( filterBy: any ) => {
		setRedirectsTable( { filterBy, page: 0, selected: [], selectAll: false } );
	};

	const handleSetDisplay = ( displayType: string, displaySelected: string[] ) => {
		setRedirectsTable( { displayType, displaySelected } );
	};

	const handleSetAll = ( allOrClear: boolean ) => {
		setRedirectsTable( {
			selected: allOrClear ? rows.map( ( r ) => r.id ) : [],
			selectAll: allOrClear,
		} );
	};

	const logOptions: LogOptions = {
		displayFilters: getDisplayOptions(),
		displayGroups: getDisplayGroups(),
		searchOptions: getSearchOptions(),
		groupBy: getGroups( groupRows ),
		bulk: getBulk(),
		rowFilters: getFilterOptions(),
		headers: getHeaders().filter( ( item ) => isAvailable( item.name, table ) ),
		validateDisplay,
	};

	const logActions: LogActions = {
		onChangePage: handleChangePage,
		onBulk: handleBulk,
		onSelect: handleSelect,
		onSetOrder: handleSetOrder,
		onFilter: ( filter: FilterBy ) => {
			handleFilter( {
				...filter,
				...( table.filterBy?.group ? { group: String( table.filterBy.group ) } : {} ),
			} );
		},
		onSetDisplay: handleSetDisplay,
		onSetAll: handleSetAll,
		onGroup: ( group: string ) => {
			handleFilter(
				parseInt( group, 10 ) > 0
					? {
							...( table.filterBy || {} ),
							group,
					  }
					: {}
			);
		},
	};

	// Convert TableState to LogPage's Table format (camelCase)
	const logPageTable: LogPageTable = {
		page: table.page,
		perPage: table.per_page,
		orderBy: table.orderby,
		direction: table.direction,
		selected: table.selected,
		selectAll: table.selectAll ?? false,
		filter: '',
		filterBy: ( table.filterBy ?? {} ) as FilterBy,
		displayType: table.displayType ?? 'standard',
		displaySelected: table.displaySelected ?? [],
		groupBy: String( table.filterBy?.group ?? '0' ),
	};

	return (
		<div className="redirects">
			{ addTop && has_capability( CAP_REDIRECT_ADD ) && <CreateRedirect defaultFlags={ defaultFlags } addTop /> }

			<LogPage
				logOptions={ logOptions as any }
				logActions={ logActions as any }
				table={ logPageTable as any }
				status={ status as any }
				total={ total }
				rows={ rows as any }
				saving={ [] }
				getRow={ ( row: any, rowParams: any ) =>
					getColumns( row, rowParams, false, defaultFlags, { rows: groupRows } )
				}
				getRowActions={ ( row: any, rowParams: any ) => (
					<RedirectRowActions disabled={ false } row={ row } rowParams={ rowParams } />
				) }
			/>

			{ canAdd && ! addTop && <CreateRedirect defaultFlags={ defaultFlags } addTop={ false } /> }
		</div>
	);
}

export default Redirects;
