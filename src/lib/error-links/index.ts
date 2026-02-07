/* global Redirectioni10n, REDIRECTION_VERSION */

interface ErrorLinks {
	url: string;
	http: string;
	api: string;
	rootUrl: string;
	siteHealth: string;
}

export function getErrorLinks(): ErrorLinks {
	return {
		url: 'https://redirection.me/support/problems/rest-api/#url',
		http: 'https://redirection.me/support/problems/rest-api/#http',
		api: 'https://redirection.me/support/problems/rest-api/',
		rootUrl: Redirectioni10n.api.WP_API_root,
		siteHealth: Redirectioni10n.api.site_health,
	};
}

export function getErrorDetails(): string[] {
	return Redirectioni10n.versions.split( '\n' ).concat( [ 'Query: ' + document.location.search ] );
}

export function getCacheBuster(): string {
	return 'Buster: ' + REDIRECTION_VERSION + ' === ' + Redirectioni10n.version;
}
