import { useQuery, useMutation, useQueryClient, UseQueryOptions, UseMutationOptions } from '@tanstack/react-query';
import apiFetch from '@wp-plugin-lib/api-fetch';
import { RedirectionApi } from 'lib/api-request';
import { PaginatedResponseSchema } from 'types';
import { queryKeys } from '../query-keys';
import { handleApiError } from '../errors';
import { cleanApiParams } from '../utils';
import { z } from 'zod';
import { useMessageStore, useTableStore } from 'stores';

// Log schema for redirect logs - handles both individual entries and grouped results
// Individual entries have numeric id, grouped results have string id (url/ip/agent value)
const LogSchema = z.object( {
	id: z.union( [ z.number().int(), z.string() ] ),
	created: z.string().optional(),
	url: z.string().optional(),
	sent_to: z.string().optional().nullable(),
	agent: z.string().optional().nullable(),
	referrer: z.string().optional().nullable(),
	ip: z.string().optional().nullable(),
	domain: z.string().optional().nullable(),
	redirect_id: z.number().int().optional(),
	redirection_id: z.number().int().optional(),
	request_method: z.string().optional().nullable(),
	http_code: z.number().int().optional(),
	redirect_by: z.string().optional().nullable(),
	count: z.coerce.number().int().optional(), // Only present in grouped results
} );

// 404 Error schema - handles both individual entries and grouped results
// Individual entries have numeric id, grouped results have string id (url/ip/agent value)
const Error404Schema = z.object( {
	id: z.union( [ z.number().int(), z.string() ] ),
	created: z.string().optional(),
	created_time: z.string().optional(),
	url: z.string().optional(),
	agent: z.string().optional().nullable(),
	referrer: z.string().optional().nullable(),
	domain: z.string().optional().nullable(),
	ip: z.string().optional().nullable(),
	http_code: z.number().int().optional(),
	request_method: z.string().optional().nullable(),
	request_data: z.unknown().optional().nullable(),
	count: z.coerce.number().int().optional(), // Only present in grouped results
} );

const LogListResponseSchema = PaginatedResponseSchema( LogSchema );
type LogListResponse = z.infer< typeof LogListResponseSchema >;

const Error404ListResponseSchema = PaginatedResponseSchema( Error404Schema );
type Error404ListResponse = z.infer< typeof Error404ListResponseSchema >;

/**
 * Query hook for fetching logs
 * @param params
 * @param options
 */
export function useLogList(
	params: any = {},
	options?: Omit< UseQueryOptions< LogListResponse >, 'queryKey' | 'queryFn' >
) {
	const cleanedParams = cleanApiParams( params );
	const { addError } = useMessageStore();

	return useQuery( {
		queryKey: queryKeys.logs.list( cleanedParams ),
		queryFn: async () => {
			try {
				const response = await apiFetch( RedirectionApi.log.list( cleanedParams ) );
				return LogListResponseSchema.parse( response );
			} catch ( error ) {
				const handledError = handleApiError( error );
				addError( handledError.message || 'Failed to fetch logs' );
				throw handledError;
			}
		},
		...options,
	} );
}

/**
 * Mutation hook for deleting all logs
 * @param options
 */
export function useLogDeleteAll( options?: Omit< UseMutationOptions< any, Error, any >, 'mutationFn' > ) {
	const queryClient = useQueryClient();
	const { incrementProgress, decrementProgress, addNotice, addError } = useMessageStore();

	return useMutation( {
		mutationFn: async ( params: any = {} ) => {
			incrementProgress();
			try {
				const response = await apiFetch( RedirectionApi.log.deleteAll( params ) );
				return response;
			} catch ( error ) {
				decrementProgress();
				throw handleApiError( error );
			}
		},
		onSuccess: () => {
			decrementProgress();
			addNotice( 'Logs deleted' );
			queryClient.invalidateQueries( { queryKey: queryKeys.logs.all } );
		},
		onError: ( error ) => {
			addError( error.message || 'Failed to delete logs' );
		},
		...options,
	} );
}

/**
 * Mutation hook for bulk log actions
 * @param options
 */
