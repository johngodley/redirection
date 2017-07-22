/**
 * External dependencies
 */

import React from 'react';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import { STATUS_IN_PROGRESS, STATUS_FAILED, STATUS_COMPLETE } from 'state/settings/type';
import TableHeader from './header';
import DataRow from './row/data-row';
import LoadingRow from './row/loading-row';
import EmptyRow from './row/empty-row';
import FailedRow from './row/failed-row';

const isDisabledHeader = ( status, rows ) => status !== STATUS_COMPLETE || rows.length === 0;
const isSelectedHeader = ( selected, rows ) => selected.length === rows.length && rows.length !== 0;

const Table = props => {
	const { headers, row, rows, total, table, status, onSetAllSelected, onSetOrderBy } = props;
	const isDisabled = isDisabledHeader( status, rows );
	const isSelected = isSelectedHeader( table.selected, rows );

	let content = null;

	if ( status === STATUS_IN_PROGRESS && rows.length === 0 ) {
		content = <LoadingRow headers={ headers } rows={ rows } />;
	} else if ( rows.length === 0 && status === STATUS_COMPLETE ) {
		content = <EmptyRow headers={ headers } />;
	} else if ( status === STATUS_FAILED ) {
		content = <FailedRow headers={ headers } />;
	} else if ( rows.length > 0 ) {
		content = <DataRow rows={ rows } status={ status } selected={ table.selected } row={ row } />;
	}

	return (
		<table className="wp-list-table widefat fixed striped items">
			<thead>
				<TableHeader table={ table } isDisabled={ isDisabled } isSelected={ isSelected } headers={ headers } rows={ rows } total={ total } onSetOrderBy={ onSetOrderBy } onSetAllSelected={ onSetAllSelected } />
			</thead>

			{ content }

			<tfoot>
				<TableHeader table={ table } isDisabled={ isDisabled } isSelected={ isSelected } headers={ headers } rows={ rows } total={ total } onSetOrderBy={ onSetOrderBy } onSetAllSelected={ onSetAllSelected } />
			</tfoot>
		</table>
	);
};

Table.propTypes = {
	headers: PropTypes.array.isRequired,
	row: PropTypes.func.isRequired,
	rows: PropTypes.array.isRequired,
	table: PropTypes.object.isRequired,
	onSetAllSelected: PropTypes.func,
	onSetOrderBy: PropTypes.func,
	status: PropTypes.string.isRequired,
	total: PropTypes.number.isRequired,
};

export default Table;
