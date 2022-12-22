/**
 * External dependencies
 */

import { __, sprintf } from '@wordpress/i18n';
import * as parseUrl from 'url';

/**
 * Internal dependencies
 */

import { ExternalLink, createInterpolateElement } from 'wp-plugin-components';
import TableRow from './table-row';

export const isRegex = ( text ) => {
	if ( text.match( /[\*\\\(\)\^\$]/ ) !== null ) {
		return true;
	}

	if ( text.indexOf( '.?' ) !== -1 ) {
		return true;
	}

	return false;
};

const getRelativeAbsolute = ( url ) => {
	const matched = url.match( /^\/([a-zA-Z0-9_\-%]*\..*)\// );

	if ( matched && url.indexOf( 'index.php' ) === -1 ) {
		return matched[ 0 ];
	}

	return null;
};

const beginsWith = ( str, match ) => match.indexOf( str ) === 0 || str.substr( 0, match.length ) === match;

export const getWarningFromState = ( item ) => {
	const { url, flag_regex, action_data = {} } = item;
	if ( action_data === null || !url || !item || typeof url !== 'string' ) {
		return [];
	}

	const warnings = [];
	const { url: targetUrl = '', logged_in = '', logged_out = '', url_from = '', url_notfrom = '' } = action_data;

	if ( Array.isArray( url ) || url.length === 0 || url === undefined ) {
		return warnings;
	}

	// Anchor value
	if ( url.indexOf( '#' ) !== -1 ) {
		warnings.push(
			<ExternalLink url="https://redirection.me/support/faq/#anchor">
				{ __( 'Anchor values are not sent to the server and cannot be redirected.', 'redirection' ) }
			</ExternalLink>
		);
	}

	// Server redirect
	if ( url.substr( 0, 4 ) === 'http' && url.indexOf( document.location.origin ) === -1 ) {
		console.log( parseUrl.parse( url ).hostname );
		warnings.push(
			<ExternalLink url="https://redirection.me/support/matching-redirects/#server">
				{ createInterpolateElement(
					sprintf(
						__( 'This will be converted to a server redirect for the domain {{code}}%(server)s{{/code}}.', 'redirection' ),
						{ server: parseUrl.parse( url ).hostname }
					),
					{
						code: <code />,
					},
				) }
			</ExternalLink>
		);
	}

	// Relative URL without leading slash
	if (
		url.substr( 0, 4 ) !== 'http' &&
		url.substr( 0, 1 ) !== '/' &&
		url.length > 0 &&
		flag_regex === false &&
		url.indexOf( '[source]' ) === -1
	) {
		warnings.push(
			createInterpolateElement( __( 'The source URL should probably start with a {{code}}/{{/code}}', 'redirection' ),
				{
					code: <code />,
				}
			)
		);
	}

	// Regex without checkbox
	if ( isRegex( url ) && flag_regex === false ) {
		warnings.push(
			<ExternalLink url="https://redirection.me/support/redirect-regular-expressions/">
				{ __( 'Remember to enable the "regex" option if this is a regular expression.', 'redirection' ) }
			</ExternalLink>
		);
	}

	// Permalink
	if ( url.match( /%\w+%/ ) ) {
		warnings.push(
			<ExternalLink url="tools.php?page=redirection.php&sub=site">
				{ __( 'Please add migrated permalinks to the Site page under the "Permalink Migration" section.', 'redirection' ) }
			</ExternalLink>
		);
	}

	// Anchor
	if ( url.indexOf( '^' ) === -1 && url.indexOf( '$' ) === -1 && flag_regex ) {
		warnings.push(
			createInterpolateElement(
				sprintf(
					__(
						'To prevent a greedy regular expression you can use {{code}}^{{/code}} to anchor it to the start of the URL. For example: {{code}}%(example)s{{/code}}',
						'redirection'
					),
					{ example: '^' + url },
				),
				{
					code: <code />,
				},
			)
		);
	}

	if ( flag_regex && url.indexOf( '^' ) > 0 ) {
		warnings.push(
			createInterpolateElement(
				sprintf(
					__( 'The caret {{code}}^{{/code}} should be at the start. For example: {{code}}%(example)s{{/code}}', 'redirection' ),
					{ example: '^' + url.replace( '^', '' ) }
				),
				{
					code: <code />,
				},
			)
		);
	}

	if ( flag_regex && url.match( /[a-zA-Z0-9\/]\?/ ) ) {
		warnings.push(
			createInterpolateElement(
				__( 'To match {{code}}?{{/code}} you need to escape it with {{code}}\\?{{/code}}', 'redirection' ),
				{
					code: <code />,
				},
			)
		);
	}

	if ( flag_regex && url.match( /[a-zA-Z0-9 ]\*/ ) ) {
		warnings.push(
			createInterpolateElement(
				__( 'Wildcards are not supported. You need to use a {{link}}regular expression{{/link}}.', 'redirection' ),
				{
					link: <ExternalLink url="https://redirection.me/support/redirect-regular-expressions/" />
				}
			)
		);
	}

	// Redirect everything
	if ( url === '/(.*)' || url === '^/(.*)' ) {
		warnings.push(
			__( 'If you want to redirect everything please use a site relocation or alias from the Site page.', 'redirection' )
		);
	}

	// If matched/unmatched that is the same as the source URL
	if (
		url.length > 0 &&
		( url_from === url || url_notfrom === url || logged_in === url || logged_out === url || targetUrl === url )
	) {
		warnings.push(
			__(
				'Your source is the same as a target and this will create a loop. Leave a target blank if you do not want to take action.',
				'redirection'
			)
		);
	}

	const targets = [
		action_data.url,
		action_data.url_from,
		action_data.url_notfrom,
		action_data.logged_in,
		action_data.logged_out,
	].filter( ( filt ) => filt );

	if (
		targetUrl &&
		!beginsWith( targetUrl, 'https://' ) &&
		!beginsWith( targetUrl, 'http://' ) &&
		targetUrl.substr( 0, 1 ) !== '/'
	) {
		warnings.push(
			createInterpolateElement(
				sprintf(
					__( 'Your target URL should be an absolute URL like {{code}}https://domain.com/%(url)s{{/code}} or start with a slash {{code}}/%(url)s{{/code}}.', 'redirection' ),
					{ url: action_data.url }
				),
				{
					code: <code />,
				},
			)
		);
	}

	if ( flag_regex === false ) {
		targets.forEach( ( target ) => {
			const matches = target.match( /[|\\\$]/g );

			if ( matches !== null ) {
				warnings.push(
					createInterpolateElement(
						sprintf( __( 'Your target URL contains the invalid character {{code}}%(invalid)s{{/code}}', 'redirection' ), { invalid: matches } ),
						{
							code: <code />,
						},
					)
				);
			}
		} );
	}

	// People often try and use a relative absolute domain - /something.com/
	[ url, ...targets ].forEach( ( target ) => {
		const relative = getRelativeAbsolute( target );

		if ( relative ) {
			warnings.push(
				createInterpolateElement(
					sprintf(
						__( 'Your URL appears to contain a domain inside the path: {{code}}%(relative)s{{/code}}. Did you mean to use {{code}}%(absolute)s{{/code}} instead?', 'redirection' ),
						{ relative, absolute: 'https://' + relative }
					),
					{
						code: <code />,
					},
				)
			);
		}
	} );

	// Warning if a URL with a common file extension
	if ( url.match( /(\.html|\.htm|\.php|\.pdf|\.jpg)$/ ) !== null ) {
		warnings.push(
			<ExternalLink url="https://redirection.me/support/problems/url-not-redirecting/">
				{ __(
					'Some servers may be configured to serve file resources directly, preventing a redirect occurring.',
					'redirection'
				) }
			</ExternalLink>
		);
	}

	return warnings;
};

export const Warnings = ( { warnings } ) => {
	if ( warnings.length === 0 ) {
		return null;
	}

	return (
		<TableRow>
			<div className="redirect-edit_warning notice notice-warning">
				{ warnings.map( ( text, pos ) => (
					<p key={ pos }>
						<span className="dashicons dashicons-info" />
						{ text }
					</p>
				) ) }
			</div>
		</TableRow>
	);
};
