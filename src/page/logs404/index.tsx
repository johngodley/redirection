import { useState } from 'react';
import { __ } from '@wordpress/i18n';
import {
	getBulk,
	getDisplayOptions,
	getDisplayGroups,
	getGroupBy,
	getSearchOptions,
	getHeaders,
	getFilterOptions,
} from './constants';
import { useTableStore, useSettingsStore } from 'stores';
import { useErrorList, useErrorBulkAction } from 'lib/api/hooks';
import { useTableUrlSync } from 'lib/hooks';
import { has_capability, CAP_404_DELETE } from 'lib/capabilities';
import { STATUS_IDLE, STATUS_LOADING, STATUS_COMPLETE } from 'lib/constants';
import getCreateAction from './create-action';
import LogPage from 'component/log-page';
import CreateRedirect from './create-redirect';
import ErrorRowActions from './row-actions';
import TableButtons from 'component/table/table-buttons';
import getColumns from './columns';

interface TableState {
	filterBy: Record< string, any >;
	groupBy?: string;
	displaySelected: string[];
	selected: number[];
	[ key: string ]: any;
}

function validateDisplay( selected: string[] ): string[] {
	if ( ! selected.includes( 'url' ) ) {
		return selected.concat( [ 'url' ] );
	}

	return selected;
}

function canDeleteAll( table: TableState ): boolean {
	const { filterBy, groupBy } = table;
	if ( filterBy.url !== undefined ) {
		return true;
	}

	if ( groupBy ) {
		return false;
	}

	return Object.keys( filterBy ).length === 0;
}

