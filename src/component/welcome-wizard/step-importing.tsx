import { useEffect, useCallback } from 'react';
import { __ } from '@wordpress/i18n';
import { useImportRunner } from 'lib/api/hooks';

interface StepImportingOptions {
	importers: string[];
}

interface StepImportingProps {
	step: number;
	setStep: ( step: number ) => void;
	options: StepImportingOptions;
}

export default function StepImporting( { step, setStep, options }: StepImportingProps ) {
	const importRunner = useImportRunner();
	const { mutate } = importRunner;

	let importingStatus: 'idle' | 'loading' | 'success' | 'error' = 'idle';
	if ( importRunner.isPending ) {
		importingStatus = 'loading';
	} else if ( importRunner.isSuccess ) {
		importingStatus = 'success';
	} else if ( importRunner.isError ) {
		importingStatus = 'error';
	}

	const doImport = useCallback( () => {
		const importers = options.importers.filter( ( importer ) => importer.length > 0 );

		if ( importers.length === 0 ) {
			setStep( step + 1 );
			return;
		}

		// Setup creates the default group before the importer step runs.
		mutate( {
			sourceType: 'plugin',
			mode: 'import',
			pluginId: importers,
			groupId: 1,
			duplicateMode: 'import',
		} );
	}, [ mutate, options.importers, setStep, step ] );

	useEffect( () => {
		doImport();
	}, [ doImport ] );

	return (
		<div>
			<h2>{ __( 'Import Existing Redirects', 'redirection' ) }</h2>

			{ importingStatus === 'loading' && (
				<>
					<p>{ __( 'Please wait, importing.', 'redirection' ) }</p>

					<div className="loader-wrapper loader-textarea">
						<div className="wpl-placeholder__loading" />
					</div>
				</>
			) }

			{ ( importingStatus === 'success' || importingStatus === 'error' ) && (
				<>
					<p>
						{ importingStatus === 'success'
							? __( 'Import finished.', 'redirection' )
							: __( 'Importing failed.', 'redirection' ) }
					</p>

					<div className="wizard-buttons">
						{ importingStatus === 'error' && (
							<button className="button-secondary button" onClick={ doImport }>
								{ __( 'Retry', 'redirection' ) }
							</button>
						) }

						<button className="button-primary button" onClick={ () => setStep( step + 1 ) }>
							{ __( 'Continue', 'redirection' ) }
						</button>
					</div>
				</>
			) }
		</div>
	);
}
