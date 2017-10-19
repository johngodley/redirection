/**
 * External dependencies
 */

import React from 'react';
import { connect } from 'react-redux';
import { translate as __ } from 'lib/locale';

/**
 * Internal dependencies
 */
import { setFilter, setSelected, performTableAction, deleteExact } from 'state/log/action';
import RowActions from 'component/table/row-action';
import Referrer from './referrer';
import EditRedirect from 'component/redirects/edit';
import { getDefaultItem } from 'state/redirect/selector';
import Spinner from 'component/wordpress/spinner';
import { STATUS_IN_PROGRESS, STATUS_SAVING } from 'state/settings/type';
import Modal from 'component/modal';

class LogRow404 extends React.Component {
	constructor( props ) {
		super( props );

		this.handleSelected = this.onSelect.bind( this );
		this.handleDelete = this.onDelete.bind( this );
		this.handleAdd = this.onAdd.bind( this );
		this.handleShow = this.onShow.bind( this );
		this.handleClose = this.onClose.bind( this );
		this.handleSave = this.onSave.bind( this );
		this.handleDeleteLog = this.onDeleteLog.bind( this );

		this.state = {
			editing: false,
			delete_log: false,
		};
	}

	onSelect() {
		this.props.onSetSelected( [ this.props.item.id ] );
	}

	onDelete( ev ) {
		ev.preventDefault();
		this.props.onDelete( this.props.item.id );
	}

	onShow( ev ) {
		ev.preventDefault();
		this.props.onShowIP( this.props.item.ip );
	}

	onAdd( ev ) {
		ev.preventDefault();
		this.setState( { editing: true } );
	}

	onClose() {
		this.setState( { editing: false } );
	}

	onDeleteLog( ev ) {
		this.setState( { delete_log: ev.target.checked } );
	}

	onSave() {
		if ( this.state.delete_log ) {
			this.props.onDeleteFilter( this.props.item.url );
		}
	}

	renderEdit() {
		return (
			<Modal show={ this.state.editing } onClose={ this.handleClose } width="700">
				<div className="add-new">
					<EditRedirect item={ getDefaultItem( this.props.item.url, 0 ) } saveButton={ __( 'Add Redirect' ) } advanced={ false } onCancel={ this.handleClose } childSave={ this.handleSave } autoFocus>
						<tr>
							<th>{ __( 'Delete 404s' ) }</th>
							<td>
								<label>
									<input type="checkbox" name="delete_log" checked={ this.state.delete_log } onChange={ this.handleDeleteLog } />

									{ __( 'Delete all logs for this 404' ) }
								</label>
							</td>
						</tr>
					</EditRedirect>
				</div>
			</Modal>
		);
	}

	render() {
		const { created, ip, referrer, url, agent, id } = this.props.item;
		const { selected, status } = this.props;
		const isLoading = status === STATUS_IN_PROGRESS;
		const isSaving = status === STATUS_SAVING;
		const hideRow = isLoading || isSaving;

		return (
			<tr className={ hideRow ? 'disabled' : '' }>
				<th scope="row" className="check-column">
					{ ! isSaving && <input type="checkbox" name="item[]" value={ id } disabled={ isLoading } checked={ selected } onClick={ this.handleSelected } /> }
					{ isSaving && <Spinner size="small" /> }
				</th>
				<td>
					{ created }
					<RowActions disabled={ isSaving }>
						<a href="#" onClick={ this.handleDelete }>{ __( 'Delete' ) }</a> |&nbsp;
						<a href="#" onClick={ this.handleAdd }>{ __( 'Add Redirect' ) }</a>
					</RowActions>

					{ this.state.editing && this.renderEdit() }
				</td>
				<td>
					<a href={ url } rel="noreferrer noopener" target="_blank">{ url.substring( 0, 100 ) }</a>
				</td>
				<td>
					<Referrer url={ referrer } />
					{ agent && <RowActions>{ [ agent ] }</RowActions> }
				</td>
				<td>
					<a href={ 'http://urbangiraffe.com/map/?ip=' + ip } rel="noreferrer noopener" target="_blank">
						{ ip }
					</a>
					<RowActions>
						<a href="#" onClick={ this.handleShow }>{ __( 'Show only this IP' ) }</a>
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
			dispatch( performTableAction( 'delete', item, { logType: '404' } ) );
		},
		onDeleteFilter: filter => {
			dispatch( deleteExact( 'url-exact', filter ) );
		},
	};
}

export default connect(
	null,
	mapDispatchToProps
)( LogRow404 );
