import { QueryClient } from '@tanstack/react-query';

/**
 * TanStack Query client configuration
 *
 * Default options optimized for WordPress REST API:
 * - 5 minute stale time (data fresh for 5 minutes)
 * - No refetch on window focus (WordPress admin context)
 */
export const queryClient = new QueryClient( {
	defaultOptions: {
		queries: {
			staleTime: 1000 * 60 * 5, // 5 minutes
			retry: false,
			refetchOnWindowFocus: false,
		},
		mutations: {
			retry: false, // Don't retry mutations by default
		},
	},
} );
