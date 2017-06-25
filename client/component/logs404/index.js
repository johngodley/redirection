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
import { loadLogs, deleteAll } from 'state/log/action';
import { LOGS_TYPE_404 } from 'state/log/type';
import DeleteAll from 'component/logs/delete-all';
import ExportCSV from 'component/logs/export-csv';

class Logs404 extends React.Component {
	constructor( props ) {
		super( props );

		props.onLoad( LOGS_TYPE_404 );
	}

	render() {
		const headers = [
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

		return (
			<div>
				<Table headers={ headers } row={ LOGS_TYPE_404 } />

				<br />
				<DeleteAll onDelete={ this.props.onDeleteAll } />
				<br />
				<ExportCSV logType={ LOGS_TYPE_404 } />
			</div>
		);
	}
}

function mapDispatchToProps( dispatch ) {
	return {
		onLoad: logType => {
			dispatch( loadLogs( logType ) );
		},
		onDeleteAll: () => {
			dispatch( deleteAll() );
		},
		onExportCSV: () => {
			dispatch( exportCSV() );
		},
	};
}

export default connect(
	null,
	mapDispatchToProps
)( Logs404 );
