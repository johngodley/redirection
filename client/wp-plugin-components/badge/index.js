/**
 * External dependencies
 */

import React from 'react';
import classnames from 'classnames';

/**
 * Internal dependencies
 */
import './style.scss';

/**
 * onClick callback.
 *
 * @callback clickCallback
 * @param {Object} ev Event handler object
 */

/**
 * onCancel callback.
 *
 * @callback cancelCallback
 * @param {Event} ev Event handler object
 */

/**
 * A small badge, used to indicate a status. Can show a close button to remove the badge.
 *
 * @param {Object} props - Component props
 * @param {Object} props.children - Child components
 * @param {String} [props.className] - Class name for the wrapper
 * @param {clickCallback} [props.onClick] - Callback when badge is clicked
 * @param {cancelCallback} [props.onCancel] - Callback when close button is clicked
 * @param {string} [props.title] - HTML title
 * @param {boolean} [props.disabled=false] - Badge is disabled
 * @param {boolean} [props.small=false]
 */
const Badge = ( props ) => {
	const { children, className, onClick = null, title = '', onCancel, disabled = false, small = false } = props;
	const extra = {
		title,
		onClick,
	};

	/**
	 * @param {MouseEvent} ev Event
	 */
	const cancel = ( ev ) => {
		ev.preventDefault();
		! disabled && onCancel && onCancel( ev );
	};

	return (
		<div
			className={ classnames( 'wpl-badge', className, {
				'wpl-badge__click': onClick,
				'wpl-badge__small': small,
			} ) }
			{ ...extra }
		>
			<div className="wpl-badge__content">{ children }</div>
			{ onCancel && <div className="wpl-badge__close" onClick={ cancel }>⨯</div> }
		</div>
	);
};

export default Badge;