function isAvailable( item: string, table: TableState ): boolean {
	return table.displaySelected.includes( item );
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

function Logs404() {
	// Get table UI state from table store
	const table = useTableStore( ( state ) => state.errors );
	const { setErrorsTable, setErrorsSelected } = useTableStore();

	const settings = useSettingsStore( ( state ) => state.values );
	const [ showCreate, setShowCreate ] = useState< any >( null );

	// Sync table state with URL query parameters
	useTableUrlSync( {
		table,
		setTable: setErrorsTable,
		allowedGroup: [ 'url', 'ip', 'agent' ],
		allowedFilters: [ 'url', 'url-exact', 'referrer', 'agent', 'ip', 'domain', 'method' ],
		pageName: '404s',
	} );

	const errorBulkAction = useErrorBulkAction();

	// Fetch errors with current table params - read directly from Query
	const { data: errorData, isFetching: isLoading } = useErrorList( table );
	const rows = errorData?.items ?? [];
	const total = errorData?.total ?? 0;

	// Derive status from Query states
	let status = STATUS_IDLE;
	if ( isLoading ) {
		status = STATUS_LOADING;
	} else if ( errorData ) {
		status = STATUS_COMPLETE;
	}

	// Note: Groups are needed for the create redirect modal
	// They should be loaded by a parent component or globally

	function onCreate( create: any ) {
		setErrorsSelected( [] );
		setShowCreate( create );
	}

	function handleDelete( id: number | string ) {
		if ( ! window.confirm( __( 'Are you sure you want to delete this item?', 'redirection' ) ) ) {
			return;
		}

		const params = table.groupBy ? { groupBy: table.groupBy } : {};
		errorBulkAction.mutate( { action: 'delete', items: [ id ], params } );
	}

	function handleBulk( action: string ) {
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
		if ( action === 'delete' ) {
			if ( table.selectAll ) {
				// Delete all items matching current filters
				errorBulkAction.mutate( {
					action: 'delete',
					items: [],
					params: { ...params, global: true, filterBy: table.filterBy },
				} );
			} else {
				// Delete only selected items
				errorBulkAction.mutate( { action: 'delete', items: table.selected, params } );
			}
		} else {
			setShowCreate( getCreateAction( action, table.selected as any ) );
		}
	}

	const handleChangePage = ( page: number ) => {
		setErrorsTable( { page, selected: [], selectAll: false } );
	};

	const handleSetOrder = ( column: string, direction: string ) => {
		// Date sorting is implicit (default order), so don't send orderby param
		const orderby = column === 'date' ? '' : column;
		setErrorsTable( { orderby, direction: direction as 'asc' | 'desc' } );
	};

	const handleGroup = ( groupBy: string ) => {
		setErrorsTable( { groupBy, selected: [], selectAll: false } );
	};

	const handleFilter = ( filterBy: Record< string, any > ) => {
		setErrorsTable( { filterBy, page: 0, selected: [], selectAll: false } );
	};

	const handleSetDisplay = ( displayType: string, displaySelected: string[] ) => {
		setErrorsTable( { displayType, displaySelected } );
	};

	const handleSelect = ( items: ( number | string )[] | boolean | number | string ) => {
		if ( typeof items === 'boolean' ) {
			setErrorsSelected( items ? rows.map( ( r ) => r.id ) : [] );
		} else if ( typeof items === 'number' || typeof items === 'string' ) {
			// Toggle single item selection - clear selectAll if active
			const currentSelected = Array.isArray( table.selected ) ? table.selected : [];
			const newSelected = currentSelected.includes( items )
				? currentSelected.filter( ( id ) => id !== items )
				: [ ...currentSelected, items ];
			setErrorsTable( { selected: newSelected, selectAll: false } );
		} else {
			setErrorsSelected( items );
		}
	};

	const handleSetAll = ( allOrClear: boolean ) => {
		setErrorsTable( {
			selected: allOrClear ? rows.map( ( r ) => r.id ) : [],
			selectAll: allOrClear,
		} );
	};

	const groupedTable = { ...table, ...getGroupByTable( table.groupBy ) };

	// Convert TableState to LogPage's Table format (camelCase)
	const logPageTable = {
		page: table.page,
		perPage: table.per_page,
		orderBy: table.orderby || 'date',
		direction: table.direction,
		selected: table.selected ?? [],
		selectAll: table.selectAll ?? false,
		filter: '',
		filterBy: ( table.filterBy ?? {} ) as Record< string, string >,
		displayType: table.displayType ?? 'standard',
		displaySelected: groupedTable.displaySelected ?? [],
		groupBy: table.groupBy ?? '',
	};

	const logOptions = {
		displayFilters: getDisplayOptions( groupedTable.groupBy ),
		displayGroups: getDisplayGroups( groupedTable.groupBy ),
		searchOptions: getSearchOptions(),
		groupBy: getGroupBy( settings?.ip_logging || 0 ),
		bulk: getBulk( groupedTable.groupBy ),
		rowFilters: groupedTable.groupBy ? [] : getFilterOptions(),
		headers: getHeaders( groupedTable.groupBy ).filter( ( item ) => isAvailable( item.name, groupedTable as any ) ),
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
		<>
			{ showCreate && (
				<CreateRedirect onClose={ () => setShowCreate( null ) } redirect={ showCreate } rows={ rows as any } />
			) }

			<LogPage
				logOptions={ logOptions as any }
				logActions={ logActions as any }
				table={ logPageTable as any }
				status={ status as any }
				total={ total }
				rows={ rows as any }
				saving={ [] }
				getRow={ ( row: any, rowParams: any ) =>
					getColumns( row, {
						...rowParams,
						onFilter: handleFilter,
					} )
				}
				getRowActions={ ( row: any, rowParams: any ) => (
					<ErrorRowActions
						disabled={ false }
						row={ row as any }
						onCreate={ onCreate }
						onDelete={ handleDelete }
						table={ rowParams.table }
					/>
				) }
				renderTableActions={ () => {
					if ( has_capability( CAP_404_DELETE ) && canDeleteAll( groupedTable as any ) ) {
						return <TableButtons enabled={ rows.length > 0 }>{ null }</TableButtons>;
					}
					return <></>;
				} }
			/>
		</>
	);
}

export default Logs404;
