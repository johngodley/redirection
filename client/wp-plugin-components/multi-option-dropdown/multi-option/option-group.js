/**
 * External dependencies
 */

import React from 'react';

/**
 * Internal dependencies
 */
import MultiOptionItem from './option-item';

/** @typedef {import('./index.js').selectCallback} selectCallback */

/**
 * Display a MultiOptionDropdown option
 *
 * @param {object} props - Component props
 * @param {MultiOptionGroupValue} props.option - Option to display
 * @param {string[]} props.selected - Array of selected option values
 * @param {selectCallback} props.onSelect - Callback when an option is checked
 */
function MultiOptionGroup( props ) {
	const { option, selected, onSelect } = props;
	const { options, label } = option;

	return (
		<div className="wpl-multioption__group">
			<h5>{ label }</h5>

			{ options.map(
				/**
				 * @param {MultiOptionValue} groupOption
				 * @param {number} key
				 */ ( groupOption, key ) => (
					<MultiOptionItem
						option={ groupOption }
						onSelect={ onSelect }
						selected={ selected }
						key={ key }
					/>
				)
			) }
		</div>
	);
}

export default MultiOptionGroup;
