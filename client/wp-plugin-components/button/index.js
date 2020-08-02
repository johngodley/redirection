/**
 * External dependencies
 */

import React from 'react';
import classnames from 'classnames';

/**
 * Button component
 *
 * @param {object} props - Component props
 * @param {boolean} [props.isPrimary=false] - Primary button
 * @param {boolean} [props.isSecondary=true] - Secondary button
 * @param {boolean} [props.isSubmit=false] - Submit button
 * @param {boolean} [props.isDestructive=false]
 * @param {string} [props.className] - Class name
 * @param {boolean} [props.disabled=false]
 */
function Button( props ) {
	const {
		isPrimary = false,
		isSecondary = false,
		isSubmit = false,
		className,
		children,
		disabled = false,
		isDestructive = false,
		...extra
	} = props;
	const classes = classnames( 'button', className, {
		'button-primary': isPrimary,
		'button-secondary': isSecondary,
		'button-delete': isDestructive,
	} );

	return (
		<button className={ classes } disabled={ disabled } type={ isSubmit ? 'submit' : 'button' } { ...extra }>
			{ children }
		</button>
	);
}

export default Button;
