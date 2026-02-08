import { __ } from '@wordpress/i18n';
import { ExternalLink } from '@wp-plugin-components';
import ApiResultItem from './api-result-item';
import apiFetch from '@wp-plugin-lib/api-fetch';

interface ApiError {
	code?: string;
	name?: string;
	message: string;
	data?: {
		status: number;
	};
	request?: any;
}

interface ApiTestResult {
	status: string;
	error?: ApiError;
	code?: string;
}

interface TestResult {
	GET: ApiTestResult;
	POST: ApiTestResult;
	[ key: string ]: any;
}

interface RouteItem {
	text: string;
	value: string;
}

interface ApiResultProps {
	item: RouteItem;
	result: TestResult;
	routes: {
		[ key: string ]: string;
	};
	isCurrent: boolean;
	allowChange: boolean;
}

const isLoading = ( result: TestResult ): boolean =>
	Object.keys( result ).length === 0 || result.GET.status === 'loading' || result.POST.status === 'loading';

const ApiResult = ( { item, result, routes, isCurrent, allowChange }: ApiResultProps ) => {
	if ( isLoading( result ) ) {
		return null;
	}

	return (
		<div className="api-result-log">
			<form
				className="api-result-select"
				action={ window.Redirectioni10n.pluginRoot + '&sub=support' }
				method="POST"
			>
				{ allowChange && ! isCurrent && (
					<input
						type="submit"
						className="button button-secondary"
						value={ __( 'Switch to this API', 'redirection' ) }
					/>
				) }
				{ allowChange && isCurrent && <span>{ __( 'Current API', 'redirection' ) }</span> }

				<input type="hidden" name="rest_api" value={ item.value } />
				<input type="hidden" name="_wpnonce" value={ apiFetch.nonceMiddleware.nonce } />
				<input type="hidden" name="action" value="rest_api" />
			</form>

			<h4>{ item.text }</h4>

			<p>
				URL:{ ' ' }
				<code>
					<ExternalLink url={ routes[ item.value ] || '' }>{ routes[ item.value ] || '' }</ExternalLink>
				</code>
			</p>

			<ApiResultItem result={ result } />
		</div>
	);
};

export default ApiResult;
