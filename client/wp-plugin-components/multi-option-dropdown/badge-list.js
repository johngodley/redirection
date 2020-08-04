/**
 * External dependencies
 */

import React from 'react';

/**
 * Internal dependencies
 */

import Badge from '../badge';

/** @typedef {import('./multi-option/index.js').applyCallback} applyCallback */

/** @type {number} */
const MAX_BADGES = 3;

/**
 * Find the option with the value
 *
 * @param {MultiOptionGroupValue[]|MultiOptionValue[]} options - Array of options
 * @param {string} optionValue - Option value to find
 * @returns {MultiOptionValue|null}
 */
function findOption( options, optionValue ) {
	for ( let index = 0; index < options.length; index++ ) {
		const option = options[ index ];

		if ( option.value === optionValue ) {
			return option;
		}

		if ( option.options ) {
			const subOption = findOption( option.options, optionValue );

			if ( subOption ) {
				return subOption;
			}
		}
	}

	return null;
}

/**
 * Remove the badge from the list of selected items
 *
 * @param {Event} ev Event.
 * @param {applyCallback} onApply - Callback when an option is enabled or disabled
 * @param {string} optionValue - Value
 * @param {string[]} selected - Array of selected option values
 */
function removeBadge( ev, onApply, optionValue, selected ) {
	ev.preventDefault();
	ev.stopPropagation();

	// Signal that the option was removed
	onApply( selected, optionValue, false );
}

/**
 * Show a list of badges
 *
 * @param {object} props - Component props
 * @param {string[]} props.selected - Array of selected option values
 * @param {MultiOptionGroupValue[]|MultiOptionValue[]} props.options - Array of options
 * @param {boolean} [props.disabled=false] - `true` if disabled, `false` otherwise
 * @param {applyCallback} props.onApply - Callback when an option is enabled or disabled
 * @param {CustomBadge} [props.customBadge] - Perform custom badge filtering
 * @returns {Element[]|null}
 */
export default function getBadgeList( props ) {
	const { selected, options, disabled, onApply } = props;
	const customBadge = props.customBadge ? props.customBadge : ( badge ) => badge;
	const keys = customBadge( selected );

	if ( keys.length === 0 ) {
		return null;
	}

	return keys
		.slice( 0, MAX_BADGES )
		.map(
			/**
			 * @param {MultiOptionGroupValue|MultiOptionValue} optionValue - Option
			 */
			( optionValue ) => {
				const found = findOption( options, optionValue );
				if ( found === null ) {
					return null;
				}

				return (
					<Badge
						key={ optionValue }
						small
						onCancel={
							/**
							 * @param {Event} ev
							 */
							( ev ) =>
								removeBadge(
									ev,
									onApply,
									optionValue,
									selected.filter( ( item ) => item !== optionValue )
								)
						}
						disabled={ disabled }
					>
						{ found.label }
					</Badge>
				);
			}
		)
		.concat( [ keys.length > MAX_BADGES ? <span key="end">...</span> : null ] );
}
