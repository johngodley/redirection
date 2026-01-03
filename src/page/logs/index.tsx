import { useEffect } from 'react';
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
import { useLogStore, useSettingsStore } from 'stores';
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
	// Direct property access instead of destructuring
	const status = useLogStore( ( state ) => state.status );
	const total = useLogStore( ( state ) => state.total );
	const table = useLogStore( ( state ) => state.table );
	const rows = useLogStore( ( state ) => state.rows );
	const saving = useLogStore( ( state ) => state.saving );
	const setTable = useLogStore( ( state ) => state.setTable );
	const setSelected = useLogStore( ( state ) => state.setSelected );
	const setRows = useLogStore( ( state ) => state.setRows );
	const setTotal = useLogStore( ( state ) => state.setTotal );
	const setStatus = useLogStore( ( state ) => state.setStatus );

	const settings = useSettingsStore( ( state ) => state.values );
	const token = settings?.token || '';

	const logBulkAction = useErrorBulkAction();

	// Fetch logs with current table params
	const { data: queryData, isLoading: queryLoading } = useLogList( table );

	// Update store when query data changes
	useEffect( () => {
		if ( queryData ) {
			setRows( queryData.items as any );
			setTotal( queryData.total );
			setStatus( 'success' );
		}
	}, [ queryData, setRows, setTotal, setStatus ] );

	// Set loading status when query starts
	useEffect( () => {
		if ( queryLoading ) {
			setStatus( 'loading' );
		} else if ( queryData ) {
			setStatus( 'success' );
		}
	}, [ queryLoading, queryData, setStatus ] );

	const handleChangePage = ( page: number ) => {
		setTable( { page } );
	};

	const handleBulk = ( action: string, items: number[] ) => {
		logBulkAction.mutate( { action, items } );
	};

	const handleSetOrder = ( column: string, direction: string ) => {
		setTable( { orderby: column, direction: direction as 'asc' | 'desc' } );
	};

	const handleGroup = ( groupBy: string ) => {
		setTable( { groupBy } );
	};

	const handleFilter = ( filterBy: Record< string, any > ) => {
		setTable( { filterBy, page: 0 } );
	};

	const handleSetDisplay = ( displayType: string, displaySelected: string[] ) => {
		setTable( { displayType, displaySelected } );
	};

	const handleSelect = ( items: number[] | boolean ) => {
		if ( typeof items === 'boolean' ) {
			setSelected( items ? rows.map( ( r ) => r.id ) : [] );
		} else {
			setSelected( items );
		}
	};

	const handleSetAll = ( allOrClear: any ) => {
		if ( allOrClear ) {
			setSelected(
				rows.map( ( r ) => r.id ),
				true
			);
		} else {
			setSelected( [] );
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
			saving={ saving }
			getRow={ ( row: any ) => getColumns( row ) }
			getRowActions={ ( row: any ) => (
				<LogRowActions
					disabled={ saving.includes( row.id ) }
					row={ row }
					onDelete={ ( id ) => handleBulk( 'delete', [ id ] ) }
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
