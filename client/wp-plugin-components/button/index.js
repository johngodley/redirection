/**
 * External dependencies
 */

import classnames from 'classnames';

/**
 * @callback Click
 * @param {object} ev
 */

/**
 * Button component
 *
 * @param {object} props - Component props
 * @param {boolean} [props.isPrimary=false] - Primary button
 * @param {boolean} [props.isSecondary=true] - Secondary button
 * @param {boolean} [props.isSubmit=false] - Submit button
 * @param {boolean} [props.isDestructive=false]
 * @param {string} [props.className] - Class name
 * @param {string} [props.name] - Button `name`
 * @param {boolean} [props.disabled=false]
 * @param {Click} [props.onClick] - Click callback
 * @param {string} props.children - Button contents
 */
function Button( props ) {
	const {
		isPrimary = false,
		isSecondary = true,
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
