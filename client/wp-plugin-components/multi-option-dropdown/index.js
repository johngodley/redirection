/**
 * External dependencies
 */

import React from 'react';
import classnames from 'classnames';

/**
 * Internal dependencies
 */

import Dropdown from '../dropdown';
import MultiOption from './multi-option';
import getBadgeList from './badge-list';
import DropdownIcon from '../dropdown-menu/icon';
import './style.scss';

/** @typedef {import('./multi-option/index.js').applyCallback} applyCallback */

/**
 * Should we show a title?
 *
 * @param {string[]} selected - Array of selected option values
 * @param {boolean} hideTitle - `true` if title should be hidden, `false` otherwise
 * @returns {boolean}
 */
function shouldShowTitle( selected, hideTitle ) {
	if ( hideTitle === false ) {
		return true;
	}

	return selected.length === 0;
}

/**
 * A dropdown that displays multiple groups of options
 *
 * @param {object} props - Component props
 * @param {string[]} props.selected - Array of selected option values
 * @param {MultiOptionGroupValue[]|MultiOptionValue[]} props.options - Array of options
 * @param {applyCallback} props.onApply - Callback when an option is enabled or disabled
 * @param {string} [props.title=''] - Title to display in the dropdown
 * @param {boolean} [props.badges=false] - `true` to show badges, `false` otherwise
 * @param {boolean} [props.disabled=false] - `true` if disabled, `false` otherwise
 * @param {boolean} [props.multiple=false] - `true` if multiple options can be selected, `false` otherwise
 * @param {boolean} [props.hideTitle] - `true` if title should be hidden when badges are shown, `false` otherwise
 * @param {string} [props.className] - class to add to the dropdown
 * @param {CustomBadge} [props.customBadge] - Perform custom badge filtering
 */
function MultiOptionDropdown( props ) {
	const {
		options,
		selected,
		onApply,
		title = '',
		badges = false,
		disabled = false,
		multiple = false,
		className,
		hideTitle = false,
	} = props;
	const badgeList = getBadgeList( props );

	return (
		<Dropdown
			renderToggle={ ( isOpen, toggle ) => (
				<div
					className={ classnames(
						'button',
						'action',
						'wpl-multioption__button',
						disabled && 'wpl-multioption__disabled',
						isOpen ? 'wpl-multioption__button_enabled' : null
					) }
					onClick={ toggle }
					disabled={ disabled }
				>
					{ shouldShowTitle( selected, hideTitle ) && title.length > 0 && <h5>{ title }</h5> }
					{ badges && badgeList }

					<DropdownIcon />
				</div>
			) }
			align="right"
			renderContent={ () => (
				<div className={ classnames( 'wpl-multioption', className ) }>
					{ options.map( ( option, key ) => (
						<MultiOption
							option={ option }
							selected={ selected }
							key={ key }
							onApply={ onApply }
							multiple={ multiple || option.multiple }
						/>
					) ) }
				</div>
			) }
		/>
	);
}

export default MultiOptionDropdown;
