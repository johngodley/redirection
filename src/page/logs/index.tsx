import { __ } from '@wordpress/i18n';
import {
	getHeaders,
	getBulk,
	getDisplayOptions,
	getDisplayGroups,
	getSearchOptions,
	getFilterOptions,
	getGroupBy,
} from './constants';
import { useTableStore, useSettingsStore } from 'stores';
import { useLogList, useLogBulkAction } from 'lib/api/hooks';
import { cleanApiParams } from 'lib/api/utils';
import { getRssUrl } from 'lib/wordpress-url';
import { useTableUrlSync } from 'lib/hooks';
import { STATUS_IDLE, STATUS_LOADING, STATUS_COMPLETE, type LoadingStatus } from 'lib/constants';
import LogPage, {
	type LogPageTable,
	type LogOptions,
	type LogActions,
	type TableRow,
	type FilterBy,
	type RowId,
} from 'component/log-page';
import LogRowActions from './row-actions';
import TableButtons from 'component/table/table-buttons';
import getColumns from './columns';

interface TableState {
	groupBy?: string;
	displaySelected: string[];
	[ key: string ]: any;
}

function validateDisplay( selected: string[] ): string[] {
	if ( ! selected.includes( 'url' ) ) {
		return selected.concat( [ 'url' ] );
	}

	return selected;
}

function getGroupByTable( groupBy?: string ): Partial< TableState > {
	if ( groupBy ) {
		return {
			displayOptions: getDisplayOptions( groupBy ),
			displaySelected: getDisplayGroups( groupBy )[ 0 ]?.grouping || [],
		};
	}

	return {};
}

function isAvailable( item: string, table: TableState ): boolean {
	return table.displaySelected.includes( item );
}

