/**
 * External dependencies
 */

import React, { useState, useEffect } from 'react';
import { connect } from 'react-redux';

/**
 * Internal dependencies
 */

import TableButtons from '../../component/table/table-buttons';
import CreateRedirect from './create-redirect';
import { getGroup } from '../../state/group/action';
import {
	loadLogs,
	setFilter,
	setPage,
	performTableAction,
	setOrderBy,
	setGroupBy,
	setSelected,
	setDisplay,
} from '../../state/error/action';
import {
	getBulk,
	getDisplayOptions,
	getDisplayGroups,
	getGroupBy,
	getSearchOptions,
	getHeaders,
	getFilterOptions,
} from './constants';
import { has_capability, CAP_404_DELETE } from '../../lib/capabilities';
import getCreateAction from './create-action';
import LogPage from '../../component/log-page';
import ErrorRowActions from './row-actions';
import getColumns from '../../component/log-page/log-columns';

function validateDisplay( selected ) {
	// Ensure we have at least source or title
	if ( selected.indexOf( 'url' ) === -1 ) {
		return selected.concat( [ 'url' ] );
	}

	return selected;
}

/**
 *
 * @param {*} param0
 */
function canDeleteAll( table ) {
	const { filterBy, groupBy } = table;
	if ( filterBy.url !== undefined ) {
		return true;
	}

	if ( groupBy ) {
		return false;
	}

	return Object.keys( filterBy ).length === 0;
}

/**
 * @param {string} item
 * @param {Table} table - Table params
 **/
function isAvailable( item, table ) {
	return table.displaySelected.indexOf( item ) !== -1;
}

function getGroupByTable( groupBy ) {
	if ( groupBy ) {
		return {
			displayOptions: getDisplayOptions( groupBy ),
			displaySelected: getDisplayGroups( groupBy )[ 0 ].grouping,
		};
	}

	return {};
}

/**
 *
 * @param {*} props
 */
function Logs404( props ) {
	const { onSelect, error } = props;
	const { status, total, table, rows, saving } = error;
	const [ showCreate, setShowCreate ] = useState( null );

	function onCreate( create ) {
		onSelect( false );
		setShowCreate( create );
	}

	function onBulk( action, items ) {
		if ( action === 'delete' ) {
			props.onBulk( action, items );
		} else {
			setShowCreate( getCreateAction( action, table.selected ) );
		}
	}

	useEffect(() => {
		props.onLoad();
		props.onLoadGroups();
	}, []);

	const groupedTable = { ...table, ...getGroupByTable( table.groupBy ) };
	const logOptions = {
		displayFilters: getDisplayOptions( groupedTable.groupBy ),
		displayGroups: getDisplayGroups( groupedTable.groupBy ),
		searchOptions: getSearchOptions(),
		groupBy: getGroupBy( props.settings.values.ip_logging ),
		bulk: getBulk( groupedTable.groupBy, Object.keys( groupedTable.filterBy ).length > 0 ),
		rowFilters: groupedTable.groupBy ? [] : getFilterOptions(),
		headers: getHeaders( groupedTable.groupBy ).filter( ( item ) => isAvailable( item.name, groupedTable ) ),
		validateDisplay,
	};

	return (
		<>
			{ showCreate && <CreateRedirect onClose={ () => setShowCreate( null ) } redirect={ showCreate } /> }

			<LogPage
				logOptions={ logOptions }
				logActions={ { ...props, onBulk } }
				table={ groupedTable }
				status={ status }
				total={ total }
				rows={ rows }
				saving={ saving }
				getRow={ ( row, rowParams ) =>
					getColumns( row, rowParams, props, saving.indexOf( row.id ) !== -1 )
				}
				getRowActions={ ( row, rowParams ) => (
					<ErrorRowActions
						disabled={ saving.indexOf( row.id ) !== -1 }
						row={ row }
						onCreate={ onCreate }
						onDelete={ ( id ) => onBulk( 'delete', [ id ] ) }
						table={ rowParams.table }
					/>
				) }
				renderTableActions={ () =>
					has_capability( CAP_404_DELETE ) &&
					canDeleteAll( groupedTable ) && <TableButtons enabled={ rows.length > 0 } />
				}
			/>
		</>
	);
}


function mapStateToProps( state ) {
	const { error, settings } = state;

	return {
		error,
		settings,
	};
}

function mapDispatchToProps( dispatch ) {
	return {
		onLoad: () => {
			dispatch( loadLogs() );
		},
		onLoadGroups: () => {
			dispatch( getGroup() );
		},
		onChangePage: ( page ) => {
			dispatch( setPage( page ) );
		},
		onBulk: ( action, items ) => {
			dispatch( performTableAction( action, items ) );
		},
		onSetOrder: ( column, direction ) => {
			dispatch( setOrderBy( column, direction ) );
		},
		onGroup: ( groupBy ) => {
			dispatch( setGroupBy( groupBy ) );
		},
		onSelect: ( items ) => {
			dispatch( setSelected( items ) );
		},
		onFilter: ( filterBy ) => {
			dispatch( setFilter( filterBy ) );
		},
		onSetDisplay: ( displayType, displaySelected ) => {
			dispatch( setDisplay( displayType, displaySelected ) );
		},
		onSetAll: ( allOrClear ) => {
			dispatch( setSelected( allOrClear, allOrClear ) );
		},
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps
)( Logs404 );
