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
import DeleteAll from 'component/logs/delete-all';
import ExportCSV from 'component/logs/export-csv';
import Row404 from './row';
import TableButtons from 'component/table/table-buttons';
import { LOGS_TYPE_404 } from 'state/log/type';
import { getGroup } from 'state/group/action';
import { loadLogs, deleteAll, setSearch, setPage, performTableAction, setAllSelected, setOrderBy } from 'state/log/action';
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
	},
	{
		name: 'referrer',
		title: __( 'Referrer' ),
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

		props.onLoad( LOGS_TYPE_404 );

		this.props.onLoadGroups();
		this.handleRender = this.renderRow.bind( this );
	}

	componentWillReceiveProps( nextProps ) {
		if ( nextProps.clicked !== this.props.clicked ) {
			nextProps.onLoad( LOGS_TYPE_404 );
		}
	}

	renderRow( row, key, status ) {
		const { saving } = this.props.log;
		const loadingStatus = status.isLoading ? STATUS_IN_PROGRESS : STATUS_COMPLETE;
		const rowStatus = saving.indexOf( row.id ) !== -1 ? STATUS_SAVING : loadingStatus;

		return <Row404 item={ row } key={ key } selected={ status.isSelected } status={ rowStatus } />;
	}

	render() {
		const { status, total, table, rows } = this.props.log;

		return (
			<div>
				<SearchBox status={ status } table={ table } onSearch={ this.props.onSearch } />
				<TableNav total={ total } selected={ table.selected } table={ table } status={ status } onChangePage={ this.props.onChangePage } onAction={ this.props.onTableAction } bulk={ bulk } />
				<Table headers={ headers } rows={ rows } total={ total } row={ this.handleRender } table={ table } status={ status } onSetAllSelected={ this.props.onSetAllSelected } onSetOrderBy={ this.props.onSetOrderBy } />

				<TableNav total={ total } selected={ table.selected } table={ table } status={ status } onChangePage={ this.props.onChangePage } onAction={ this.props.onTableAction }>
					<TableButtons enabled={ rows.length > 0 }>
						<DeleteAll onDelete={ this.props.onDeleteAll } />
						<ExportCSV logType={ LOGS_TYPE_404 } />
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
		onLoad: logType => {
			dispatch( loadLogs( logType ) );
		},
		onLoadGroups: () => {
			dispatch( getGroup() );
		},
		onDeleteAll: () => {
			dispatch( deleteAll() );
		},
		onSearch: search => {
			dispatch( setSearch( search ) );
		},
		onChangePage: page => {
			dispatch( setPage( page ) );
		},
		onTableAction: action => {
			dispatch( performTableAction( action, null, { logType: '404' } ) );
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
)( Logs404 );
