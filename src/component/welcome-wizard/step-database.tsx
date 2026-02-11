import { __ } from '@wordpress/i18n';
import Database from 'component/database';
import ManualInstall from './manual-install';

interface Options {
	manual: boolean;
	[ key: string ]: any;
}

interface StepDatabaseProps {
	step: number;
	setStep: ( step: number ) => void;
	options: Options;
	setOptions: ( options: Partial< Options > ) => void;
}

export default function StepDatabase( { step, setStep, options, setOptions }: StepDatabaseProps ) {
	const { manual } = options;

	function stopManual() {
		setStep( 0 );
		setOptions( { manual: false } );
	}

	function continueToNext() {
		setStep( step + 1 );
	}

	if ( manual ) {
		return <ManualInstall onCancel={ stopManual } onComplete={ continueToNext } />;
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
