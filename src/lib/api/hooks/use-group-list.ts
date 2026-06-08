import { useQuery, UseQueryOptions } from '@tanstack/react-query';
import apiFetch from '@wp-plugin-lib/api-fetch';
import { RedirectionApi } from 'lib/api-request';
import { GroupListResponseSchema, type GroupListResponse } from 'types';
import { queryKeys } from '../query-keys';
import { handleApiError } from '../errors';
import { cleanApiParams } from '../utils';

/**
 * Query hook for fetching groups list
 * @param params
 * @param options
 */
export function useGroupList(
	params: any = {},
	options?: Omit< UseQueryOptions< GroupListResponse >, 'queryKey' | 'queryFn' >
) {
	const cleanedParams = cleanApiParams( params );

	return useQuery( {
		queryKey: queryKeys.groups.list( cleanedParams ),
		refetchOnMount: 'always',
		refetchOnReconnect: true,
		queryFn: async () => {
			try {
				const response = await apiFetch( RedirectionApi.group.list( cleanedParams ) );
				return GroupListResponseSchema.parse( response );
			} catch ( error ) {
				throw handleApiError( error );
			}
		},
		...options,
	} );
}
