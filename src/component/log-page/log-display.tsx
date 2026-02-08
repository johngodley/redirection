import DisplayOptions from 'component/display-options';
import SearchBox from 'component/search-box';

interface LabelValue {
	label: string;
	value: string;
}

interface LabelTitle {
	name: string;
	title: string;
}

interface LabelValueGrouping {
	label: string;
	value: string;
	grouping: string[];
}

interface FilterBy {
	[ key: string ]: string;
}

interface Table {
	filter: string;
	filterBy: FilterBy;
	displayType: string;
	displaySelected: string[];
}

interface LogDisplayProps {
	disabled: boolean;
	filterOptions: LabelValue[];
	searchOptions: LabelTitle[];
	predefinedGroups: LabelValueGrouping[];
	table: Table;
	onSetDisplay: ( group: string, option: string[] ) => void;
	onFilter: ( filterBy: FilterBy ) => void;
	validateDisplay?: ( selected: string[] ) => string[];
}

function LogDisplay( props: LogDisplayProps ) {
	const { disabled, filterOptions, searchOptions, predefinedGroups, table, onSetDisplay, onFilter, validateDisplay } =
		props;

	function onSearch( search: string, type: string ) {
		const filterBy = { ...table.filterBy };

		searchOptions.map( ( item ) => delete filterBy[ item.name ] );

		if ( search ) {
			filterBy[ type ] = search;
		}

		onFilter( filterBy );
	}

	return (
		<div className="redirect-table-display">
			<DisplayOptions
				disabled={ disabled }
				customOptions={ filterOptions }
				predefinedGroups={ predefinedGroups }
				table={ table }
				setDisplay={ onSetDisplay }
				{ ...( validateDisplay ? { validation: validateDisplay } : {} ) }
			/>

			<SearchBox
				disabled={ disabled }
				table={ table }
				onSearch={ onSearch }
				selected={ table.filterBy }
				searchTypes={ searchOptions }
			/>
		</div>
	);
}

export default LogDisplay;
