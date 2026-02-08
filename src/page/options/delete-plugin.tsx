import { useState } from 'react';
import { __ } from '@wordpress/i18n';
import { Modal } from '@wp-plugin-components';

interface DeletePluginProps {
	onDelete: () => void;
}

function DeletePlugin( props: DeletePluginProps ) {
	const [ isModal, setIsModal ] = useState( false );

	function handleSubmit( ev: React.FormEvent ) {
		setIsModal( true );
		ev.preventDefault();
	}

	function closeModal() {
		setIsModal( false );
	}

	function handleDelete() {
		props.onDelete();
		closeModal();
	}

	function showModal() {
		return (
			<Modal onClose={ closeModal }>
				<div style={ { padding: '15px 10px 5px 15px' } }>
					<h1>{ __( 'Delete the plugin - are you sure?', 'redirection' ) }</h1>
					<p>
						{ __(
							'Deleting the plugin will remove all your redirections, logs, and settings. Do this if you want to remove the plugin for good, or if you want to reset the plugin.',
							'redirection'
						) }
					</p>
					<p>
						{ __(
							'Once deleted your redirections will stop working. If they appear to continue working then please clear your browser cache.',
							'redirection'
						) }
					</p>
					<p>
						<button className="button-primary button-delete" onClick={ handleDelete }>
							{ __( 'Yes! Delete the plugin', 'redirection' ) }
						</button>{ ' ' }
						<button className="button-secondary" onClick={ closeModal }>
							{ __( "No! Don't delete the plugin" ) }
						</button>
					</p>
				</div>
			</Modal>
		);
	}

	return (
		<div className="wrap">
			<form action="" method="post" onSubmit={ handleSubmit }>
				<h2>{ __( 'Delete Redirection', 'redirection' ) }</h2>

				<p>
					{ __(
						'Selecting this option will delete all redirections, all logs, and any options associated with the Redirection plugin. Make sure this is what you want to do.',
						'redirection'
					) }
				</p>
				<input
					className="button-secondary button-delete"
					type="submit"
					name="delete"
					value={ __( 'Delete', 'redirection' ) }
				/>
			</form>

			{ isModal && showModal() }
		</div>
	);
}

export default DeletePlugin;
