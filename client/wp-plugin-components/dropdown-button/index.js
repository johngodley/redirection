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
		this.props.onChange( name );
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
					<h5 onClick={ this.props.onSelect }>{ selectedItem ? selectedItem.title : '' }</h5>

					{ options.length > 1 && <svg onClick={ toggle } height="20" width="20" viewBox="0 0 20 20" aria-hidden="true" focusable="false"><path d="M4.516 7.548c0.436-0.446 1.043-0.481 1.576 0l3.908 3.747 3.908-3.747c0.533-0.481 1.141-0.446 1.574 0 0.436 0.445 0.408 1.197 0 1.615-0.406 0.418-4.695 4.502-4.695 4.502-0.217 0.223-0.502 0.335-0.787 0.335s-0.57-0.112-0.789-0.335c0 0-4.287-4.084-4.695-4.502s-0.436-1.17 0-1.615z"></path></svg> }
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
