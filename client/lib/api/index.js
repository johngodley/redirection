/* global fetch, Redirectioni10n */
const addData = ( form, data, preName ) => {
	for ( const variable in data ) {
		if ( data[ variable ] !== undefined ) {
			if ( typeof data[ variable ] === 'object' ) {
				addData( form, data[ variable ], variable + '_' );
			} else {
				form.append( preName + variable, data[ variable ] );
			}
		}
	}
};

const addFileData = ( form, data, preName ) => {
	for ( const variable in data ) {
		if ( data[ variable ] !== undefined ) {
			form.append( preName + variable, data[ variable ] );
		}
	}
};

const getApiRequest = ( action, data ) => {
	const form = new FormData();

	form.append( 'action', action );
	form.append( '_wpnonce', Redirectioni10n.WP_API_nonce );

	if ( data ) {
		if ( action === 'red_import_data' ) {
			addFileData( form, data, '' );
		} else {
			addData( form, data, '' );
		}
	}

	return fetch( Redirectioni10n.WP_API_root, {
		method: 'post',
		body: form,
		credentials: 'same-origin',
	} );
};

const getApi = ( action, params ) => {
	const request = {
		action,
		params,
	};

	return getApiRequest( action, params )
		.then( data => {
			request.status = data.status;
			request.statusText = data.statusText;
			return data.text();
		} )
		.then( text => {
			request.raw = text;

			try {
				const json = JSON.parse( text );

				if ( json === 0 ) {
					throw { message: 'No response returned - WordPress did not understand AJAX request', code: 0 };
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
