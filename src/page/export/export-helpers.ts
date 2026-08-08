import { __ } from '@wordpress/i18n';
import type { ExportFormat, ExportType, RedirectModule, RedirectScopeType } from './types';

function getExportTypeLabel( exportType: ExportType ) {
	if ( exportType === 'redirect' ) {
		return __( 'Redirects', 'redirection' );
	}

	if ( exportType === 'log' ) {
		return __( 'Redirect logs', 'redirection' );
	}

	if ( exportType === '404' ) {
		return __( '404 logs', 'redirection' );
	}

	if ( exportType === 'group' ) {
		return __( 'Groups', 'redirection' );
	}

	return __( 'Settings', 'redirection' );
}

function getExportTypesLabel( exportTypes: ExportType[] ) {
	return exportTypes.map( ( exportType ) => getExportTypeLabel( exportType ) ).join( ', ' );
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

	if ( format === 'redirects-file' ) {
		return __( '_redirects (Netlify/Cloudflare)', 'redirection' );
	}

	return __( 'Nginx rewrite rules', 'redirection' );
}

function getExportFormatOptionLabel( format: ExportFormat ) {
	if ( format === 'json' ) {
		return __( 'JSON (complete data)', 'redirection' );
	}

	return getExportFormatLabel( format );
}

function getExportFilename( exportType: ExportType, format: ExportFormat ) {
	if ( format === 'redirects-file' ) {
		return '_redirects';
	}

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

	if ( exportType === '404' ) {
		return `404-logs.${ format }`;
	}

	if ( exportType === 'group' ) {
		return `groups.${ format }`;
	}

	return `settings.${ format }`;
}

function getExportSelectionFilename( exportTypes: ExportType[], format: ExportFormat ) {
	if ( exportTypes.length === 1 && exportTypes[ 0 ] ) {
		return getExportFilename( exportTypes[ 0 ], format );
	}

	return `redirection-export.${ format }`;
}

export {
	getExportFilename,
	getExportFormatLabel,
	getExportFormatOptionLabel,
	getExportSelectionFilename,
	getExportTypeLabel,
	getExportTypesLabel,
	getRedirectModuleLabel,
	getRedirectScopeLabel,
};
