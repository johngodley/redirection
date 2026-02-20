import { useQuery, useMutation, useQueryClient, UseQueryOptions, UseMutationOptions } from '@tanstack/react-query';
import apiFetch from '@wp-plugin-lib/api-fetch';
import { RedirectionApi } from 'lib/api-request';
import {
	LogListResponseSchema,
	Error404ListResponseSchema,
	type LogListResponse,
	type Error404ListResponse,
} from 'types';
import { queryKeys } from '../query-keys';
import { handleApiError } from '../errors';
import { cleanApiParams, getTableResetOnDelete } from '../utils';
import { useMessageStore, useTableStore } from 'stores';

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
			setLogsTable( getTableResetOnDelete( variables.params ) );
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
			setErrorsTable( getTableResetOnDelete( variables.params ) );
			queryClient.invalidateQueries( { queryKey: queryKeys.errors.all } );
		},
		onError: ( error ) => {
			addError( error.message || 'Failed to perform 404 error action' );
		},
		...options,
	} );
}
