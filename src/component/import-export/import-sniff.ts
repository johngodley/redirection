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
			type?: 'redirects' | 'groups' | 'logs' | 'errors_404';
			importSupported?: boolean;
			separator?: ',' | ';' | '|' | '\t';
			columns?: number;
			rows?: number;
			error?: 'empty-csv' | 'separator-not-detected' | 'unknown-csv-layout' | 'read-failed';
	  }
	| {
			format: 'apache';
			valid: boolean;
			importSupported?: boolean;
			rules?: number;
			ruleTypes?: Array< 'rewrite' | 'redirect' | 'redirectmatch' >;
			error?: 'unknown-apache-layout';
	  }
	| {
			format: 'redirects-file';
			valid: boolean;
			importSupported?: boolean;
			rules?: number;
			error?: 'unknown-redirects-file-layout';
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

	if ( file.name.toLowerCase() === '_redirects' ) {
		return sniffRedirectsFileText( text );
	}

	if ( isJsonFile( file ) ) {
		return sniffJsonText( text );
	}

	if ( file.name.toLowerCase().endsWith( '.csv' ) ) {
		return sniffCsvText( text );
	}

	if ( file.name.toLowerCase().endsWith( '.htaccess' ) ) {
		return sniffApacheText( text );
	}

	const genericResult = sniffImportText( text );

	if ( genericResult.valid ) {
		return genericResult;
	}

	return {
		format: 'other',
		valid: false,
		error: 'unsupported-file-type',
	};
}

export function sniffImportText( text: string ): ImportSniffResult {
	const normalized = text.replace( /^\ufeff/, '' ).trim();

	if ( normalized.length === 0 ) {
		return {
			format: 'other',
			valid: false,
			error: 'read-failed',
		};
	}

	if ( normalized.startsWith( '{' ) || normalized.startsWith( '[' ) ) {
		return sniffJsonText( text );
	}

	const apacheResult = sniffApacheText( text );
	if ( apacheResult.valid ) {
		return apacheResult;
	}

	const redirectsFileResult = sniffRedirectsFileText( text );
	if ( redirectsFileResult.valid ) {
		return redirectsFileResult;
	}

	return sniffCsvText( text );
}

export function sniffApacheText( text: string ): ImportSniffResult {
	const lines = text
		.replace( /^\ufeff/, '' )
		.replace( /\r\n/g, '\n' )
		.replace( /\r/g, '\n' )
		.split( '\n' )
		.map( ( line ) => line.trim() )
		.filter( ( line ) => line.length > 0 && ! line.startsWith( '#' ) );

	if ( lines.length === 0 ) {
		return {
			format: 'apache',
			valid: false,
			error: 'unknown-apache-layout',
		};
	}

	let rules = 0;
	const ruleTypes = new Set< 'rewrite' | 'redirect' | 'redirectmatch' >();

	lines.forEach( ( line ) => {
		if ( /^rewriterule\s+/i.test( line ) ) {
			rules += 1;
			ruleTypes.add( 'rewrite' );
		} else if ( /^redirectmatch\s+/i.test( line ) ) {
			rules += 1;
			ruleTypes.add( 'redirectmatch' );
		} else if ( /^redirect\s+/i.test( line ) ) {
			rules += 1;
			ruleTypes.add( 'redirect' );
		}
	} );

	if ( rules === 0 ) {
		return {
			format: 'apache',
			valid: false,
			error: 'unknown-apache-layout',
		};
	}

	return {
		format: 'apache',
		valid: true,
		importSupported: true,
		rules,
		ruleTypes: Array.from( ruleTypes ),
	};
}

const REDIRECTS_FILE_STATUS_CODES = [ '301', '302', '303', '307', '308' ];

