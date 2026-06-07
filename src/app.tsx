import { __ } from '@wordpress/i18n';
import { QueryClientProvider } from '@tanstack/react-query';
import { ReactQueryDevtools } from '@tanstack/react-query-devtools';
import { Error as ErrorDisplay, ErrorBoundary } from '@wp-plugin-components';
import type { ErrorInfo } from 'react';
import { useEffect, useState } from 'react';
import Home from './page/home';
import apiFetch from '@wp-plugin-lib/api-fetch';
import { getErrorDetails, getErrorLinks } from 'lib/error-links';
import { queryClient } from 'lib/query-client';

let hasInitializedApp = false;

interface AppConfig {
	apiRoot: string;
	apiNonce: string;
	locale: string;
	localeWasInvalid: boolean;
}

function getSafeRedirectionData() {
	if ( typeof window.Redirectioni10n !== 'object' || ! window.Redirectioni10n ) {
		return null;
	}

	return {
		version: window.Redirectioni10n.version,
		locale: window.Redirectioni10n.locale,
		apiRoot: window.Redirectioni10n.api?.WP_API_root,
	};
}

// Create error renderer for app-level crashes
function AppCrashHandler( error: Error | null, errorInfo: ErrorInfo | null ) {
	const stack = error?.stack || '';
	const hasRedirectionData =
		typeof window.Redirectioni10n === 'object' &&
		!! window.Redirectioni10n &&
		!! window.Redirectioni10n.api &&
		typeof window.Redirectioni10n.api === 'object' &&
		typeof window.Redirectioni10n.versions === 'string';

	// eslint-disable-next-line no-console
	console.error( 'Redirection app crashed:', { error, errorInfo, redirectionData: getSafeRedirectionData() } );

	return (
		<ErrorDisplay
			errors={ '' }
			type="fixed"
			links={ hasRedirectionData ? getErrorLinks() : undefined }
			details={ ( hasRedirectionData ? getErrorDetails() : [] ).concat( [
				stack,
				errorInfo?.componentStack || '',
			] ) }
			locale="redirection"
			title={ __( 'Redirection plugin error', 'redirection' ) }
		>
			<p>{ __( 'The Redirection plugin encountered an error and could not load properly.', 'redirection' ) }</p>
			<p>
				<strong>{ __( 'Possible solutions:', 'redirection' ) }</strong>
			</p>
			<ul>
				<li>{ __( 'Refresh the page', 'redirection' ) }</li>
				<li>{ __( 'Clear browser cache', 'redirection' ) }</li>
				<li>{ __( 'Disable conflicting plugins', 'redirection' ) }</li>
				<li>{ __( 'Check for JavaScript errors in browser console', 'redirection' ) }</li>
			</ul>
		</ErrorDisplay>
	);
}

// Validate global data without side effects so the boundary can catch failures during render.
function getAppConfig(): AppConfig {
	// Validate Redirectioni10n exists
	if ( typeof window.Redirectioni10n !== 'object' || ! window.Redirectioni10n ) {
		throw new Error(
			'Redirectioni10n global data is missing. This may indicate a plugin conflict or caching issue.'
		);
	}

	// Validate API data exists
	if ( ! window.Redirectioni10n.api ) {
		throw new Error( 'Redirectioni10n.api is missing. The WordPress REST API configuration was not loaded.' );
	}

	const apiRoot = window.Redirectioni10n.api.WP_API_root;
	if ( ! apiRoot ) {
		throw new Error( 'WP_API_root is missing from Redirectioni10n.api' );
	}

	const apiNonce = window.Redirectioni10n.api.WP_API_nonce ?? '';
	let locale = window.Redirectioni10n.locale;
	let localeWasInvalid = false;

	// Validate the locale works with the browser, but don't mutate globals during render.
	try {
		new Intl.NumberFormat( locale );
	} catch ( error ) {
		locale = 'en-US';
		localeWasInvalid = true;
	}

	return { apiRoot, apiNonce, locale, localeWasInvalid };
}

export default function App(): React.ReactElement {
	return (
		<ErrorBoundary renderCrash={ AppCrashHandler }>
			<AppInitializer />
		</ErrorBoundary>
	);
}

function AppInitializer(): React.ReactElement {
	const [ isReady, setIsReady ] = useState( hasInitializedApp );
	const config = getAppConfig();

	useEffect( () => {
		if ( hasInitializedApp ) {
			setIsReady( true );
			return;
		}

		if ( config.localeWasInvalid ) {
			// eslint-disable-next-line no-console
			console.warn( 'Invalid locale:', window.Redirectioni10n.locale, 'falling back to en-US' );
			window.Redirectioni10n.locale = config.locale;
		}

		if ( ! config.apiNonce ) {
			// eslint-disable-next-line no-console
			console.warn( 'WP_API_nonce is missing from Redirectioni10n.api' );
		}

		apiFetch.resetMiddlewares();
		apiFetch.use( apiFetch.createRootURLMiddleware( config.apiRoot ) );
		apiFetch.use( apiFetch.createNonceMiddleware( config.apiNonce ) );
		hasInitializedApp = true;
		setIsReady( true );
	}, [ config.apiNonce, config.apiRoot, config.locale, config.localeWasInvalid ] );

	if ( ! isReady ) {
		return <></>;
	}

	return (
		<QueryClientProvider client={ queryClient }>
			<Home />
			{ process.env.NODE_ENV === 'development' && <ReactQueryDevtools initialIsOpen={ false } /> }
		</QueryClientProvider>
	);
}
