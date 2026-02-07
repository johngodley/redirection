import { __ } from '@wordpress/i18n';
import TextareaAutosize from 'react-textarea-autosize';
import { useSettingsStore } from 'stores';
import { useFixStatus } from 'lib/api/hooks/use-settings';

interface ManualInstallProps {
	onCancel: () => void;
}

export default function ManualInstall( { onCancel }: ManualInstallProps ) {
	const loadStatus = useSettingsStore( ( state ) => state.loadStatus );
	const { mutate: fixStatus } = useFixStatus();

	function onComplete() {
		fixStatus( { reason: 'database', current: Redirectioni10n.database.next } );
	}

	return (
		<div className="redirection-database">
			<h1>{ __( 'Manual Install', 'redirection' ) }</h1>
			<p>
				{ __(
					'If your site needs special database permissions, or you would rather do it yourself, you can manually run the following SQL.',
					'redirection'
				) }{ ' ' }
				{ __( 'Click "Finished! 🎉" when finished.', 'redirection' ) }
			</p>
			<p>
				<TextareaAutosize
					readOnly
					cols={ 120 }
					value={ Redirectioni10n.database.manual.join( ';\n\n' ) + ';' }
					spellCheck={ false }
				/>
			</p>
			{ loadStatus === 'error' && (
				<div className="redirection-database_error wpl-error">
					<h3>{ __( 'Database problem', 'redirection' ) }</h3>
					<p>
						{ __(
							'The Redirection database does not appear to exist. Have you run the above SQL?',
							'redirection'
						) }
					</p>
				</div>
			) }
			<button className="button button-primary" onClick={ onComplete } type="button">
				{ __( 'Finished! 🎉', 'redirection' ) }
			</button>{ ' ' }
			<button className="button button-secondary" onClick={ onCancel } type="button">
				{ __( 'Go back', 'redirection' ) }
			</button>
			<p>{ __( 'If you do not complete the manual install you will be returned here.', 'redirection' ) }</p>
		</div>
	);
}
