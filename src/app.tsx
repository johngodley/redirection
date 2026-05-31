import { QueryClientProvider } from '@tanstack/react-query';
import { ReactQueryDevtools } from '@tanstack/react-query-devtools';
import Home from './page/home';
import apiFetch from '@wp-plugin-lib/api-fetch';
import { queryClient } from 'lib/query-client';
import ErrorBoundary from 'wp-plugin-components/error-boundary';

// Create error renderer for app-level crashes
function AppCrashHandler( error: Error | null, errorInfo: any ) {
	console.error( 'Redirection app crashed:', { error, errorInfo, redirectionData: window.Redirectioni10n } );

	return (
		<div style={ { padding: '20px', background: '#fff3cd', border: '1px solid #ffeaa7', borderRadius: '4px', margin: '20px' } }>
			<h3>⚠️ Redirection Plugin Error</h3>
			<p>The Redirection plugin encountered an error and could not load properly.</p>
			<details style={ { marginTop: '10px' } }>
				<summary>Technical Details</summary>
				<pre style={ { background: '#f8f9fa', padding: '10px', fontSize: '12px', overflow: 'auto' } }>
					{ error?.message || 'Unknown error' }
					{ error?.stack && '\n\nStack trace:\n' + error.stack }
				</pre>
			</details>
			<p>
				<strong>Possible solutions:</strong>
				<br />• Refresh the page
				<br />• Clear browser cache
				<br />• Disable conflicting plugins
				<br />• Check for JavaScript errors in browser console
			</p>
		</div>
	);
}

// Validate and initialize global data
function initializeApp() {
	// Validate Redirectioni10n exists
	if ( typeof window.Redirectioni10n !== 'object' || !window.Redirectioni10n ) {
		throw new Error( 'Redirectioni10n global data is missing. This may indicate a plugin conflict or caching issue.' );
	}

	// Validate API data exists
	if ( !window.Redirectioni10n.api ) {
		throw new Error( 'Redirectioni10n.api is missing. The WordPress REST API configuration was not loaded.' );
	}

	// Validate the locale works with the browser
	try {
		new Intl.NumberFormat( window.Redirectioni10n.locale );
	} catch ( error ) {
		console.warn( 'Invalid locale:', window.Redirectioni10n.locale, 'falling back to en-US' );
		window.Redirectioni10n.locale = 'en-US';
	}

	// Set API nonce and root URL with validation
	apiFetch.resetMiddlewares();

	const apiRoot = window.Redirectioni10n.api.WP_API_root;
	if ( !apiRoot ) {
		throw new Error( 'WP_API_root is missing from Redirectioni10n.api' );
	}

	const apiNonce = window.Redirectioni10n.api.WP_API_nonce;
	if ( !apiNonce ) {
		throw new Error( 'WP_API_nonce is missing from Redirectioni10n.api' );
	}

	apiFetch.use( apiFetch.createRootURLMiddleware( apiRoot ) );
	apiFetch.use( apiFetch.createNonceMiddleware( apiNonce ) );
}

export default function App(): React.ReactElement {
	return (
		<ErrorBoundary renderCrash={ AppCrashHandler }>
			<AppInitializer />
		</ErrorBoundary>
	);
}

function AppInitializer(): React.ReactElement {
	// Initialize app data - this can throw errors that the boundary will catch
	initializeApp();

	return (
		<QueryClientProvider client={ queryClient }>
			<Home />
			{ process.env.NODE_ENV === 'development' && <ReactQueryDevtools initialIsOpen={ false } /> }
		</QueryClientProvider>
	);
}
