import { useQuery, UseQueryOptions } from '@tanstack/react-query';
import apiFetch from '@wp-plugin-lib/api-fetch';
import { RedirectionApi } from 'lib/api-request';
import { RedirectListResponseSchema, type RedirectListResponse } from 'types';
import { queryKeys } from '../query-keys';
import { handleApiError } from '../errors';
import { cleanApiParams } from '../utils';

/**
 * Query hook for fetching redirects list
 *
 * @param params  - Table parameters (page, per_page, orderby, filterBy, etc.)
 * @param options - React Query options
 */
export function useRedirectList(
	params: any = {},
	options?: Omit< UseQueryOptions< RedirectListResponse >, 'queryKey' | 'queryFn' >
) {
	const cleanedParams = cleanApiParams( params );

	return useQuery( {
		queryKey: queryKeys.redirects.list( cleanedParams ),
		refetchOnMount: 'always',
		refetchOnReconnect: true,
		queryFn: async () => {
			try {
				const response = await apiFetch( RedirectionApi.redirect.list( cleanedParams ) );
				// Validate response with Zod
				return RedirectListResponseSchema.parse( response );
			} catch ( error ) {
				throw handleApiError( error );
			}
		},
		...options,
	} );
}
