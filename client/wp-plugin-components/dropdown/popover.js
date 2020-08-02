/**
 * External dependencies
 */

import React, { useState, useCallback } from 'react';

/**
 * Local dependencies
 */

import { getAdjustedPosition, adjustArrowStyle, TogglePosition, DropdownPosition } from './dimensions';
import PopoverArrow from './arrow';

/**
 * The actual popover content.
 *
 * @param {object} props - Component props.
 * @param {string} props.align - Our alignment.
 * @param {boolean} props.hasArrow - Show an arrow or not
 * @param {TogglePosition|null} props.togglePosition - The toggle position.
 * @param {DropdownPosition|null} props.position - Our position.
 * @param {object} props.children - Child components to show inside the popover.
 */
function Popover( props ) {
	const { position, children, togglePosition, align, hasArrow } = props;
	const [ style, setStyle ] = useState( { arrow: {}, content: { visibility: 'none', ...position } } );
	const popoverRef = useCallback(
		( node ) => {
			if ( node ) {
				const content = getAdjustedPosition( position, togglePosition, align, node, hasArrow );

				setStyle( {
					content,
					arrow: adjustArrowStyle( content, node ),
				} );
			}
		},
		[ position  ]
	);

	return (
		<>
			{ hasArrow && <PopoverArrow style={ style.arrow } align={ align } /> }

			<div
				className="wpl-popover__content"
				style={ { ...style.content, visibility: position && position.left ? 'visible' : 'hidden' } }
				ref={ popoverRef }
			>
				{ children }
			</div>
		</>
	);
}

export default Popover;
