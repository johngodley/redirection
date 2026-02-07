import { useEffect, useCallback } from 'react';
import { __ } from '@wordpress/i18n';
import { usePluginImport } from 'lib/api/hooks';

interface StepImportingOptions {
	importers: string[];
}

interface StepImportingProps {
	step: number;
	setStep: ( step: number ) => void;
	options: StepImportingOptions;
}

export default function StepImporting( { step, setStep, options }: StepImportingProps ) {
	const pluginImport = usePluginImport();

	let importingStatus: 'idle' | 'loading' | 'success' | 'error' = 'idle';
	if ( pluginImport.isPending ) {
		importingStatus = 'loading';
	} else if ( pluginImport.isSuccess ) {
		importingStatus = 'success';
	} else if ( pluginImport.isError ) {
		importingStatus = 'error';
	}

	const doImport = useCallback( () => {
		pluginImport.mutate( options.importers );
	}, [ pluginImport, options.importers ] );

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
