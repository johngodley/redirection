import { useEffect } from 'react';
import {
	getFilterOptions,
	getDisplayGroups,
	getDisplayOptions,
	getHeaders,
	getBulk,
	getSearchOptions,
} from './constants';
import { useGroupStore } from 'stores';
import { useGroupList, useGroupBulkAction } from 'lib/api/hooks';
import { getModules } from 'lib/modules';
import { has_capability, CAP_GROUP_ADD } from 'lib/capabilities';
import LogPage from 'component/log-page';
import CreateGroup from './create-group';
import GroupRowActions from './row-actions';
import getColumns from './columns';
import type { TableState, Group } from 'types';

function validateDisplay( selected: string[] ): string[] {
	if ( ! selected.includes( 'name' ) ) {
		return selected.concat( [ 'name' ] );
	}

	return selected;
}

function isAvailable( item: string, table: TableState ): boolean {
	return table.displaySelected?.includes( item ) ?? false;
}

function Groups() {
	// Direct property access instead of destructuring
	const status = useGroupStore( ( state ) => state.status );
	const total = useGroupStore( ( state ) => state.total );
	const table = useGroupStore( ( state ) => state.table );
	const rows = useGroupStore( ( state ) => state.rows );
	const saving = useGroupStore( ( state ) => state.saving );
	const { setTable, setSelected, setRows, setTotal, setStatus } = useGroupStore();

	const groupBulkAction = useGroupBulkAction();

	// Fetch groups with current table params
	const { data: queryData, isLoading: queryLoading } = useGroupList( table, {
		enabled: status !== 'loading',
	} );

	// Update store when query data changes
	useEffect( () => {
		if ( queryData ) {
			setRows( queryData.items );
			setTotal( queryData.total );
			setStatus( 'success' );
		}
	}, [ queryData, setRows, setTotal, setStatus ] );

	// Set loading status when query starts
	useEffect( () => {
		if ( queryLoading ) {
			setStatus( 'loading' );
		}
	}, [ queryLoading, setStatus ] );

	const handleChangePage = ( page: number ) => {
		setTable( { page } );
	};

	const handleBulk = ( action: string ) => {
		const selectedIds = table.selected as number[];
		groupBulkAction.mutate( { action, items: selectedIds } );
	};

	const handleSelect = ( id: number ) => {
		const currentSelected = table.selected as number[];
		if ( currentSelected.includes( id ) ) {
			setSelected( currentSelected.filter( ( i ) => i !== id ) );
		} else {
			setSelected( [ ...currentSelected, id ] );
		}
	};

	const handleSetOrder = ( column: string, direction: string ) => {
		setTable( { orderby: column, direction: direction as 'asc' | 'desc' } );
	};

	const handleGroup = ( groupBy: string ) => {
		setTable( { groupBy, page: 0 } );
	};

	const handleFilter = ( filterBy: Record< string, any > ) => {
		setTable( { filterBy, page: 0 } );
	};

	const handleSetDisplay = ( displayType: string, displaySelected: string[] ) => {
		setTable( { displayType, displaySelected } );
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

	const logOptions = {
		displayFilters: getDisplayOptions(),
		displayGroups: getDisplayGroups(),
		searchOptions: getSearchOptions(),
		groupBy: [],
		bulk: getBulk(),
		rowFilters: getFilterOptions(
			getModules().map( ( module ) => ( { label: module.label, value: `${ module.value }` } ) )
		),
		headers: getHeaders().filter( ( item ) => isAvailable( item.name, table ) ),
		validateDisplay,
	};

	const logActions = {
		onChangePage: handleChangePage,
		onBulk: handleBulk,
		onSelect: handleSelect,
		onSetOrder: handleSetOrder,
		onFilter: handleFilter,
		onSetDisplay: handleSetDisplay,
		onSetAll: handleSetAll,
		onGroup: handleGroup,
	};

	// Convert TableState to LogPage's Table format
	const logPageTable = {
		page: table.page,
		perPage: table.per_page,
		orderBy: table.orderby,
		direction: table.direction,
		selected: table.selected as number[],
		filterBy: ( table.filterBy ?? {} ) as Record< string, string >,
		displayType: table.displayType ?? 'standard',
		displaySelected: table.displaySelected ?? [],
		groupBy: table.groupBy ?? '',
	};

	// Convert GroupStatus to TableStatus
	const tableStatus = status === 'idle' || status === 'success' ? 'complete' : status;

	return (
		<>
			<LogPage
				logOptions={ logOptions }
				logActions={ logActions }
				table={ logPageTable as any }
				status={ tableStatus }
				total={ total }
				rows={ rows }
				saving={ saving }
				getRow={ ( row, rowParams ) => getColumns( row as Group, rowParams ) }
				getRowActions={ ( row, rowParams ) => (
					<GroupRowActions
						disabled={ saving.includes( row.id ) }
						row={ row as Group }
						rowParams={ rowParams }
					/>
				) }
			/>

			{ has_capability( CAP_GROUP_ADD ) && <CreateGroup disabled={ false } /> }
		</>
	);
}

export default Groups;
