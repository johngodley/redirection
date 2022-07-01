/* global Redirectioni10n */
/**
 * External dependencies
 */

import React, { useEffect } from 'react';
import { translate as __ } from 'i18n-calypso';
import { useDispatch, useSelector } from 'react-redux';
import { pluginImport } from 'state/io/action';
import { STATUS_IN_PROGRESS, STATUS_COMPLETE, STATUS_FAILED } from 'state/settings/type';

export default function StepImporting( { step, setStep, options } ) {
	const dispatch = useDispatch();
	const { importingStatus } = useSelector( ( state ) => {
		const { importingStatus } = state.io;

		return {
			importingStatus,
		};
	} );

	function doImport() {
		dispatch( pluginImport( options.importers ) );
	}

	useEffect( () => {
		doImport();
	}, [] );

	return (
		<div>
			<h2>{ __( 'Import Existing Redirects' ) }</h2>

			{ importingStatus === STATUS_IN_PROGRESS &&
				<>
					<p>{ __( 'Please wait, importing.' ) }</p>

					<div className="loader-wrapper loader-textarea">
						<div className="wpl-placeholder__loading" />
					</div>
				</>
			}

			{ ( importingStatus === STATUS_COMPLETE || importingStatus == STATUS_FAILED ) &&
				<>
					<p>{ importingStatus === STATUS_COMPLETE ? __( 'Import finished.' ) : __( 'Importing failed.' ) }</p>

					<div className="wizard-buttons">
						{ importingStatus === STATUS_FAILED &&
							<button className="button-secondary button" onClick={ doImport }>
								{ __( 'Retry' ) }
							</button>
						}

						<button className="button-primary button" onClick={ () => setStep( step + 1 ) }>
							{ __( 'Continue' ) }
						</button>
					</div>
				</>
			}
		</div>
	);
}
