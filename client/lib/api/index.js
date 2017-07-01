/* global fetch, Redirectioni10n */

const addData = ( form, data, preName ) => {
	for ( const variable in data ) {
		if ( data[ variable ] !== '' && data[ variable ] !== undefined ) {
			if ( typeof data[ variable ] === 'object' ) {
				addData( form, data[ variable ], variable + '_' );
			} else {
				form.append( preName + variable, data[ variable ] );
			}
		}
	}
};

const getApi = ( action, data ) => {
	const form = new FormData();

	form.append( 'action', action );
	form.append( '_wpnonce', Redirectioni10n.WP_API_nonce );

	if ( data ) {
		addData( form, data, '' );
	}

	return fetch( Redirectioni10n.WP_API_root, {
		method: 'post',
		body: form,
		credentials: 'same-origin',
	} );
};

export default getApi;
