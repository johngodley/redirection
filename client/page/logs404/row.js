/**
 * External dependencies
 */

import React from 'react';
import { connect } from 'react-redux';
import { translate as __ } from 'lib/locale';

/**
 * Internal dependencies
 */
import { setFilter, setSelected, performTableAction, deleteExact } from 'state/error/action';
import RowActions from 'component/table/row-action';
import Referrer from './referrer';
import EditRedirect from 'page/redirects/edit';
import { getDefaultItem } from 'state/redirect/selector';
import Spinner from 'component/wordpress/spinner';
import { STATUS_IN_PROGRESS, STATUS_SAVING } from 'state/settings/type';
import Modal from 'component/modal';
import GeoMap from 'component/geo-map';
import Useragent from 'component/useragent';

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

	renderEdit() {
		return (
			<Modal onClose={ this.onClose } width="700">
				<div className="add-new">
					<EditRedirect item={ getDefaultItem( this.props.item.url, 0 ) } saveButton={ __( 'Add Redirect' ) } onCancel={ this.onClose } childSave={ this.onSave } autoFocus>
						<tr>
							<th>{ __( 'Delete 404s' ) }</th>
							<td>
								<label>
									<input type="checkbox" name="delete_log" checked={ this.state.delete_log } onChange={ this.onDeleteLog } />

									{ __( 'Delete all logs for this 404' ) }
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
		if ( ip ) {
			return (
				<a href={ 'https://redirect.li/map/?ip=' + encodeURIComponent( ip ) } onClick={ this.showMap }>
					{ ip }
				</a>
			);
		}

		return '-';
	}

	render() {
		const { created, created_time, ip, referrer, url, agent, id } = this.props.item;
		const { selected, status } = this.props;
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
				<td className="column-date">
					{ created }<br />{ created_time }
				</td>
				<td className="column-url column-primary">
					<a href={ url } rel="noreferrer noopener" target="_blank">{ url.substring( 0, 100 ) }</a>
					<RowActions disabled={ isSaving }>
						{ menu.reduce( ( prev, curr ) => [ prev, ' | ', curr ] ) }
					</RowActions>

					{ this.state.editing && this.renderEdit() }
					{ this.state.showMap && this.renderMap() }
					{ this.state.showAgent && this.renderAgent() }
				</td>
				<td className="column-referrer">
					<Referrer url={ referrer } />
					{ referrer && <br /> }
					<span>{ agent }</span>
				</td>
				<td className="column-ip">
					{ this.renderIp( ip ) }

					<RowActions>
						{ ip && <a href="#" onClick={ this.onShow }>{ __( 'Filter by IP' ) }</a> }
					</RowActions>
				</td>
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
			dispatch( deleteExact( 'url-exact', filter ) );
		},
	};
}

function mapStateToProps( state ) {
	const { status: infoStatus } = state.info;

	return {
		infoStatus,
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps
)( LogRow404 );
