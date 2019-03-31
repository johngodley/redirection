/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import * as parseUrl from 'url';

/**
 * Internal dependencies
 */

import ExternalLink from 'component/external-link';
import TableRow from './table-row';

export const isRegex = ( text ) => {
	if ( text.match( /[\*\\\(\)\[\]\^\$]/ ) !== null ) {
		return true;
	}

	if ( text.indexOf( '.?' ) !== -1 ) {
		return true;
	}

	return false;
};

const beginsWith = ( str, match ) => match.indexOf( str ) === 0 || str.substr( 0, match.length ) === match;

export const getWarningFromState = ( item ) => {
	const warnings = [];
	const { url, flag_regex, action_data = {} } = item;
	const { url: targetUrl = '', logged_in = '', logged_out = '', url_from = '', url_notfrom = '' } = item.action_data;

	if ( Array.isArray( url ) ) {
		return warnings;
	}

	// Anchor value
	if ( url.indexOf( '#' ) !== -1 ) {
		warnings.push(
			<ExternalLink url="https://redirection.me/support/faq/#anchor">{ __( 'Anchor values are not sent to the server and cannot be redirected.' ) }</ExternalLink>,
		);
	}

	// Server redirect
	if ( url.substr( 0, 4 ) === 'http' && url.indexOf( document.location.origin ) === -1 ) {
		warnings.push(
			<ExternalLink url="https://redirection.me/support/matching-redirects/#server">
				{ __( 'This will be converted to a server redirect for the domain {{code}}%(server)s{{/code}}.', {
					components: {
						code: <code />,
					},
					args: {
						server: parseUrl.parse( url ).hostname,
					},
				} ) }
			</ExternalLink>
		);
	}

	// Relative URL without leading slash
	if ( url.substr( 0, 4 ) !== 'http' && url.substr( 0, 1 ) !== '/' && url.length > 0 && flag_regex === false ) {
		warnings.push( __( 'The source URL should probably start with a {{code}}/{{/code}}', {
			components: {
				code: <code />,
			},
		} ) );
	}

	// Regex without checkbox
	if ( isRegex( url ) && flag_regex === false ) {
		warnings.push(
			<ExternalLink url="https://redirection.me/support/redirect-regular-expressions/">
				{ __( 'Remember to enable the "regex" option if this is a regular expression.' ) }
			</ExternalLink>
		);
	}

	// Permalink
	if ( url.indexOf( '%postname%' ) !== -1 ) {
		warnings.push(
			<ExternalLink url="https://redirection.me/support/redirect-regular-expressions/">
				{ __( 'WordPress permalink structures do not work in normal URLs. Please use a regular expression.' ) }
			</ExternalLink>
		);
	}

	// Period without escape
	// TODO
	// if ( flag_regex && url.match( /(?<!\\)\.(?![\*\+])/ ) ) {
	// 	warnings.push( __( 'A literal period {{code}}.{{/code}} should be escaped like {{code}}\\.{{/code}} otherwise it will interpreted as a regular expression.', {
	// 		components: {
	// 			code: <code />,
	// 		},
	// 	} ) );
	// }

	// Anchor
	if ( url.indexOf( '^' ) === -1 && url.indexOf( '$' ) === -1 && flag_regex ) {
		warnings.push(
			__( 'To prevent a greedy regular expression you can use {{code}}^{{/code}} to anchor it to the start of the URL. For example: {{code}}%(example)s{{/code}}', {
				components: {
					code: <code />,
				},
				args: {
					example: '^' + url,
				},
			} ),
		);
	}

	// Redirect everything
	if ( url === '/(.*)' || url === '^/(.*)' ) {
		warnings.push( __( 'This will redirect everything, including the login pages. Please be sure you want to do this.' ) );
	}

	// If matched/unmatched that is the same as the source URL
	if ( url.length > 0 && ( url_from === url || url_notfrom === url || logged_in === url || logged_out === url || targetUrl === url ) ) {
		warnings.push( __( 'Your source is the same as a target and this will create a loop. Leave a target blank if you do not want to take action.' ) );
	}

	if ( targetUrl && ! beginsWith( targetUrl, 'https://' ) && ! beginsWith( targetUrl, 'http://' ) && targetUrl.substr( 0, 1 ) !== '/' ) {
		warnings.push( __( 'Your target URL should be an absolute URL like {{code}}https://domain.com/%(url)s{{/code}} or start with a slash {{code}}/%(url)s{{/code}}.', {
			components: {
				code: <code />,
			},
			args: {
				url: action_data.url,
			},
		} ) );
	}

	if ( targetUrl.indexOf( '|' ) !== -1 || url_from.indexOf( '|' ) !== -1 || url_notfrom.indexOf( '|' ) !== -1 || logged_in.indexOf( '|' ) !== -1 || logged_out.indexOf( '|' ) !== -1 ) {
		warnings.push( __( 'WordPress does not allow the pipe {{code}}|{{/code}} in a target URL.', {
			components: {
				code: <code />,
			},
		} ) );
	}

	return warnings;
};

export const Warnings = ( { warnings } ) => {
	if ( warnings.length === 0 ) {
		return null;
	}

	return (
		<TableRow>
			<div className="edit-redirection_warning notice notice-warning">
				{ warnings.map( ( text, pos ) => <p key={ pos }><span className="dashicons dashicons-info"></span>{ text }</p> ) }
			</div>
		</TableRow>
	);
};
