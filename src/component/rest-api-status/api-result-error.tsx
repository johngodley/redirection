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
							{ method } { error.data && error.data.status }
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
