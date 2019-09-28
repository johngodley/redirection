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
import SearchBox from 'component/search-box';
import TableDisplay from 'component/table/table-display';
import BulkAction from 'component/table/bulk-action';
import Select from 'component/select';
import MultiOptionDropdown from 'component/multi-option-dropdown';
import RedirectRow from './row';
import EditRedirect from 'component/redirect-edit';
import {
	getRedirect,
	setPage,
	performTableAction,
	setAllSelected,
	setOrderBy,
	setFilter,
	setDisplay,
} from 'state/redirect/action';
import { getGroup } from 'state/group/action';
import { getDefaultItem } from 'state/redirect/selector';
import { getFlags } from 'state/settings/selector';
import { STATUS_COMPLETE, STATUS_SAVING, STATUS_IN_PROGRESS } from 'state/settings/type';
import { nestedGroups } from 'state/group/selector';
import { getDisplayGroups, getDisplayOptions, getBulk, getHeaders, getFilterOptions, getSearchOptions } from './constants';
import { isEnabled } from 'component/table/utils';

class Redirects extends React.Component {
	componentDidMount() {
		this.props.onLoadRedirects();
		this.props.onLoadGroups();
	}

	renderRow = ( row, key, status, currentDisplayType, currentDisplaySelected ) => {
		const { saving } = this.props.redirect;
		const loadingStatus = status.isLoading ? STATUS_IN_PROGRESS : STATUS_COMPLETE;
		const rowStatus = saving.indexOf( row.id ) !== -1 ? STATUS_SAVING : loadingStatus;

		return (
			<RedirectRow
				item={ row }
				key={ key }
				selected={ status.isSelected }
				status={ rowStatus }
				currentDisplayType={ currentDisplayType }
				currentDisplaySelected={ currentDisplaySelected }
				defaultFlags={ this.props.defaultFlags }
				filters={ this.props.redirect.table.filterBy }
			/>
		);
	}

	getGroupOptions( groups ) {
		return nestedGroups( groups ).map( item => ( { label: item.label, options: item.value } ) );
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

	getHeaders( selected ) {
		return getHeaders().filter( header => isEnabled( selected, header.name ) || header.name === 'cb' || header.name === 'url' );
	}

	validateDisplay( selected ) {
		// Ensure we have at least source or title
		if ( selected.indexOf( 'title' ) === -1 && selected.indexOf( 'source' ) === -1 ) {
			return selected.concat( [ 'title' ] );
		}

		return selected;
	}

	setFilter = ( filterName, filterValue ) => {
		const { filterBy } = this.props.group.table;

		this.props.onFilter( { ...filterBy, [ filterName ]: filterValue ? filterValue : undefined } );
	}

	onSearch = ( search, type ) => {
		const filterBy = { ...this.props.redirect.table.filterBy };

		getSearchOptions().map( item => delete filterBy[ item.name ] );

		if ( search ) {
			filterBy[ type ] = search;
		}

		this.props.onFilter( filterBy );
	}

	onGroup = ev => {
		this.setFilter( 'group', parseInt( ev.target.value, 10 ) === 0 ? undefined : parseInt( ev.target.value, 10 ) );
	}

	getGroups( groups ) {
		return [
			{
				value: 0,
				label: __( 'All groups' ),
			},
		].concat( nestedGroups( groups ) );
	}

	render() {
		const { status, total, table, rows, addTop } = this.props.redirect;
		const { group } = this.props;
		const canAdd = status === STATUS_COMPLETE && group.status === STATUS_COMPLETE;

		return (
			<div className="redirects">
				{ addTop && this.renderNew() }

				<div className="redirect-table-display">
					<TableDisplay
						disable={ status === STATUS_IN_PROGRESS }
						options={ getDisplayOptions() }
						groups={ getDisplayGroups() }
						store="redirect"
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

				<TableNav
					total={ total }
					selected={ table.selected }
					table={ table }
					onChangePage={ this.props.onChangePage }
					onAction={ this.props.onAction }
					bulk={ getBulk() }
					status={ status }
				>
					{ group.rows.length > 1 && <BulkAction>
						<Select
							name="group"
							items={ this.getGroups( group.rows ) }
							value={ table.filterBy.group ? table.filterBy.group : 0 }
							onChange={ this.onGroup }
						/>
					</BulkAction> }

					<BulkAction>
						<MultiOptionDropdown
							options={ getFilterOptions() }
							selected={ table.filterBy ? table.filterBy : {} }
							onApply={ this.props.onFilter }
							title={ __( 'Filters' ) }
							isEnabled={ this.canFilter( group, status ) }
							badges
						/>
					</BulkAction>
				</TableNav>
				<Table
					headers={ this.getHeaders( table.displaySelected ) }
					rows={ rows }
					total={ total }
					row={ this.renderRow }
					table={ table }
					status={ status }
					onSetAllSelected={ this.props.onSetAllSelected }
					onSetOrderBy={ this.props.onSetOrderBy }
					currentDisplayType={ table.displayType }
					currentDisplaySelected={ table.displaySelected }
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
		onSetDisplay: ( displayType, displaySelected ) => {
			dispatch( setDisplay( displayType, displaySelected ) );
		},
	};
}

export default connect( mapStateToProps, mapDispatchToProps )( Redirects );
