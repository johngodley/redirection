/**
 * External dependencies
 */

import React from 'react';
import PropTypes from 'prop-types';
import { translate as __ } from 'lib/locale';

/**
 * Internal dependencies
 */

import MultiOptionDropdown from 'component/multi-option-dropdown';

class TableDisplay extends React.Component {
	static propTypes = {
		disable: PropTypes.bool.isRequired,
		options: PropTypes.array.isRequired,
		groups: PropTypes.array.isRequired,
		store: PropTypes.string.isRequired,
		currentDisplayType: PropTypes.string.isRequired,
		currentDisplaySelected: PropTypes.array.isRequired,
		setDisplay: PropTypes.func.isRequired,
		validation: PropTypes.func,
	};

	getSelected( stored ) {
		const { currentDisplayType } = this.props;
		const obj = {
			custom: [],
			pre: currentDisplayType,
		};

		stored.map( item => obj.custom.push( item ) );

		return obj;
	}

	saveDisplay = ( displayType, displaySelected ) => {
		this.props.setDisplay( displayType, displaySelected );

		localStorage.setItem( this.props.store + '_displayType', displayType );
		localStorage.setItem( this.props.store + '_displaySelected', displaySelected.join( ',' ) );
	}

	onChange = ( selected, optionValue ) => {
		const groups = this.getGroupedOptions();

		// If a preset then just switch, otherwise its custom
		const preset = optionValue === 'all' ? groups[ groups.length - 1 ] : groups.find( item => item.value === optionValue );

		if ( preset ) {
			this.saveDisplay( optionValue, preset.grouping );
		} else {
			this.saveDisplay( 'custom', this.props.validation ? this.props.validation( selected.custom ) : selected.custom );
		}
	}

	getGroupedOptions() {
		return [
			...this.props.groups,
			{
				value: 'all',
				label: __( 'Display All' ),
				grouping: this.props.options.map( item => item.value ),
			},
		];
	}

	getPlaceholder() {
		const { currentDisplayType } = this.props;
		const groups = this.getGroupedOptions();

		if ( currentDisplayType === 'custom' ) {
			return __( 'Custom Display' );
		}

		const tofind = groups.find( item => item.value === currentDisplayType );
		if ( tofind ) {
			return tofind.label;
		}

		return groups[ 0 ].label;
	}

	render() {
		const { disable, options, currentDisplaySelected } = this.props;
		const groupedOptions = [
			{
				label: __( 'Pre-defined' ),
				value: 'pre',
				options: this.getGroupedOptions(),
			},
			{
				label: __( 'Custom' ),
				value: 'custom',
				multiple: true,
				options,
			},
		];

		return (
			<MultiOptionDropdown
				className="redirect-table-display__filter"
				options={ groupedOptions }
				selected={ this.getSelected( currentDisplaySelected ) }
				onApply={ this.onChange }
				title={ this.getPlaceholder() }
				isEnabled={ ! disable }
			/>
		);
	}
}

export default TableDisplay;
