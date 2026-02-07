interface UrlParts {
	protocol?: string;
	host?: string;
	pathname?: string;
	[ key: string ]: any;
}

function parseUrlParts( url: string ): UrlParts {
	try {
		let fullUrl = url;
		if ( url.slice( 0, 5 ) !== 'http:' && url.slice( 0, 6 ) !== 'https:' ) {
			fullUrl = document.location.protocol + '//' + url;
		}

		const parsed = new URL( fullUrl );
		return {
			protocol: parsed.protocol,
			host: parsed.host,
			pathname: parsed.pathname,
		};
	} catch ( e ) {
		return {};
	}
}

export function getParts( url: string | false, buildUrl: ( parts: UrlParts ) => string ): string | false {
	if ( url === false ) {
		return false;
	}

	const parts = parseUrlParts( url );
	if ( parts.host && parts.host.length > 0 ) {
		return buildUrl( parts );
	}

	return '';
}

export function getDomainAndPathOnly( url: string | false ): string | false {
	return getParts( url, ( parts ) => {
		return parts.protocol + '//' + ( parts.host || '' ) + ( parts.pathname || '' );
	} );
}

export function getDomainOnly( url: string | false ): string | false {
	return getParts( url, ( parts ) => {
		return parts.protocol + '//' + ( parts.host || '' );
	} );
}
