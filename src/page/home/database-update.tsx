import { useState } from 'react';
import { sprintf, __ } from '@wordpress/i18n';
import { createInterpolateElement, ExternalLink, Error } from '@wp-plugin-components';
import TextareaAutosize from 'react-textarea-autosize';
import Database from 'component/database';
import { useExport } from 'lib/api/hooks';
import { useFixStatus, useFinishUpgrade } from 'lib/api/hooks/use-settings';
import { useSettingsStore } from 'stores';
import { getErrorLinks, getErrorDetails } from 'lib/error-links';
import DebugReport from 'page/home/debug';

function hasFinished( status: string ) {
	return status === 'finish-install' || status === 'finish-update';
}

function getUpgradeNotice() {
	const { current, next } = window.Redirectioni10n.database;

	if ( current === next ) {
		return <p>{ __( 'A database upgrade is in progress. Please continue to finish.', 'redirection' ) }</p>;
	}

	return (
		<>
			<p>
				{ /* translators: %(current)s is the current database version, %(latest)s is the latest database version */ }
				{ createInterpolateElement(
					sprintf(
						// translators: %(current)s is the current database version, %(latest)s is the latest database version
						__(
							'Redirection stores data in your database and sometimes this needs upgrading. Your database is at version {{strong}}%(current)s{{/strong}} and the latest is {{strong}}%(latest)s{{/strong}}.',
							'redirection'
						),
						{
							current: window.Redirectioni10n.database.current,
							latest: window.Redirectioni10n.database.next,
						}
					),
					{
						strong: <strong />,
					}
				) }
			</p>
		</>
	);
}

function ManualUpgrade() {
	const { mutate: fixStatus } = useFixStatus();

	function onComplete() {
		fixStatus( { reason: 'database', current: window.Redirectioni10n.database.next } );
	}

	if ( window.Redirectioni10n.database.manual.length === 0 ) {
		return (
			<>
				<p>
					{ __( 'Your site already has the latest SQL.', 'redirection' ) +
						' ' +
						__( 'Click "Complete Upgrade" when finished.', 'redirection' ) }
				</p>
				<p>
					<button className="button-primary" onClick={ onComplete }>
						{ __( 'Complete Upgrade', 'redirection' ) }
					</button>
				</p>
			</>
		);
	}

	return (
		<>
			<p>
				{ __(
					'If your site needs special database permissions, or you would rather do it yourself, you can manually run the following SQL.'
				) }{ ' ' }
				{ __( 'Click "Complete Upgrade" when finished.', 'redirection' ) }
			</p>
			<p>
				<TextareaAutosize
					readOnly={ true }
					cols={ 120 }
					value={ window.Redirectioni10n.database.manual.join( ';\n' ) + ';' }
					spellCheck={ false }
				/>
			</p>
			<p>
				<button className="button-primary" onClick={ onComplete }>
					{ __( 'Complete Upgrade', 'redirection' ) }
				</button>
			</p>
		</>
	);
}

interface AutomaticUpgradeProps {
	onShowUpgrade: () => void;
}

function AutomaticUpgrade( { onShowUpgrade }: AutomaticUpgradeProps ) {
	return (
		<>
			<p>{ __( 'Click the "Upgrade Database" button to automatically upgrade the database.', 'redirection' ) }</p>
			<p>
				<input
					className="button-primary"
					type="submit"
					value={ __( 'Upgrade Database', 'redirection' ) }
					onClick={ onShowUpgrade }
				/>
			</p>
		</>
	);
}

function ShowDatabase() {
	const reason = useSettingsStore( ( state ) => state.database.reason );
	const status = useSettingsStore( ( state ) => state.database.status );
	const result = useSettingsStore( ( state ) => state.database.result );
	const { mutate: finishUpgrade } = useFinishUpgrade();

	function onFinish() {
		finishUpgrade();
	}

	return (
		<>
			{ result === 'error' && (
				<Error
					details={ getErrorDetails() }
					errors={ reason }
					renderDebug={ DebugReport }
					links={ getErrorLinks() }
					locale="redirection"
				>
					{ __( 'Something went wrong when upgrading Redirection.', 'redirection' ) }
				</Error>
			) }

			<div className="wizard-wrapper">
				<div className="wizard">
					<Database />

					{ hasFinished( status ) && (
						<button className="button button-primary" onClick={ onFinish }>
							{ __( 'Finished! 🎉', 'redirection' ) }
						</button>
					) }
				</div>
			</div>
		</>
	);
}

interface ShowNoticeProps {
	onShowUpgrade: () => void;
}

function DownloadBackupLink() {
	const exportMutation = useExport();

	function onClick( ev: React.MouseEvent< HTMLButtonElement > ) {
		ev.preventDefault();

		if ( exportMutation.isPending ) {
			return;
		}

		exportMutation.mutate( {
			exportType: 'redirect',
			format: 'json',
			redirectScopeType: 'all',
			redirectModule: 'all',
			redirectGroup: 0,
			download: true,
			filename: 'redirection-backup.json',
			completionNotice: { message: __( 'Backup downloaded', 'redirection' ) },
		} );
	}

	return (
		<button type="button" className="button-link" onClick={ onClick } disabled={ exportMutation.isPending }>
			{ __( 'downloading a backup', 'redirection' ) }
		</button>
	);
}

function ShowNotice( { onShowUpgrade }: ShowNoticeProps ) {
	const [ isManual, setManual ] = useState( false );

	function onToggle( ev: React.MouseEvent< HTMLButtonElement > ) {
		ev.preventDefault();
		setManual( ! isManual );
	}

	return (
		<>
			<h1 className="wp-heading-inline">{ __( 'Upgrade Required', 'redirection' ) }</h1>

			<div className="wpl-error">
				<h3>{ __( 'Redirection database needs upgrading', 'redirection' ) }</h3>

				{ getUpgradeNotice() }

				<p>
					{ createInterpolateElement(
						__(
							'Please make a backup of your Redirection data: {{download}}downloading a backup{{/download}}. If you experience any issues you can import this back into Redirection.',
							'redirection'
						),
						{
							download: <DownloadBackupLink />,
							import: <ExternalLink url="https://redirection.me/support/import-export-redirects/" />,
						}
					) }
				</p>

				{ isManual ? <ManualUpgrade /> : <AutomaticUpgrade onShowUpgrade={ onShowUpgrade } /> }
			</div>

			<div className="database-switch">
				{ ! isManual && (
					<button type="button" onClick={ onToggle }>
						{ __( 'Manual Upgrade', 'redirection' ) }
					</button>
				) }
				{ isManual && (
					<button type="button" onClick={ onToggle }>
						{ __( 'Automatic Upgrade', 'redirection' ) }
					</button>
				) }
			</div>
		</>
	);
}

interface DatabaseUpdateProps {
	showDatabase: boolean;
	onShowUpgrade: () => void;
}

export default function DatabaseUpdate( { showDatabase, onShowUpgrade }: DatabaseUpdateProps ) {
	if ( showDatabase ) {
		return <ShowDatabase />;
	}

	return <ShowNotice onShowUpgrade={ onShowUpgrade } />;
}
