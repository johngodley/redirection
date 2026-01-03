import ApiResultError from './api-result-error';
import ApiResultPass from './api-result-pass';

interface ApiError {
	code?: string;
	name?: string;
	message: string;
	data?: {
		status: number;
	};
	request?: any;
}

interface ApiResult {
	status: string;
	error?: ApiError;
	code?: string;
}

interface TestResult {
	GET: ApiResult;
	POST: ApiResult;
}

interface ApiResultItemProps {
	result: TestResult;
}

const getErrorCode = ( result: ApiResult ): string | number => ( result.code ? result.code : 0 );

const ApiResultItem = ( { result }: ApiResultItemProps ) => {
	const details: JSX.Element[] = [];
	const { GET, POST } = result;

	if ( GET.status === POST.status && getErrorCode( GET ) === getErrorCode( POST ) ) {
		if ( GET.status === 'fail' && GET.error ) {
			details.push( <ApiResultError key="get-post" error={ GET.error } methods={ [ 'GET', 'POST' ] } /> );
		} else {
			details.push( <ApiResultPass key="get-post" methods={ [ 'GET', 'POST' ] } /> );
		}

		return details;
	}

	if ( GET.status === 'fail' && GET.error ) {
		details.push( <ApiResultError key="get" error={ GET.error } methods={ [ 'GET' ] } /> );
	} else {
		details.push( <ApiResultPass key="get" methods={ [ 'GET' ] } /> );
	}

	if ( POST.status === 'fail' && POST.error ) {
		details.push( <ApiResultError key="post" error={ POST.error } methods={ [ 'POST' ] } /> );
	} else {
		details.push( <ApiResultPass key="post" methods={ [ 'POST' ] } /> );
	}

	return details;
};

export default ApiResultItem;
