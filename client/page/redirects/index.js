/**
 * External dependencies
 */

import React, { useEffect } from 'react';
import { translate as __ } from 'lib/locale';
import { connect } from 'react-redux';

/**
 * Internal dependencies
 */

import {
	getRedirect,
	setPage,
	performTableAction,
	setAllSelected,
	setOrderBy,
	setFilter,
	setDisplay,
	setSelected,
} from 'state/redirect/action';
import { getGroup } from 'state/group/action';
import { getFlags } from 'state/settings/selector';
import { STATUS_COMPLETE } from 'state/settings/type';
import {
	getDisplayGroups,
	getDisplayOptions,
	getBulk,
	getHeaders,
	getFilterOptions,
	getSearchOptions,
} from './constants';
import { has_capability, CAP_REDIRECT_ADD } from 'lib/capabilities';
import LogPage from 'component/log-page';
import CreateRedirect from './create';
import RedirectRowActions from './row-actions';
import getColumns from './columns';
import { nestedGroups } from 'state/group/selector';

function alwaysHasSource( selected ) {
	if ( selected.indexOf( 'source' ) === -1 ) {
		return selected.concat( [ 'source'] );
	}

	return selected;
}

function validateDisplay( selected ) {
	// Ensure we have at least source or title
	if ( selected.indexOf( 'title' ) === -1 && selected.indexOf( 'source' ) === -1 ) {
		return alwaysHasSource( selected.concat( [ 'title' ] ) );
	}

	return alwaysHasSource( selected );
}

function getGroups( groups ) {
	return [
		{
			value: 0,
			label: __( 'All groups' ),
		},
	].concat( nestedGroups( groups ) );
}

function isAvailable( item, table ) {
	return table.displaySelected.indexOf( item ) !== -1;
}

function Redirects( props ) {
	const { onDelete, group, redirect, defaultFlags } = props;
	const { status, total, table, rows, addTop, saving } = redirect;
	const canAdd = status === STATUS_COMPLETE && group.status === STATUS_COMPLETE && has_capability( CAP_REDIRECT_ADD );

	useEffect(() => {
		props.onLoadRedirects();
		props.onLoadGroups();
	}, []);

	const logOptions = {
		displayFilters: getDisplayOptions( table.groupBy ),
		displayGroups: getDisplayGroups( table.groupBy ),
		searchOptions: getSearchOptions(),
		groupBy: getGroups( group.rows ),
		bulk: getBulk(),
		rowFilters: getFilterOptions(),
		headers: getHeaders().filter( ( item ) => isAvailable( item.name, table ) ),
		validateDisplay,
	};

	return (
		<div className="redirects">
			{ addTop && has_capability( CAP_REDIRECT_ADD ) && <CreateRedirect defaultFlags={ defaultFlags } addTop /> }

			<LogPage
				logOptions={ logOptions }
				logActions={ props }
				table={ table }
				status={ status }
				total={ total }
				rows={ rows }
				saving={ saving }
				getRow={ ( row, rowParams ) =>
					getColumns( row, rowParams, saving.indexOf( row.id ) !== -1, defaultFlags, group )
				}
				getRowActions={ ( row, rowParams ) => (
					<RedirectRowActions
						disabled={ saving.indexOf( row.id ) !== -1 }
						row={ row }
						onDelete={ onDelete }
						rowParams={ rowParams }
					/>
				) }
			/>

			{ canAdd && ! addTop && <CreateRedirect defaultFlags={ defaultFlags } addTop={ false } /> }
		</div>
	);
}

function mapStateToProps( state ) {
	const { redirect, group } = state;

	return {
		redirect,
		group,
		defaultFlags: getFlags( state ),
	};
}

function mapDispatchToProps( dispatch ) {
	return {
		onLoadGroups: () => {
			dispatch( getGroup() );
		},
		onLoadRedirects: ( args ) => {
			dispatch( getRedirect( args ) );
		},
		onChangePage: ( page ) => {
			dispatch( setPage( page ) );
		},
		onBulk: ( action ) => {
			dispatch( performTableAction( action ) );
		},
		onSetAll: ( onoff ) => {
			dispatch( setAllSelected( onoff ) );
		},
		onGroup: ( group ) => {
			dispatch( setFilter( parseInt( group, 10 ) > 0 ? { group } : {} ) );
		},
		onSelect: ( items ) => {
			dispatch( setSelected( items ) );
		},
		onSetOrderBy: ( column, direction ) => {
			dispatch( setOrderBy( column, direction ) );
		},
		onFilter: ( filterBy ) => {
			dispatch( setFilter( filterBy ) );
		},
		onSetDisplay: ( displayType, displaySelected ) => {
			dispatch( setDisplay( displayType, displaySelected ) );
		},
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps
)( Redirects );
