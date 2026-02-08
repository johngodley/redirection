/**
 * External dependencies
 */

import React, { useEffect } from 'react';
import { createPortal } from 'react-dom';
import classnames from 'classnames';
import FocusLock from 'react-focus-lock';

/**
 * Internal dependencies
 */
import PopoverContainer from './container';
import ClickOutside from '../click-outside';
import { getPosition } from './dimensions';
import { DROPDOWN_PORTAL } from '../constant';
import getPortal from '../../wp-plugin-lib/portal';
import isOutside from '../click-outside/is-outside';
import { WORDPRESS_WRAP } from '../constant';
import './style.scss';

/**
 * Render callback.
 *
 * @callback contentRender
 * @param {toggleCallback} toggle
 */

/**
 * Toggle callback.
 *
 * @callback toggleCallback
 */

/**
 * Render callback.
 *
 * @callback toggleRender
 * @param {boolean} isShowing Is the menu currently visible?
 * @param {toggleCallback} toggle Toggle the dropdown on/off.
 */

/**
 * Displays a dropdown - a toggle that when clicked shows a dropdown area.
 *
 * @param {object} props - Component props.
 * @param {string} [props.className] - Additional class name.
 * @param {('left'|'right'|'centre')} [props.align='left'] - Align the popover on the `left` or `right`.
 * @param {('top'|'bottom')} [props.valign='bottom'] - Vertical alignment
 * @param {boolean} [props.hasArrow=false] - Show a small arrow pointing at the toggle when the popover is shown.
 * @param {boolean} [props.focusLock=true] - Lock focus into the popover
 * @param {toggleCallback} [props.onClose] - Callback when the popover is closed.
 * @param {Element|JSX.Element} props.children - Called when the popover should be shown
 * @param {} props.popoverPosition - Position where the popover should be shown
 * @param {object|null} [props.style=null] - Additional style params
 */
function Popover( props ) {
	const {
		children,
		className,
		align = 'left',
		valign = 'bottom',
		onClose,
		hasArrow = false,
		popoverPosition,
		style = null,
		focusLock = true,
	} = props;

	/**
	 * Hide the dropdown
	 * @param {Event} ev - Event
	 */
	function onOutside( ev ) {
		if ( isOutside( ev, popoverPosition.ref ) === false && ev.key !== 'Escape' ) {
			return;
		}

		onClose();
	}

	// Close popover when window resized
	useEffect(() => {
		window.addEventListener( 'resize', onClose );

		return () => {
			window.removeEventListener( 'resize', onClose );
		};
	}, []);

	return createPortal(
		<ClickOutside className={ classnames( 'wpl-popover', className ) } onOutside={ onOutside }>
			<FocusLock returnFocus disabled={ ! focusLock }>
				<PopoverContainer
					position={ getPosition( popoverPosition ) }
					popoverPosition={ popoverPosition }
					align={ align }
					hasArrow={ hasArrow }
					valign={ valign }
					style={ style }
				>
					{ children }
				</PopoverContainer>
			</FocusLock>
		</ClickOutside>,
		getPortal( DROPDOWN_PORTAL )
	);
}

/**
 * Get the dimensions of the node.
 *
 * @param {HTMLElement|null} ref - The dom node.
 */
export function getPopoverPosition( ref, verticalAlign = 'bottom' ) {
	const parentNode = document.getElementById( WORDPRESS_WRAP );
	if ( ref === null || parentNode === null ) {
		return {};
	}

	const parentRect = parentNode.getBoundingClientRect();
	const { height, width, left, top } = ref.getBoundingClientRect();

	return {
		left: verticalAlign === 'bottom' ? left - parentRect.left : left + width / 2 - parentRect.left - 7,
		top: top - parentRect.top + 1,
		width,
		height,
		parentWidth: parentRect.width,
		parentHeight: parentRect.height,
		ref,
	};
}

export default Popover;
