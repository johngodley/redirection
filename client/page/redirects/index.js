/**
 * External dependencies
 *
 * @format
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import { connect } from 'react-redux';
import classnames from 'classnames';

/**
 * Internal dependencies
 */

import Table from 'component/table';
import TableNav from 'component/table/navigation';
import SearchBox from 'component/table/search';
import TableFilter from 'component/table/filter';
import { tableKey } from 'lib/table';
import RedirectRow from './row';
import EditRedirect from 'component/redirect-edit';
import {
	getRedirect,
	setPage,
	setSearch,
	performTableAction,
	setAllSelected,
	setOrderBy,
	setFilter,
} from 'state/redirect/action';
import { getGroup } from 'state/group/action';
import { getDefaultItem } from 'state/redirect/selector';
import { getFlags } from 'state/settings/selector';
import { STATUS_COMPLETE, STATUS_SAVING, STATUS_IN_PROGRESS } from 'state/settings/type';
import { nestedGroups } from 'state/group/selector';

const getHeaders = () => [
	{
		name: 'cb',
		check: true,
	},
	{
		name: 'code',
		title: __( 'Type' ),
		sortable: false,
	},
	{
		name: 'url',
		title: __( 'URL' ),
		primary: true,
	},
	{
		name: 'position',
		title: __( 'Pos' ),
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

const getBulk = () => [
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
	{
		id: 'reset',
		name: __( 'Reset hits' ),
	},
];

class Redirects extends React.Component {
	constructor( props ) {
		super( props );

		this.handleRender = this.renderRow.bind( this );
		this.props.onLoadRedirects();
		this.props.onLoadGroups();
	}

	renderRow( row, key, status ) {
		const { saving } = this.props.redirect;
		const loadingStatus = status.isLoading ? STATUS_IN_PROGRESS : STATUS_COMPLETE;
		const rowStatus = saving.indexOf( row.id ) !== -1 ? STATUS_SAVING : loadingStatus;

		return (
			<RedirectRow item={ row } key={ key } selected={ status.isSelected } status={ rowStatus } />
		);
	}

	getGroups( groups ) {
		return [
			{
				value: 0,
				text: __( 'All groups' ),
			},
		].concat( nestedGroups( groups ) );
	}

	renderNew() {
		const { addTop } = this.props.redirect;
		const classes = classnames( {
			'add-new': true,
			edit: true,
			addTop,
		} );

		return (
			<React.Fragment>
				{ ! addTop && <h2>{ __( 'Add new redirection' ) }</h2> }
				<div className={ classes }>
					<EditRedirect
						item={ getDefaultItem( '', 0, this.props.defaultFlags ) }
						saveButton={ __( 'Add Redirect' ) }
						autoFocus={ addTop }
					/>
				</div>
			</React.Fragment>
		);
	}

	canFilter( group, status ) {
		if ( group.status !== STATUS_COMPLETE ) {
			return false;
		}

		if ( status === STATUS_IN_PROGRESS ) {
			return false;
		}

		return true;
	}

	render() {
		const { status, total, table, rows, addTop } = this.props.redirect;
		const { group } = this.props;
		const canAdd = status === STATUS_COMPLETE && group.status === STATUS_COMPLETE;

		return (
			<div className="redirects">
				{ addTop && this.renderNew() }

				<SearchBox
					status={ status }
					table={ table }
					onSearch={ this.props.onSearch }
					ignoreFilter={ [ 'group' ] }
				/>
				<TableNav
					total={ total }
					selected={ table.selected }
					table={ table }
					onChangePage={ this.props.onChangePage }
					onAction={ this.props.onAction }
					bulk={ getBulk() }
					status={ status }
				>
					<TableFilter
						selected={ table.filter ? table.filter : '0' }
						options={ this.getGroups( group.rows ) }
						isEnabled={ this.canFilter( group, status ) }
						onFilter={ this.props.onFilter }
						key={ tableKey( table ) }
					/>
				</TableNav>
				<Table
					headers={ getHeaders() }
					rows={ rows }
					total={ total }
					row={ this.handleRender }
					table={ table }
					status={ status }
					onSetAllSelected={ this.props.onSetAllSelected }
					onSetOrderBy={ this.props.onSetOrderBy }
				/>
				<TableNav
					total={ total }
					selected={ table.selected }
					table={ table }
					onChangePage={ this.props.onChangePage }
					onAction={ this.props.onAction }
					status={ status }
				/>

				{ canAdd && ! addTop && this.renderNew() }
			</div>
		);
	}
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
		onLoadRedirects: args => {
			dispatch( getRedirect( args ) );
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

export default connect( mapStateToProps, mapDispatchToProps )( Redirects );
