/**
 * External dependencies
 */

import { translate as __ } from 'i18n-calypso';

/**
 * Internal dependencies
 */

import { ExternalLink } from 'wp-plugin-components';
import ApiResultItem from './api-result-item';
import apiFetch from 'wp-plugin-lib/api-fetch';

const isLoading = result => Object.keys( result ).length === 0 || result.GET.status === 'loading' || result.POST.status === 'loading';

const ApiResult = ( { item, result, routes, isCurrent, allowChange } ) => {
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
					<input type="submit" className="button button-secondary" value={ __( 'Switch to this API' ) } />
				) }
				{ allowChange && isCurrent && <span>{ __( 'Current API' ) }</span> }

				<input type="hidden" name="rest_api" value={ item.value } />
				<input type="hidden" name="_wpnonce" value={ apiFetch.nonceMiddleware.nonce } />
				<input type="hidden" name="action" value="rest_api" />
			</form>

			<h4>{ item.text }</h4>

			<p>
				URL:{' '}
				<code>
					<ExternalLink url={ routes[ item.value ] }>{ routes[ item.value ] }</ExternalLink>
				</code>
			</p>

			<ApiResultItem result={ result } />
		</div>
	);
};

export default ApiResult;
