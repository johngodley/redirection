/**
 * External dependencies
 */

import React from 'react';

/**
 * Three vertical dots
 *
 * @param {object} props Component props
 * @param {boolean} [props.isOpen=false] Is the menu in the open state
 */
function DotMenuIcon( { isOpen = false } ) {
	if ( isOpen ) {
		return (
			<svg
				xmlns="http://www.w3.org/2000/svg"
				viewBox="0 0 24 24"
				width="24"
				height="24"
				role="img"
				aria-hidden="true"
				focusable="false"
			>
				<path d="M11 13h2v-2h-2v2zm-6 0h2v-2H5v2zm12-2v2h2v-2h-2z" />
			</svg>
		);
	}
	return (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			viewBox="0 0 24 24"
			width="24"
			height="24"
			role="img"
			aria-hidden="true"
			focusable="false"
		>
			<path d="M13 19h-2v-2h2v2zm0-6h-2v-2h2v2zm0-6h-2V5h2v2z" />
		</svg>
	);
}

export default DotMenuIcon;
