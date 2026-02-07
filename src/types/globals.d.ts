/**
 * Global type definitions for Redirection plugin
 */

interface RedirectionDatabase {
	current: string;
	next: string;
	manual: string[];
}

interface RedirectionApi {
	WP_API_root: string;
	WP_API_nonce: string;
	current: string;
	site_health: string;
	routes: {
		[key: string]: string;
	};
}

interface RedirectionCaps {
	capabilities: string[];
	pages: string[];
}

interface Redirectioni10n {
	pluginRoot: string;
	pluginBaseUrl: string;
	locale: string;
	version: string;
	versions: string;
	database: RedirectionDatabase;
	api: RedirectionApi;
	caps: RedirectionCaps;
	update_notice: string | false;
	settings: any;
	per_page: string;
}

declare global {
	interface Window {
		Redirectioni10n: Redirectioni10n;
		redirection?: string;
		wpApiSettings?: {
			nonce: string;
		};
	}

	var Redirectioni10n: Redirectioni10n;
	var REDIRECTION_VERSION: string;

	namespace NodeJS {
		interface ProcessEnv {
			NODE_ENV?: 'development' | 'production' | 'test';
		}
	}

	var process: {
		env: NodeJS.ProcessEnv;
	};
}

export {};
