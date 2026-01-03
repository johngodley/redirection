import { __ } from '@wordpress/i18n';
import { useRedirectList, useGroupList } from 'lib/api/hooks';
import { useTableStore, useSettingsStore } from 'stores';
import {
	getDisplayGroups,
	getDisplayOptions,
	getBulk,
	getHeaders,
	getFilterOptions,
	getSearchOptions,
} from './constants';
import { has_capability, CAP_REDIRECT_ADD } from 'lib/capabilities';
import { nestedGroups } from 'lib/wordpress-url';
import LogPage from 'component/log-page';
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

	// Fetch groups for dropdown - read directly from Query
	const { data: groupData, isSuccess: groupSuccess } = useGroupList( {} );
	const groupRows = groupData?.items ?? [];

	// Fetch redirects with current table params - read directly from Query
	const { data: redirectData, isLoading, isSuccess } = useRedirectList( table );
	const rows = redirectData?.items ?? [];
	const total = redirectData?.total ?? 0;

	// Derive status from Query states
	let status = 'idle';
	if ( isLoading ) {
		status = 'loading';
	} else if ( isSuccess ) {
		status = 'success';
	}

	// Get default flags from settings
	const defaultFlags = {
		flag_query: settings?.flag_query ?? 'ignore',
		flag_case: settings?.flag_case ?? false,
		flag_trailing: settings?.flag_trailing ?? false,
		flag_regex: settings?.flag_regex ?? false,
	};

	const canAdd = status === 'success' && groupSuccess && has_capability( CAP_REDIRECT_ADD );

	const handleChangePage = ( page: number ) => {
		setRedirectsTable( { page } );
	};

	const handleBulk = () => {
		// Note: Bulk actions are handled through mutations
		// XXX fix this - bulk actions are not implemented yet.
	};

	const handleSelect = ( items: number[] | boolean ) => {
		if ( typeof items === 'boolean' ) {
			setRedirectsSelected( items ? rows.map( ( r ) => r.id ) : [] );
		} else {
			setRedirectsSelected( items );
		}
	};

	const handleSetOrder = ( column: string, direction: string ) => {
		setRedirectsTable( { orderby: column, direction: direction as 'asc' | 'desc' } );
	};

	const handleFilter = ( filterBy: any ) => {
		setRedirectsTable( { filterBy, page: 0 } );
	};

	const handleSetDisplay = ( displayType: string, displaySelected: string[] ) => {
		setRedirectsTable( { displayType, displaySelected } );
	};

	const handleSetAll = ( allOrClear: any ) => {
		if ( allOrClear ) {
			setRedirectsSelected( rows.map( ( r ) => r.id ) );
		} else {
			setRedirectsSelected( [] );
		}
	};

	const logOptions = {
		displayFilters: getDisplayOptions(),
		displayGroups: getDisplayGroups(),
		searchOptions: getSearchOptions(),
		groupBy: getGroups( groupRows ),
		bulk: getBulk(),
		rowFilters: getFilterOptions(),
		headers: getHeaders().filter( ( item ) => isAvailable( item.name, table ) ),
		validateDisplay,
	};

	const logActions = {
		onChangePage: handleChangePage,
		onBulk: handleBulk,
		onSelect: handleSelect,
		onSetOrder: handleSetOrder,
		onFilter: ( filter: any ) => {
			handleFilter( {
				...filter,
				...( table.filterBy?.group ? { group: table.filterBy.group } : {} ),
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

	return (
		<div className="redirects">
			{ addTop && has_capability( CAP_REDIRECT_ADD ) && <CreateRedirect defaultFlags={ defaultFlags } addTop /> }

			<LogPage
				logOptions={ logOptions as any }
				logActions={ logActions as any }
				table={ { ...table, groupBy: table.filterBy?.group ? table.filterBy.group : 0 } as any }
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
