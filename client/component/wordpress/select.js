/**
 * External dependencies
 */

import React from 'react';

const SelectItem = props => {
	const { value, text } = props;

	if ( typeof value === 'object' ) {
		return (
			<optgroup label={ text }>
				{ value.map( item => <SelectItem text={ item.text } value={ item.value } key={ item.value } /> ) }
			</optgroup>
		);
	}

	return (
		<option value={ value }>{ text }</option>
	);
};

const Select = props => {
	const { items, value, name, onChange } = props;

	return (
		<select name={ name } value={ value } onChange={ onChange }>
			{ items.map( item => <SelectItem value={ item.value } text={ item.text } key={ item.value } /> ) }
		</select>
	);
};

export default Select;
