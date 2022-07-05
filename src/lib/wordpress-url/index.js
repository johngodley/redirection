/**
 * Internal dependencies
 */

export function getRssUrl( token ) {
	return window.Redirectioni10n.pluginRoot + '&sub=rss&module=1&token=' + encodeURIComponent( token );
}

export function getServerUrl( domain, path ) {
	return domain.replace( /\/$/, '' ) + '/' + path.replace( /^\//, '' );
}

export const isRedirection = headers => headers.find( item => ( item.name === 'x-redirect-agent' || item.name === 'x-redirect-by' ) && item.value.toLowerCase() === 'redirection' );
export const isCached = headers => headers.find( item => item.name.toLowerCase().slice( 0, 3 ) === 'cf-' );
