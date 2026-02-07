import { __ } from '@wordpress/i18n';
import type { ErrorInfo } from 'react';
import { ExternalLink, Error, createInterpolateElement } from '@wp-plugin-components';
import { getErrorLinks, getErrorDetails } from 'lib/error-links';
import DebugReport from './debug';

function isBrowserExtensionError( stack: string, message: string ): boolean {
	// Check for extension URLs in stack trace
	if ( stack && ( stack.includes( 'chrome-extension://' ) || stack.includes( 'moz-extension://' ) ) ) {
		return true;
	}

	// Check for common DOM manipulation errors caused by extensions modifying the page
	const domErrors = [
		"Failed to execute 'removeChild'",
		"Failed to execute 'insertBefore'",
		"Failed to execute 'appendChild'",
		'The node to be removed is not a child of this node',
	];

	if ( message ) {
		for ( const error of domErrors ) {
			if ( message.includes( error ) ) {
				return true;
			}
		}
	}

	return false;
}

function BrowserExtensionWarning() {
	return (
		<>
			<p>
				{ __(
					'This error may be caused by a browser extension modifying the page. Please try disabling browser extensions for this page.',
					'redirection'
				) }
			</p>

			<p>
				{ __( 'You can also try using a different browser, or using private/incognito mode.', 'redirection' ) }
			</p>
		</>
	);
}

function DefaultCrashMessage() {
	return (
		<>
			<p>
				{ __(
					'Redirection is not working. Try clearing your browser cache and reloading this page.',
					'redirection'
				) }
				&nbsp;
				{ __(
					'If you are using a page caching plugin or service (CloudFlare, OVH, etc) then you can also try clearing that cache.',
					'redirection'
				) }
			</p>

			<p>
				{ createInterpolateElement(
					__(
						"If that doesn't help, open your browser's error console and create a {{link}}new issue{{/link}} with the details.",
						'redirection'
					),
					{
						link: <ExternalLink url="https://github.com/johngodley/redirection/issues" />,
					}
				) }
			</p>
		</>
	);
}

// eslint-disable-next-line @typescript-eslint/no-unused-vars
function CrashHandler( error: Error | null, errorInfo: ErrorInfo | null, _extra?: unknown ): JSX.Element {
	const stack = error?.stack || '';
	const message = error?.message || '';
	const isExtensionError = isBrowserExtensionError( stack, message );

	return (
		<Error
			errors={ '' }
			renderDebug={ DebugReport }
			type="fixed"
			links={ getErrorLinks() }
			details={ getErrorDetails().concat( [ stack, errorInfo?.componentStack || '' ] ) }
			locale="redirection"
		>
			{ isExtensionError ? <BrowserExtensionWarning /> : <DefaultCrashMessage /> }
		</Error>
	);
}

export default CrashHandler;
