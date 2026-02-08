import { __ } from '@wordpress/i18n';
import BulkAction from 'component/table/bulk-action';
import { MultiOptionDropdown } from '@wp-plugin-components';
import TableGroup from 'component/table/group';

interface FilterOption {
	label: string;
	value: string;
}

interface FilterGroup {
	label: string;
	value: string;
	options: FilterOption[];
}

interface FilterBy {
	[ key: string ]: string;
}

interface Table {
	groupBy: string;
	filterBy: FilterBy;
}

interface LogFiltersProps {
	table: Table;
	disabled: boolean;
	groupOptions: FilterOption[];
	filterOptions: FilterGroup[];
	onGroup: ( group: string ) => void;
	onFilter: ( filter: FilterBy ) => void;
}

function findGroup( group: FilterGroup, item: string ): FilterOption | undefined {
	return group.options.find( ( groupItem ) => groupItem.value === item );
}

function getSelectedFilters( enabled: FilterBy, filters: FilterGroup[] ): string[] {
	const selectedFilters: string[] = [];

	Object.keys( enabled ).forEach( ( key ) => {
		const group = filters.find( ( item ) => item.value === key );

		if ( group ) {
			const enabledValue = enabled[ key ];
			if ( enabledValue ) {
				const filter = group.options.find( ( item ) => item.value === enabledValue );

				if ( filter ) {
					selectedFilters.push( enabledValue );
				}
			}
		}
	} );

	return selectedFilters;
}

function LogFilters( props: LogFiltersProps ) {
	const { table, disabled, groupOptions, filterOptions, onGroup, onFilter } = props;

	function onChange( selected: string[] | Record< string, string | boolean | undefined > ) {
		const filter: FilterBy = {};

		if ( ! Array.isArray( selected ) ) {
			return;
		}

		for ( let index = 0; index < selected.length; index++ ) {
			const selectedValue = selected[ index ];
			if ( selectedValue ) {
				const group = filterOptions.find( ( groupItem ) => findGroup( groupItem, selectedValue ) );

				if ( group ) {
					filter[ group.value ] = selectedValue;
				}
			}
		}

		onFilter( filter );
	}

	return (
		<>
			{ groupOptions.length > 0 && (
				<TableGroup
					selected={ table.groupBy ? table.groupBy : '' }
					options={ groupOptions }
					isEnabled={ ! disabled }
					onGroup={ onGroup }
					key={ table.groupBy }
				/>
			) }

			{ filterOptions.length > 0 && (
				<BulkAction>
					<MultiOptionDropdown
						options={ filterOptions }
						selected={ getSelectedFilters( table.filterBy, filterOptions ) }
						onChange={ onChange }
						title={ __( 'Filters', 'redirection' ) }
						disabled={ disabled }
						multiple
						badges
					/>
				</BulkAction>
			) }
		</>
	);
}

export default LogFilters;
