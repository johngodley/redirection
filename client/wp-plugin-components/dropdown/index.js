/**
 * External dependencies
 */

import React, { useState, useRef, useEffect } from 'react';
import { createPortal } from 'react-dom';
import classnames from 'classnames';

/**
 * Internal dependencies
 */
import './style.scss';
import Popover from './popover';
import ClickOutside from '../click-outside';
import { getPosition, getDimensions } from './dimensions';
import { DROPDOWN_PORTAL } from '../constant';
import getPortal from '../../wp-plugin-lib/portal';
import isOutside from '../click-outside/is-outside';

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
 * @param {('left'|'right'|'centre')} [props.align='left'] - Align the dropdown on the `left` or `right`.
 * @param {number} [props.widthAdjust=0] - Change the dropdown menu to match the width of the toggle.
 * @param {boolean} [props.hasArrow=false] - Show a small arrow pointing at the toggle when the dropdown is toggled.
 * @param {boolean} [props.disabled=false] - Is the dropdown disabled
 * @param {toggleCallback} [props.onHide] - Callback when the dropdown is hidden.
 * @param {contentRender} props.renderContent - Called when the dropdown menu should be shown
 * @param {toggleRender} props.renderToggle - Called to display the toggle.
 */
function Dropdown( props ) {
	const {
		renderContent,
		className,
		renderToggle,
		align = 'left',
		onHide,
		widthAdjust = -1,
		hasArrow = false,
	} = props;
	const [ isShowing, setShowing ] = useState( false );
	const [ togglePosition, setTogglePosition ] = useState( null );
	const toggleRef = useRef( null );
	const onResize = () => setShowing( false );

	/**
	 * Toggle the dropdown
	 * @param {Event} ev - Event
	 */
	const toggle = ( ev ) => {
		ev && ev.stopPropagation();
		setShowing( ! isShowing );
	};

	/**
	 * Hide the dropdown
	 * @param {Event} ev - Event
	 */
	const hide = ( ev ) => {
		if ( isOutside( ev, toggleRef.current ) === false && ev.key !== 'Escape' ) {
			return;
		}

		setShowing( false );
		onHide && onHide();
	};

	// Update the sizes when matchToggle changes
	useEffect(() => {
		if ( isShowing ) {
			setTogglePosition( getDimensions( toggleRef.current ) );
		}
	}, [ isShowing, widthAdjust ]);

	// Close popover when window resized
	useEffect(() => {
		if ( ! isShowing ) {
			return;
		}

		window.addEventListener( 'resize', onResize );

		return () => {
			window.removeEventListener( 'resize', onResize );
		};
	}, [ isShowing ]);

	return (
		<>
			<div className={ classnames( 'wpl-popover__toggle', className ) } ref={ toggleRef }>
				{ renderToggle( isShowing, toggle ) }
			</div>

			{ isShowing &&
				createPortal(
					<ClickOutside className="wpl-popover" onOutside={ hide }>
						<Popover
							position={ getPosition( togglePosition, widthAdjust !== -1 ) }
							togglePosition={ togglePosition }
							align={ align }
							hasArrow={ hasArrow }
						>
							{ renderContent( () => setShowing( false ) ) }
						</Popover>
					</ClickOutside>,
					getPortal( DROPDOWN_PORTAL )
				) }
		</>
	);
}

export default Dropdown;
