/**
 * External dependencies
 */

import React from 'react';
import { __ } from '@wordpress/i18n';

/** @typedef {import('../index.js').SetAllSelected} SetAllSelected */

/**
 * A checkable column
 *
 * @param {object} props - Component props
 * @param {SetAllSelected} props.onSelect - When clicking the 'set all'
 * @param {boolean} props.disabled - Is the row disabled?
 * @param {boolean} props.selected - Is the column selected?
 */
const CheckColumn = ( props ) => {
	const { onSelect, disabled, selected } = props;

	return (
		<td className="manage-column column-cb check-column-red">
			<label className="screen-reader-text">{ __( 'Select All', 'redirection' ) }</label>
			<input
				type="checkbox"
				disabled={ disabled }
				checked={ selected }
				onChange={ ( ev ) => onSelect( ev.target.checked ) }
			/>
		</td>
	);
};

export default CheckColumn;
