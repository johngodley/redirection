/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'i18n-calypso';
import { useDispatch, useSelector } from 'react-redux';
import TextareaAutosize from 'react-textarea-autosize';

/**
 * Internal dependencies
 */

import { fixStatus } from 'state/settings/action';
import { STATUS_FAILED } from 'state/settings/type';

export default function ManualInstall( { onCancel } ) {
	const dispatch = useDispatch();
	const { loadStatus } = useSelector( state => state.settings );

	function onComplete() {
		dispatch( fixStatus( 'database', Redirectioni10n.database.next ) );
	}

	return (
		<div className="redirection-database">
			<h1>{ __( 'Manual Install' ) }</h1>

			<p>
				{ __(
					'If your site needs special database permissions, or you would rather do it yourself, you can manually run the following SQL.'
				) }{ ' ' }
				{ __( 'Click "Finished! 🎉" when finished.' ) }
			</p>
			<p>
				<TextareaAutosize
					readOnly
					cols={ 120 }
					// @ts-ignore
					value={ Redirectioni10n.database.manual.join( ';\n\n' ) + ';' }
					spellCheck={ false }
				/>
			</p>

			{ loadStatus === STATUS_FAILED &&
				<div className="redirection-database_error wpl-error">
					<h3>{ __( 'Database problem' ) }</h3>
					<p>{ __( 'The Redirection database does not appear to exist. Have you run the above SQL?' ) }</p>
				</div>
			}

			<button className="button button-primary" onClick={ onComplete } type="button">
				{ __( 'Finished! 🎉' ) }
			</button>{ ' ' }
			<button className="button button-secondary" onClick={ onCancel } type="button">
				{ __( 'Go back' ) }
			</button>
			<p>{ __( 'If you do not complete the manual install you will be returned here.' ) }</p>
		</div>
	);
}
