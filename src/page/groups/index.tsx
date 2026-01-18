import {
	getFilterOptions,
	getDisplayGroups,
	getDisplayOptions,
	getHeaders,
	getBulk,
	getSearchOptions,
} from './constants';
import { useTableStore } from 'stores';
import { useGroupList, useGroupBulkAction } from 'lib/api/hooks';
import { useTableUrlSync } from 'lib/hooks';
import { getModules } from 'lib/modules';
import { has_capability, CAP_GROUP_ADD } from 'lib/capabilities';
import LogPage from 'component/log-page';
import CreateGroup from './create-group';
import GroupRowActions from './row-actions';
import getColumns, { type RowParams } from './columns';
import type { TableState, Group, RowId } from 'types';
import './style.scss';

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
	// Get table UI state from table store
	const table = useTableStore( ( state ) => state.groups );
	const { setGroupsTable, setGroupsSelected } = useTableStore();

	// Sync table state with URL query parameters
	useTableUrlSync( {
		table,
		setTable: setGroupsTable,
		allowedOrder: [ 'name', 'id' ],
		allowedFilters: [ 'name', 'status', 'module' ],
		defaultOrder: 'name',
		pageName: 'groups',
	} );

	const groupBulkAction = useGroupBulkAction();

	// Fetch groups with current table params - read directly from Query
	const { data: groupData, isLoading } = useGroupList( table );
	const rows = groupData?.items ?? [];
	const total = groupData?.total ?? 0;

	// Derive status from Query states
	const status: 'loading' | 'complete' = isLoading ? 'loading' : 'complete';

	const handleChangePage = ( page: number ) => {
		setGroupsTable( { page } );
	};

	const handleBulk = ( action: string ) => {
		const selectedIds = table.selected as number[];
		groupBulkAction.mutate( { action, items: selectedIds } );
	};

	const handleSelect = ( id: RowId | RowId[] | boolean ) => {
		const currentSelected = table.selected;

		if ( typeof id === 'boolean' ) {
			// Select all or clear all
			setGroupsSelected( id ? rows.map( ( r ) => r.id ) : [] );
		} else if ( Array.isArray( id ) ) {
			// Multiple items selected
			setGroupsSelected( id );
		} else if ( currentSelected.includes( id ) ) {
			// Toggle single item - remove if selected
			setGroupsSelected( currentSelected.filter( ( i ) => i !== id ) );
		} else {
			// Toggle single item - add if not selected
			setGroupsSelected( [ ...currentSelected, id ] );
		}
	};

	const handleSetOrder = ( column: string, direction: string ) => {
		setGroupsTable( { orderby: column, direction: direction as 'asc' | 'desc' } );
	};

	const handleGroup = ( groupBy: string ) => {
		setGroupsTable( { groupBy, page: 0 } );
	};

	const handleFilter = ( filterBy: Record< string, any > ) => {
		setGroupsTable( { filterBy, page: 0 } );
	};

	const handleSetDisplay = ( displayType: string, displaySelected: string[] ) => {
		setGroupsTable( { displayType, displaySelected } );
	};

	const handleSetAll = ( allOrClear: boolean ) => {
		setGroupsTable( {
			selected: allOrClear ? rows.map( ( r ) => r.id ) : [],
			selectAll: allOrClear,
		} );
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
		selectAll: table.selectAll ?? false,
		filter: '',
		filterBy: ( table.filterBy ?? {} ) as Record< string, string >,
		displayType: table.displayType ?? 'standard',
		displaySelected: table.displaySelected ?? [],
		groupBy: table.groupBy ?? '',
	};

	return (
		<>
			<LogPage
				logOptions={ logOptions }
				logActions={ logActions }
				table={ logPageTable }
				status={ status }
				total={ total }
				rows={ rows }
				saving={ [] }
				getRow={ ( row, rowParams ) => getColumns( row as Group, rowParams as unknown as RowParams ) }
				getRowActions={ ( row, rowParams ) => (
					<GroupRowActions
						disabled={ false }
						row={ row as Group }
						rowParams={ rowParams as unknown as RowParams }
					/>
				) }
			/>

			{ has_capability( CAP_GROUP_ADD ) && <CreateGroup disabled={ false } /> }
		</>
	);
}

export default Groups;
