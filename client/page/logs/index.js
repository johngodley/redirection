/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import { connect } from 'react-redux';

/**
 * Internal dependencies
 */

import Table from 'component/table';
import TableNav from 'component/table/navigation';
import SearchBox from 'component/search-box';
import DeleteAll from 'page/logs/delete-all';
import TableDisplay from 'component/table/table-display';
import LogRow from './row';
import { STATUS_COMPLETE, STATUS_IN_PROGRESS, STATUS_SAVING } from 'state/settings/type';
import { getOption } from 'state/settings/selector';
import {
	loadLogs,
	deleteAll,
	setPage,
	performTableAction,
	setAllSelected,
	setOrderBy,
	setFilter,
	setDisplay,
} from 'state/log/action';
import TableButtons from 'component/table/table-buttons';
import { getRssUrl } from 'lib/wordpress-url';
import { getHeaders, getBulk, getDisplayOptions, getDisplayGroups, getSearchOptions } from './constants';
import { isEnabled } from 'component/table/utils';

class Logs extends React.Component {
	componentDidMount() {
		this.props.onLoad( this.props.log.table );
	}

	onRSS = () => {
		document.location = getRssUrl( this.props.token );
	}

	renderRow = ( row, key, status, currentDisplayType, currentDisplaySelected ) => {
		const { saving, table } = this.props.log;
		const loadingStatus = status.isLoading ? STATUS_IN_PROGRESS : STATUS_COMPLETE;
		const rowStatus = saving.indexOf( row.id ) !== -1 ? STATUS_SAVING : loadingStatus;

		return (
			<LogRow
				item={ row }
				key={ key }
				selected={ status.isSelected }
				status={ rowStatus }
				currentDisplayType={ currentDisplayType }
				currentDisplaySelected={ currentDisplaySelected }
				filters={ table.filterBy }
				setFilter={ this.setFilter }
			/>
		);
	}

	setFilter = ( filterName, filterValue ) => {
		const { filterBy } = this.props.log.table;

		this.props.onFilter( { ...filterBy, [ filterName ]: filterValue ? filterValue : undefined } );
	}

	onSearch = ( search, type ) => {
		const filterBy = { ...this.props.log.table.filterBy };

		getSearchOptions().map( item => delete filterBy[ item.name ] );

		if ( search ) {
			filterBy[ type ] = search;
		}

		this.props.onFilter( filterBy );
	}

	getHeaders( selected ) {
		return getHeaders().filter( header => isEnabled( selected, header.name ) || header.name === 'cb' || header.name === 'url' );
	}

	validateDisplay( selected ) {
		// Ensure we have at least source
		if ( selected.indexOf( 'url' ) === -1 ) {
			return selected.concat( [ 'url' ] );
		}

		return selected;
	}

	render() {
		const { status, total, table, rows } = this.props.log;

		return (
			<React.Fragment>
				<div className="redirect-table-display">
					<TableDisplay
						disable={ status === STATUS_IN_PROGRESS }
						options={ getDisplayOptions() }
						groups={ getDisplayGroups() }
						store="log"
						currentDisplayType={ table.displayType }
						currentDisplaySelected={ table.displaySelected }
						setDisplay={ this.props.onSetDisplay }
						validation={ this.validateDisplay }
					/>
					<SearchBox
						status={ status }
						table={ table }
						onSearch={ this.onSearch }
						selected={ table.filterBy }
						searchTypes={ getSearchOptions() }
					/>
				</div>

				<TableNav total={ total } selected={ table.selected } table={ table } status={ status } onChangePage={ this.props.onChangePage } onAction={ this.props.onTableAction } bulk={ getBulk() } />
				<Table
					headers={ this.getHeaders( table.displaySelected ) }
					rows={ rows }
					total={ total }
					row={ this.renderRow }
					table={ table }
					status={ status }
					onSetAllSelected={ this.props.onSetAllSelected }
					onSetOrderBy={ this.props.onSetOrderBy }
					currentDisplayType={ table.displayType }
					currentDisplaySelected={ table.displaySelected }
				/>

				<TableNav total={ total } selected={ table.selected } table={ table } status={ status } onChangePage={ this.props.onChangePage } onAction={ this.props.onTableAction }>
					<TableButtons enabled={ rows.length > 0 }>
						<button className="button-secondary" onClick={ this.onRSS }>RSS</button>
						<DeleteAll onDelete={ this.props.onDeleteAll } table={ table } />
					</TableButtons>
				</TableNav>
			</React.Fragment>
		);
	}
}

function mapStateToProps( state ) {
	const { log } = state;

	return {
		log,
		token: getOption( state, 'token' ),
	};
}

function mapDispatchToProps( dispatch ) {
	return {
		onLoad: params => {
			dispatch( loadLogs( params ) );
		},
		onDeleteAll: ( filterBy ) => {
			dispatch( deleteAll( filterBy ) );
		},
		onChangePage: page => {
			dispatch( setPage( page ) );
		},
		onTableAction: action => {
			dispatch( performTableAction( action ) );
		},
		onSetAllSelected: onoff => {
			dispatch( setAllSelected( onoff ) );
		},
		onSetOrderBy: ( column, direction ) => {
			dispatch( setOrderBy( column, direction ) );
		},
		onFilter: ( filterBy ) => {
			dispatch( setFilter( filterBy ) );
		},
		onSetDisplay: ( displayType, displaySelected ) => {
			dispatch( setDisplay( displayType, displaySelected ) );
		},
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps
)( Logs );
