import { __ } from '@wordpress/i18n';
import { useIoStore } from 'stores';

const IMPORTER_WP = 'wordpress-old-slugs';

interface StepImporterOptions {
	importers: string[];
}

interface StepImporterProps {
	setOptions: ( options: Partial< StepImporterOptions > ) => void;
	options: StepImporterOptions;
	setStep: ( step: number ) => void;
	step: number;
}

export default function StepImporter( { setOptions, options, setStep, step }: StepImporterProps ) {
	const { importers = [ IMPORTER_WP ] } = options;
	const availableImporters = useIoStore( ( state ) => state.importers );
	const wpImport = availableImporters.find( ( item ) => item.id === IMPORTER_WP );
	const otherImporters = availableImporters.filter( ( item ) => item.id !== IMPORTER_WP );

	function toggleImporter( ev: React.ChangeEvent< HTMLInputElement > ) {
		const newImporters = importers.filter( ( importer ) => importer !== ev.target.name );

		if ( ev.target.checked ) {
			setOptions( { importers: newImporters.concat( ev.target.name ) } );
		} else {
			setOptions( { importers: newImporters } );
		}
	}

	return (
		<div>
			<h2>{ __( 'Import Existing Redirects', 'redirection' ) }</h2>

			<p>
				{ __(
					'Importing existing redirects from WordPress or other plugins is a good way to get started with Redirection. Check each set of redirects you wish to import.',
					'redirection'
				) }
			</p>

			{ wpImport && (
				<>
					<p>
						{ __(
							'WordPress automatically creates redirects when you change a post URL. Importing these into Redirection will allow you to manage and monitor them.',
							'redirection'
						) }
					</p>
					<ul>
						<li>
							<input
								id="wizard-importer-wordpress-old-slugs"
								type="checkbox"
								name={ IMPORTER_WP }
								onChange={ toggleImporter }
								checked={ importers.includes( IMPORTER_WP ) }
							/>
							<label htmlFor="wizard-importer-wordpress-old-slugs">
								{ wpImport.name } ({ wpImport.total })
							</label>
						</li>
					</ul>
				</>
			) }

			{ otherImporters.length > 0 && (
				<>
					<p>{ __( 'The following plugins have been detected.', 'redirection' ) }</p>
					<ul>
						{ otherImporters.map( ( item ) => {
							const importerId = `wizard-importer-${ item.id }`;

							return (
								<li key={ item.id }>
									<input
										id={ importerId }
										type="checkbox"
										name={ item.id }
										onChange={ toggleImporter }
										checked={ importers.includes( item.id ) }
									/>
									<label htmlFor={ importerId }>
										{ item.name } ({ item.total })
									</label>
								</li>
							);
						} ) }
					</ul>
				</>
			) }

			<div className="wizard-buttons">
				<button className="button-primary button" onClick={ () => setStep( step + 1 ) }>
					{ __( 'Continue', 'redirection' ) }
				</button>
				&nbsp;
				<button className="button" onClick={ () => setStep( step - 1 ) }>
					{ __( 'Go back', 'redirection' ) }
				</button>
			</div>
		</div>
	);
}
