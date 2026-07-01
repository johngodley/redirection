import DecodeError from '@wp-plugin-components/error/decode-error';
import { __ } from '@wordpress/i18n';
import ApiResultRaw from 'component/rest-api-status/api-result-raw';
import { getErrorLinks } from 'lib/error-links';

interface ImportExportApiErrorShape {
	code?: string | number;
	name?: string;
	message?: string;
	data?: unknown;
	request?: {
		raw?: string;
	};
}

interface ImportExportApiErrorProps {
	error: unknown;
}

function getApiErrorName( error: ImportExportApiErrorShape ): string | null {
	if ( error.code !== undefined ) {
		return String( error.code );
	}

	if ( error.name && error.name !== 'Error' ) {
		return error.name;
	}

	return null;
}

function ImportExportApiError( { error }: ImportExportApiErrorProps ) {
	if ( ! error || typeof error !== 'object' ) {
		return null;
	}

	const apiError = error as ImportExportApiErrorShape;
	const errorName = getApiErrorName( apiError );

	return (
		<div className="wpl-error import-export-error">
			<p>
				{ errorName && <strong>{ errorName }: </strong> }
				{ apiError.message || __( 'An unknown error occurred.', 'redirection' ) }
			</p>

			<DecodeError error={ apiError } links={ getErrorLinks() } locale="redirection" />
			<ApiResultRaw error={ apiError } />
		</div>
	);
}

export default ImportExportApiError;
