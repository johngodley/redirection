/**
 * External dependencies
 */

import React from 'react';
import { __ } from '@wordpress/i18n';
import { Modal } from 'wp-plugin-components';

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

	showModal() {
		return (
			<Modal onClose={ this.onClose }>
				<div style={ { padding: '15px 10px 5px 15px' } }>
					<h1>{ __( 'Delete the plugin - are you sure?', 'redirection' ) }</h1>
					<p>{ __( 'Deleting the plugin will remove all your redirections, logs, and settings. Do this if you want to remove the plugin for good, or if you want to reset the plugin.', 'redirection' ) }</p>
					<p>{ __( 'Once deleted your redirections will stop working. If they appear to continue working then please clear your browser cache.', 'redirection' ) }</p>
					<p>
						<button className="button-primary button-delete" onClick={ this.onDelete }>{ __( 'Yes! Delete the plugin', 'redirection' ) }</button> <button className="button-secondary" onClick={ this.onClose }>{ __( "No! Don't delete the plugin" ) }</button>
					</p>
				</div>
			</Modal>
		);
	}

	render() {
		return (
			<div className="wrap">
				<form action="" method="post" onSubmit={ this.onSubmit }>
					<h2>{ __( 'Delete Redirection', 'redirection' ) }</h2>

					<p>{ __( 'Selecting this option will delete all redirections, all logs, and any options associated with the Redirection plugin.  Make sure this is what you want to do.', 'redirection' ) }</p>
					<input className="button-primary button-delete" type="submit" name="delete" value={ __( 'Delete', 'redirection' ) } />
				</form>

				{ this.state.isModal && this.showModal() }
			</div>
		);
	}
}

export default DeletePlugin;
