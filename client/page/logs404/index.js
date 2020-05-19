/**
 * External dependencies
 */

import React from 'react';
import { connect } from 'react-redux';

/**
 * Internal dependencies
 */

import Table from 'component/table';
import TableNav from 'component/table/navigation';
import SearchBox from 'component/search-box';
import TableGroup from 'component/table/group';
import DeleteAll from 'page/logs/delete-all';
import TableDisplay from 'component/table/table-display';
import Row404 from './row';
import RowUrl from './row-url';
import RowIp from './row-ip';
import TableButtons from 'component/table/table-buttons';
import CreateRedirect from './create-redirect';
import { getGroup } from 'state/group/action';
import {
	loadLogs,
	deleteAll,
	setFilter,
	setPage,
	performTableAction,
	setAllSelected,
	setOrderBy,
	setGroupBy,
	setSelected,
	setDisplay,
} from 'state/error/action';
import { STATUS_COMPLETE, STATUS_IN_PROGRESS, STATUS_SAVING } from 'state/settings/type';
import { MATCH_IP, ACTION_URL, ACTION_ERROR, MATCH_URL, ACTION_NOTHING } from 'state/redirect/selector';
import { getHeaders, getBulk, getDisplayOptions, getDisplayGroups, getGroupBy, getSearchOptions } from './constants';
import { isEnabled } from 'component/table/utils';
import { has_capability, CAP_404_DELETE } from 'lib/capabilities';

class Logs404 extends React.Component {
	constructor( props ) {
		super( props );

		this.state = { create: null };
	}

	componentDidMount() {
		this.props.onLoad();
		this.props.onLoadGroups();
	}

	renderRow = ( row, key, status, currentDisplayType, currentDisplaySelected ) => {
		const { saving, table } = this.props.error;
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
			defaultFlags: this.props.defaultFlags,
			filters: this.props.error.table.filterBy,
		};

		if ( table.groupBy === 'url' ) {
			return <RowUrl { ...props } />;
		} else if ( table.groupBy === 'ip' ) {
			return <RowIp { ...props } />;
		}

		return <Row404 { ...props } />;
	}

	onCreate = ( id, create ) => {
		this.props.onSetAllSelected( false );
		this.props.onSetSelected( id );
		this.setState( { create } );
	}

	onClose = () => {
		this.props.onSetAllSelected( false );
		this.setState( { create: false } );
	}

	onBulk = action => {
		const { table } = this.props.error;

		if ( action === 'redirect-ip' ) {
			const create = { regex: true, match_type: MATCH_IP, action_type: ACTION_URL, action_data: { ip: table.selected } };

			this.setState( { create } );
		} else if ( action === 'block' ) {
			const create = { regex: true, match_type: MATCH_IP, action_type: ACTION_ERROR, action_data: { ip: table.selected }, action_code: 403 };

			this.setState( { create } );
		} else if ( action === 'redirect-url' ) {
			const create = { match_type: MATCH_URL, action_type: ACTION_URL };

			this.setState( { create } );
		} else if ( action === 'ignore' ) {
			const create = { match_type: MATCH_URL, action_type: ACTION_NOTHING };

			this.setState( { create } );
		} else {
			this.props.onTableAction( action );
		}
	}

	onSearch = ( search, type ) => {
		const filterBy = { ...this.props.error.table.filterBy };

		getSearchOptions().map( item => delete filterBy[ item.name ] );

		if ( search ) {
			filterBy[ type ] = search;
		}

		this.props.onFilter( filterBy );
	}

	transformRow = id => {
		const { rows } = this.props.error;
		const found = rows.find( item => item.id === id );

		if ( found ) {
			return found.url ? found.url : found.id;
		}

		return '';
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

	canDeleteAll( filters, groupBy ) {
		if ( filters.url !== undefined ) {
			return true;
		}

		if ( groupBy ) {
			return false;
		}

		return Object.keys( filters ).length === 0 ;
	}

	render() {
		const { status, total, table, rows } = this.props.error;
		const { create } = this.state;

		return (
			<>
				{ create && <CreateRedirect onClose={ this.onClose } create={ create } transform={ this.transformRow } /> }

				<div className="redirect-table-display">
					<TableDisplay
						disable={ status === STATUS_IN_PROGRESS }
						options={ getDisplayOptions() }
						groups={ getDisplayGroups() }
						store="404s"
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
					status={ status }
					onChangePage={ this.props.onChangePage }
					onAction={ this.onBulk }
					bulk={ getBulk( table.groupBy ) }
				>
					<TableGroup
						selected={ table.groupBy ? table.groupBy : '0' }
						options={ getGroupBy( this.props.settings.values.ip_logging ) }
						isEnabled={ status !== STATUS_IN_PROGRESS }
						onGroup={ this.props.onGroup }
						key={ table.groupBy }
					/>
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

				<TableNav
					total={ total }
					selected={ table.selected }
					table={ table }
					status={ status }
					onChangePage={ this.props.onChangePage }
					onAction={ this.props.onTableAction }
				>
					{ has_capability( CAP_404_DELETE ) && this.canDeleteAll( table.filterBy, table.groupBy ) && (
						<TableButtons enabled={ rows.length > 0 }>
							<DeleteAll onDelete={ this.props.onDeleteAll } table={ table } />
						</TableButtons>
					) }
				</TableNav>
			</>
		);
	}
}

function mapStateToProps( state ) {
	const { error, settings } = state;

	return {
		error,
		settings,
	};
}

function mapDispatchToProps( dispatch ) {
	return {
		onLoad: () => {
			dispatch( loadLogs() );
		},
		onLoadGroups: () => {
			dispatch( getGroup() );
		},
		onDeleteAll: ( filterBy, filter ) => {
			dispatch( deleteAll( filterBy, filter ) );
		},
		onChangePage: page => {
			dispatch( setPage( page ) );
		},
		onTableAction: action => {
			dispatch( performTableAction( action, null ) );
		},
		onSetAllSelected: onoff => {
			dispatch( setAllSelected( onoff ) );
		},
		onSetOrderBy: ( column, direction ) => {
			dispatch( setOrderBy( column, direction ) );
		},
		onGroup: groupBy => {
			dispatch( setGroupBy( groupBy ) );
		},
		onSetSelected: items => {
			dispatch( setSelected( items ) );
		},
		onFilter: ( filterBy ) => {
			dispatch( setFilter( filterBy ) );
		},
		onSetDisplay: ( displayType, displaySelected ) => {
			dispatch( setDisplay( displayType, displaySelected ) );
		},
	};
}

export default connect( mapStateToProps, mapDispatchToProps )( Logs404 );
