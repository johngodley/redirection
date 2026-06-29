import { __ } from '@wordpress/i18n';
import type { ExportFormat, ExportType, RedirectModule, RedirectScopeType } from './types';

function getExportTypeLabel( exportType: ExportType ) {
	if ( exportType === 'redirect' ) {
		return __( 'Redirects', 'redirection' );
	}

	if ( exportType === 'log' ) {
		return __( 'Redirect logs', 'redirection' );
	}

	return __( '404 logs', 'redirection' );
}

function getRedirectModuleLabel( redirectModule: RedirectModule ) {
	if ( redirectModule === '1' ) {
		return __( 'WordPress redirects', 'redirection' );
	}

	if ( redirectModule === '2' ) {
		return __( 'Apache redirects', 'redirection' );
	}

	if ( redirectModule === '3' ) {
		return __( 'Nginx redirects', 'redirection' );
	}

	return __( 'Everything', 'redirection' );
}

function getRedirectScopeLabel( scopeType: RedirectScopeType, groupName: string ) {
	if ( scopeType === 'module' ) {
		return __( 'Module', 'redirection' );
	}

	if ( scopeType === 'group' ) {
		return groupName;
	}

	return __( 'Everything', 'redirection' );
}

function getExportFormatLabel( format: ExportFormat ) {
	if ( format === 'json' ) {
		return __( 'JSON', 'redirection' );
	}

	if ( format === 'csv' ) {
		return __( 'CSV', 'redirection' );
	}

	if ( format === 'apache' ) {
		return __( 'Apache .htaccess', 'redirection' );
	}

	return __( 'Nginx rewrite rules', 'redirection' );
}

function getExportFormatOptionLabel( format: ExportFormat ) {
	if ( format === 'json' ) {
		return __( 'Complete data (JSON)', 'redirection' );
	}

	return getExportFormatLabel( format );
}

function getExportFilename( exportType: ExportType, format: ExportFormat ) {
	if ( exportType === 'redirect' ) {
		if ( format === 'apache' ) {
			return 'redirects.htaccess';
		}

		if ( format === 'nginx' ) {
			return 'redirects.nginx';
		}

		return `redirects.${ format }`;
	}

	if ( exportType === 'log' ) {
		return `redirect-logs.${ format }`;
	}

	return `404-logs.${ format }`;
}

export {
	getExportFilename,
	getExportFormatLabel,
	getExportFormatOptionLabel,
	getExportTypeLabel,
	getRedirectModuleLabel,
	getRedirectScopeLabel,
};
