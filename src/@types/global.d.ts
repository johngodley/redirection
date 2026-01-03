interface API {
	WP_API_root: string;
	WP_API_nonce: string;
	current: string;
	site_health: string;
	routes: {
		[key: string]: string;
	};
}

interface Database {
	current: string;
	next: string;
	manual: string[];
}

interface Caps {
	capabilities: string[];
	pages: string[];
}

interface Redirectioni10n {
	pluginRoot: string;
	locale: string;
	versions: string;
	pluginBaseUrl: string;
	api: API;
	version: string;
	database: Database;
	update_notice: string;
	settings: any;
	per_page: string;
	caps: Caps;
}

declare global {
	interface Window {
		Redirectioni10n: Redirectioni10n;
		redirection: any;
	}

	var Redirectioni10n: Redirectioni10n;
}

export {}
