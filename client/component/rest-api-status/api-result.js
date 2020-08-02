/* global Redirectioni10n */
/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'wp-plugin-lib/locale';

/**
 * Internal dependencies
 */

import ExternalLink from 'wp-plugin-components/external-link';
import ApiResultItem from './api-result-item';
import { getApiNonce } from 'lib/api';

const isLoading = result => Object.keys( result ).length === 0 || result.GET.status === 'loading' || result.POST.status === 'loading';

const ApiResult = ( { item, result, routes, isCurrent, allowChange } ) => {
	if ( isLoading( result ) ) {
		return null;
	}

	return (
		<div className="api-result-log">
			<form className="api-result-select" action={ Redirectioni10n.pluginRoot + '&sub=support' } method="POST">
				{ allowChange && ! isCurrent && <input type="submit" className="button button-secondary" value={ __( 'Switch to this API' ) } /> }
				{ allowChange && isCurrent && <span>{ __( 'Current API' ) }</span> }

				<input type="hidden" name="rest_api" value={ item.value } />
				<input type="hidden" name="_wpnonce" value={ getApiNonce() } />
				<input type="hidden" name="action" value="rest_api" />
			</form>

			<h4>
				{ item.text }
			</h4>

			<p>URL: <code><ExternalLink url={ routes[ item.value ] }>{ routes[ item.value ] }</ExternalLink></code></p>

			<ApiResultItem result={ result } />
		</div>
	);
};

export default ApiResult;
