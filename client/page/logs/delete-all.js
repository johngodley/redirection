/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import Modal from 'component/modal';
import PropTypes from 'prop-types';

class DeleteAll extends React.Component {
	static propTypes = {
		table: PropTypes.object.isRequired,
	};

	constructor( props ) {
		super( props );

		this.state = { isModal: false };
		this.onShow = this.showDelete.bind( this );
		this.onClose = this.closeModal.bind( this );
		this.onDelete = this.handleDelete.bind( this );
	}

	showDelete( ev ) {
		this.setState( { isModal: true } );
		ev.preventDefault();
	}

	closeModal() {
		this.setState( { isModal: false } );
	}

	handleDelete() {
		const { table } = this.props;

		this.setState( { isModal: false } );
		this.props.onDelete( table.filterBy );
	}

	getTitle( filterBy ) {
		if ( filterBy.ip ) {
			return __( 'Delete all from IP %s', {
				args: filterBy.ip,
			} );
		}

		if ( filterBy.url ) {
			return __( 'Delete all matching "%s"', {
				args: filterBy.url.substring( 0, 15 ),
			} );
		}

		return __( 'Delete All' );
	}

	render() {
		const { table } = this.props;
		const title = this.getTitle( table.filterBy );

		return (
			<div className="table-button-item">
				<input className="button" type="submit" name="" value={ title } onClick={ this.onShow } />

				{ this.state.isModal &&
					<Modal onClose={ this.onClose }>
						<div>
							<h1>{ __( 'Delete the logs - are you sure?' ) }</h1>
							<p>{ __( 'Once deleted your current logs will no longer be available. You can set a delete schedule from the Redirection options if you want to do this automatically.' ) }</p>
							<p>
								<button className="button-primary" onClick={ this.onDelete }>{ __( 'Yes! Delete the logs' ) }</button> <button className="button-secondary" onClick={ this.onClose }>{ __( "No! Don't delete the logs" ) }</button>
							</p>
						</div>
					</Modal>
				}
			</div>
		);
	}
}

export default DeleteAll;
