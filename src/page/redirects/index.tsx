import { useEffect } from 'react';
import { __ } from '@wordpress/i18n';
import { useRedirectList, useGroupList } from 'lib/api/hooks';
import { useRedirectStore, useGroupStore, useSettingsStore } from 'stores';
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
	const status = useRedirectStore( ( state ) => state.status );
	const total = useRedirectStore( ( state ) => state.total );
	const table = useRedirectStore( ( state ) => state.table );
	const rows = useRedirectStore( ( state ) => state.rows );
	const addTop = useRedirectStore( ( state ) => state.addTop );
	const saving = useRedirectStore( ( state ) => state.saving );
	const { setTable, setSelected, setRows, setTotal, setStatus } = useRedirectStore();

	const groupRows = useGroupStore( ( state ) => state.rows );
	const groupStatus = useGroupStore( ( state ) => state.status );
	const { setRows: setGroupRows, setTotal: setGroupTotal, setStatus: setGroupStatus } = useGroupStore();
	const settings = useSettingsStore( ( state ) => state.values );

	// Fetch groups for dropdown
	const { data: groupData } = useGroupList( {} );

	// Fetch redirects with current table params
	const { data: queryData, isLoading: queryLoading } = useRedirectList( table, {
		enabled: status !== 'loading',
	} );

	// Update group store when group data changes
	useEffect( () => {
		if ( groupData ) {
			setGroupRows( groupData.items );
			setGroupTotal( groupData.total );
			setGroupStatus( 'success' );
		}
	}, [ groupData, setGroupRows, setGroupTotal, setGroupStatus ] );

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

	// Get default flags from settings
	const defaultFlags = {
		flag_query: settings?.flag_query || 'ignore',
		flag_case: settings?.flag_case || false,
		flag_trailing: settings?.flag_trailing || false,
		flag_regex: settings?.flag_regex || false,
	};

	const canAdd = status === 'success' && groupStatus === 'success' && has_capability( CAP_REDIRECT_ADD );

	const handleChangePage = ( page: number ) => {
		setTable( { page } );
	};

	const handleBulk = () => {
		// Note: Bulk actions are handled through store actions, not hooks
		// The redirectBulkAction hook defined above can be used for default actions
		// For dynamic actions, we'd need to refactor to use store actions directly
		// XXX fix this - bulk actions are not implemented yet.
	};

	const handleSelect = ( items: number[] | boolean ) => {
		if ( typeof items === 'boolean' ) {
			setSelected( items ? rows.map( ( r ) => r.id ) : [] );
		} else {
			setSelected( items );
		}
	};

	const handleSetOrder = ( column: string, direction: string ) => {
		setTable( { orderby: column, direction: direction as 'asc' | 'desc' } );
	};

	const handleFilter = ( filterBy: any ) => {
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
				saving={ saving }
				getRow={ ( row: any, rowParams: any ) =>
					getColumns( row, rowParams, saving.includes( row.id ), defaultFlags, { rows: groupRows } )
				}
				getRowActions={ ( row: any, rowParams: any ) => (
					<RedirectRowActions
						disabled={ saving.includes( row.id ) }
						row={ row }
						rowParams={ rowParams }
					/>
				) }
			/>

			{ canAdd && ! addTop && <CreateRedirect defaultFlags={ defaultFlags } addTop={ false } /> }
		</div>
	);
}

export default Redirects;
