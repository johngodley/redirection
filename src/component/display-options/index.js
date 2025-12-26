/**
 * External dependencies
 */

import React from 'react';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */

import { MultiOptionDropdown } from '@wp-plugin-components';

/** @typedef {import('component/table').Table} Table */
/** @typedef {import('component/log-page').LabelValueGrouping} LabelValueGrouping */
/** @typedef {import('component/log-page').LabelValue} LabelValue */

/**
 * @callback SetDisplayCallback
 * @param {string} group
 * @param {string[]} option
 **/

/**
 * @param {string} currentDisplayType
 * @param {{label: string|typeof React, value: string, options: object[], multiple: boolean}[]} groups
 */
function getPlaceholder( currentDisplayType, groups ) {
	if ( currentDisplayType === 'custom' ) {
		return __( 'Custom Display', 'redirection' );
	}

	for ( let index = 0; index < groups.length; index++ ) {
		const tofind = groups[ index ].options.find( ( item ) => item.value === currentDisplayType );
		if ( tofind ) {
			return tofind.label;
		}
	}

	return groups[ 0 ].label;
}

/**
 * @callback Validation
 * @param {string[]} selected
 */

/**
 *
 * @param {object} props - Component props
 * @param {boolean} props.disabled
 * @param {LabelValueGrouping[]} props.predefinedGroups
 * @param {LabelValue[]} props.customOptions
 * @param {Table} props.table
 * @param {SetDisplayCallback} props.setDisplay
 * @param {Validation} props.validation
 */

function DisplayOptions( props ) {
	const { disabled, predefinedGroups, customOptions, table, setDisplay, validation } = props;
	const { displayType, displaySelected } = table;
	const groupedOptions = [
		{
			label: __( 'Pre-defined', 'redirection' ),
			value: 'pre',
			options: predefinedGroups,
		},
		{
			label: __( 'Custom', 'redirection' ),
			value: 'custom',
			options: customOptions,
		},
	];

	/**
	 * @param {*} selected
	 */
	function onChange( selected ) {
		if ( ! Array.isArray( selected ) ) {
			return;
		}

		// Check what changed: compare with current state
		const currentState = displaySelected.concat( [ displayType ] );

		// Find what was added
		const added = selected.filter( item => ! currentState.includes( item ) );

		// If a predefined option was added, switch to it
		if ( added.length > 0 ) {
			const preset = groupedOptions[ 0 ].options.find( ( item ) => item.value === added[ 0 ] );
			if ( preset ) {
				// A predefined option was selected, switch to it
				setDisplay( preset.value, preset.grouping );
				return;
			}
		}

		// Otherwise, it's a custom selection change
		// Filter to get only custom option values (exclude displayType and predefined values)
		const customSelected = selected.filter( item => {
			// Exclude displayType
			if ( item === displayType ) return false;
			// Exclude predefined values
			const isPredefined = groupedOptions[ 0 ].options.find( preset => preset.value === item );
			if ( isPredefined ) return false;
			// Only include if it's a valid custom option
			const isCustom = customOptions.find( opt => opt.value === item );
			return isCustom;
		} );

		setDisplay( 'custom', validation ? validation( customSelected ) : customSelected );
	}

	return (
		<MultiOptionDropdown
			className="redirect-table-display__filter"
			options={ groupedOptions }
			selected={ displaySelected.concat( [ displayType ] ) }
			onChange={ onChange }
			title={ getPlaceholder( displayType, groupedOptions ) }
			disabled={ disabled }
		/>
	);
}

export default DisplayOptions;
