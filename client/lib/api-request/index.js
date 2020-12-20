/**
 * Internal dependencies
 */

import { postApiRequest, getApiRequest, uploadApiRequest, deleteApiRequest } from 'wp-plugin-lib/api-fetch/api-method';

/** @typedef {import('state/preset/type.js').PresetValue} PresetValue */

export const RedirectionApi = {
	setting: {
		get: () => getApiRequest( 'redirection/v1/setting' ),
		update: ( settings ) => postApiRequest( 'redirection/v1/setting', settings ),
	},
	redirect: {
		list: ( data ) => getApiRequest( 'redirection/v1/redirect', data ),
		update: ( id, data ) => postApiRequest( 'redirection/v1/redirect/' + id, data ),
		create: ( data, query ) => postApiRequest( 'redirection/v1/redirect', data, query ),
		matchPost: ( text ) => getApiRequest( 'redirection/v1/redirect/post', { text } ),
	},
	group: {
		list: ( data ) => getApiRequest( 'redirection/v1/group', data ),
		update: ( id, data ) => postApiRequest( 'redirection/v1/group/' + id, data ),
		create: ( data, query ) => postApiRequest( 'redirection/v1/group', data, query ),
	},
	log: {
		list: ( data ) => getApiRequest( 'redirection/v1/log', data ),
		deleteAll: ( data ) => deleteApiRequest( 'redirection/v1/log', data ),
	},
	error: {
		list: ( data ) => getApiRequest( 'redirection/v1/404', data ),
		deleteAll: ( data ) => deleteApiRequest( 'redirection/v1/404', data ),
	},
	import: {
		get: () => getApiRequest( 'redirection/v1/import' ),
		upload: ( group, file ) => uploadApiRequest( 'redirection/v1/import/file/' + group, {}, file ),
		pluginList: () => getApiRequest( 'redirection/v1/import/plugin' ),
		pluginImport: ( plugin ) => postApiRequest( 'redirection/v1/import/plugin', { plugin } ),
	},
	export: {
		file: ( module, format ) => getApiRequest( 'redirection/v1/export/' + module + '/' + format ),
	},
	plugin: {
		status: () => getApiRequest( 'redirection/v1/plugin' ),
		fix: ( name, value ) => postApiRequest( 'redirection/v1/plugin', { name, value } ),
		delete: () => deleteApiRequest( 'redirection/v1/plugin/delete' ),
		upgradeDatabase: ( upgrade ) => postApiRequest( 'redirection/v1/plugin/data', upgrade ? { upgrade } : {} ),
		checkApi: ( url, post = false ) => {
			const request = post
				? postApiRequest( 'redirection/v1/plugin/test', { test: 'ping' } )
				: getApiRequest( 'redirection/v1/plugin/test' );

			// Replace normal request URL with the URL to check
			request.url = url + request.url;

			return request;
		},
	},
	bulk: {
		redirect: ( action, data, table ) => postApiRequest( 'redirection/v1/bulk/redirect/' + action, data, table ),
		group: ( action, data, table ) => postApiRequest( 'redirection/v1/bulk/group/' + action, data, table ),
		log: ( action, data, table ) => postApiRequest( 'redirection/v1/bulk/log/' + action, data, table ),
		error: ( action, data, table ) => postApiRequest( 'redirection/v1/bulk/404/' + action, data, table ),
	},
};

const getRedirectLiUrl = ( url ) => {
	const base = 'https://api.redirect.li/v1/';
	return base + url + ( url.indexOf( '?' ) === -1 ? '?' : '&' ) + 'ref=redirection';
};

export const RedirectLiApi = {
	ip: {
		getGeo: ( ip ) => ( {
			url: getRedirectLiUrl( 'ip/' + ip + '?locale=' + Redirectioni10n.locale.localeSlug.substr( 0, 2 ) ),
			method: 'get',
		} ),
	},
	agent: {
		get: ( agent ) => ( {
			url: getRedirectLiUrl( 'useragent/' + encodeURIComponent( agent ) ),
			method: 'get',
		} ),
	},
	http: {
		get: ( url ) => ( {
			url: getRedirectLiUrl( 'http?url=' + encodeURIComponent( url ) ),
			method: 'get',
		} ),
	},
};
