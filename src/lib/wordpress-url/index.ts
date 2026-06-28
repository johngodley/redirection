const ALLOWED_PAGES = [ 'groups', '404s', 'log', 'import', 'export', 'options', 'support', 'site' ];

interface QueryParams {
	[ key: string ]: string | number | undefined;
}

export function getPageUrl( query?: string ): any {
	const searchParams = new URLSearchParams( query ? query.slice( 1 ) : document.location.search.slice( 1 ) );
	const result: any = {};

	searchParams.forEach( ( value, key ) => {
		result[ key ] = value;
	} );

	return result;
}

export function getWordPressUrl( query: QueryParams, defaults: QueryParams, url?: string ): string {
	const existing = getPageUrl( url );

	for ( const param in query ) {
		if ( query[ param ] && defaults[ param ] !== query[ param ] ) {
			existing[ param.toLowerCase() ] = query[ param ];
		} else if ( defaults[ param ] === query[ param ] ) {
			delete existing[ param.toLowerCase() ];
		}
	}

	const searchParams = new URLSearchParams();
	for ( const key in existing ) {
		if ( existing[ key ] !== undefined ) {
			searchParams.append( key, String( existing[ key ] ) );
		}
	}

	return '?' + searchParams.toString();
}

export function getPluginPage( url?: string ): string {
	const params = getPageUrl( url );

	if ( params.sub && ALLOWED_PAGES.includes( params.sub as string ) ) {
		return params.sub as string;
	}

	return 'redirect';
}

export function getRssUrl( token: string ): string {
	return window.Redirectioni10n.pluginRoot + '&sub=rss&module=1&token=' + encodeURIComponent( token );
}

export function getServerUrl( domain: string, path: string ): string {
	return domain.replace( /\/$/, '' ) + '/' + path.replace( /^\//, '' );
}

export function getOrigin( domain: string | null | undefined ): string {
	if ( ! domain ) {
		return document.location.origin;
	}
	// domain is stored as protocol://hostname by Redirection_Request::get_server()
	return /^https?:\/\//i.test( domain ) ? domain : 'https://' + domain;
}

interface Header {
	name: string;
	value: string;
}

export const isRedirection = ( headers: Header[] ): Header | undefined =>
	headers.find(
		( item ) =>
			( item.name === 'x-redirect-agent' || item.name === 'x-redirect-by' ) &&
			item.value.toLowerCase() === 'redirection'
	);

export const isCached = ( headers: Header[] ): Header | undefined =>
	headers.find( ( item ) => item.name.toLowerCase().slice( 0, 3 ) === 'cf-' );

interface Group {
	id: number;
	name: string;
	moduleName: string;
	[ key: string ]: any;
}

interface NestedGroupItem {
	value: number;
	label: string;
}

interface NestedGroup {
	label: string;
	value: NestedGroupItem[];
}

export function nestedGroups( groups: Group[] ): NestedGroup[] {
	const nested: { [ key: string ]: NestedGroupItem[] } = {};

	for ( const group of groups ) {
		if ( ! group ) {
			continue;
		}

		if ( ! nested[ group.moduleName ] ) {
			nested[ group.moduleName ] = [];
		}

		nested[ group.moduleName ]!.push( { value: group.id, label: group.name } );
	}

	return Object.keys( nested ).map( ( moduleName ) => ( { label: moduleName, value: nested[ moduleName ]! } ) );
}

export function getExportUrl( moduleId: string, modType: string ): string {
	const nonce = window.Redirectioni10n.api.WP_API_nonce;

	return (
		window.Redirectioni10n.pluginRoot +
		'&sub=export&export=' +
		encodeURIComponent( moduleId ) +
		'&exporter=' +
		encodeURIComponent( modType ) +
		'&_wpnonce=' +
		encodeURIComponent( nonce )
	);
}
