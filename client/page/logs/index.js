/**
 * External dependencies
 */

import React, { useEffect } from 'react';
import { connect } from 'react-redux';
import { translate as __ } from 'i18n-calypso';

/**
 * Internal dependencies
 */

import Table from 'component/table';
import DeleteAll from 'page/logs/delete-all';
import { getOption } from 'state/settings/selector';
import {
	loadLogs,
	deleteAll,
	setPage,
	performTableAction,
	setAllSelected,
	setOrderBy,
	setGroupBy,
	setFilter,
	setDisplay,
	setSelected,
} from 'state/log/action';
import { getRssUrl } from 'lib/wordpress-url';
import {
	getHeaders,
	getBulk,
	getDisplayOptions,
	getDisplayGroups,
	getSearchOptions,
	getFilterOptions,
	getGroupBy,
} from './constants';
import { has_capability, CAP_LOG_DELETE } from 'lib/capabilities';
import LogPage from 'component/log-page';
import getColumns from 'component/log-page/log-columns';
import LogRowActions from './row-actions';

function validateDisplay( selected ) {
	// Ensure we have at least source or title
	if ( selected.indexOf( 'url' ) === -1 ) {
		return selected.concat( [ 'url' ] );
	}

	return selected;
}

function canDeleteAll( { filterBy } ) {
	return Object.keys( filterBy ).length === 0;
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
 * @param {string} item
 * @param {Table} table - Table params
 **/
function isAvailable( item, table ) {
	return table.displaySelected.indexOf( item ) !== -1;
}

function Logs( props ) {
	const { onFilter, onDelete, onDeleteAll, token } = props;
	const { status, total, table, rows, saving } = props.log;

	useEffect(() => {
		props.onLoad();
	}, []);

	const groupedTable = { ...table, ...getGroupByTable( table.groupBy ) };
	const logOptions = {
		displayFilters: getDisplayOptions( groupedTable.groupBy ),
		displayGroups: getDisplayGroups( groupedTable.groupBy ),
		searchOptions: getSearchOptions(),
		groupBy: getGroupBy( props.settings.values.ip_logging ),
		bulk: getBulk(),
		rowFilters: groupedTable.groupBy ? [] : getFilterOptions(),
		headers: getHeaders( groupedTable.groupBy ).filter( ( item ) => isAvailable( item.name, groupedTable ) ),
		validateDisplay,
	};

	return (
		<LogPage
			logOptions={ logOptions }
			logActions={ props }
			table={ groupedTable }
			status={ status }
			total={ total }
			rows={ rows }
			saving={ saving }
			getRow={ ( row, rowParams ) =>
				getColumns( row, rowParams, onFilter, onDelete, () => {}, saving.indexOf( row.id ) !== -1 )
			}
			getRowActions={ ( row, rowParams ) => (
				<LogRowActions
					disabled={ saving.indexOf( row.id ) !== -1 }
					row={ row }
					onDelete={ onDelete }
					table={ rowParams.table }
				/>
			) }
			renderTableActions={ () => (
				<>
					{ token && (
						<div className="table-button-item">
							<a href={ getRssUrl( token ) } className="button-secondary">
								RSS
							</a>
						</div>
					) }
					{ has_capability( CAP_LOG_DELETE ) && canDeleteAll( table ) && (
						<DeleteAll onDelete={ onDeleteAll } table={ table } />
					) }
				</>
			) }
		/>
	);
}

function mapStateToProps( state ) {
	const { log, settings } = state;

	return {
		log,
		settings,
		token: getOption( state, 'token' ),
	};
}

function mapDispatchToProps( dispatch ) {
	return {
		onLoad: ( params ) => {
			dispatch( loadLogs( params ) );
		},
		onDeleteAll: ( filterBy ) => {
			dispatch( deleteAll( filterBy ) );
		},
		onChangePage: ( page ) => {
			dispatch( setPage( page ) );
		},
		onTableAction: ( action ) => {
			dispatch( performTableAction( action ) );
		},
		onGroup: ( groupBy ) => {
			dispatch( setGroupBy( groupBy ) );
		},
		onSetAll: ( onoff ) => {
			dispatch( setAllSelected( onoff ) );
		},
		onSetOrderBy: ( column, direction ) => {
			dispatch( setOrderBy( column, direction ) );
		},
		onFilter: ( filterBy ) => {
			dispatch( setFilter( filterBy ) );
		},
		onSetDisplay: ( displayType, displaySelected ) => {
			dispatch( setDisplay( displayType, displaySelected, 'log' ) );
		},
		onSelect: ( items ) => {
			dispatch( setSelected( items ) );
		},
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps
)( Logs );
