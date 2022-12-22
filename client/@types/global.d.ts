interface API {
	WP_API_root: string;
	routes: object;
}

interface Database {
	current: string;
	next: string;
	manual: string;
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
}

declare global {
	interface Window {
		Redirectioni10n: Redirectioni10n;
		redirection: any;
	}
}

export {}
