/**
 * External dependencies
 */

import React from 'react';
import ClickOutside from '../click-outside';
import classnames from 'classnames';

/**
 * onClose callback.
 *
 * @callback requestCallback
 * @param {Object} ev Event handler object
 */

/**
 * The modal content.
 *
 * @param {{onClose: requestCallback}} props - Provide the URL and child components
 */
function ModalContent( { onClose, children, className } ) {
	function onOutside( ev ) {
		if ( ev.target.classList.contains( 'wpl-modal_main' ) ) {
			onClose();
		}
	}

	return (
		<ClickOutside className="wpl-click-outside" onOutside={ onOutside }>
			<div className={ classnames( 'wpl-modal_content', className ) }>
				<div className="wpl-modal_close">
					<button type="button" onClick={ onClose }>&#x2716;</button>
				</div>

				{ children }
			</div>
		</ClickOutside>
	);
}

export default ModalContent;
