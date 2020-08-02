/**
 * External dependencies
 */

import React from 'react';

/**
 * Internal dependencies
 */
import { getPluginPage } from 'lib/plugin';
import MenuItem from './menu-item';
import './style.scss';

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
 * Determine if the menu item is currently selected.
 *
 * @param {string} page - Current plugin page
 * @param {MenuItem} item - Menu item
 * @param {string} home - 'Home' value, which is the menu item without a page URL.
 */
const isCurrent = ( page, item, home ) => page === item.value || page === home && item.value === '';

/**
 * A WordPress subsubsub menu
 *
 * @param {Object} props - Component props
 * @param {MenuItem[]} props.menu - Menu items
 * @param {String} props.home - Menu item without a home URL
 * @param {ChangePage} props.onChangePage - Change page callback
 * @param {String} props.urlBase - The base URL for the menu links
 */
const Menu = props => {
	const page = getPluginPage();
	const { onChangePage, menu, home, urlBase } = props;

	// Don't show the menu if only 1 item
	if ( menu.length < 2 ) {
		return null;
	}

	return (
		<div className="subsubsub-container">
			<ul className="subsubsub">
				{ menu.map( ( item, pos ) => (
					<MenuItem
						key={ pos }
						item={ item }
						isCurrent={ isCurrent( page, item, home ) }
						isLast={ pos === menu.length - 1 }
						onClick={ onChangePage }
						urlBase={ urlBase }
					/>
				) ) }
			</ul>
		</div>
	);
};

export default Menu;
