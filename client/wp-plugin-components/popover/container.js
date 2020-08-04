/**
 * External dependencies
 */

import React, { useState, useCallback } from 'react';

/**
 * Local dependencies
 */

import { getAdjustedPosition, adjustArrowStyle, PopoverPosition, DropdownPosition } from './dimensions';
import PopoverArrow from './arrow';

/**
 * The actual popover content.
 *
 * @param {object} props - Component props.
 * @param {string} props.align - Our alignment.
 * @param {boolean} props.hasArrow - Show an arrow or not
 * @param {PopoverPosition|null} props.popoverPosition - The popover position, based on an existing DOM element
 * @param {DropdownPosition|null} props.position - Our position.
 * @param {object} props.children - Child components to show inside the popover.
 */
function PopoverContainer( props ) {
	const { position, children, popoverPosition, align, hasArrow } = props;
	const [ style, setStyle ] = useState( { arrow: {}, content: { visibility: 'none', ...position } } );
	const popoverRef = useCallback(
		( node ) => {
			if ( node ) {
				const content = getAdjustedPosition( position, popoverPosition, align, node, hasArrow );

				setStyle( {
					content,
					arrow: adjustArrowStyle( content, node ),
				} );
			}
		},
		[ position ]
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

export default PopoverContainer;
