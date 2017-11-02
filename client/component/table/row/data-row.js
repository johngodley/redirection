/**
 * External dependencies
 */

import React from 'react';

/**
 * Internal dependencies
 */
import { STATUS_IN_PROGRESS } from 'state/settings/type';

const isSelected = ( selected, id ) => selected.indexOf( id ) !== -1;

const getRowStatus = ( status, selected, item ) => ( {
	isLoading: status === STATUS_IN_PROGRESS,
	isSelected: isSelected( selected, item.id ),
} );

const TableContent = props => {
	const { rows, status, selected, row } = props;

	return (
		<tbody>
			{ rows.map( ( item, pos ) => row( item, pos, getRowStatus( status, selected, item ) ) ) }
		</tbody>
	);
};

export default TableContent;