export function sniffRedirectsFileText( text: string ): ImportSniffResult {
	const lines = text
		.replace( /^\ufeff/, '' )
		.replace( /\r\n/g, '\n' )
		.replace( /\r/g, '\n' )
		.split( '\n' )
		.map( ( line ) => line.trim() )
		.filter( ( line ) => line.length > 0 && ! line.startsWith( '#' ) );

	let rules = 0;

	lines.forEach( ( line ) => {
		const parts = line.split( /\s+/ );

		if ( parts.length < 2 || parts.length > 3 ) {
			return;
		}

		const from = parts[ 0 ] || '';
		const to = parts[ 1 ] || '';
		const status = ( parts[ 2 ] || '301' ).replace( /!$/, '' );

		if ( ! REDIRECTS_FILE_STATUS_CODES.includes( status ) ) {
			return;
		}

		if ( /:(?!splat\b)[a-zA-Z_]/.test( `${ from } ${ to }` ) ) {
			return;
		}

		const splatCount = ( from.match( /\*/g ) || [] ).length;

		if ( splatCount > 1 || ( splatCount === 1 && ! from.endsWith( '*' ) ) ) {
			return;
		}

		if ( splatCount === 0 && to.includes( ':splat' ) ) {
			return;
		}

		rules++;
	} );

	if ( rules === 0 ) {
		return {
			format: 'redirects-file',
			valid: false,
			error: 'unknown-redirects-file-layout',
		};
	}

	return {
		format: 'redirects-file',
		valid: true,
		importSupported: true,
		rules,
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

		const result: ImportSniffResult = {
			format: 'json',
			valid: true,
			contents,
		};

		if ( typeof data.plugin?.version === 'string' ) {
			result.version = data.plugin.version;
		}

		return result;
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

	const header = parseCsvLine( sample[ 0 ] || '', detected.separator ).map( normalizeCsvHeader );
	const csvType = getCsvTypeFromHeader( header );

	if ( csvType === null ) {
		const firstRow = parseCsvLine( sample[ 0 ] || '', detected.separator );

		if ( looksLikeRedirectCsvRow( firstRow ) ) {
			return {
				format: 'csv',
				valid: true,
				type: 'redirects',
				importSupported: true,
				separator: detected.separator,
				columns: detected.columns,
				rows: lines.length,
			};
		}

		return {
			format: 'csv',
			valid: false,
			separator: detected.separator,
			columns: detected.columns,
			rows: Math.max( lines.length - 1, 0 ),
			error: 'unknown-csv-layout',
		};
	}

	return {
		format: 'csv',
		valid: true,
		type: csvType,
		importSupported: csvType === 'redirects',
		separator: detected.separator,
		columns: detected.columns,
		rows: Math.max( lines.length - 1, 0 ),
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

function normalizeCsvHeader( value: string ) {
	return value.trim().toLowerCase().replace( /\s+/g, ' ' );
}

function getCsvTypeFromHeader( header: string[] ) {
	if ( matchesHeader( header, [ 'source', 'target' ] ) || matchesHeader( header, [ 'source url', 'target url' ] ) ) {
		return 'redirects' as const;
	}

	if ( matchesHeader( header, [ 'id', 'name', 'module_id', 'status' ] ) ) {
		return 'groups' as const;
	}

	if ( matchesHeader( header, [ 'date', 'source', 'target', 'ip', 'referrer', 'agent' ] ) ) {
		return 'logs' as const;
	}

	if ( matchesHeader( header, [ 'date', 'source', 'ip', 'referrer', 'useragent' ] ) ) {
		return 'errors_404' as const;
	}

	return null;
}

function matchesHeader( header: string[], expected: string[] ) {
	if ( header.length < expected.length ) {
		return false;
	}

	return expected.every( ( value, index ) => header[ index ] === value );
}

function looksLikeRedirectCsvRow( row: string[] ) {
	if ( row.length < 2 || row.length > 4 ) {
		return false;
	}

	const source = row[ 0 ]?.trim() || '';
	const target = row[ 1 ]?.trim() || '';
	const regex = row[ 2 ]?.trim();
	const code = row[ 3 ]?.trim();

	if ( source.length === 0 || target.length === 0 ) {
		return false;
	}

	if ( regex !== undefined && regex !== '' && regex !== '0' && regex !== '1' ) {
		return false;
	}

	if ( code !== undefined && code !== '' ) {
		const codeNumber = Number( code );

		if ( ! Number.isInteger( codeNumber ) || codeNumber < 100 || codeNumber > 599 ) {
			return false;
		}
	}

	return true;
}

/**
 * The importer used server-side is normally guessed from the uploaded filename, which can
 * disagree with what was actually sniffed from the content (eg a `_redirects` file saved as
 * `_redirects.txt`, or a file with no recognised extension at all). Where sniffing already
 * identified a supported format, pass it through explicitly so import uses the same format
 * the UI told the user it detected.
 * @param result Sniff result for the file or pasted content being imported.
 */
export function getSniffedImportFormat(
	result: ImportSniffResult | null
): 'apache' | 'csv' | 'redirects-file' | 'json' | undefined {
	if ( result === null || ! result.valid ) {
		return undefined;
	}

	if ( result.format === 'json' ) {
		return result.format;
	}

	if ( result.format !== 'apache' && result.format !== 'csv' && result.format !== 'redirects-file' ) {
		return undefined;
	}

	return result.importSupported ? result.format : undefined;
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
