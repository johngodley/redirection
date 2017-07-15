/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import { connect } from 'react-redux';

/**
 * Internal dependencies
 */

import Table from 'component/table';
import TableNav from 'component/table/navigation';
import SearchBox from 'component/table/search';
import TableFilter from 'component/table/filter';
import GroupRow from './row';
import ErrorNotice from 'component/wordpress/error-notice';
import EditRedirect from './edit';
import { getRedirect, setPage, setSearch, performTableAction, setAllSelected, setOrderBy, setFilter } from 'state/redirect/action';
import { getGroup } from 'state/group/action';
import { getDefaultItem } from 'state/redirect/selector';
import { STATUS_COMPLETE } from 'state/settings/type';
import { nestedGroups } from 'state/group/selector';

const headers = [
	{
		name: 'cb',
		check: true,
	},
	{
		name: 'type',
		title: __( 'Type' ),
		sortable: false,
	},
	{
		name: 'url',
		title: __( 'URL' ),
	},
	{
		name: 'last_count',
		title: __( 'Hits' ),
	},
	{
		name: 'last_access',
		title: __( 'Last Access' ),
	},
];

const bulk = [
	{
		id: 'delete',
		name: __( 'Delete' ),
	},
	{
		id: 'enable',
		name: __( 'Enable' ),
	},
	{
		id: 'disable',
		name: __( 'Disable' ),
	},
];

class Redirects extends React.Component {
	constructor( props ) {
		super( props );

		this.props.onLoadRedirects();
		this.props.onLoadGroups();
	}

	renderRow( row, key, status ) {
		return <GroupRow item={ row } key={ key } selected={ status.isSelected } isLoading={ status.isLoading } />;
	}

	getGroups( groups ) {
		return [
			{
				value: '',
				text: __( 'All groups' ),
			}
		].concat( nestedGroups( groups ) );
	}

	render() {
		const { status, total, table, rows, error } = this.props.redirect;
		const { group } = this.props;

		return (
			<div className="redirects">
				{ error && total > 0 && <ErrorNotice message={ error } /> }

				<SearchBox status={ status } table={ table } onSearch={ this.props.onSearch } />
				<TableNav total={ total } selected={ table.selected } table={ table } onChangePage={ this.props.onChangePage } onAction={ this.props.onAction } bulk={ bulk }>
					<TableFilter selected="0" options={ this.getGroups( group.rows ) } isEnabled={ group.status === STATUS_COMPLETE } onFilter={ this.props.onFilter } />
				</TableNav>
				<Table headers={ headers } rows={ rows } total={ total } row={ this.renderRow } table={ table } status={ status } error={ error } onSetAllSelected={ this.props.onSetAllSelected } onSetOrderBy={ this.props.onSetOrderBy } />
				<TableNav total={ total } selected={ table.selected } table={ table } onChangePage={ this.props.onChangePage } onAction={ this.props.onAction } />

				<h1>{ __( 'Add new redirection' ) }</h1>
				<div className="add-new edit">
					<EditRedirect item={ getDefaultItem( '', 0 ) } saveButton={ __( 'Add Redirect' ) } />
				</div>
			</div>
		);
	}
}

function mapStateToProps( state ) {
	const { redirect, group } = state;

	return {
		redirect,
		group,
	};
}

function mapDispatchToProps( dispatch ) {
	return {
		onLoadGroups: () => {
			dispatch( getGroup() );
		},
		onLoadRedirects: () => {
			dispatch( getRedirect() );
		},
		onSearch: search => {
			dispatch( setSearch( search ) );
		},
		onChangePage: page => {
			dispatch( setPage( page ) );
		},
		onAction: action => {
			dispatch( performTableAction( action ) );
		},
		onSetAllSelected: onoff => {
			dispatch( setAllSelected( onoff ) );
		},
		onSetOrderBy: ( column, direction ) => {
			dispatch( setOrderBy( column, direction ) );
		},
		onFilter: groupId => {
			dispatch( setFilter( 'group', groupId ) );
		},
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps,
)( Redirects );
