import { getExportFilename, getExportFormatLabel } from './export-helpers';

describe( 'export-helpers', () => {
	describe( 'getExportFormatLabel', () => {
		it( 'labels the redirects-file format for Netlify/Cloudflare', () => {
			expect( getExportFormatLabel( 'redirects-file' ) ).toBe( '_redirects (Netlify/Cloudflare)' );
		} );
	} );

	describe( 'getExportFilename', () => {
		it( 'always names the redirects-file export _redirects, with no extension', () => {
			expect( getExportFilename( 'redirect', 'redirects-file' ) ).toBe( '_redirects' );
		} );
	} );
} );
