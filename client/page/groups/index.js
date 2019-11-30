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
import SearchBox from 'component/search-box';
import BulkAction from 'component/table/bulk-action';
import TableDisplay from 'component/table/table-display';
import GroupRow from './row';
import { getGroup, createGroup, setPage, performTableAction, setAllSelected, setOrderBy, setFilter, setDisplay } from 'state/group/action';
import { STATUS_COMPLETE, STATUS_IN_PROGRESS, STATUS_SAVING } from 'state/settings/type';
import { getModules } from 'state/io/selector';
import { getFilterOptions, getDisplayGroups, getDisplayOptions, getHeaders, getBulk, getSearchOptions } from './constants';
import Select from 'component/select';
import MultiOptionDropdown from 'component/multi-option-dropdown';
import { isEnabled } from 'component/table/utils';
import { has_capability, CAP_GROUP_ADD } from 'lib/capabilities';
import './style.scss';

class Groups extends React.Component {
	constructor( props ) {
		super( props );

		this.state = { name: '', moduleId: 1 };
	}

	componentDidMount() {
		this.props.onLoadGroups();
	}

	onRenderRow = ( row, key, status, currentDisplayType, currentDisplaySelected ) => {
		const { saving } = this.props.group;
		const loadingStatus = status.isLoading ? STATUS_IN_PROGRESS : STATUS_COMPLETE;
		const rowStatus = saving.indexOf( row.id ) !== -1 ? STATUS_SAVING : loadingStatus;

		return (
			<GroupRow
				item={ row }
				key={ row.id }
				selected={ status.isSelected }
				status={ rowStatus }
				currentDisplayType={ currentDisplayType }
				currentDisplaySelected={ currentDisplaySelected }
				setFilter={ this.setFilter }
				filters={ this.props.group.table.filterBy }
			/>
		);
	}

	setFilter = ( filterName, filterValue ) => {
		const { filterBy } = this.props.group.table;

		this.props.onFilter( { ...filterBy, [ filterName ]: filterValue ? filterValue : undefined } );
	}

	getHeaders( selected ) {
		return getHeaders().filter( header => isEnabled( selected, header.name ) || header.name === 'cb' || header.name === 'name' );
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

	onSearch = ( search, type ) => {
		const filterBy = { ...this.props.group.table.filterBy };

		getSearchOptions().map( item => delete filterBy[ item.name ] );

		if ( search ) {
			filterBy[ type ] = search;
		}

		this.props.onFilter( filterBy );
	}

	validateDisplay( selected ) {
		// Ensure we have at least source or title
		if ( selected.indexOf( 'name' ) === -1 ) {
			return selected.concat( [ 'name' ] );
		}

		return selected;
	}

	render() {
		const { status, total, table, rows, saving } = this.props.group;
		const isSaving = saving.indexOf( 0 ) !== -1;

		return (
			<React.Fragment>
				<div className="redirect-table-display">
					<TableDisplay
						disable={ status === STATUS_IN_PROGRESS }
						options={ getDisplayOptions() }
						groups={ getDisplayGroups() }
						store="group"
						currentDisplayType={ table.displayType }
						currentDisplaySelected={ table.displaySelected }
						setDisplay={ this.props.onSetDisplay }
						validation={ this.validateDisplay }
					/>
					<SearchBox
						status={ status }
						table={ table }
						onSearch={ this.onSearch }
						selected={ table.filterBy }
						searchTypes={ getSearchOptions() }
					/>
				</div>

				<TableNav total={ total } selected={ table.selected } table={ table } onChangePage={ this.props.onChangePage } onAction={ this.props.onAction } status={ status } bulk={ getBulk() }>
					<BulkAction>
						<MultiOptionDropdown
							options={ getFilterOptions( getModules() ) }
							selected={ table.filterBy ? table.filterBy : {} }
							onApply={ this.props.onFilter }
							title={ __( 'Filters' ) }
							isEnabled={ status !== STATUS_IN_PROGRESS }
						/>
					</BulkAction>
				</TableNav>

				<Table
					headers={ this.getHeaders( table.displaySelected ) }
					rows={ rows }
					total={ total }
					row={ this.onRenderRow }
					table={ table }
					status={ status }
					onSetAllSelected={ this.props.onSetAllSelected }
					onSetOrderBy={ this.props.onSetOrderBy }
					currentDisplayType={ table.displayType }
					currentDisplaySelected={ table.displaySelected }
				/>

				<TableNav total={ total } selected={ table.selected } table={ table } onChangePage={ this.props.onChangePage } onAction={ this.props.onAction } status={ status } />

				{ has_capability( CAP_GROUP_ADD ) && (
					<React.Fragment>
						<h2>{ __( 'Add Group' ) }</h2>
						<p>{ __( 'Use groups to organise your redirects. Groups are assigned to a module, which affects how the redirects in that group work. If you are unsure then stick to the WordPress module.' ) }</p>

						<form onSubmit={ this.onSubmit }>
							<table className="form-table redirect-groups">
								<tbody>
									<tr>
										<th>{ __( 'Name' ) }</th>
										<td>
											<input size="30" className="regular-text" type="text" name="name" value={ this.state.name } onChange={ this.onChange } disabled={ isSaving } />

											<Select name="group" value={ this.state.moduleId } onChange={ this.onModule } items={ getModules() } disabled={ isSaving } />

											&nbsp;
											<input className="button-primary" type="submit" name="add" value="Add" disabled={ isSaving || this.state.name === '' } />
										</td>
									</tr>
								</tbody>
							</table>

							{ parseInt( this.state.moduleId, 10 ) === 2 && <p>{ __( 'Note that you will need to set the Apache module path in your Redirection options.' ) }</p> }
						</form>
					</React.Fragment>
				) }
			</React.Fragment>
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
		onFilter: ( filterBy ) => {
			dispatch( setFilter( filterBy ) );
		},
		onCreate: item => {
			dispatch( createGroup( item ) );
		},
		onSetDisplay: ( displayType, displaySelected ) => {
			dispatch( setDisplay( displayType, displaySelected ) );
		},
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps,
)( Groups );
