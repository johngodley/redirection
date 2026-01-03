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
import { useLogList, useErrorBulkAction } from 'lib/api/hooks';
import { getRssUrl } from 'lib/wordpress-url';
import LogPage from 'component/log-page';
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

	const logBulkAction = useErrorBulkAction();

	// Fetch logs with current table params - read directly from Query
	const { data: logData, isLoading, isSuccess } = useLogList( table );
	const rows = logData?.items ?? [];
	const total = logData?.total ?? 0;

	// Derive status from Query states
	let status = 'idle';
	if ( isLoading ) {
		status = 'loading';
	} else if ( isSuccess ) {
		status = 'success';
	}

	const handleChangePage = ( page: number ) => {
		setLogsTable( { page } );
	};

	const handleBulk = ( action: string, items: number[] ) => {
		logBulkAction.mutate( { action, items } );
	};

	const handleSetOrder = ( column: string, direction: string ) => {
		setLogsTable( { orderby: column, direction: direction as 'asc' | 'desc' } );
	};

	const handleGroup = ( groupBy: string ) => {
		setLogsTable( { groupBy } );
	};

	const handleFilter = ( filterBy: Record< string, any > ) => {
		setLogsTable( { filterBy, page: 0 } );
	};

	const handleSetDisplay = ( displayType: string, displaySelected: string[] ) => {
		setLogsTable( { displayType, displaySelected } );
	};

	const handleSelect = ( items: number[] | boolean ) => {
		if ( typeof items === 'boolean' ) {
			setLogsSelected( items ? rows.map( ( r ) => r.id ) : [] );
		} else {
			setLogsSelected( items );
		}
	};

	const handleSetAll = ( allOrClear: any ) => {
		if ( allOrClear ) {
			setLogsSelected( rows.map( ( r ) => r.id ) );
		} else {
			setLogsSelected( [] );
		}
	};

	const groupedTable = { ...table, ...getGroupByTable( table.groupBy ) };
	const logOptions = {
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

	const logActions = {
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
			logOptions={ logOptions as any }
			logActions={ logActions as any }
			table={ groupedTable as any }
			status={ status as any }
			total={ total }
			rows={ rows as any }
			saving={ [] }
			getRow={ ( row: any ) => getColumns( row ) }
			getRowActions={ ( row: any ) => (
				<LogRowActions disabled={ false } row={ row } onDelete={ ( id ) => handleBulk( 'delete', [ id ] ) } />
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
