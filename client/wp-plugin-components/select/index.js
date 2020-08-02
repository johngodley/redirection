/**
 * External dependencies
 */

import React from 'react';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */

import Option from './option';

/**
 * onChange callback.
 *
 * @callback changeCallback
 * @param {Object} ev Event handler object
 */

 /**
 * @typedef SelectOption
 * @type {object}
 * @property {string} label - a descriptive label.
 * @property {string|SelectOption[]} value - value for the option, or an array of SelectOption
 */

/**
 * Show a browser `select` box.
 *
 * @param {Object} props - Component props
 * @param {SelectOption[]} props.items - Array of select options
 * @param {String} props.name - Name of the select box
 * @param {String} props.value - Currently selected value
 * @param {changeCallback} props.onChange - onChange callback
 * @param {boolean} props.disabled - Determine if select should be disabled
 */
const Select = props => {
	const { items, value, name, onChange, disabled = false } = props;

	return (
		<select name={ name } value={ value } onChange={ onChange } disabled={ disabled } >
			{ items.map( ( item, pos ) => (
				<Option
					value={ item.value }
					label={ item.label }
					key={ pos }
				/>
			) ) }
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
	disabled: PropTypes.bool,
};

export default Select;
