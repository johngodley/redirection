/**
 * External dependencies
 *
 * @format
 */

import React from 'react';
import { connect } from 'react-redux';

/**
 * Internal dependencies
 */

import Table from 'component/table';
import TableNav from 'component/table/navigation';
import SearchBox from 'component/table/search';
import TableGroup from 'component/table/group';
import DeleteAll from 'page/logs/delete-all';
import ExportCSV from 'page/logs/export-csv';
import Row404 from './row';
import RowUrl from './row-url';
import RowIp from './row-ip';
import TableButtons from 'component/table/table-buttons';
import CreateRedirect from './create-redirect';
import { LOGS_TYPE_404 } from 'state/error/type';
import { tableKey } from 'lib/table';
import { getGroup } from 'state/group/action';
import {
	loadLogs,
	deleteAll,
	setSearch,
	setPage,
	performTableAction,
	setAllSelected,
	setOrderBy,
	setGroupBy,
	setSelected,
} from 'state/error/action';
import { STATUS_COMPLETE, STATUS_IN_PROGRESS, STATUS_SAVING } from 'state/settings/type';
import { MATCH_IP, ACTION_URL, ACTION_ERROR, MATCH_URL, ACTION_NOTHING } from 'state/redirect/selector';
import { getBulk, getGroupBy, getHeaders } from './constants';

class Logs404 extends React.Component {
	constructor( props ) {
		super( props );

		props.onLoad();

		this.props.onLoadGroups();
		this.state = { create: null };
	}

	onRenderRow = ( row, key, status ) => {
		const { saving, table } = this.props.error;
		const loadingStatus = status.isLoading ? STATUS_IN_PROGRESS : STATUS_COMPLETE;
		const rowStatus = saving.indexOf( row.id ) !== -1 ? STATUS_SAVING : loadingStatus;

		if ( status.isLoading ) {
			return null;
		}

		if ( table.groupBy === 'url' ) {
			return <RowUrl item={ row } key={ key } selected={ status.isSelected } status={ rowStatus } onCreate={ this.onCreate } />;
		} else if ( table.groupBy === 'ip' ) {
			return <RowIp item={ row } key={ key } selected={ status.isSelected } status={ rowStatus } onCreate={ this.onCreate } />;
		}

		return <Row404 item={ row } key={ key } selected={ status.isSelected } status={ rowStatus } onCreate={ this.onCreate } />;
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

	render() {
		const { status, total, table, rows } = this.props.error;
		const { create } = this.state;

		return (
			<div>
				{ create && <CreateRedirect onClose={ this.onClose } create={ create } /> }

				<SearchBox status={ status } table={ table } onSearch={ this.props.onSearch } key={ tableKey( table ) } />
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
						options={ getGroupBy() }
						isEnabled={ status !== STATUS_IN_PROGRESS }
						onGroup={ this.props.onGroup }
						key={ table.groupBy }
					/>
				</TableNav>

				<Table
					headers={ getHeaders( table.groupBy ) }
					rows={ rows }
					total={ total }
					row={ this.onRenderRow }
					table={ table }
					status={ status }
					onSetAllSelected={ this.props.onSetAllSelected }
					onSetOrderBy={ this.props.onSetOrderBy }
				/>

				<TableNav
					total={ total }
					selected={ table.selected }
					table={ table }
					status={ status }
					onChangePage={ this.props.onChangePage }
					onAction={ this.props.onTableAction }
				>
					<TableButtons enabled={ rows.length > 0 }>
						<ExportCSV logType={ LOGS_TYPE_404 } />
						<DeleteAll onDelete={ this.props.onDeleteAll } table={ table } />
					</TableButtons>
				</TableNav>
			</div>
		);
	}
}

function mapStateToProps( state ) {
	const { error } = state;

	return {
		error,
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
		onSearch: ( search, filterBy ) => {
			dispatch( setSearch( search, filterBy ) );
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
	};
}

export default connect( mapStateToProps, mapDispatchToProps )( Logs404 );
