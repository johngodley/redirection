/* global fetch, Redirectioni10n */

const getApi = ( action, data ) => {
	const form = new FormData();

	form.append( 'action', action );
	form.append( '_wpnonce', Redirectioni10n.WP_API_nonce );

	if ( data ) {
		for ( const variable in data ) {
			if ( data[ variable ] !== '' ) {
				form.append( variable, data[ variable ] );
			}
		}
	}

	return fetch( Redirectioni10n.WP_API_root, {
		method: 'post',
		body: form,
		credentials: 'same-origin',
	} );
};

export default getApi;
