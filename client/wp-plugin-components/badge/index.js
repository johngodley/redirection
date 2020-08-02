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
 */
const Badge = ( props ) => {
	const { children, className, onClick = null, title = '', onCancel, disabled = false } = props;
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
		<div className={ classnames( 'wpl-badge', className, onClick && 'wpl-badge__click' ) } { ...extra }>
			<div>
				{ children }
				{ onCancel && <span onClick={ cancel }>⨯</span> }
			</div>
		</div>
	);
};

export default Badge;
