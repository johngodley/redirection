/**
 * External dependencies
 */

import React, { useState, useRef } from 'react';
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
 * @param {('top'|'bottom')} [props.valign='bottom'] - Vertical alignment
 * @param {boolean} [props.hasArrow=false] - Show a small arrow pointing at the toggle when the dropdown is toggled.
 * @param {boolean} [props.disabled=false] - Is the dropdown disabled
 * @param {boolean} [props.matchMinimum=false] - Match minimum width of toggle
 * @param {} [props.onClose] - Callback if dropdown is closed
 * @param {contentRender} props.renderContent - Called when the dropdown menu should be shown
 * @param {toggleRender} props.renderToggle - Called to display the toggle.
 */
function Dropdown( props ) {
	const {
		renderContent,
		className,
		renderToggle,
		align = 'left',
		valign = 'bottom',
		hasArrow = false,
		matchMinimum = false,
		disabled = false,
		onClose,
	} = props;
	const [ isShowing, setShowing ] = useState( false );
	const [ togglePosition, setTogglePosition ] = useState( null );
	const toggleRef = useRef( null );

	/**
	 * Toggle the dropdown
	 * @param {Event} ev - Event
	 */
	const toggleDropdown = ( ev ) => {
		const position = getPopoverPosition( toggleRef.current, valign );

		ev && ev.stopPropagation();

		if ( ! disabled ) {
			setTogglePosition( position );
			setShowing( ! isShowing );
		}
	};

	function maybeToggle( ev ) {
		if ( ev.key && ev.code === 'Space' ) {
			toggleDropdown();
		}
	}

	function close() {
		setShowing( false );

		if ( onClose ) {
			onClose();
		}
	}

	return (
		<>
			<div
				className={ classnames(
					'wpl-popover__toggle',
					className,
					disabled && 'wpl-popover__toggle__disabled'
				) }
				ref={ toggleRef }
				onKeyDown={ maybeToggle }
			>
				{ renderToggle( isShowing, toggleDropdown ) }
			</div>

			{ isShowing && (
				<Popover
					align={ align }
					valign={ valign }
					hasArrow={ hasArrow }
					className={ className }
					onClose={ close }
					popoverPosition={ togglePosition }
					style={ matchMinimum ? { minWidth: togglePosition.width + 'px' } : null }
				>
					{ renderContent( () => setShowing( false ) ) }
				</Popover>
			) }
		</>
	);
}

export default Dropdown;
