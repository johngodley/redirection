import { QueryClient } from '@tanstack/react-query';

/**
 * TanStack Query client configuration
 *
 * Default options optimized for WordPress REST API:
 * - No stale time (immediate invalidation when data changes)
 * - No refetch on window focus (WordPress admin context)
 * - Enhanced error logging for debugging
 */
export const queryClient = new QueryClient( {
	defaultOptions: {
		queries: {
			staleTime: 0, // Immediate staleness - always refetch when invalidated
			retry: false,
			refetchOnWindowFocus: false,
			onError: ( error ) => {
				// eslint-disable-next-line no-console
				console.error( 'TanStack Query error:', {
					error,
					userAgent: navigator.userAgent,
					timestamp: new Date().toISOString(),
					redirectionData: window.Redirectioni10n,
				} );
			},
		},
		mutations: {
			retry: false, // Don't retry mutations by default
			onError: ( error ) => {
				// eslint-disable-next-line no-console
				console.error( 'TanStack Mutation error:', {
					error,
					userAgent: navigator.userAgent,
					timestamp: new Date().toISOString(),
					redirectionData: window.Redirectioni10n,
				} );
			},
		},
	},
} );
