/* global Redirectioni10n */
/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'wp-plugin-lib/locale';

/**
 * Internal dependencies
 */

import { getApiNonce } from 'lib/api';

const Fixit = () => {
	return (
		<form action={ Redirectioni10n.pluginRoot + '&sub=support' } method="POST">
			<input type="hidden" name="_wpnonce" value={ getApiNonce() } />
			<input type="hidden" name="action" value="fixit" />

			<p>{ __( "If the magic button doesn't work then you should read the error and see if you can fix it manually, otherwise follow the 'Need help' section below." ) }</p>
			<p><input type="submit" className="button-primary" value={ __( '⚡️ Magic fix ⚡️' ) } /></p>
		</form>
	);
};

const PluginStatusItem = ( props ) => {
	const { item } = props;

	return (
		<tr>
			<th>{ item.name }</th>
			<td><span className={ 'plugin-status-' + item.status }>{ item.status === 'good' ? __( 'Good' ) : __( 'Problem' ) }</span> { item.message }</td>
		</tr>
	);
};

const PluginStatus = ( props ) => {
	const { status } = props;
	const hasProblem = status.filter( item => item.status !== 'good' );

	return (
		<>
			<table className="plugin-status">
				<tbody>
					{ status.map( ( item, pos ) => <PluginStatusItem item={ item } key={ pos } /> ) }
				</tbody>
			</table>

			{ hasProblem.length > 0 && <Fixit /> }
		</>
	);
};

export default PluginStatus;
