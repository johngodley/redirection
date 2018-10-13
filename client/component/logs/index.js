/**
 * External dependencies
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
import { LOGS_TYPE_REDIRECT } from 'state/log/type';
import DeleteAll from 'component/logs/delete-all';
import ExportCSV from 'component/logs/export-csv';
import LogRow from './row';
import { STATUS_COMPLETE, STATUS_IN_PROGRESS, STATUS_SAVING } from 'state/settings/type';
import { loadLogs, deleteAll, setSearch, setPage, performTableAction, setAllSelected, setOrderBy } from 'state/log/action';
import TableButtons from 'component/table/table-buttons';
import { getRssUrl } from 'lib/wordpress-url';

const getHeaders = () => [
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

const getBulk = () => [
	{
		id: 'delete',
		name: __( 'Delete' ),
	},
];

class Logs extends React.Component {
	constructor( props ) {
		super( props );

		props.onLoad( props.log.table );

		this.handleRender = this.renderRow.bind( this );
		this.handleRSS = this.onRSS.bind( this );
	}

	onRSS() {
		document.location = getRssUrl();
	}

	renderRow( row, key, status ) {
		const { saving } = this.props.log;
		const loadingStatus = status.isLoading ? STATUS_IN_PROGRESS : STATUS_COMPLETE;
		const rowStatus = saving.indexOf( row.id ) !== -1 ? STATUS_SAVING : loadingStatus;

		return <LogRow item={ row } key={ key } selected={ status.isSelected } status={ rowStatus } />;
	}

	render() {
		const { status, total, table, rows } = this.props.log;

		return (
			<div>
				<SearchBox status={ status } table={ table } onSearch={ this.props.onSearch } />
				<TableNav total={ total } selected={ table.selected } table={ table } status={ status } onChangePage={ this.props.onChangePage } onAction={ this.props.onTableAction } bulk={ getBulk() } />
				<Table headers={ getHeaders() } rows={ rows } total={ total } row={ this.handleRender } table={ table } status={ status } onSetAllSelected={ this.props.onSetAllSelected } onSetOrderBy={ this.props.onSetOrderBy } />
				<TableNav total={ total } selected={ table.selected } table={ table } status={ status } onChangePage={ this.props.onChangePage } onAction={ this.props.onTableAction }>
					<TableButtons enabled={ rows.length > 0 }>
						<ExportCSV logType={ LOGS_TYPE_REDIRECT } />
						<button className="button-secondary" onClick={ this.handleRSS }>RSS</button>
						<DeleteAll onDelete={ this.props.onDeleteAll } table={ table } />
					</TableButtons>
				</TableNav>
			</div>
		);
	}
}

function mapStateToProps( state ) {
	const { log } = state;

	return {
		log,
	};
}

function mapDispatchToProps( dispatch ) {
	return {
		onLoad: params => {
			dispatch( loadLogs( params ) );
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
			dispatch( performTableAction( action ) );
		},
		onSetAllSelected: onoff => {
			dispatch( setAllSelected( onoff ) );
		},
		onSetOrderBy: ( column, direction ) => {
			dispatch( setOrderBy( column, direction ) );
		},
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps
)( Logs );
