/**
 * External dependencies
 */

import React, { useState, useRef, useEffect } from 'react';
import classnames from 'classnames';

/**
 * Internal dependencies
 */
import './style.scss';
import Popover, { getPopoverPosition } from '../popover';

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
 * @param {boolean} [props.hasArrow=false] - Show a small arrow pointing at the toggle when the dropdown is toggled.
 * @param {boolean} [props.disabled=false] - Is the dropdown disabled
 * @param {contentRender} props.renderContent - Called when the dropdown menu should be shown
 * @param {toggleRender} props.renderToggle - Called to display the toggle.
 */
function Dropdown( props ) {
	const {
		renderContent,
		className,
		renderToggle,
		align = 'left',
		hasArrow = false,
	} = props;
	const [ isShowing, setShowing ] = useState( false );
	const [ togglePosition, setTogglePosition ] = useState( null );
	const toggleRef = useRef( null );

	/**
	 * Toggle the dropdown
	 * @param {Event} ev - Event
	 */
	const toggleDropdown = ( ev ) => {
		ev && ev.stopPropagation();
		setTogglePosition( getPopoverPosition( toggleRef.current ) );
		setShowing( ! isShowing );
	};

	return (
		<>
			<div className={ classnames( 'wpl-popover__toggle', className ) } ref={ toggleRef }>
				{ renderToggle( isShowing, toggleDropdown ) }
			</div>

			{ isShowing && (
				<Popover
					align={ align }
					hasArrow={ hasArrow }
					className={ className }
					onClose={ () => setShowing( false ) }
					popoverPosition={ togglePosition }
				>
					{ renderContent( () => setShowing( false ) ) }
				</Popover>
			) }
		</>
	);
}

export default Dropdown;
