/**
 * External dependencies
 */

import React, { useEffect } from 'react';
import classnames from 'classnames';

/**
 * Internal dependencies
 */

import ModalContent from './content';

/**
 * @type {String}
 */
const CLASS = 'wpl-modal_shown';

/**
 * onClose callback.
 *
 * @callback requestCallback
 */

/**
 * The modal wrapper.
 *
 * @param {Object} props - Component props
 * @param {Boolean} props.padding - Include padding, defaults to `true`
 * @param {requestCallback} props.onClose - Function to call to close the modal
 */
function ModalWrapper( props ) {
	const { padding = true } = props;

	useEffect( () => {
		document.body.classList.add( CLASS );

		return () => {
			document.body.classList.remove( CLASS );
		};
	} );

	const classes = classnames( {
		'wpl-modal_wrapper': true,
		'wpl-modal_wrapper-padding': padding,
	} );

	return (
		<div className={ classes }>
			<div className="wpl-modal_backdrop"></div>

			<div className="wpl-modal_main">
				<ModalContent { ...props } />
			</div>
		</div>
	);
}

export default ModalWrapper;
