/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'i18n-calypso';
import { useSelector } from 'react-redux';

const IMPORTER_WP = 'wordpress-old-slugs';

export default function StepImporter( { setOptions, options, setStep, step } ) {
	const { importers = [ IMPORTER_WP ] } = options;
	const { wpImport, otherImporters } = useSelector( ( state ) => {
		const { importers } = state.io;

		return {
			wpImport: importers.find( ( item ) => item.id === IMPORTER_WP ),
			otherImporters: importers.filter( ( item ) => item.id !== IMPORTER_WP ),
		};
	} );

	function toggleImporter( ev ) {
		const newImporters = importers.filter( ( importer ) => importer !== ev.target.name );

		if ( ev.target.checked ) {
			setOptions( { importers: newImporters.concat( ev.target.name ) } );
		} else {
			setOptions( { importers: newImporters } );
		}
	}

	return (
		<div>
			<h2>{ __( 'Import Existing Redirects' ) }</h2>

			<p>
				{ __(
					'Importing existing redirects from WordPress or other plugins is a good way to get started with Redirection. Check each set of redirects you wish to import.'
				) }
			</p>

			{ wpImport && (
				<>
					<p>
						{ __(
							'WordPress automatically creates redirects when you change a post URL. Importing these into Redirection will allow you to manage and monitor them.'
						) }
					</p>
					<ul>
						<li>
							<label>
								<input
									type="checkbox"
									name={ IMPORTER_WP }
									onChange={ toggleImporter }
									checked={ importers.indexOf( IMPORTER_WP ) !== -1 }
								/>{' '}
								{ wpImport.name } ({ wpImport.total })
							</label>
						</li>
					</ul>
				</>
			) }

			{ otherImporters.length > 0 && (
				<>
					<p>{ __( 'The following plugins have been detected.' ) }</p>
					<ul>
						{ otherImporters.map( ( item ) => (
							<li key={ item.id }>
								<label>
									<input
										type="checkbox"
										name={ item.id }
										onChange={ toggleImporter }
										checked={ importers.indexOf( item.id ) !== -1 }
									/>{' '}
									{ item.name } ({ item.total })
								</label>
							</li>
						) ) }
					</ul>
				</>
			) }

			<div className="wizard-buttons">
				<button className="button-primary button" onClick={ () => setStep( step + 1 ) }>
					{ __( 'Continue' ) }
				</button>
				&nbsp;
				<button className="button" onClick={ () => setStep( step - 1 ) }>
					{ __( 'Go back' ) }
				</button>
			</div>
		</div>
	);
}