function Logs() {
	// Get table UI state from table store
	const table = useTableStore( ( state ) => state.logs );
	const { setLogsTable, setLogsSelected } = useTableStore();

	const settings = useSettingsStore( ( state ) => state.values );
	const token = settings?.token || '';

	// Sync table state with URL query parameters
	useTableUrlSync( {
		table,
		setTable: setLogsTable,
		allowedGroup: [ 'url', 'ip', 'agent' ],
		allowedFilters: [ 'url', 'url-exact', 'referrer', 'agent', 'ip', 'target', 'domain', 'method', 'redirect_by' ],
		pageName: 'log',
	} );

	const logBulkAction = useLogBulkAction();

	// Fetch logs with current table params - read directly from Query
	const { data: logData, isLoading, isSuccess } = useLogList( table );
	const rows = logData?.items ?? [];
	const total = logData?.total ?? 0;

	// Derive status from Query states
	let status = STATUS_IDLE;
	if ( isLoading && ! logData ) {
		status = STATUS_LOADING;
	} else if ( isSuccess || logData ) {
		status = STATUS_COMPLETE;
	}

	const handleChangePage = ( page: number ) => {
		setLogsTable( { page, selected: [], selectAll: false } );
	};

	const handleDelete = ( id: number | string ) => {
		if ( ! window.confirm( __( 'Are you sure you want to delete this item?', 'redirection' ) ) ) {
			return;
		}

		const params = table.groupBy ? { groupBy: table.groupBy } : {};
		logBulkAction.mutate( { action: 'delete', items: [ id ], params } );
	};

	const handleBulk = ( action: string ) => {
		if ( table.selected.length === 0 && ! table.selectAll ) {
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

		const params = table.groupBy ? { groupBy: table.groupBy } : {};

		if ( table.selectAll ) {
			// Delete all items matching current filters
			logBulkAction.mutate( {
				action,
				items: [],
				params: cleanApiParams( { ...params, global: true, filterBy: table.filterBy } ),
			} );
		} else {
			// Delete only selected items
			logBulkAction.mutate( { action, items: table.selected, params } );
		}
	};

	const handleSetOrder = ( column: string, direction: string ) => {
		setLogsTable( { orderby: column === 'date' ? '' : column, direction: direction as 'asc' | 'desc' } );
	};

	const handleGroup = ( groupBy: string ) => {
		setLogsTable( { groupBy, selected: [], selectAll: false } );
	};

	const handleFilter = ( filterBy: FilterBy ) => {
		setLogsTable( { filterBy, page: 0, selected: [], selectAll: false } );
	};

	const handleSetDisplay = ( displayType: string, displaySelected: string[] ) => {
		setLogsTable( { displayType, displaySelected } );
	};

	const handleSelect = ( items: RowId | RowId[] | boolean ) => {
		if ( typeof items === 'boolean' ) {
			setLogsSelected( items ? rows.map( ( r ) => r.id ) : [] );
		} else if ( typeof items === 'number' || typeof items === 'string' ) {
			// Toggle single item selection - clear selectAll if active
			const currentSelected = Array.isArray( table.selected ) ? table.selected : [];
			const newSelected = currentSelected.some( ( id ) => id === items )
				? currentSelected.filter( ( id ) => id !== items )
				: [ ...currentSelected, items ];
			setLogsTable( { selected: newSelected, selectAll: false } );
		} else {
			setLogsSelected( items );
		}
	};

	const handleSetAll = ( allOrClear: boolean ) => {
		setLogsTable( {
			selected: allOrClear ? rows.map( ( r ) => r.id ) : [],
			selectAll: allOrClear,
		} );
	};

	const groupedTable = { ...table, ...getGroupByTable( table.groupBy ) };

	// Convert TableState to LogPage's Table format (camelCase)
	const logPageTable: LogPageTable = {
		page: table.page,
		perPage: table.per_page,
		orderBy: table.orderby || 'date',
		direction: table.direction,
		selected: table.selected ?? [],
		selectAll: table.selectAll ?? false,
		filter: '',
		filterBy: ( table.filterBy ?? {} ) as FilterBy,
		displayType: table.displayType ?? 'standard',
		displaySelected: groupedTable.displaySelected ?? [],
		groupBy: table.groupBy ?? '',
	};

	const logOptions: LogOptions = {
		displayFilters: getDisplayOptions( groupedTable.groupBy ),
		displayGroups: getDisplayGroups( groupedTable.groupBy ),
		searchOptions: getSearchOptions(),
		groupBy: getGroupBy( settings?.ip_logging || 0 ),
		bulk: getBulk(),
		rowFilters: groupedTable.groupBy ? [] : getFilterOptions(),
		headers: getHeaders( groupedTable.groupBy ).filter( ( item ) =>
			isAvailable( item.name, groupedTable as TableState )
		),
		validateDisplay,
	};

	const logActions: LogActions = {
		onChangePage: handleChangePage,
		onBulk: handleBulk,
		onGroup: handleGroup,
		onSetOrder: handleSetOrder,
		onFilter: handleFilter,
		onSetDisplay: handleSetDisplay,
		onSelect: handleSelect,
		onSetAll: handleSetAll,
	};

	return (
		<LogPage
			logOptions={ logOptions }
			logActions={ logActions }
			table={ logPageTable }
			status={ status as LoadingStatus }
			total={ total }
			rows={ rows as TableRow[] }
			saving={ [] }
			getRow={ ( row, rowParams ) =>
				getColumns( row as Parameters< typeof getColumns >[ 0 ], {
					...rowParams,
					onFilter: handleFilter,
				} )
			}
			getRowActions={ ( row ) => (
				<LogRowActions
					disabled={ false }
					row={ row as Parameters< typeof getColumns >[ 0 ] }
					onDelete={ handleDelete }
					groupBy={ table.groupBy }
				/>
			) }
			renderTableActions={ () => (
				<>
					<TableButtons enabled={ rows.length > 0 }>
						{ token && (
							<div className="table-button-item">
								<a href={ getRssUrl( token ) } className="button-secondary">
									{ __( 'RSS', 'redirection' ) }
								</a>
							</div>
						) }
					</TableButtons>
				</>
			) }
		/>
	);
}

export default Logs;
