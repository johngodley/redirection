/**
 * External dependencies
 */

import React from 'react';
import { connect } from 'react-redux';

/**
 * Internal dependencies
 */
import { STATUS_IN_PROGRESS, STATUS_FAILED, STATUS_COMPLETE } from 'state/settings/type';
import TableNav from './navigation';
import TableContent from './table-content';
import SearchBox from './search';
import LoadingRow from './loading-row';
import TableHeader from './header/';
import EmptyRow from './empty-row';
import FailedRow from './failed-row';

const Table = props => {
	const { logs, status, selected, headers, row, error } = props;

	let content = null;

	if ( logs.length > 0 ) {
		content = <TableContent rows={ logs } status={ status } selected={ selected } rowType={ row } />;
	} else if ( logs.length === 0 && status === STATUS_IN_PROGRESS ) {
		content = <LoadingRow headers={ headers } />;
	} else if ( logs.length === 0 && status === STATUS_COMPLETE ) {
		content = <EmptyRow headers={ headers } />;
	} else if ( status === STATUS_FAILED ) {
		content = <FailedRow headers={ headers } error={ error } />;
	}

	return (
		<div>
			<SearchBox />
			<TableNav />

			<table className="wp-list-table widefat fixed striped items">
				<thead>
					<TableHeader isDisabled={ status !== STATUS_COMPLETE || logs.length === 0 } isSelected={ selected.length === logs.length && logs.length !== 0 } headers={ headers } />
				</thead>

				{ content }

				<tfoot>
					<TableHeader isDisabled={ status !== STATUS_COMPLETE || logs.length === 0 } isSelected={ selected.length === logs.length && logs.length !== 0 } headers={ headers } />
				</tfoot>
			</table>

			<TableNav />
		</div>
	);
};

function mapStateToProps( state ) {
	const { logs, status, selected, error } = state.log;

	return {
		logs,
		status,
		selected,
		error,
	};
}

export default connect(
	mapStateToProps,
	null,
)( Table );
