/**
 * External dependencies
 *
 * @format
 */

import React from 'react';
import { connect } from 'react-redux';
import { translate as __ } from 'lib/locale';

/**
 * Internal dependencies
 */

import Table from 'component/table';
import TableNav from 'component/table/navigation';
import SearchBox from 'component/table/search';
import DeleteAll from 'component/logs/delete-all';
import ExportCSV from 'component/logs/export-csv';
import Row404 from './row';
import TableButtons from 'component/table/table-buttons';
import { LOGS_TYPE_404 } from 'state/error/type';
import { getGroup } from 'state/group/action';
import {
	loadLogs,
	deleteAll,
	setSearch,
	setPage,
	performTableAction,
	setAllSelected,
	setOrderBy,
} from 'state/error/action';
import { STATUS_COMPLETE, STATUS_IN_PROGRESS, STATUS_SAVING } from 'state/settings/type';

const headers = [
	{
		name: 'cb',
		check: true,
	},
	{
		name: 'date',
		title: __( 'Date' ),
	},
	{
		name: 'url',
		title: __( 'Source URL' ),
		primary: true,
	},
	{
		name: 'referrer',
		title: __( 'Referrer / User Agent' ),
		sortable: false,
	},
	{
		name: 'ip',
		title: __( 'IP' ),
		sortable: false,
	},
];

const bulk = [
	{
		id: 'delete',
		name: __( 'Delete' ),
	},
];

class Logs404 extends React.Component {
	constructor( props ) {
		super( props );

		props.onLoad( props.error.table );

		this.props.onLoadGroups();
		this.handleRender = this.renderRow.bind( this );
	}

	componentWillReceiveProps( nextProps ) {
		if ( nextProps.clicked !== this.props.clicked ) {
			nextProps.onLoad();
		}
	}

	renderRow( row, key, status ) {
		const { saving } = this.props.error;
		const loadingStatus = status.isLoading ? STATUS_IN_PROGRESS : STATUS_COMPLETE;
		const rowStatus = saving.indexOf( row.id ) !== -1 ? STATUS_SAVING : loadingStatus;

		return <Row404 item={ row } key={ key } selected={ status.isSelected } status={ rowStatus } />;
	}

	render() {
		const { status, total, table, rows } = this.props.error;

		return (
			<div>
				<SearchBox status={ status } table={ table } onSearch={ this.props.onSearch } />
				<TableNav
					total={ total }
					selected={ table.selected }
					table={ table }
					status={ status }
					onChangePage={ this.props.onChangePage }
					onAction={ this.props.onTableAction }
					bulk={ bulk }
				/>
				<Table
					headers={ headers }
					rows={ rows }
					total={ total }
					row={ this.handleRender }
					table={ table }
					status={ status }
					onSetAllSelected={ this.props.onSetAllSelected }
					onSetOrderBy={ this.props.onSetOrderBy }
				/>

				<TableNav
					total={ total }
					selected={ table.selected }
					table={ table }
					status={ status }
					onChangePage={ this.props.onChangePage }
					onAction={ this.props.onTableAction }
				>
					<TableButtons enabled={ rows.length > 0 }>
						<ExportCSV logType={ LOGS_TYPE_404 } />
						<DeleteAll onDelete={ this.props.onDeleteAll } table={ table } />
					</TableButtons>
				</TableNav>
			</div>
		);
	}
}

function mapStateToProps( state ) {
	const { error } = state;

	return {
		error,
	};
}

function mapDispatchToProps( dispatch ) {
	return {
		onLoad: params => {
			dispatch( loadLogs( params ) );
		},
		onLoadGroups: () => {
			dispatch( getGroup() );
		},
		onDeleteAll: ( filterBy, filter ) => {
			dispatch( deleteAll( filterBy, filter ) );
		},
		onSearch: ( search, filterBy ) => {
			dispatch( setSearch( search, filterBy ) );
		},
		onChangePage: page => {
			dispatch( setPage( page ) );
		},
		onTableAction: action => {
			dispatch( performTableAction( action, null ) );
		},
		onSetAllSelected: onoff => {
			dispatch( setAllSelected( onoff ) );
		},
		onSetOrderBy: ( column, direction ) => {
			dispatch( setOrderBy( column, direction ) );
		},
	};
}

export default connect( mapStateToProps, mapDispatchToProps )( Logs404 );
