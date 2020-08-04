/**
 * External dependencies
 */

import React from 'react';
import classnames from 'classnames';

/**
 * Internal dependencies
 */

import Dropdown from '../dropdown';
import './style.scss';
import DropdownIcon from '../dropdown-menu/icon';

/**
 * A menu item.
 *
 * @typedef {{name: string, title: string}} ButtonOption
 */

/**
 * A dropdown button
 *
 * @param {object} props - Component props
 * @param {boolean} props.disabled - Is this component disabled?
 * @param {ButtonOption[]} props.options - Dropdown options
 * @param {string} props.selected - Currently selected option `name`
 */
function DropdownButton( props ) {
	const { options, disabled = false, selected } = props;
	const selectedItem = options.find( ( item ) => item.name === selected ) || options[ 0 ];

	const onButton = ev => {
		if ( ev.clientX !== 0 ) {
			ev.preventDefault();
		}
	};

	const onChange = ( ev, name, toggle ) => {
		ev.preventDefault();
		ev.stopPropagation();
		toggle();
		props.onChange( name );
	};

	return (
		<Dropdown
			renderToggle={ ( isOpen, toggle ) => (
				<button
					className={ classnames( 'button', 'action', disabled && 'wpl-dropdownbutton__disabled', isOpen ? 'wpl-dropdownbutton__button_enabled' : null ) }
					disabled={ disabled }
					onClick={ onButton }
					type="button"
				>
					<h5 onClick={ props.onSelect }>{ selectedItem ? selectedItem.title : '' }</h5>

					{ options.length > 1 && <DropdownIcon onClick={ toggle } /> }
				</button>
			) }
			align="right"
			className={ classnames( 'wpl-dropdownbutton', options.length <= 1 ? 'wpl-dropdownbutton__single' : null ) }
			renderContent={ ( toggle ) => (
				<ul>
					{ options.map( ( { title, name } ) => (
						<li key={ name } className={ classnames( {
							'wpl-dropdownbutton__selected': selectedItem.name === name,
							[ 'wpl-dropdownbutton__' + name ]: true,
						} ) }>
							<a href="#" onClick={ ev => onChange( ev, name, toggle ) }>
								<span className="wpl-dropdownbutton__check">{ selectedItem.name === name && '✓' }</span>
								{ title }
							</a>
						</li>
					) ) }
				</ul>
			) }
		/>
	);
}

export default DropdownButton;
