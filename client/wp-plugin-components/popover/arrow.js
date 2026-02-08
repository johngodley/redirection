/**
 * External dependencies
 */

import React from 'react';
import classnames from 'classnames';

/**
 * An arrow attached to a popovor.
 *
 * @param {object} props - Component props.
 * @param {object} props.style - Any style properties to attach to the arrow.
 * @param {string} props.align - The current dropdown alignment (`left`, `right`, `centre`).
 */
export default function PopoverArrows( { style, align } ) {
	const classes = classnames( 'wpl-popover__arrows', {
		'wpl-popover__arrows__left': align === 'left',
		'wpl-popover__arrows__right': align === 'right',
		'wpl-popover__arrows__centre': align === 'centre',
	} );

	return (
		<div className={ classes } style={ style } />
	);
}
