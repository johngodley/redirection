/**
 * External dependencies
 */

import React from 'react';

/**
 * External dependencies
 */

import MultiOptionItem from './option-item';
import MultiOptionGroup from './option-group';

/**
 * onApply callback.
 *
 * @callback applyCallback
 * @param {string[]} selected - Current selected items
 * @param {string} value - Value of option that was changed
 * @param {boolean} enabled - Was the option enabled or disabled
 */

/**
 * Select callback
 *
 * @callback selectCallback
 * @param {React.ChangeEvent<HTMLInputElement>} ev - Event
 */

/**
 * Display a MultiOptionDropdown option
 *
 * @param {object} props - Component props
 * @param {MultiOptionGroupValue|MultiOptionValue} props.option - Option to display
 * @param {string[]} props.selected - Array of selected option values
 * @param {boolean} props.multiple - Does this option accept multiple selections?
 * @param {applyCallback} props.onApply - Callback when an option is enabled or disabled
 */
function MultiOption( props ) {
	const { option, selected, onApply, multiple } = props;
	const { options } = option;

	/**
	 * Select callback
	 * @param {React.ChangeEvent<HTMLInputElement>} ev - Event
	 */
	const onSelect = ( ev ) => {
		const { checked, name, value } = ev.target;

		// Now add the new option
		if ( checked ) {
			onApply( multiple ? selected.concat( [ name ] ) : [ name ], name, parseInt( value, 10 ) !== 0 );
		} else {
			onApply( selected.filter( item => item !== name ), name, parseInt( value, 10 ) !== 0 );
		}
	};

	if ( options ) {
		return <MultiOptionGroup option={ option } selected={ selected } onSelect={ onSelect } />;
	}

	return <MultiOptionItem option={ option } selected={ selected } onSelect={ onSelect } />;
}

export default MultiOption;
