/**
 * External dependencies
 */

import React from 'react';
import { connect } from 'react-redux';

import { STATUS_IN_PROGRESS } from 'state/settings/type';
import { setSelected } from 'state/log/action';
import { LOGS_TYPE_REDIRECT, LOGS_TYPE_404 } from 'state/log/type';
import LogRow from './row/log';
import LogRow404 from './row/404';

const isSelected = ( selected, id ) => selected.indexOf( id );

const getRow = ( rowType, item, key ) => {
	const data = {
		item,
		key,
		isLoading: item.isLoading,
		selected: item.isSelected,
		onSetSelected: item.onSetSelected,
	};

	if ( rowType === LOGS_TYPE_REDIRECT ) {
		return <LogRow { ... data } />;
	} else if ( rowType === LOGS_TYPE_404 ) {
		return <LogRow404 { ... data } />;
	}
};

const TableContent = props => {
	const { rows, status, selected, onSetSelected, rowType } = props;
	const extra = {
		isLoading: status === STATUS_IN_PROGRESS,
		onSetSelected,
	};

	return (
		<tbody>
			{ rows.map( ( item, pos ) => getRow( rowType, Object.assign( { isSelected: isSelected( selected, item.id ) !== -1 }, item, extra ), pos ) ) }
		</tbody>
	);
};

function mapDispatchToProps( dispatch ) {
	return {
		onSetSelected: items => {
			dispatch( setSelected( items ) );
		}
	};
}

export default connect(
	null,
	mapDispatchToProps,
)( TableContent );
