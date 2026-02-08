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
const MultiOptionItem = ( { option, onSelect, selected, label } ) => {
	const { value, disabled = false } = option;

	return (
		<p>
			<label aria-label={ label || option.label }>
				<input
					type="checkbox"
					name={ value }
					onChange={ onSelect }
					checked={
						selected.indexOf( String( value ) ) !== -1 ||
						selected.indexOf( parseInt( value, 10 ) ) !== -1
					}
					disabled={ disabled }
					tabIndex={ 0 }
				/>

				{ option.label }
			</label>
		</p>
	);
};

export default MultiOptionItem;
