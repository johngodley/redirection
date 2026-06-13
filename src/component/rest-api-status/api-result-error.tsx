import ApiResultRaw from './api-result-raw';
import DecodeError from '@wp-plugin-components/error/decode-error';
import { getErrorLinks } from 'lib/error-links';

interface ApiError {
	code?: string;
	name?: string;
	message: string;
	data?: {
		status: number;
	};
	request?: any;
}

interface ApiResultErrorProps {
	error: ApiError;
	methods: string[];
}

const getApiErrorName = ( error: ApiError ): string | null => {
	if ( error.code ) {
		return error.code;
	}

	if ( error.name ) {
		return error.name;
	}

	return null;
};

const getMethodStatus = ( error: ApiError, method: string ) => {
	const status = error.data?.status;

	if ( typeof status === 'number' && status > 0 ) {
		return `${ method } ${ status }`;
	}

	return `${ method } ${ error.code === 'rest_api_cors_mismatch' ? 'blocked' : 'failed' }`;
};

const ApiResultError = ( { error, methods }: ApiResultErrorProps ) => {
	const name = getApiErrorName( error );

	return (
		<div className="api-result-log_details" key={ methods.join( '' ) }>
			<p>
				<span className="dashicons dashicons-no" />
			</p>

			<div>
				<p>
					{ methods.map( ( method, key ) => (
						<span key={ key } className="api-result-method_fail">
							{ getMethodStatus( error, method ) }
						</span>
					) ) }

					{ name && <strong>{ name }: </strong> }
					{ error.message }
				</p>

				<DecodeError error={ error } links={ getErrorLinks() } locale="redirection" />
				<ApiResultRaw error={ error } />
			</div>
		</div>
	);
};

export default ApiResultError;
