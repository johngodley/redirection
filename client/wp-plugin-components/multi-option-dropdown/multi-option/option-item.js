/**
 * External dependencies
 */

import React from 'react';

/** @typedef {import('./index.js').selectCallback} selectCallback */

/**
 * Display a MultiOptionDropdown option
 *
 * @param {object} props - Component props
 * @param {MultiOptionValue} props.option - Option to display
 * @param {string[]} props.selected - Array of selected option values
 * @param {selectCallback} props.onSelect - Callback when an option is checked
 */
const MultiOptionItem = ( { option, onSelect, selected } ) => {
	const { label, value } = option;

	return (
		<p>
			<label>
				<input
					type="checkbox"
					name={ value }
					onChange={ onSelect }
					checked={ selected.indexOf( value ) !== -1 }
				/>

				{ label }
			</label>
		</p>
	);
};

export default MultiOptionItem;
