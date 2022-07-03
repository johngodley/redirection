/**
 * External dependencies
 */

import React, { useEffect } from 'react';
import { __ } from '@wordpress/i18n';
import { connect } from 'react-redux';

/**
 * Internal dependencies
 */

import {
	getGroup,
	setPage,
	performTableAction,
	setOrderBy,
	setFilter,
	setDisplay,
	setSelected,
} from 'state/group/action';
import { getModules } from 'state/io/selector';
import {
	getFilterOptions,
	getDisplayGroups,
	getDisplayOptions,
	getHeaders,
	getBulk,
	getSearchOptions,
} from './constants';
import LogPage from 'component/log-page';
import { has_capability, CAP_GROUP_ADD } from 'lib/capabilities';
import CreateGroup from './create-group';
import GroupRowActions from './row-actions';
import getColumns from './columns';
import './style.scss';

function validateDisplay( selected ) {
	// Ensure we have at least source or title
	if ( selected.indexOf( 'name' ) === -1 ) {
		return selected.concat( [ 'name' ] );
	}

	return selected;
}

function isAvailable( item, table ) {
	return table.displaySelected.indexOf( item ) !== -1;
}

function Groups( props ) {
	const { onDelete, group } = props;
	const { status, total, table, rows, saving } = group;

	useEffect(() => {
		props.onLoadGroups();
	}, []);

	const logOptions = {
		displayFilters: getDisplayOptions(),
		displayGroups: getDisplayGroups(),
		searchOptions: getSearchOptions(),
		groupBy: [],
		bulk: getBulk(),
		rowFilters: getFilterOptions(
			getModules().map( ( module ) => ( { label: module.label, value: `${ module.value }` } ) )
		),
		headers: getHeaders().filter( ( item ) => isAvailable( item.name, table ) ),
		validateDisplay,
	};

	return (
		<>
			<LogPage
				logOptions={ logOptions }
				logActions={ props }
				table={ table }
				status={ status }
				total={ total }
				rows={ rows }
				saving={ saving }
				getRow={ ( row, rowParams ) => getColumns( row, rowParams, saving.indexOf( row.id ) !== -1 ) }
				getRowActions={ ( row, rowParams ) => (
					<GroupRowActions
						disabled={ saving.indexOf( row.id ) !== -1 }
						row={ row }
						onDelete={ onDelete }
						rowParams={ rowParams }
					/>
				) }
			/>

			{ has_capability( CAP_GROUP_ADD ) && <CreateGroup /> }
		</>
	);
}

function mapStateToProps( state ) {
	const { group } = state;

	return {
		group,
	};
}

function mapDispatchToProps( dispatch ) {
	return {
		onLoadGroups: () => {
			dispatch( getGroup() );
		},
		onChangePage: ( page ) => {
			dispatch( setPage( page ) );
		},
		onBulk: ( action, ids ) => {
			dispatch( performTableAction( action, ids ) );
		},
		onSelect: ( items ) => {
			dispatch( setSelected( items ) );
		},
		onSetOrder: ( column, direction ) => {
			dispatch( setOrderBy( column, direction ) );
		},
		onFilter: ( filterBy ) => {
			dispatch( setFilter( filterBy ) );
		},
		onSetDisplay: ( displayType, displaySelected ) => {
			dispatch( setDisplay( displayType, displaySelected ) );
		},
		onSetAll: ( allOrClear ) => {
			dispatch( setSelected( allOrClear, allOrClear ) );
		},
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps
)( Groups );
