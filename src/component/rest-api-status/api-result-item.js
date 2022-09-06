/**
 * Internal dependencies
 */

import ApiResultError from './api-result-error';
import ApiResultPass from './api-result-pass';

const getErrorCode = result => result.code ? result.code : 0;

const ApiResultItem = ( { result } ) => {
	const details = [];
	const { GET, POST } = result;

	if ( GET.status === POST.status && getErrorCode( GET ) === getErrorCode( POST ) ) {
		if ( GET.status === 'fail' ) {
			details.push( ApiResultError( GET.error, [ 'GET', 'POST' ] ) );
		} else {
			details.push( ApiResultPass( [ 'GET', 'POST' ] ) );
		}

		return details;
	}

	if ( GET.status === 'fail' ) {
		details.push( ApiResultError( GET.error, [ 'GET' ] ) );
	} else {
		details.push( ApiResultPass( [ 'GET' ] ) );
	}

	if ( POST.status === 'fail' ) {
		details.push( ApiResultError( POST.error, [ 'POST' ] ) );
	} else {
		details.push( ApiResultPass( [ 'POST' ] ) );
	}

	return details;
};

export default ApiResultItem;
