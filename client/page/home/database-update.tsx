/**
 * External dependencies
 */

import { useDispatch, useSelector } from 'react-redux';
import { useState } from 'react';
import { __, sprintf } from '@wordpress/i18n';
import TextareaAutosize from 'react-textarea-autosize';

/**
 * Internal dependencies
 */
import Database from 'component/database';
import DebugReport from 'page/home/debug';
import { getExportUrl } from 'state/io/selector';
import { ExternalLink, Error, createInterpolateElement } from 'wp-plugin-components';
import { STATUS_FAILED } from 'state/settings/type';
import { fixStatus, finishUpgrade } from 'state/settings/action';
import { getErrorLinks, getErrorDetails } from 'lib/error-links';

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
				{ createInterpolateElement(
					sprintf(
						__(
							'Redirection stores data in your database and sometimes this needs upgrading. Your database is at version {{strong}}%(current)s{{/strong}} and the latest is {{strong}}%(latest)s{{/strong}}.',
							'redirection'
						),
						{
							current: window.Redirectioni10n.database.current,
							latest: window.Redirectioni10n.database.next,
						},
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
	const dispatch = useDispatch();

	function onComplete() {
		dispatch( fixStatus( 'database', window.Redirectioni10n.database.next ) );
	}

	if ( window.Redirectioni10n.database.manual.length === 0 ) {
		return (
			<>
				<p>
					{ __( 'Your site already has the latest SQL.', 'redirection' ) + ' ' + __( 'Click "Complete Upgrade" when finished.', 'redirection' ) }
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

function AutomaticUpgrade( { onShowUpgrade } ) {
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
	const dispatch = useDispatch();
	const { reason, status, result } = useSelector( ( state ) => state.settings.database );

	function onFinish() {
		dispatch( finishUpgrade() );
	}

	return (
		<>
			{ result === STATUS_FAILED && (
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

function ShowNotice( { onShowUpgrade } ) {
	const [ isManual, setManual ] = useState( false );

	function onToggle( ev ) {
		ev.preventDefault();
		setManual( !isManual );
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
							download: <ExternalLink url={ getExportUrl( 'all', 'json' ) } />,
							import: <ExternalLink url="https://redirection.me/support/import-export-redirects/" />,
						}
					) }
				</p>

				{ isManual ? <ManualUpgrade /> : <AutomaticUpgrade onShowUpgrade={ onShowUpgrade } /> }
			</div>

			<div className="database-switch">
				{ !isManual && (
					<a href="#" onClick={ onToggle }>
						{ __( 'Manual Upgrade', 'redirection' ) }
					</a>
				) }
				{ isManual && (
					<a href="#" onClick={ onToggle }>
						{ __( 'Automatic Upgrade', 'redirection' ) }
					</a>
				) }
			</div>
		</>
	);
}

export default function DatabaseUpdate( { showDatabase, onShowUpgrade } ) {
	if ( showDatabase ) {
		return <ShowDatabase />;
	}

	return <ShowNotice onShowUpgrade={ onShowUpgrade } />
}
