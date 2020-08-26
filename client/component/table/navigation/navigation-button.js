/**
 * External dependencies
 */

import React from 'react';

function NavigationButton( props ) {
	const { title, button, className, disabled, onClick } = props;
	function click( ev ) {
		ev.preventDefault();
		onClick();
	}

	if ( disabled ) {
		return (
			<span className="tablenav-pages-navspan button disabled" aria-hidden="true">
				{ button }
			</span>
		);
	}

	return (
		<a className={ className + ' button' } href="#" onClick={ click }>
			<span className="screen-reader-text">{ title }</span>
			<span aria-hidden="true">{ button }</span>
		</a>
	);
}

export default NavigationButton;
