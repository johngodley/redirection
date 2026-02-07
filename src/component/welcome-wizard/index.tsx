import { useState } from 'react';
import { __ } from '@wordpress/i18n';
import { ExternalLink, Error } from '@wp-plugin-components';
import { getErrorLinks, getErrorDetails } from 'lib/error-links';
import { useSettingsStore } from 'stores';
import DebugReport from 'page/home/debug';
import StepWelcome from './step-welcome';
import StepOptions from './step-options';
import StepAPI from './step-api';
import StepDatabase from './step-database';
import StepImporter from './step-importer';
import StepImporting from './step-importing';
import StepFinish from './step-finish';
import {
	STEP_IMPORT,
	STEP_DATABASE,
	STEP_API,
	STEP_BASIC,
	STEP_WELCOME,
	STEP_SAVE_IMPORT,
	STEP_FINISH,
} from './constants';
import type { WizardStep } from './constants';
import './style.scss';

interface WizardSettings {
	log: boolean;
	ip: boolean;
	monitor: boolean;
}

interface WizardOptions {
	settings: WizardSettings;
	importers: string[];
}

type StepComponent = React.ComponentType< {
	options?: WizardOptions;
	step?: WizardStep;
	setStep?: ( step: WizardStep ) => void;
	setOptions?: ( options: Partial< WizardOptions > ) => void;
} >;

function getContentForStep( step: WizardStep ): StepComponent {
	if ( step === STEP_IMPORT ) {
		return StepImporter as unknown as StepComponent;
	}

	if ( step === STEP_DATABASE ) {
		return StepDatabase as unknown as StepComponent;
	}

	if ( step === STEP_API ) {
		return StepAPI as unknown as StepComponent;
	}

	if ( step === STEP_BASIC ) {
		return StepOptions as unknown as StepComponent;
	}

	if ( step === STEP_SAVE_IMPORT ) {
		return StepImporting as unknown as StepComponent;
	}

	if ( step === STEP_FINISH ) {
		return StepFinish as unknown as StepComponent;
	}

	return StepWelcome as unknown as StepComponent;
}

function WelcomeWizard() {
	const [ step, setStep ] = useState< WizardStep >( STEP_WELCOME );
	const [ options, setOptions ] = useState< WizardOptions >( {
		settings: { log: false, ip: false, monitor: false },
		importers: [],
	} );

	// Direct property access instead of destructuring
	const result = useSettingsStore( ( state ) => state.database.status );
	const reason = useSettingsStore( ( state ) => state.database.reason );

	function changeStep( nextStep: WizardStep ) {
		let actualStep = nextStep;

		if ( nextStep === STEP_SAVE_IMPORT && options.importers.length === 0 ) {
			actualStep = ( nextStep + 1 ) as WizardStep;
		}

		setStep( actualStep );
	}

	const Content = getContentForStep( step );

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
					{ __( 'Something went wrong when installing Redirection.', 'redirection' ) }
				</Error>
			) }

			<div className="wizard-wrapper">
				{ step !== 0 && step !== 3 && <h1>{ __( 'Redirection', 'redirection' ) }</h1> }

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
				<ExternalLink url="https://redirection.me/contact/">
					{ __( 'I need support!', 'redirection' ) }
				</ExternalLink>
			</div>
		</>
	);
}

export default WelcomeWizard;
