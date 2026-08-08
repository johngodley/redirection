import {
	getSeparatorLabel,
	getSniffedImportFormat,
	sniffApacheText,
	sniffCsvText,
	sniffImportFile,
	sniffImportText,
	sniffJsonText,
	sniffRedirectsFileText,
} from './import-sniff';

function createTextFile( content: string, name: string ) {
	const file = new File( [ content ], name );

	// jsdom's File doesn't implement Blob#text(), which sniffImportFile relies on.
	Object.defineProperty( file, 'text', { value: () => Promise.resolve( content ) } );

	return file;
}

describe( 'import-sniff', () => {
	it( 'detects a valid Redirection JSON export', () => {
		const result = sniffJsonText(
			JSON.stringify( {
				plugin: {
					version: '5.8.0',
					date: 'Mon, 22 Jun 2026 12:00:00 +0000',
				},
				groups: [ { id: 1, name: 'Redirections' } ],
				redirects: [
					{ id: 1, url: '/source' },
					{ id: 2, url: '/source-2' },
				],
			} )
		);

		expect( result ).toEqual( {
			format: 'json',
			valid: true,
			version: '5.8.0',
			contents: {
				groups: 1,
				redirects: 2,
			},
		} );
	} );

	it( 'detects a mixed Redirection JSON export', () => {
		const result = sniffJsonText(
			JSON.stringify( {
				plugin: {
					version: '5.8.0',
					date: 'Mon, 22 Jun 2026 12:00:00 +0000',
				},
				settings: {
					https: true,
					monitor_post: false,
				},
				groups: [ { id: 1, name: 'Redirections' } ],
				logs: [ { url: '/test', ip: '127.0.0.1' } ],
				errors_404: [ { url: '/missing', ip: '127.0.0.1' } ],
			} )
		);

		expect( result ).toEqual( {
			format: 'json',
			valid: true,
			version: '5.8.0',
			contents: {
				settings: 2,
				groups: 1,
				logs: 1,
				errors_404: 1,
			},
		} );
	} );

	it( 'detects sectioned Redirection JSON without plugin metadata', () => {
		const result = sniffJsonText(
			JSON.stringify( {
				settings: {
					https: true,
					monitor_post: false,
				},
				logs: [ { url: '/test', ip: '127.0.0.1' } ],
				errors_404: [ { url: '/missing', ip: '127.0.0.1' } ],
			} )
		);

		expect( result ).toEqual( {
			format: 'json',
			valid: true,
			version: undefined,
			contents: {
				settings: 2,
				logs: 1,
				errors_404: 1,
			},
		} );
	} );

	it( 'accepts redirects-only Redirection JSON', () => {
		expect( sniffJsonText( '{"redirects":[]}' ) ).toEqual( {
			format: 'json',
			valid: true,
			version: undefined,
			contents: {
				redirects: 0,
			},
		} );
	} );

	it( 'rejects JSON without supported Redirection sections', () => {
		expect( sniffJsonText( '{"plugin":{"version":"5.8.0"},"foo":[]}' ) ).toEqual( {
			format: 'json',
			valid: false,
			error: 'not-redirection-json',
		} );
	} );

	it( 'detects a CSV separator', () => {
		const result = sniffCsvText( 'source;target;regex\n/one;/two;0\n/three;/four;1' );

		expect( result ).toEqual( {
			format: 'csv',
			valid: true,
			type: 'redirects',
			importSupported: true,
			separator: ';',
			columns: 3,
			rows: 2,
		} );
		expect( getSeparatorLabel( ';' ) ).toBe( 'semicolon' );
	} );

	it( 'handles quoted separators in CSV', () => {
		const result = sniffCsvText( '"source","target","regex"\n"/one,here","/two",0' );

		expect( result ).toEqual( {
			format: 'csv',
			valid: true,
			type: 'redirects',
			importSupported: true,
			separator: ',',
			columns: 3,
			rows: 1,
		} );
	} );

	it( 'falls back to headerless redirect CSV', () => {
		expect( sniffCsvText( '/one,/two,0,301\n/three,/four,1,302' ) ).toEqual( {
			format: 'csv',
			valid: true,
			type: 'redirects',
			importSupported: true,
			separator: ',',
			columns: 4,
			rows: 2,
		} );
	} );

	it( 'detects group CSV and marks it as not importable', () => {
		expect( sniffCsvText( 'id,name,module_id,status\n1,Group,1,enabled' ) ).toEqual( {
			format: 'csv',
			valid: true,
			type: 'groups',
			importSupported: false,
			separator: ',',
			columns: 4,
			rows: 1,
		} );
	} );

	it( 'detects redirect log CSV and marks it as not importable', () => {
		expect( sniffCsvText( 'date,source,target,ip,referrer,agent\n2026-07-02,/one,/two,127.0.0.1,,Test' ) ).toEqual(
			{
				format: 'csv',
				valid: true,
				type: 'logs',
				importSupported: false,
				separator: ',',
				columns: 6,
				rows: 1,
			}
		);
	} );

	it( 'detects 404 log CSV and marks it as not importable', () => {
		expect( sniffCsvText( 'date,source,ip,referrer,useragent\n2026-07-02,/one,127.0.0.1,,Test' ) ).toEqual( {
			format: 'csv',
			valid: true,
			type: 'errors_404',
			importSupported: false,
			separator: ',',
			columns: 5,
			rows: 1,
		} );
	} );

	it( 'rejects unknown CSV layouts', () => {
		expect( sniffCsvText( 'alpha,beta,gamma\n1,2,3' ) ).toEqual( {
			format: 'csv',
			valid: false,
			separator: ',',
			columns: 3,
			rows: 1,
			error: 'unknown-csv-layout',
		} );
	} );

	it( 'detects Apache .htaccess redirect content', () => {
		expect( sniffApacheText( 'RewriteRule ^old-path$ /new-path [R=301,L]\nRedirect /one /two' ) ).toEqual( {
			format: 'apache',
			valid: true,
			importSupported: true,
			rules: 2,
			ruleTypes: [ 'rewrite', 'redirect' ],
		} );
	} );

	it( 'rejects unknown Apache .htaccess layouts', () => {
		expect( sniffApacheText( 'Options +FollowSymLinks\nRewriteEngine On' ) ).toEqual( {
			format: 'apache',
			valid: false,
			error: 'unknown-apache-layout',
		} );
	} );

	it( 'detects a valid _redirects file', () => {
		expect( sniffRedirectsFileText( '/old /new 301\n/blog/* /news/:splat 301' ) ).toEqual( {
			format: 'redirects-file',
			valid: true,
			importSupported: true,
			rules: 2,
		} );
	} );

	it( 'ignores comments and blank lines when counting _redirects rules', () => {
		expect( sniffRedirectsFileText( '# comment\n\n/old /new 301' ) ).toEqual( {
			format: 'redirects-file',
			valid: true,
			importSupported: true,
			rules: 1,
		} );
	} );

	it( 'rejects a _redirects file with no recognisable rules', () => {
		expect( sniffRedirectsFileText( '/old /new 200\n/a/*/b /c 301' ) ).toEqual( {
			format: 'redirects-file',
			valid: false,
			error: 'unknown-redirects-file-layout',
		} );
	} );

	it( 'detects pasted _redirects content', () => {
		expect( sniffImportText( '/old /new 301\n/blog/* /news/:splat 301' ) ).toEqual( {
			format: 'redirects-file',
			valid: true,
			importSupported: true,
			rules: 2,
		} );
	} );

	it( 'still detects pasted Apache content ahead of _redirects content', () => {
		expect( sniffImportText( 'RewriteRule ^old-path$ /new-path [R=301,L]' ) ).toEqual( {
			format: 'apache',
			valid: true,
			importSupported: true,
			rules: 1,
			ruleTypes: [ 'rewrite' ],
		} );
	} );

	it( 'falls back to CSV when pasted content matches neither Apache nor _redirects', () => {
		expect( sniffImportText( 'source,target\n/one,/two' ) ).toEqual( {
			format: 'csv',
			valid: true,
			type: 'redirects',
			importSupported: true,
			separator: ',',
			columns: 2,
			rows: 1,
		} );
	} );

	it( 'detects a _redirects file dropped with a non-standard name', async () => {
		const file = createTextFile( '/old /new 301', '_redirects.txt' );

		await expect( sniffImportFile( file ) ).resolves.toEqual( {
			format: 'redirects-file',
			valid: true,
			importSupported: true,
			rules: 1,
		} );
	} );

	it( 'rejects an unrecognised file that matches no known format', async () => {
		const file = createTextFile( 'just some random prose about redirects', 'notes' );

		await expect( sniffImportFile( file ) ).resolves.toEqual( {
			format: 'other',
			valid: false,
			error: 'unsupported-file-type',
		} );
	} );

	it( 'detects a Redirection JSON export dropped with a non-standard name', async () => {
		const file = createTextFile( JSON.stringify( { redirects: [ { id: 1, url: '/source' } ] } ), 'export' );

		await expect( sniffImportFile( file ) ).resolves.toEqual( {
			format: 'json',
			valid: true,
			contents: { redirects: 1 },
		} );
	} );

	describe( 'getSniffedImportFormat', () => {
		it( 'forwards a valid Redirection JSON sniff so the server uses the JSON importer', () => {
			expect( getSniffedImportFormat( { format: 'json', valid: true, contents: { redirects: 1 } } ) ).toBe(
				'json'
			);
		} );

		it( 'does not forward an invalid JSON sniff', () => {
			expect( getSniffedImportFormat( { format: 'json', valid: false, error: 'invalid-json' } ) ).toBeUndefined();
		} );

		it( 'forwards a supported _redirects sniff', () => {
			expect(
				getSniffedImportFormat( { format: 'redirects-file', valid: true, importSupported: true, rules: 1 } )
			).toBe( 'redirects-file' );
		} );

		it( 'does not forward an unsupported _redirects sniff', () => {
			expect(
				getSniffedImportFormat( { format: 'redirects-file', valid: true, importSupported: false, rules: 1 } )
			).toBeUndefined();
		} );

		it( 'returns undefined for a null sniff result', () => {
			expect( getSniffedImportFormat( null ) ).toBeUndefined();
		} );
	} );
} );
