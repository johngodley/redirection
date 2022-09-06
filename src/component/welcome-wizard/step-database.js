/**
 * External dependencies
 */

import React from 'react';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import Database from '../database';
import ManualInstall from './manual-install';

export default function StepDatabase( { step, setStep, options, setOptions } ) {
	const { manual } = options;

	function stopManual() {
		setStep( 0 );
		setOptions( { manual: false } );
	}

	if ( manual ) {
		return <ManualInstall onCancel={ stopManual } />
	}

	return (
		<Database>
			<div className="wizard-buttons">
				<button className="button-primary button" onClick={ () => setStep( step + 1 ) }>
					{ __( 'Continue', 'redirection' ) }
				</button>
			</div>
		</Database>
	);
}
