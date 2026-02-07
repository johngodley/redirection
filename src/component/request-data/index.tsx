import { Fragment } from 'react';
import { __ } from '@wordpress/i18n';
import './style.scss';

interface RequestHeaders {
	[ key: string ]: string;
}

interface RequestData {
	headers?: RequestHeaders;
	source?: string[];
}

interface RequestHeadersProps {
	headers?: RequestHeaders;
}

interface RequestSourceProps {
	source?: string[];
}

interface RequestDataProps {
	data: RequestData;
}

const RequestHeaders = ( { headers }: RequestHeadersProps ) => {
	if ( ! headers || Object.keys( headers ).length === 0 ) {
		return null;
	}

	return (
		<Fragment>
			<h3>{ __( 'Request Headers', 'redirection' ) }</h3>

			<table>
				<tbody>
					{ Object.keys( headers ).map( ( key ) => (
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

const RequestSource = ( { source }: RequestSourceProps ) => {
	if ( ! source || source.length === 0 ) {
		return null;
	}

	return (
		<Fragment>
			<h3>{ __( 'Redirect Source', 'redirection' ) }</h3>

			<ul>
				{ source.map( ( item, key ) => (
					<li key={ key }>{ item }</li>
				) ) }
			</ul>
		</Fragment>
	);
};

const RequestData = ( { data }: RequestDataProps ) => {
	const { headers, source } = data;

	return (
		<div className="redirect-requestdata">
			{ headers && <RequestHeaders headers={ headers } /> }
			{ source && <RequestSource source={ source } /> }
		</div>
	);
};

export default RequestData;
