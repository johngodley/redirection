/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import Modal from 'component/modal';

class DeletePlugin extends React.Component {
	constructor( props ) {
		super( props );

		this.state = { isModal: false };
		this.onSubmit = this.handleSubmit.bind( this );
		this.onClose = this.closeModal.bind( this );
		this.onDelete = this.handleDelete.bind( this );
	}

	handleSubmit( ev ) {
		this.setState( { isModal: true } );
		ev.preventDefault();
	}

	closeModal() {
		this.setState( { isModal: false } );
	}

	handleDelete() {
		this.props.onDelete();
		this.closeModal();
	}

	render() {
		return (
			<div className="wrap">
				<form action="" method="post" onSubmit={ this.onSubmit }>
					<h2>{ __( 'Delete Redirection' ) }</h2>

					<p>{ ( 'Selecting this option will delete all redirections, all logs, and any options associated with the Redirection plugin.  Make sure this is what you want to do.' ) }</p>
					<input className="button-primary button-delete" type="submit" name="delete" value={ __( 'Delete' ) } />
				</form>

				<Modal show={ this.state.isModal } onClose={ this.onClose }>
					<div>
						<h1>{ __( 'Delete the plugin - are you sure?' ) }</h1>
						<p>{ __( 'Deleting the plugin will remove all your redirections, logs, and settings. Do this if you want to remove the plugin for good, or if you want to reset the plugin.' ) }</p>
						<p>{ __( 'Once deleted your redirections will stop working. If they appear to continue working then please clear your browser cache.' ) }</p>
						<p>
							<button className="button-primary button-delete" onClick={ this.onDelete }>{ __( 'Yes! Delete the plugin' ) }</button> <button className="button-secondary" onClick={ this.onClose }>{ __( "No! Don't delete the plugin" ) }</button>
						</p>
					</div>
				</Modal>
			</div>
		);
	}
}

export default DeletePlugin;
