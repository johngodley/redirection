import { __ } from '@wordpress/i18n';
import { MultiOptionDropdown } from '@wp-plugin-components';

interface LabelValue {
	label: string;
	value: string;
}

interface LabelValueGrouping extends LabelValue {
	grouping: string[];
}

interface Table {
	displayType: string;
	displaySelected: string[];
}

interface GroupedOption {
	label: string;
	value: string;
	options: LabelValueGrouping[] | LabelValue[];
}

interface DisplayOptionsProps {
	disabled: boolean;
	predefinedGroups: LabelValueGrouping[];
	customOptions: LabelValue[];
	table: Table;
	setDisplay: ( group: string, option: string[] ) => void;
	validation?: ( selected: string[] ) => string[];
}

function getPlaceholder( currentDisplayType: string, groups: GroupedOption[] ): string {
	if ( currentDisplayType === 'custom' ) {
		return __( 'Custom Display', 'redirection' );
	}

	for ( let index = 0; index < groups.length; index++ ) {
		const tofind = groups[ index ]?.options.find( ( item ) => item.value === currentDisplayType );
		if ( tofind ) {
			return tofind.label;
		}
	}

	return groups[ 0 ]?.label || '';
}

function DisplayOptions( props: DisplayOptionsProps ) {
	const { disabled, predefinedGroups, customOptions, table, setDisplay, validation } = props;
	const { displayType, displaySelected } = table;
	const groupedOptions: GroupedOption[] = [
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

	function onChange( selected: string[] | string ) {
		if ( ! Array.isArray( selected ) ) {
			return;
		}

		const currentState = displaySelected.concat( [ displayType ] );
		const added = selected.filter( ( item ) => ! currentState.includes( item ) );

		if ( added.length > 0 ) {
			const preset = ( groupedOptions[ 0 ]?.options as LabelValueGrouping[] )?.find(
				( item ) => item.value === added[ 0 ]
			);
			if ( preset ) {
				setDisplay( preset.value, preset.grouping );
				return;
			}
		}

		const customSelected = selected.filter( ( item ) => {
			if ( item === displayType ) {
				return false;
			}
			const isPredefined = groupedOptions[ 0 ]?.options.find( ( preset ) => preset.value === item );
			if ( isPredefined ) {
				return false;
			}
			const isCustom = customOptions.find( ( opt ) => opt.value === item );
			return isCustom;
		} );

		setDisplay( 'custom', validation ? validation( customSelected ) : customSelected );
	}

	return (
		<MultiOptionDropdown
			className="redirect-table-display__filter"
			options={ groupedOptions }
			selected={ displaySelected.concat( [ displayType ] ) }
			onChange={ onChange as any }
			title={ getPlaceholder( displayType, groupedOptions ) }
			disabled={ disabled }
		/>
	);
}

export default DisplayOptions;
