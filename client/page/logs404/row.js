/**
 * External dependencies
 */

import React from 'react';
import { connect } from 'react-redux';
import { translate as __ } from 'lib/locale';
import Highlighter from 'react-highlight-words';

/**
 * Internal dependencies
 */
import { setFilter, setSelected, performTableAction, deleteExact } from 'state/error/action';
import RowActions from 'component/table/row-action';
import Referrer from './referrer';
import EditRedirect from 'component/redirect-edit';
import { getDefaultItem } from 'state/redirect/selector';
import Spinner from 'component/spinner';
import { STATUS_IN_PROGRESS, STATUS_SAVING } from 'state/settings/type';
import Modal from 'component/modal';
import GeoMap from 'component/geo-map';
import Useragent from 'component/useragent';
import ExternalLink from 'component/external-link';
import Column from 'component/table/column';
import { getFlags } from 'state/settings/selector';

class LogRow404 extends React.Component {
	constructor( props ) {
		super( props );

		this.state = {
			editing: false,
			delete_log: false,
			showMap: false,
			showAgent: false,
		};
	}

	onSelect = () => {
		this.props.onSetSelected( [ this.props.item.id ] );
	}

	onDelete = ev => {
		ev.preventDefault();
		this.props.onDelete( this.props.item.id );
	}

	onShow = ev => {
		ev.preventDefault();
		this.props.onShowIP( this.props.item.ip );
	}

	onAdd = ev => {
		ev.preventDefault();
		this.setState( { editing: true } );
	}

	onClose = () => {
		this.setState( { editing: false } );
	}

	onDeleteLog = ev => {
		this.setState( { delete_log: ev.target.checked } );
	}

	onSave = () => {
		if ( this.state.delete_log ) {
			this.props.onDeleteFilter( this.props.item.url );
		}
	}

	setHeight = height => {
		this.setState( { height } );
	}

	renderEdit() {
		return (
			<Modal onClose={ this.onClose } width="700">
				<div className="add-new">
					<EditRedirect item={ getDefaultItem( this.props.item.url, 0, this.props.defaultFlags ) } saveButton={ __( 'Add Redirect' ) } onCancel={ this.onClose } callback={ this.setHeight } childSave={ this.onSave } autoFocus>
						<tr>
							<th>{ __( 'Delete 404s' ) }</th>
							<td className="edit-left" style={ { padding: '7px 0px' } }>
								<label>
									<input type="checkbox" name="delete_log" checked={ this.state.delete_log } onChange={ this.onDeleteLog } />

									{ __( 'Delete all logs for this entry' ) }
								</label>
							</td>
						</tr>
					</EditRedirect>
				</div>
			</Modal>
		);
	}

	renderMap() {
		return (
			<Modal onClose={ this.closeMap } padding={ false }>
				<GeoMap ip={ this.props.item.ip } />
			</Modal>
		);
	}

	renderAgent() {
		return (
			<Modal onClose={ this.closeAgent } width="800">
				<Useragent agent={ this.props.item.agent } />
			</Modal>
		);
	}

	showMap = ev => {
		ev.preventDefault();
		this.setState( { showMap: true } );
	}

	showAgent = ev => {
		ev.preventDefault();
		this.setState( { showAgent: true } );
	}

	closeMap = () => {
		this.setState( { showMap: false } );
	}

	closeAgent = () => {
		this.setState( { showAgent: false } );
	}

	renderIp( ip ) {
		if ( ! ip ) {
			return '-';
		}

		return (
			<a href={ 'https://redirect.li/map/?ip=' + encodeURIComponent( ip ) } onClick={ this.showMap }>
				<Highlighter searchWords={ [ this.props.filters.ip ] } textToHighlight={ ip } />
			</a>
		);
	}

	render() {
		const { created, created_time, ip, referrer, url, agent, id } = this.props.item;
		const { selected, status, currentDisplaySelected, filters } = this.props;
		const isLoading = status === STATUS_IN_PROGRESS;
		const isSaving = status === STATUS_SAVING;
		const hideRow = isLoading || isSaving;
		const menu = [
			<a href="#" onClick={ this.onDelete } key="0">{ __( 'Delete' ) }</a>,
			<a href="#" onClick={ this.onAdd } key="1">{ __( 'Add Redirect' ) }</a>,
		];

		if ( ip ) {
			menu.unshift( <a href={ 'https://redirect.li/map/?ip=' + encodeURIComponent( ip ) } onClick={ this.showMap } key="2">{ __( 'Geo Info' ) }</a> );
		}

		if ( agent ) {
			menu.unshift( <a href={ 'https://redirect.li/agent/?agent=' + encodeURIComponent( agent ) } onClick={ this.showAgent } key="3">{ __( 'Agent Info' ) }</a> );
		}

		return (
			<tr className={ hideRow ? 'disabled' : '' }>
				<th scope="row" className="check-column">
					{ ! isSaving && <input type="checkbox" name="item[]" value={ id } disabled={ isLoading } checked={ selected } onChange={ this.onSelect } /> }
					{ isSaving && <Spinner size="small" /> }
				</th>

				<Column enabled="date" className="column-date" selected={ currentDisplaySelected }>
					{ created }<br />{ created_time }
				</Column>

				<Column enabled="url" className="column-url column-primary" selected={ currentDisplaySelected }>
					<ExternalLink url={ url }>
						<Highlighter searchWords={ [ filters.url ] } textToHighlight={ url.substring( 0, 100 ) } />
					</ExternalLink>

					<RowActions disabled={ isSaving }>
						{ menu.reduce( ( prev, curr ) => [ prev, ' | ', curr ] ) }
					</RowActions>

					{ this.state.editing && this.renderEdit() }
					{ this.state.showMap && this.renderMap() }
					{ this.state.showAgent && this.renderAgent() }
				</Column>

				<Column enabled="referrer" className="column-referrer" selected={ currentDisplaySelected }>
					<Referrer url={ referrer } search={ filters.referrer } />
				</Column>

				<Column enabled="agent" className="column-agent" selected={ currentDisplaySelected }>
					<Highlighter searchWords={ [ filters.agent ] } textToHighlight={ agent || '' } />
				</Column>

				<Column enabled="ip" className="column-ip" selected={ currentDisplaySelected }>
					{ this.renderIp( ip ) }

					<RowActions>
						{ ip && <a href="#" onClick={ this.onShow }>{ __( 'Filter by IP' ) }</a> }
					</RowActions>
				</Column>
			</tr>
		);
	}
}

function mapDispatchToProps( dispatch ) {
	return {
		onShowIP: ip => {
			dispatch( setFilter( 'ip', ip ) );
		},
		onSetSelected: items => {
			dispatch( setSelected( items ) );
		},
		onDelete: item => {
			dispatch( performTableAction( 'delete', item ) );
		},
		onDeleteFilter: filter => {
			dispatch( deleteExact( [ filter ] ) );
		},
	};
}

function mapStateToProps( state ) {
	const { status: infoStatus } = state.info;

	return {
		infoStatus,
		defaultFlags: getFlags( state ),
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps
)( LogRow404 );
