/**
 * External dependencies
 */

import React from 'react';

/** @callback OnSubmit */
/** @typedef {import('react').SyntheticEvent} SyntheticEvent */

/**
 * Form component
 *
 * @param {object} props - Component props
 * @param {string} [props.className] - Optional class name
 * @param {object} props.children - Form children
 * @param {OnSubmit} props.onSubmit - Form submission
 */
const Form = ( { className, children, onSubmit } ) => {
	/** @param {SyntheticEvent} ev - Event */
	function save( ev ) {
		ev.preventDefault();
		onSubmit();
	}

	return (
		<form className={ className } onSubmit={ save }>
			{ children }
		</form>
	);
};

export default Form;
