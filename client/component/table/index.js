/**
 * External dependencies
 */

import React from 'react';

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

const isDisabled = ( status, rows ) => status !== STATUS_COMPLETE || rows.length === 0;
const isSelected = ( selected, rows ) => selected.length === rows.length && rows.length !== 0;
const hasNavigation = store => typeof store.filter !== 'undefined';

const Table = props => {
	const { store, headers, row } = props;
	const { rows, status, selected, error } = store;

	let content = null;

	if ( rows.length > 0 ) {
		content = <TableContent rows={ rows } status={ status } selected={ selected } row={ row } />;
	} else if ( rows.length === 0 && status === STATUS_IN_PROGRESS ) {
		content = <LoadingRow headers={ headers } />;
	} else if ( rows.length === 0 && status === STATUS_COMPLETE ) {
		content = <EmptyRow headers={ headers } />;
	} else if ( status === STATUS_FAILED ) {
		content = <FailedRow headers={ headers } error={ error } />;
	}

	return (
		<div>
			{ hasNavigation( store ) && <SearchBox redux={ store } /> }
			{ hasNavigation( store ) && <TableNav /> }

			<table className="wp-list-table widefat fixed striped items">
				<thead>
					<TableHeader isDisabled={ isDisabled( status, rows ) } isSelected={ isSelected( selected, rows ) } headers={ headers } />
				</thead>

				{ content }

				<tfoot>
					<TableHeader isDisabled={ isDisabled( status, rows ) } isSelected={ isSelected( selected, rows ) } headers={ headers } />
				</tfoot>
			</table>

			{ hasNavigation( store ) && <TableNav /> }
		</div>
	);
};

export default Table;
