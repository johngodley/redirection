import { postApiRequest, getApiRequest, uploadApiRequest, deleteApiRequest } from '@wp-plugin-lib/api-fetch/api-method';

interface ApiRequest {
	url: string;
	method: string;
}

interface TableParams {
	[ key: string ]: any;
}

export const RedirectionApi = {
	setting: {
		get: () => getApiRequest( 'redirection/v1/setting' ),
		update: ( settings: any ) => postApiRequest( 'redirection/v1/setting', settings ),
	},
	redirect: {
		list: ( data: TableParams ) => getApiRequest( 'redirection/v1/redirect', data ),
		update: ( id: number, data: any ) => postApiRequest( 'redirection/v1/redirect/' + id, data ),
		create: ( data: any, query?: TableParams ) => postApiRequest( 'redirection/v1/redirect', data, query ),
		matchPost: ( text: string ) => getApiRequest( 'redirection/v1/redirect/post', { text } ),
	},
	group: {
		list: ( data: TableParams ) => getApiRequest( 'redirection/v1/group', data ),
		update: ( id: number, data: any ) => postApiRequest( 'redirection/v1/group/' + id, data ),
		create: ( data: any, query?: TableParams ) => postApiRequest( 'redirection/v1/group', data, query ),
	},
	log: {
		list: ( data: TableParams ) => getApiRequest( 'redirection/v1/log', data ),
		deleteAll: ( data: any ) => deleteApiRequest( 'redirection/v1/log', data ),
	},
	error: {
		list: ( data: TableParams ) => getApiRequest( 'redirection/v1/404', data ),
		deleteAll: ( data: any ) => deleteApiRequest( 'redirection/v1/404', data ),
	},
	import: {
		get: () => getApiRequest( 'redirection/v1/import' ),
		upload: ( group: string, file: File, options: TableParams = {} ) =>
			uploadApiRequest( 'redirection/v1/import/file/' + group, options, file ),
		pluginList: () => getApiRequest( 'redirection/v1/import/plugin' ),
		pluginImport: ( data: TableParams ) => postApiRequest( 'redirection/v1/import/plugin', data ),
		pluginPreview: ( plugin: string, data: TableParams ) =>
			getApiRequest( 'redirection/v1/import/plugin/' + encodeURIComponent( plugin ) + '/preview', data ),
	},
	export: {
		file: ( module: string, format: string ) => getApiRequest( 'redirection/v1/export/' + module + '/' + format ),
		redirect: ( data: TableParams ) => getApiRequest( 'redirection/v1/export/redirect', data ),
		redirectPreview: ( data: TableParams ) => getApiRequest( 'redirection/v1/export/redirect/preview', data ),
		bundle: ( data: TableParams ) => getApiRequest( 'redirection/v1/export/bundle', data ),
		bundlePreview: ( data: TableParams ) => getApiRequest( 'redirection/v1/export/bundle/preview', data ),
		group: ( format: string, data: TableParams = {} ) =>
			getApiRequest( 'redirection/v1/export/group/' + format, data ),
		log: ( format: string, data: TableParams = {} ) => getApiRequest( 'redirection/v1/export/log/' + format, data ),
		logPreview: ( data: TableParams = {} ) => getApiRequest( 'redirection/v1/export/log/preview', data ),
		error: ( format: string, data: TableParams = {} ) =>
			getApiRequest( 'redirection/v1/export/404/' + format, data ),
		errorPreview: ( data: TableParams = {} ) => getApiRequest( 'redirection/v1/export/404/preview', data ),
	},
	plugin: {
		status: () => getApiRequest( 'redirection/v1/plugin' ),
		fix: ( name: string, value: any ) => postApiRequest( 'redirection/v1/plugin', { name, value } ),
		delete: () => deleteApiRequest( 'redirection/v1/plugin/delete' ),
		upgradeDatabase: ( upgrade?: any ) =>
			postApiRequest( 'redirection/v1/plugin/data', upgrade ? { upgrade } : {} ),
		checkApi: ( url: string, post = false ) => {
			const request = post
				? postApiRequest( 'redirection/v1/plugin/test', { test: 'ping' } )
				: getApiRequest( 'redirection/v1/plugin/test' );

			request.url = url.slice( 0, 4 ) === 'http' ? url + request.url : request.url;

			return request;
		},
		finishSetup: () => postApiRequest( 'redirection/v1/plugin/finish', {} ),
		fixStatus: ( reason: string, current: string ) =>
			postApiRequest( 'redirection/v1/plugin/fix', { reason, current } ),
	},
	bulk: {
		redirect: ( action: string, data: any, table?: TableParams ) =>
			postApiRequest( 'redirection/v1/bulk/redirect/' + action, data, table ),
		group: ( action: string, data: any, table?: TableParams ) =>
			postApiRequest( 'redirection/v1/bulk/group/' + action, data, table ),
		log: ( action: string, data: any, table?: TableParams ) =>
			postApiRequest( 'redirection/v1/bulk/log/' + action, data, table ),
		error: ( action: string, data: any, table?: TableParams ) =>
			postApiRequest( 'redirection/v1/bulk/404/' + action, data, table ),
	},
};

const getRedirectLiUrl = ( url: string, version = 1 ): string => {
	const base = `https://api.redirect.li/v${ version }/`;

	return base + url;
};

export const RedirectLiApi = {
	ip: {
		getGeo: ( ip: string ): ApiRequest => ( {
			url: getRedirectLiUrl( 'ip/' + ip + '?locale=' + window.Redirectioni10n.locale.slice( 0, 2 ) ),
			method: 'get',
		} ),
	},
	agent: {
		get: ( agent: string ): ApiRequest => ( {
			url: getRedirectLiUrl( 'useragent/' + encodeURIComponent( agent ) ),
			method: 'get',
		} ),
	},
	http: {
		get: ( url: string ): ApiRequest => ( {
			url: getRedirectLiUrl( 'http?url=' + encodeURIComponent( url ), 2 ),
			method: 'get',
		} ),
	},
};
