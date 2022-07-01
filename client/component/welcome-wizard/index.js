/* global Redirectioni10n */
/**
 * External dependencies
 */

import React, { useState } from 'react';
import { translate as __ } from 'i18n-calypso';
import { useSelector } from 'react-redux';

/**
 * Internal dependencies
 */
import { ExternalLink, Error } from 'wp-plugin-components';
import { getErrorLinks, getErrorDetails } from 'lib/error-links';
import { STATUS_FAILED } from 'state/settings/type';
import DebugReport from 'page/home/debug';
import StepWelcome from './step-welcome';
import StepOptions from './step-options';
import StepAPI from './step-api';
import StepDatabase from './step-database';
import StepImporter from './step-importer';
import StepImporting from './step-importing';
import StepFinish from './step-finish';
import { STEP_IMPORT, STEP_DATABASE, STEP_API, STEP_BASIC, STEP_WELCOME, STEP_SAVE_IMPORT, STEP_FINISH } from './constants';
import './style.scss';

function getContentForStep( step ) {
	if ( step === STEP_IMPORT ) {
		return StepImporter;
	}

	if ( step === STEP_DATABASE ) {
		return StepDatabase;
	}

	if ( step === STEP_API ) {
		return StepAPI;
	}

	if ( step === STEP_BASIC ) {
		return StepOptions;
	}

	if ( step === STEP_SAVE_IMPORT ) {
		return StepImporting;
	}

	if ( step === STEP_FINISH ) {
		return StepFinish;
	}

	return StepWelcome;
}

function WelcomeWizard( props ) {
	const [ step, setStep ] = useState( STEP_WELCOME );
	const [ options, setOptions ] = useState( { settings: { log: false, ip: false , monitor: false }, importers: [] } );

	const { result, reason } = useSelector( ( state ) => {
		const { database } = state.settings;
		const { result } = state.settings.database;

		return {
			result,
			reason: database.reason,
		};
	} );

	function changeStep( nextStep ) {
		// Skip importer if nothing to import
		if ( nextStep === STEP_SAVE_IMPORT && options.importers.length === 0 ) {
			nextStep++;
		}

		setStep( nextStep );
	}

	const Content = getContentForStep( step );

	return (
		<>
			{ result === STATUS_FAILED && (
				<Error
					details={ getErrorDetails() }
					errors={ reason }
					renderDebug={ DebugReport }
					links={ getErrorLinks() }
				>
					{ __( 'Something went wrong when installing Redirection.' ) }
				</Error>
			) }

			<div className="wizard-wrapper">
				{ step !== 0 && step !== 3 && <h1>{ __( 'Redirection' ) }</h1> }

				<div className="wizard">
					<Content
						options={ options }
						step={ step }
						setStep={ changeStep }
						setOptions={ ( newOptions ) => setOptions( { ...options, ...newOptions } ) }
					/>
				</div>
			</div>

			<div className="wizard-support">
				<ExternalLink url="https://redirection.me/contact/">{ __( 'I need support!' ) }</ExternalLink>
			</div>
		</>
	);
}

export default WelcomeWizard;
