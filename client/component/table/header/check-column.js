/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';

/** @typedef {import('../index.js').SetAllSelected} SetAllSelected */

/**
 * A checkable column
 *
 * @param {object} props - Component props
 * @param {SetAllSelected} props.onSetAllSelected - When clicking the 'set all'
 * @param {boolean} props.disabled - Is the row disabled?
 * @param {boolean} props.selected - Is the column selected?
 */
const CheckColumn = ( props ) => {
	const { onSetAllSelected, disabled, selected } = props;

	return (
		<td className="manage-column column-cb check-column-red">
			<label className="screen-reader-text">{ __( 'Select All' ) }</label>
			<input
				type="checkbox"
				disabled={ disabled }
				checked={ selected }
				onChange={ ( ev ) => onSetAllSelected( ev.target.checked ) }
			/>
		</td>
	);
};

export default CheckColumn;
