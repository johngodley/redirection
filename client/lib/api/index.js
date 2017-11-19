/* global fetch, Redirectioni10n */

const getApiRequest = ( action, data, file ) => {
	const form = new FormData();

	form.append( 'action', action );
	form.append( '_wpnonce', Redirectioni10n.WP_API_nonce );

	if ( data ) {
		form.append( 'data', JSON.stringify( data ) );
	}

	if ( file ) {
		form.append( 'file', file );
	}

	return fetch( Redirectioni10n.WP_API_root, {
		method: 'post',
		body: form,
		credentials: 'same-origin',
	} );
};

const getApi = ( action, params, file ) => {
	const request = {
		action,
		params,
	};

	return getApiRequest( action, params, file )
		.then( data => {
			if ( request.status && request.statusText ) {
				request.status = data.status;
				request.statusText = data.statusText;
			}

			return data.text();
		} )
		.then( text => {
			request.raw = text;

			try {
				const json = JSON.parse( text );

				if ( json === 0 ) {
					throw { message: 'No response returned - WordPress did not understand the AJAX request', code: 0 };
				} else if ( json.error ) {
					throw json.error;
				}

				return json;
			} catch ( error ) {
				error.request = request;
				throw error;
			}
		} );
};

export default getApi;
