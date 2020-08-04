/**
 * External dependencies
 */

import React from 'react';

/**
 * Internal dependencies
 */
import Dropdown from '../dropdown';
import DropdownIcon from './icon';
import './style.scss';

/**
 * Display a dropdown menu behind a toggle button. The menu items contain the logic to detect clicks, if needed.
 *
 * @param {object} props - Component props
 * @param {object[]} props.menu - Array of menu items (React components)
 * @param {boolean} [props.disabled=false] - Disable the menu
 * @param {('left'|'right')} [props.align] - Which side the menu appears on
 */
function DropdownMenu( { menu, align = 'right', disabled = false } ) {
	return (
		<Dropdown
			align={ align }
			hasArrow
			renderToggle={ ( isOpen, toggle ) => (
				<button type="button" className="wpl-dropdownmenu" onClick={ toggle } disabled={ disabled }>
					<DropdownIcon />
				</button>
			) }
			renderContent={ ( toggle ) => (
				<ul className="wpl-dropdownmenu__menu" onClick={ toggle }>
					{ menu.map( ( item, key ) => (
						<li key={ key }>{ item }</li>
					) ) }
				</ul>
			) }
		/>
	);
}

export default DropdownMenu;
