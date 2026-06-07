import { MutationCache, QueryCache, QueryClient } from '@tanstack/react-query';

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

function logTanStackError( type: 'Query' | 'Mutation', error: Error ) {
	// eslint-disable-next-line no-console
	console.error( `TanStack ${ type } error:`, {
		error,
		userAgent: navigator.userAgent,
		timestamp: new Date().toISOString(),
		redirectionData: getSafeRedirectionData(),
	} );
}

/**
 * TanStack Query client configuration
 *
 * Default options optimized for WordPress REST API:
 * - No stale time (immediate invalidation when data changes)
 * - No refetch on window focus (WordPress admin context)
 * - Enhanced error logging for debugging
 */
export const queryClient = new QueryClient( {
	queryCache: new QueryCache( {
		onError: ( error ) => {
			logTanStackError( 'Query', error as Error );
		},
	} ),
	mutationCache: new MutationCache( {
		onError: ( error ) => {
			logTanStackError( 'Mutation', error as Error );
		},
	} ),
	defaultOptions: {
		queries: {
			staleTime: 0, // Immediate staleness - always refetch when invalidated
			retry: false,
			refetchOnWindowFocus: false,
		},
		mutations: {
			retry: false, // Don't retry mutations by default
		},
	},
} );
