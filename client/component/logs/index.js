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
import { loadLogs, deleteAll } from 'state/log/action';
import { LOGS_TYPE_REDIRECT } from 'state/log/type';
import DeleteAll from 'component/logs/delete-all';
import ExportCSV from 'component/logs/export-csv';
import LogRow from './row';

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

class Logs extends React.Component {
	constructor( props ) {
		super( props );

		props.onLoad( LOGS_TYPE_REDIRECT );
	}

	renderRow( row, key, status ) {
		return <LogRow item={ row } key={ key } selected={ status.isSelected } isLoading={ status.isLoading } />;
	}

	render() {
		const { status, total, table, rows } = this.props.log;

		return (
			<div>
				<SearchBox status={ status } table={ table } />
				<TableNav total={ total } selected={ table.selected } table={ table } />
				<Table headers={ headers } rows={ rows } total={ total } row={ this.renderRow } table={ table } status={ status } />
				<TableNav total={ total } selected={ table.selected } table={ table } />

				<br />
				<DeleteAll onDelete={ this.props.onDeleteAll } />
				<br />

				<ExportCSV logType={ LOGS_TYPE_REDIRECT } />
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
		onDeleteAll: () => {
			dispatch( deleteAll() );
		},
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps
)( Logs );
