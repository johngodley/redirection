/**
 * External dependencies
 */

import React, { Fragment } from 'react';
import { translate as __ } from 'lib/locale';

/**
 * Internal dependencies
 */

import './style.scss';

const RequestHeaders = ( { headers } ) => {
	if ( ! headers || headers.length === 0 ) {
		return null;
	}

	return (
		<Fragment>
			<h3>{ __( 'Request Headers' ) }</h3>

			<table>
				<tbody>
					{ Object.keys( headers ).map( key => (
						<tr key={ key }>
							<th>{ key }</th>
							<td>{ headers[ key ] }</td>
						</tr>
					) ) }
				</tbody>
			</table>
		</Fragment>
	);
};

const RequestSource = ( { source } ) => {
	if ( ! source || source.length === 0 ) {
		return null;
	}

	return (
		<Fragment>
			<h3>{ __( 'Redirect Source' ) }</h3>

			<ul>
				{ source.map( ( item, key ) => <li key={ key }>{ item }</li> ) }
			</ul>
		</Fragment>
	);
};

const RequestData = ( { data } ) => {
	const { headers, source } = data;

	return (
		<div className="redirect-requestdata">
			<RequestHeaders headers={ headers } />
			<RequestSource source={ source } />
		</div>
	);
 };

 export default RequestData;