export function useLogBulkAction(
	options?: Omit<
		UseMutationOptions< any, Error, { action: string; items: ( string | number )[]; params?: any } >,
		'mutationFn'
	>
) {
	const queryClient = useQueryClient();
	const { incrementProgress, decrementProgress, addNotice, addError } = useMessageStore();
	const { setLogsTable } = useTableStore();

	return useMutation( {
		mutationFn: async ( {
			action,
			items,
			params = {},
		}: {
			action: string;
			items: ( string | number )[];
			params?: any;
		} ) => {
			incrementProgress();
			try {
				const response = await apiFetch( RedirectionApi.bulk.log( action, { items }, params ) );
				return response;
			} catch ( error ) {
				decrementProgress();
				throw handleApiError( error );
			}
		},
		onSuccess: ( _, variables ) => {
			decrementProgress();
			const actionName = variables.action === 'delete' ? 'deleted' : variables.action;
			addNotice( `Logs ${ actionName }` );
			// Reset to first page and clear selections after bulk action
			setLogsTable( { page: 0, selected: [], selectAll: false } );
			queryClient.invalidateQueries( { queryKey: queryKeys.logs.all } );
		},
		onError: ( error ) => {
			addError( error.message || 'Failed to perform log action' );
		},
		...options,
	} );
}

/**
 * Query hook for fetching 404 errors
 * @param params
 * @param options
 */
export function useErrorList(
	params: any = {},
	options?: Omit< UseQueryOptions< Error404ListResponse >, 'queryKey' | 'queryFn' >
) {
	const cleanedParams = cleanApiParams( params );
	const { addError } = useMessageStore();

	return useQuery( {
		queryKey: queryKeys.errors.list( cleanedParams ),
		queryFn: async () => {
			try {
				const response = await apiFetch( RedirectionApi.error.list( cleanedParams ) );
				return Error404ListResponseSchema.parse( response );
			} catch ( error ) {
				const handledError = handleApiError( error );
				addError( handledError.message || 'Failed to fetch 404 errors' );
				throw handledError;
			}
		},
		...options,
	} );
}

/**
 * Mutation hook for deleting all 404 errors
 * @param options
 */
export function useErrorDeleteAll( options?: Omit< UseMutationOptions< any, Error, any >, 'mutationFn' > ) {
	const queryClient = useQueryClient();
	const { incrementProgress, decrementProgress, addNotice, addError } = useMessageStore();

	return useMutation( {
		mutationFn: async ( params: any = {} ) => {
			incrementProgress();
			try {
				const response = await apiFetch( RedirectionApi.error.deleteAll( params ) );
				return response;
			} catch ( error ) {
				decrementProgress();
				throw handleApiError( error );
			}
		},
		onSuccess: () => {
			decrementProgress();
			addNotice( '404 errors deleted' );
			queryClient.invalidateQueries( { queryKey: queryKeys.errors.all } );
		},
		onError: ( error ) => {
			addError( error.message || 'Failed to delete 404 errors' );
		},
		...options,
	} );
}

/**
 * Mutation hook for bulk 404 error actions
 * @param options
 */
export function useErrorBulkAction(
	options?: Omit<
		UseMutationOptions< any, Error, { action: string; items: ( string | number )[]; params?: any } >,
		'mutationFn'
	>
) {
	const queryClient = useQueryClient();
	const { incrementProgress, decrementProgress, addNotice, addError } = useMessageStore();
	const { setErrorsTable } = useTableStore();

	return useMutation( {
		mutationFn: async ( {
			action,
			items,
			params = {},
		}: {
			action: string;
			items: ( string | number )[];
			params?: any;
		} ) => {
			incrementProgress();
			try {
				const response = await apiFetch( RedirectionApi.bulk.error( action, { items }, params ) );
				return response;
			} catch ( error ) {
				decrementProgress();
				throw handleApiError( error );
			}
		},
		onSuccess: ( _, variables ) => {
			decrementProgress();
			const actionName = variables.action === 'delete' ? 'deleted' : variables.action;
			addNotice( `404 errors ${ actionName }` );
			// Reset to first page and clear selections after any bulk 404 error action
			setErrorsTable( { page: 0, selected: [], selectAll: false } );
			queryClient.invalidateQueries( { queryKey: queryKeys.errors.all } );
		},
		onError: ( error ) => {
			addError( error.message || 'Failed to perform 404 error action' );
		},
		...options,
	} );
}
