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
import { tableKey } from 'lib/table';
import TableFilter from 'component/table/filter';
import GroupRow from './row';
import { getGroup, createGroup, setPage, setSearch, performTableAction, setAllSelected, setOrderBy, setFilter } from 'state/group/action';
import { STATUS_COMPLETE, STATUS_IN_PROGRESS, STATUS_SAVING } from 'state/settings/type';
import { getModules } from 'state/io/selector';
import Select from 'component/wordpress/select';

const getHeaders = () => [
	{
		name: 'cb',
		check: true,
	},
	{
		name: 'name',
		title: __( 'Name' ),
		primary: true,
	},
	{
		name: 'redirects',
		title: __( 'Redirects' ),
		sortable: false,
	},
	{
		name: 'module',
		title: __( 'Module' ),
		sortable: false,
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

class Groups extends React.Component {
	constructor( props ) {
		super( props );

		this.props.onLoadGroups();

		this.state = { name: '', moduleId: 1 };
	}

	onRenderRow = ( row, key, status ) => {
		const { saving } = this.props.group;
		const loadingStatus = status.isLoading ? STATUS_IN_PROGRESS : STATUS_COMPLETE;
		const rowStatus = saving.indexOf( row.id ) !== -1 ? STATUS_SAVING : loadingStatus;

		return <GroupRow item={ row } key={ key } selected={ status.isSelected } status={ rowStatus } />;
	}

	onChange = ev => {
		this.setState( { name: ev.target.value } );
	}

	onModule = ev => {
		this.setState( { moduleId: ev.target.value } );
	}

	onSubmit = ev => {
		ev.preventDefault();
		this.props.onCreate( { id: 0, name: this.state.name, moduleId: this.state.moduleId } );
		this.setState( { name: '' } );
	}

	getModules() {
		return [
			{
				value: '',
				text: __( 'All modules' ),
			},
		].concat( getModules() );
	}

	render() {
		const { status, total, table, rows, saving } = this.props.group;
		const isSaving = saving.indexOf( 0 ) !== -1;

		return (
			<div>
				<SearchBox status={ status } table={ table } onSearch={ this.props.onSearch } ignoreFilter={ [ 'module' ] } key={ tableKey( table ) } />
				<TableNav total={ total } selected={ table.selected } table={ table } onChangePage={ this.props.onChangePage } onAction={ this.props.onAction } status={ status } bulk={ bulk }>
					<TableFilter selected={ table.filter } options={ this.getModules() } onFilter={ this.props.onFilter } isEnabled={ true } key={ tableKey( table ) } />
				</TableNav>
				<Table headers={ getHeaders() } rows={ rows } total={ total } row={ this.onRenderRow } table={ table } status={ status } onSetAllSelected={ this.props.onSetAllSelected } onSetOrderBy={ this.props.onSetOrderBy } />
				<TableNav total={ total } selected={ table.selected } table={ table } onChangePage={ this.props.onChangePage } onAction={ this.props.onAction } status={ status } />

				<h2>{ __( 'Add Group' ) }</h2>
				<p>{ __( 'Use groups to organise your redirects. Groups are assigned to a module, which affects how the redirects in that group work. If you are unsure then stick to the WordPress module.' ) }</p>

				<form onSubmit={ this.onSubmit }>
					<table className="form-table">
						<tbody>
							<tr>
								<th style={ { width: '50px' } }>{ __( 'Name' ) }</th>
								<td>
									<input size="30" className="regular-text" type="text" name="name" value={ this.state.name } onChange={ this.onChange } disabled={ isSaving } />

									<Select name="id" value={ this.state.moduleId } onChange={ this.onModule } items={ getModules() } disabled={ isSaving } />

									&nbsp;
									<input className="button-primary" type="submit" name="add" value="Add" disabled={ isSaving || this.state.name === '' } />
								</td>
							</tr>
						</tbody>
					</table>
				</form>
			</div>
		);
	}
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
		onFilter: moduleId => {
			dispatch( setFilter( 'module', moduleId ) );
		},
		onCreate: item => {
			dispatch( createGroup( item ) );
		},
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps,
)( Groups );
