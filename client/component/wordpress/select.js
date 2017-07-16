/**
 * External dependencies
 */

import React from 'react';
import PropTypes from 'prop-types';

const SelectItem = props => {
	const { value, text } = props;

	if ( typeof value === 'object' ) {
		return (
			<optgroup label={ text }>
				{ value.map( ( item, pos ) => <SelectItem text={ item.text } value={ item.value } key={ pos } /> ) }
			</optgroup>
		);
	}

	return (
		<option value={ value }>{ text }</option>
	);
};

const Select = props => {
	const { items, value, name, onChange, isEnabled = true } = props;

	return (
		<select name={ name } value={ value } onChange={ onChange } disabled={ ! isEnabled } >
			{ items.map( ( item, pos ) => <SelectItem value={ item.value } text={ item.text } key={ pos } /> ) }
		</select>
	);
};

Select.propTypes = {
	items: PropTypes.array.isRequired,
	value: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.number,
	] ).isRequired,
	name: PropTypes.string.isRequired,
	onChange: PropTypes.func.isRequired,
	isEnabled: PropTypes.bool,
};

export default Select;
