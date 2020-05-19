/**
 * External dependencies
 */

import React from 'react';
import { connect } from 'react-redux';
import { translate as __ } from 'lib/locale';

/**
 * Internal dependencies
 */

import Table from 'component/table';
import TableNav from 'component/table/navigation';
import SearchBox from 'component/search-box';
import TableGroup from 'component/table/group';
import DeleteAll from 'page/logs/delete-all';
import TableDisplay from 'component/table/table-display';
import BulkAction from 'component/table/bulk-action';
import MultiOptionDropdown from 'component/multi-option-dropdown';
import LogRow from './row';
import RowUrl from './row-url';
import RowIp from './row-ip';
import { STATUS_COMPLETE, STATUS_IN_PROGRESS, STATUS_SAVING } from 'state/settings/type';
import { getOption } from 'state/settings/selector';
import {
	loadLogs,
	deleteAll,
	setPage,
	performTableAction,
	setAllSelected,
	setOrderBy,
	setGroupBy,
	setFilter,
	setDisplay,
} from 'state/log/action';
import TableButtons from 'component/table/table-buttons';
import { getRssUrl } from 'lib/wordpress-url';
import { getHeaders, getBulk, getDisplayOptions, getDisplayGroups, getSearchOptions, getFilterOptions, getGroupBy } from './constants';
import { isEnabled } from 'component/table/utils';
import { has_capability, CAP_LOG_DELETE } from 'lib/capabilities';

// XXX try and merge with 404
class Logs extends React.Component {
	componentDidMount() {
		this.props.onLoad( this.props.log.table );
	}

	renderRow = ( row, key, status, currentDisplayType, currentDisplaySelected ) => {
		const { saving, table } = this.props.log;
		const loadingStatus = status.isLoading ? STATUS_IN_PROGRESS : STATUS_COMPLETE;
		const rowStatus = saving.indexOf( row.id ) !== -1 ? STATUS_SAVING : loadingStatus;

		if ( status.isLoading ) {
			return null;
		}

		const props = {
			item: row,
			key,
			selected: status.isSelected,
			status: rowStatus,
			onCreate: this.onCreate,
			currentDisplayType,
			currentDisplaySelected,
			filters: this.props.log.table.filterBy,
			setFilter: this.setFilter,
		};

		if ( table.groupBy === 'url' ) {
			return <RowUrl { ...props } />;
		} else if ( table.groupBy === 'ip' ) {
			return <RowIp { ...props } />;
		}

		return <LogRow { ...props } />;
	}

	setFilter = ( filterName, filterValue ) => {
		const { filterBy } = this.props.log.table;

		this.props.onFilter( { ...filterBy, [ filterName ]: filterValue ? filterValue : undefined } );
	}

	onSearch = ( search, type ) => {
		const filterBy = { ...this.props.log.table.filterBy };

		getSearchOptions().map( item => delete filterBy[ item.name ] );

		if ( search ) {
			filterBy[ type ] = search;
		}

		this.props.onFilter( filterBy );
	}

	getHeaders( selected, groupBy ) {
		return getHeaders( groupBy ).filter( header => isEnabled( selected, header.name ) || [ 'cb', 'url', 'total', 'ipx' ].indexOf( header.name ) !== -1 );
	}

	validateDisplay( selected ) {
		// Ensure we have at least source
		if ( selected.indexOf( 'url' ) === -1 ) {
			return selected.concat( [ 'url' ] );
		}

		return selected;
	}

	render() {
		const { status, total, table, rows } = this.props.log;

		return (
			<React.Fragment>
				<div className="redirect-table-display">
					<TableDisplay
						disable={ status === STATUS_IN_PROGRESS }
						options={ getDisplayOptions() }
						groups={ getDisplayGroups() }
						store="log"
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

				<TableNav total={ total } selected={ table.selected } table={ table } status={ status } onChangePage={ this.props.onChangePage } onAction={ this.props.onTableAction } bulk={ getBulk() }>
					<TableGroup
						selected={ table.groupBy ? table.groupBy : '0' }
						options={ getGroupBy( this.props.settings.values.ip_logging ) }
						isEnabled={ status !== STATUS_IN_PROGRESS }
						onGroup={ this.props.onGroup }
						key={ table.groupBy }
					/>

					<BulkAction>
						<MultiOptionDropdown
							options={ getFilterOptions() }
							selected={ table.filterBy ? table.filterBy : {} }
							onApply={ this.props.onFilter }
							title={ __( 'Filters' ) }
							isEnabled={ status !== STATUS_IN_PROGRESS }
							badges
						/>
					</BulkAction>
				</TableNav>

				<Table
					headers={ this.getHeaders( table.displaySelected, table.groupBy ) }
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

				<TableNav total={ total } selected={ table.selected } table={ table } status={ status } onChangePage={ this.props.onChangePage } onAction={ this.props.onTableAction }>
					<TableButtons enabled={ rows.length > 0 }>
						{ this.props.token && <div className="table-button-item"><a href={ getRssUrl( this.props.token ) } className="button-secondary">RSS</a></div> }
						{ has_capability( CAP_LOG_DELETE ) && Object.keys( table.filterBy ).length === 0 && (
							<DeleteAll onDelete={ this.props.onDeleteAll } table={ table } />
						) }
					</TableButtons>
				</TableNav>
			</React.Fragment>
		);
	}
}

function mapStateToProps( state ) {
	const { log, settings } = state;

	return {
		log,
		settings,
		token: getOption( state, 'token' ),
	};
}

function mapDispatchToProps( dispatch ) {
	return {
		onLoad: params => {
			dispatch( loadLogs( params ) );
		},
		onDeleteAll: ( filterBy ) => {
			dispatch( deleteAll( filterBy ) );
		},
		onChangePage: page => {
			dispatch( setPage( page ) );
		},
		onTableAction: action => {
			dispatch( performTableAction( action ) );
		},
		onGroup: groupBy => {
			dispatch( setGroupBy( groupBy ) );
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

export default connect(
	mapStateToProps,
	mapDispatchToProps
)( Logs );
