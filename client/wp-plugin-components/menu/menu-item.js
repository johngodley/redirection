/* global SearchRegexi10n */
/**
 * External dependencies
 */

import React from 'react';

/**
 * A menu item.
 *
 * @typedef {{name: string, value: string}} MenuItem
 */

/**
 * Called when a page is changed.
 *
 * @callback ChangePage
 * @param {string} value - Menu value
 * @param {string} url - Menu URL
 */

/**
 * A menu item
 *
 * @param {Object} props - Component props
 * @param {MenuItem} props.item - Menu item
 * @param {Boolean} props.isCurrent - This menu item is currently selected
 * @param {Boolean} props.isLast - This menu item is the last item
 * @param {ChangePage} props.onClick - Change page callback
 * @param {String} props.urlBase - The base URL for the menu links
 */
const MenuItem = props => {
	const { item, isCurrent, onClick, isLast, urlBase } = props;
	const url = urlBase + ( item.value === '' ? '' : '&sub=' + item.value );
	const clicker = ( ev ) => {
		ev.preventDefault();
		onClick( item.value, url );
	};

	return (
		<li>
			<a className={ isCurrent ? 'current' : '' } href={ url } onClick={ clicker }>
				{ item.name }
			</a> { ! isLast && '|'  }&nbsp;
		</li>
	);
};

export default MenuItem;
