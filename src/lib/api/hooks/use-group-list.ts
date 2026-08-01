import { useQuery, UseQueryOptions } from '@tanstack/react-query';
import apiFetch from '@wp-plugin-lib/api-fetch';
import { RedirectionApi } from 'lib/api-request';
import { GroupListResponseSchema, type GroupListResponse } from 'types';
import { queryKeys } from '../query-keys';
import { handleApiError } from '../errors';
import { cleanApiParams } from '../utils';
import { useMessageStore } from 'stores';

/**
 * Query hook for fetching the full, unpaginated group list used by dropdowns and filters.
 *
 * Unlike useGroupList this doesn't paginate, so it always sees every group (up to the
 * server-side dropdown limit) rather than only the first page.
 * @param options
 */
export function useGroupDropdown( options?: Omit< UseQueryOptions< GroupListResponse >, 'queryKey' | 'queryFn' > ) {
	return useQuery( {
		queryKey: queryKeys.groups.dropdown(),
		refetchOnMount: 'always',
		refetchOnReconnect: true,
		placeholderData: ( previousData ) => previousData,
		queryFn: async () => {
			try {
				const response = await apiFetch( RedirectionApi.group.dropdown() );
				return GroupListResponseSchema.parse( response );
			} catch ( error ) {
				const handledError = handleApiError( error );
				useMessageStore.getState().addError( handledError.message || 'Failed to fetch groups' );
				throw handledError;
			}
		},
		...options,
	} );
}

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
		placeholderData: ( previousData ) => previousData,
		queryFn: async () => {
			try {
				const response = await apiFetch( RedirectionApi.group.list( cleanedParams ) );
				return GroupListResponseSchema.parse( response );
			} catch ( error ) {
				const handledError = handleApiError( error );
				useMessageStore.getState().addError( handledError.message || 'Failed to fetch groups' );
				throw handledError;
			}
		},
		...options,
	} );
}
