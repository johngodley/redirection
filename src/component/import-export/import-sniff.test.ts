import { getSeparatorLabel, sniffCsvText, sniffJsonText } from './import-sniff';

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
} );
