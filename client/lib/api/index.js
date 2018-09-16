/* global fetch, Redirectioni10n */
/**
 *
 * @format
 */

/**
 * Internal dependencies
 */
import querystring from 'querystring';

const removeEmpty = item =>
	Object.keys( item )
		.filter( key => item[ key ] )
		.reduce( ( newObj, key ) => {
			newObj[ key ] = item[ key ];
			return newObj;
		}, {} );

const getRedirectionUrl = ( path, params = {} ) => {
	const base = Redirectioni10n.WP_API_root + 'redirection/v1/' + path + '/';

	// Some servers dont pass the X-WP-Nonce through to PHP
	params._wpnonce = Redirectioni10n.WP_API_nonce;

	if ( params && Object.keys( params ).length > 0 ) {
		params = removeEmpty( params );

		if ( Object.keys( params ).length > 0 ) {
			const querybase =
				base +
				( Redirectioni10n.WP_API_root.indexOf( '?' ) === -1 ? '?' : '&' ) +
				querystring.stringify( params );

			if ( Redirectioni10n.WP_API_root.indexOf( 'page=redirection.php' ) !== -1 ) {
				return querybase.replace( /page=(\d+)/, 'ppage=$1' );
			}

			return querybase;
		}
	}

	return base;
};

const apiHeaders = url => {
	if ( url.indexOf( 'rest_route' ) !== -1 || url.indexOf( '/wp-json/' ) !== -1 ) {
		return new Headers( {
			// 'X-WP-Nonce': Redirectioni10n.WP_API_nonce,
			'Content-Type': 'application/json; charset=utf-8',
		} );
	}

	return new Headers( {
		'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8',
	} );
};

const apiRequest = url => ( {
	url,
	headers: apiHeaders( url ),
	credentials: 'same-origin',
} );

const deleteApiRequest = ( path, params ) => ( {
	...apiRequest( getRedirectionUrl( path, params ) ),
	method: 'post',
} );
const getApiRequest = ( path, params = {} ) => ( {
	...apiRequest( getRedirectionUrl( path, params ) ),
	method: 'get',
} );
const uploadApiRequest = ( path, file ) => {
	const request = { ...apiRequest( getRedirectionUrl( path ) ), method: 'post' };

	request.headers.delete( 'Content-Type' );
	request.body = new FormData();
	request.body.append( 'file', file );

	return request;
};

const postApiRequest = ( path, params = {}, query = {} ) => {
	const request = { ...apiRequest( getRedirectionUrl( path, query ) ), method: 'post', params };

	if ( Object.keys( params ).length > 0 ) {
		request.body = JSON.stringify( params );
	}

	return request;
};

export const RedirectionApi = {
	setting: {
		get: () => getApiRequest( 'setting' ),
		update: settings => postApiRequest( 'setting', settings ),
	},
	redirect: {
		list: data => getApiRequest( 'redirect', data ),
		update: ( id, data ) => postApiRequest( 'redirect/' + id, data ),
		create: data => postApiRequest( 'redirect', data ),
	},
	group: {
		list: data => getApiRequest( 'group', data ),
		update: ( id, data ) => postApiRequest( 'group/' + id, data ),
		create: data => postApiRequest( 'group', data ),
	},
	log: {
		list: data => getApiRequest( 'log', data ),
		deleteAll: data => deleteApiRequest( 'log', data ),
	},
	error: {
		list: data => getApiRequest( '404', data ),
		deleteAll: data => deleteApiRequest( '404', data ),
	},
	import: {
		get: () => getApiRequest( 'import' ),
		upload: ( group, file ) => uploadApiRequest( 'import/file/' + group, file ),
		pluginList: () => getApiRequest( 'import/plugin' ),
		pluginImport: plugin => postApiRequest( 'import/plugin/' + plugin ),
	},
	export: {
		file: ( module, format ) => getApiRequest( 'export/' + module + '/' + format ),
	},
	plugin: {
		status: () => getApiRequest( 'plugin' ),
		fix: () => postApiRequest( 'plugin' ),
		delete: () => deleteApiRequest( 'plugin/delete' ),
	},
	bulk: {
		redirect: ( action, data, table ) => postApiRequest( 'bulk/redirect/' + action, data, table ),
		group: ( action, data, table ) => postApiRequest( 'bulk/group/' + action, data, table ),
		log: ( action, data, table ) => postApiRequest( 'bulk/log/' + action, data, table ),
		error: ( action, data, table ) => postApiRequest( 'bulk/404/' + action, data, table ),
	},
};

const getRedirectLiUrl = url => {
	const base =
		process.env.NODE_ENV === 'development'
			? 'http://localhost:5000/v1/'
			: 'https://api.redirect.li/v1/';
	return base + url + ( url.indexOf( '?' ) === -1 ? '?' : '&' ) + 'ref=redirection';
};

export const RedirectLiApi = {
	ip: {
		getGeo: ip => ( {
			url: getRedirectLiUrl( 'ip/' + ip + '?locale=' + Redirectioni10n.localeSlug.substr( 0, 2 ) ),
			method: 'get',
		} ),
	},
	agent: {
		get: agent => ( {
			url: getRedirectLiUrl( 'useragent/' + encodeURIComponent( agent ) ),
			method: 'get',
		} ),
	},
	http: {
		get: url => ( {
			url: getRedirectLiUrl( 'http?url=' + encodeURIComponent( url ) ),
			method: 'get',
		} ),
	},
};

const getAction = request =>
	request.url.replace( Redirectioni10n.WP_API_root, '' ).replace( /[\?&]_wpnonce=[a-f0-9]*/, '' ) +
	' ' +
	request.method.toUpperCase();

const getErrorMessage = json => {
	if ( json === 0 ) {
		return 'Admin AJAX returned 0';
	}

	if ( json.message ) {
		return json.message;
	}

	return 'Unknown error ' + json;
};

const getErrorCode = json => {
	if ( json.error_code ) {
		return json.error_code;
	}

	if ( json.data && json.data.error_code ) {
		return json.data.error_code;
	}

	if ( json === 0 ) {
		return 'admin-ajax';
	}

	if ( json.code ) {
		return json.code;
	}

	return 'unknown';
};

export const getApi = request => {
	request.action = getAction( request );

	return fetch( request.url, request )
		.then( data => {
			if ( ! data || ! data.status ) {
				throw { message: 'No data or status object returned in request', code: 0 };
			}

			if ( data.status && data.statusText !== undefined ) {
				request.status = data.status;
				request.statusText = data.statusText;
			}

			if ( data.headers.get( 'x-wp-nonce' ) ) {
				Redirectioni10n.WP_API_nonce = data.headers.get( 'x-wp-nonce' );
			}

			return data.text();
		} )
		.then( text => {
			request.raw = text;

			try {
				const json = JSON.parse( text.replace( /\ufeff/, '' ) );

				if ( request.status && request.status !== 200 ) {
					throw {
						message: getErrorMessage( json ),
						code: getErrorCode( json ),
						request,
						data: json.data ? json.data : null,
					};
				}

				if ( json === 0 ) {
					throw { message: 'Failed to get data', code: 'json-zero' };
				}

				return json;
			} catch ( error ) {
				error.request = request;
				throw error;
			}
		} );
};
