/**
 * External dependencies
 */

import React from 'react';

/**
 * Internal dependencies
 */
import DisplayOptions from '../display-options';
import SearchBox from '../search-box';

/** @typedef {import('./index.js').FilterCallback} FilterCallback */
/** @typedef {import('./index.js').SetDisplayCallback} SetDisplayCallback */
/** @typedef {import('./index.js').LabelValue} LabelValue */
/** @typedef {import('./index.js').LabelTitle} LabelTitle */
/** @typedef {import('./index.js').LabelValueGrouping} LabelValueGrouping */
/** @typedef {import('component/table').Table} Table */

/**
 * Filters for a log page
 *
 * @param {object} props Component props
 * @param {boolean} props.disabled - Disabled status
 * @param {LabelValue[]} props.filterOptions
 * @param {LabelTitle[]} props.searchOptions
 * @param {LabelValueGrouping[]} props.predefinedGroups
 * @param {Table} props.table
 * @param {SetDisplayCallback} props.onSetDisplay
 * @param {FilterCallback} props.onFilter
 */
function LogDisplay( props ) {
	const { disabled, filterOptions, searchOptions, predefinedGroups, table, onSetDisplay, onFilter, validateDisplay } = props;

	/**
	 * @param {string} search Search string
	 * @param {string} type Search type
	 */
	function onSearch( search, type ) {
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
				validation={ validateDisplay }
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
