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
import TableButtons from 'component/table/table-buttons';
import { getOption } from 'state/settings/selector';
import {
	loadLogs,
	setPage,
	performTableAction,
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
	const { onBulk, token } = props;
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
			getRow={ ( row, rowParams ) => getColumns( row, rowParams, props, saving.indexOf( row.id ) !== -1 ) }
			getRowActions={ ( row, rowParams ) => (
				<LogRowActions
					disabled={ saving.indexOf( row.id ) !== -1 }
					row={ row }
					onDelete={ ( id ) => onBulk( 'delete', [ id ] ) }
					table={ rowParams.table }
				/>
			) }
			renderTableActions={ () => (
				<>
					<TableButtons enabled={ rows.length > 0 }>
						{ token && (
							<div className="table-button-item">
								<a href={ getRssUrl( token ) } className="button-secondary">
									{ __( 'RSS' ) }
								</a>
							</div>
						) }
					</TableButtons>
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
		onChangePage: ( page ) => {
			dispatch( setPage( page ) );
		},
		onBulk: ( action, items ) => {
			dispatch( performTableAction( action, items ) );
		},
		onGroup: ( groupBy ) => {
			dispatch( setGroupBy( groupBy ) );
		},
		onSetOrder: ( column, direction ) => {
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
