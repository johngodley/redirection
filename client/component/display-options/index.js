/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'i18n-calypso';

/**
 * Internal dependencies
 */

import MultiOptionDropdown from 'wp-plugin-components/multi-option-dropdown';

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
		return __( 'Custom Display' );
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
			label: __( 'Pre-defined' ),
			value: 'pre',
			options: predefinedGroups,
		},
		{
			label: __( 'Custom' ),
			value: 'custom',
			multiple: true,
			options: customOptions,
		},
	];

	/**
	 * @param {*} selected
	 * @param {*} optionValue
	 */
	function onChange( selected, optionValue ) {
		// If a preset then just switch, otherwise its custom
		const preset = groupedOptions[ 0 ].options.find( ( item ) => item.value === optionValue );

		if ( preset ) {
			setDisplay( optionValue, preset.grouping );
		} else {
			setDisplay( 'custom', validation ? validation( selected ) : selected );
		}
	}

	return (
		<MultiOptionDropdown
			className="redirect-table-display__filter"
			options={ groupedOptions }
			selected={ displaySelected.concat( [ displayType ] ) }
			onApply={ onChange }
			title={ getPlaceholder( displayType, groupedOptions ) }
			isEnabled={ ! disabled }
		/>
	);
}

export default DisplayOptions;
