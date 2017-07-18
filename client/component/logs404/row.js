/**
 * External dependencies
 */

import React from 'react';
import { connect } from 'react-redux';
import { translate as __ } from 'lib/locale';
import Modal from 'react-modal';

/**
 * Internal dependencies
 */
import { setFilter, setSelected, performTableAction } from 'state/log/action';
import RowActions from 'component/table/row-action';
import Referrer from './referrer';
import EditRedirect from 'component/redirects/edit';
import { getDefaultItem } from 'state/redirect/selector';

class LogRow404 extends React.Component {
	constructor( props ) {
		super( props );

		Modal.setAppElement( 'body' );

		this.handleSelected = this.onSelect.bind( this );
		this.handleDelete = this.onDelete.bind( this );
		this.handleAdd = this.onAdd.bind( this );
		this.handleShow = this.onShow.bind( this );
		this.handleClose = this.onClose.bind( this );

		this.state = { editing: false };
	}

	onSelect() {
		this.props.onSetSelected( [ this.props.item.id ] );
	}

	onDelete() {
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

	renderEdit() {
		return (
			<Modal isOpen={ this.state.editing } onRequestClose={ this.handleClose } contentLabel="Modal" overlayClassName="modal" className="modal-table">
				<div className="add-new">
					<EditRedirect item={ getDefaultItem( this.props.item.url, 0 ) } saveButton={ __( 'Add Redirect' ) } advanced={ false } onCancel={ this.handleClose } />
				</div>
			</Modal>
		);
	}

	render() {
		const { created, ip, referrer, url, agent, id } = this.props.item;
		const { selected, isLoading } = this.props;

		return (
			<tr className={ isLoading ? 'item-loading' : '' }>
				<th scope="row" className="check-column">
					<input type="checkbox" name="item[]" value={ id } disabled={ isLoading } checked={ selected } onClick={ this.handleSelected } />
				</th>
				<td>
					{ created }
					<RowActions>
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
			dispatch( performTableAction( 'delete', item ) );
		},
	};
}

export default connect(
	null,
	mapDispatchToProps
)( LogRow404 );
