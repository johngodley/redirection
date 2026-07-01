export type ImportSniffResult =
	| {
			format: 'json';
			valid: boolean;
			version?: string;
			contents?: Partial< Record< 'settings' | 'groups' | 'redirects' | 'logs' | 'errors_404', number > >;
			error?: 'not-redirection-json' | 'invalid-json' | 'read-failed';
	  }
	| {
			format: 'csv';
			valid: boolean;
			separator?: ',' | ';' | '|' | '\t';
			columns?: number;
			rows?: number;
			error?: 'empty-csv' | 'separator-not-detected' | 'read-failed';
	  }
	| {
			format: 'other';
			valid: false;
			error?: 'unsupported-file-type' | 'read-failed';
	  };

type CsvSeparator = ',' | ';' | '|' | '\t';

const CSV_SEPARATORS: CsvSeparator[] = [ ',', ';', '|', '\t' ];
const JSON_SECTIONS = [ 'settings', 'groups', 'redirects', 'logs', 'errors_404' ] as const;

type JsonSection = ( typeof JSON_SECTIONS )[ number ];

export function isJsonFile( file: File ) {
	return file.name.toLowerCase().endsWith( '.json' );
}

export async function sniffImportFile( file: File ): Promise< ImportSniffResult > {
	const text = await file.text();

	if ( isJsonFile( file ) ) {
		return sniffJsonText( text );
	}

	if ( file.name.toLowerCase().endsWith( '.csv' ) ) {
		return sniffCsvText( text );
	}

	return {
		format: 'other',
		valid: false,
		error: 'unsupported-file-type',
	};
}

export function sniffJsonText( text: string ): ImportSniffResult {
	try {
		const data = JSON.parse( text.replace( /^\ufeff/, '' ) ) as {
			plugin?: { version?: string };
			groups?: unknown[];
			redirects?: unknown[];
			settings?: Record< string, unknown >;
			logs?: unknown[];
			errors_404?: unknown[];
		};

		const contents: Partial< Record< JsonSection, number > > = {};

		if ( typeof data !== 'object' || data === null || Array.isArray( data ) ) {
			return {
				format: 'json',
				valid: false,
				error: 'not-redirection-json',
			};
		}

		if ( Array.isArray( data.groups ) ) {
			contents.groups = data.groups.length;
		}

		if ( Array.isArray( data.redirects ) ) {
			contents.redirects = data.redirects.length;
		}

		if ( typeof data.settings === 'object' && data.settings !== null ) {
			contents.settings = Object.keys( data.settings ).length;
		}

		if ( Array.isArray( data.logs ) ) {
			contents.logs = data.logs.length;
		}

		if ( Array.isArray( data.errors_404 ) ) {
			contents.errors_404 = data.errors_404.length;
		}

		if ( Object.keys( contents ).length === 0 ) {
			return {
				format: 'json',
				valid: false,
				error: 'not-redirection-json',
			};
		}

		return {
			format: 'json',
			valid: true,
			version: typeof data.plugin?.version === 'string' ? data.plugin.version : undefined,
			contents,
		};
	} catch {
		return {
			format: 'json',
			valid: false,
			error: 'invalid-json',
		};
	}
}

export function sniffCsvText( text: string ): ImportSniffResult {
	const lines = text
		.replace( /^\ufeff/, '' )
		.replace( /\r\n/g, '\n' )
		.replace( /\r/g, '\n' )
		.split( '\n' )
		.map( ( line ) => line.trim() )
		.filter( ( line ) => line.length > 0 );

	if ( lines.length === 0 ) {
		return {
			format: 'csv',
			valid: false,
			error: 'empty-csv',
		};
	}

	const sample = lines.slice( 0, 5 );
	const detected = detectCsvSeparator( sample );

	if ( detected === null ) {
		return {
			format: 'csv',
			valid: false,
			error: 'separator-not-detected',
		};
	}

	return {
		format: 'csv',
		valid: true,
		separator: detected.separator,
		columns: detected.columns,
		rows: lines.length,
	};
}

function detectCsvSeparator( lines: string[] ) {
	let best: { separator: CsvSeparator; columns: number; score: number } | null = null;

	for ( const separator of CSV_SEPARATORS ) {
		const counts = lines.map( ( line ) => parseCsvLine( line, separator ).length );
		const first = counts[ 0 ] || 0;

		if ( first < 2 ) {
			continue;
		}

		const matches = counts.filter( ( count ) => count === first ).length;
		const score = matches * 10 + first;

		if ( best === null || score > best.score ) {
			best = {
				separator,
				columns: first,
				score,
			};
		}
	}

	return best;
}

function parseCsvLine( line: string, separator: CsvSeparator ) {
	const values: string[] = [];
	let current = '';
	let inQuotes = false;

	for ( let pos = 0; pos < line.length; pos++ ) {
		const char = line[ pos ];
		const next = line[ pos + 1 ];

		if ( char === '"' ) {
			if ( inQuotes && next === '"' ) {
				current += '"';
				pos++;
			} else {
				inQuotes = ! inQuotes;
			}
		} else if ( char === separator && ! inQuotes ) {
			values.push( current );
			current = '';
		} else {
			current += char;
		}
	}

	values.push( current );

	return values;
}

export function getSeparatorLabel( separator: CsvSeparator ) {
	if ( separator === ',' ) {
		return 'comma';
	}

	if ( separator === ';' ) {
		return 'semicolon';
	}

	if ( separator === '|' ) {
		return 'pipe';
	}

	return 'tab';
}
