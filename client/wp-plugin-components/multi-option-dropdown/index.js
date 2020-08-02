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
import './style.scss';

/** @typedef {import('./multi-option/index.js').applyCallback} applyCallback */

/**
 * Get the dropdown width adjustment value. This changes as the number of displayed badges changes, causing the dropdown
 * to update in width.
 *
 * @param {boolean} showBadges - Do we need to show badges?
 * @param {Element[]|null} badges - Array of badge values
 */
function getWidthAdjust( badges, showBadges ) {
	if ( showBadges ) {
		if ( badges === null ) {
			return 1;
		}

		return badges.length + 1;
	}

	return 0;
}

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
				<button
					className={ classnames(
						'button',
						'action',
						'wpl-multioption__button',
						disabled && 'wpl-multioption__disabled',
						isOpen ? 'wpl-multioption__button_enabled' : null
					) }
					onClick={ toggle }
					disabled={ disabled }
					type="button"
				>
					{ shouldShowTitle( selected, hideTitle ) && title.length > 0 && <h5>{ title }</h5> }
					{ badges && badgeList }

					<svg height="20" width="20" viewBox="0 0 20 20" aria-hidden="true" focusable="false">
						<path d="M4.516 7.548c0.436-0.446 1.043-0.481 1.576 0l3.908 3.747 3.908-3.747c0.533-0.481 1.141-0.446 1.574 0 0.436 0.445 0.408 1.197 0 1.615-0.406 0.418-4.695 4.502-4.695 4.502-0.217 0.223-0.502 0.335-0.787 0.335s-0.57-0.112-0.789-0.335c0 0-4.287-4.084-4.695-4.502s-0.436-1.17 0-1.615z" />
					</svg>
				</button>
			) }
			widthAdjust={ getWidthAdjust( badgeList, badges ) }
			align="right"
			renderContent={ () => (
				<div className={ classnames( 'wpl-multioption', className ) }>
					{ options.map( ( option, key ) => (
						<MultiOption option={ option } selected={ selected } key={ key } onApply={ onApply } multiple={ multiple || option.multiple } />
					) ) }
				</div>
			) }
		/>
	);
}

export default MultiOptionDropdown;
