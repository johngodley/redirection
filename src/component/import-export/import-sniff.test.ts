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
			groups: 1,
			redirects: 2,
		} );
	} );

	it( 'rejects invalid Redirection JSON', () => {
		expect( sniffJsonText( '{"redirects":[]}' ) ).toEqual( {
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
			separator: ';',
			columns: 3,
			rows: 3,
		} );
		expect( getSeparatorLabel( ';' ) ).toBe( 'semicolon' );
	} );

	it( 'handles quoted separators in CSV', () => {
		const result = sniffCsvText( '"source","target","regex"\n"/one,here","/two",0' );

		expect( result ).toEqual( {
			format: 'csv',
			valid: true,
			separator: ',',
			columns: 3,
			rows: 2,
		} );
	} );
} );
