/**
 * External dependencies
 */

import React from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { translate as __ } from 'i18n-calypso';
import TextareaAutosize from 'react-textarea-autosize';

/**
 * Internal dependencies
 */
import { upgradeDatabase } from 'state/settings/action';

function getErrorMessage( reason, current, next, debug ) {
	const message = [
		reason ? 'Message: ' + reason : null,
		'Installed: ' + current,
		'Next: ' + next,
		debug.length > 0 ? 'Debug: ' + debug.join( '\n' ) : null,
	];

	return message.filter( ( item ) => item ).join( '\n' );
}

export default function DatabaseError( { onRetry, error } ) {
	const { current, next, debug, reason } = useSelector( state => state.settings.database );
	const dispatch = useDispatch();
	const recovery = getErrorMessage( reason, current, next, debug );

	function onSkip() {
		dispatch( upgradeDatabase( 'skip' ) );
	}

	function onStop() {
		dispatch( upgradeDatabase( 'stop' ) );
	}

	return (
		<div className="redirection-database_error wpl-error">
			<h3>{ __( 'Database problem' ) }</h3>
			<p>{ error }</p>
			<p>
				<button className="button button-primary" onClick={ onRetry }>
					{ __( 'Try again' ) }
				</button>
				&nbsp;
				{ current !== '-' && (
					<button className="button button-secondary" onClick={ onSkip }>
						{ __( 'Skip this stage' ) }
					</button>
				) }
				&nbsp;
				{ current !== '-' && (
					<button className="button button-secondary" onClick={ onStop }>
						{ __( 'Stop upgrade' ) }
					</button>
				) }
			</p>

			<p>
				{ __( 'If you want to {{support}}ask for support{{/support}} please include these details:', {
					components: {
						support: (
							<a
								href={
									'mailto:john@redirection.me?subject=Redirection%20Database&body=' +
									// @ts-ignore
									encodeURIComponent( 'Redirection: ' + Redirectioni10n.versions )
								}
							/>
						),
					},
				} ) }
			</p>

			<TextareaAutosize readOnly value={ recovery } rows={ 15 } />
		</div>
	);
}
