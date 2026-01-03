import { useMutation, useQueryClient, UseMutationOptions } from '@tanstack/react-query';
import apiFetch from '@wp-plugin-lib/api-fetch';
import { RedirectionApi } from 'lib/api-request';
import { RedirectItemResponseSchema, type Redirect, type CreateRedirectInput, type UpdateRedirectInput } from 'types';
import { queryKeys } from '../query-keys';
import { handleApiError } from '../errors';
import { useRedirectStore, useMessageStore } from 'stores';

/**
 * Mutation hook for creating a redirect
 * @param options
 */
export function useRedirectCreate(
	options?: Omit< UseMutationOptions< Redirect, Error, CreateRedirectInput >, 'mutationFn' >
) {
	const queryClient = useQueryClient();
	const { incrementProgress, decrementProgress, addNotice, addError } = useMessageStore();
	const { removeSaving } = useRedirectStore();

	return useMutation( {
		mutationFn: async ( data: CreateRedirectInput ) => {
			incrementProgress();
			try {
				const response = await apiFetch( RedirectionApi.redirect.create( data ) );
				const validated = RedirectItemResponseSchema.parse( response );
				return validated.item;
			} catch ( error ) {
				decrementProgress();
				throw handleApiError( error );
			}
		},
		onSuccess: ( data ) => {
			decrementProgress();
			addNotice( 'Redirect created' );
			queryClient.invalidateQueries( { queryKey: queryKeys.redirects.lists() } );
			removeSaving( data.id );
		},
		onError: ( error ) => {
			addError( error.message || 'Failed to create redirect' );
		},
		...options,
	} );
}

/**
 * Mutation hook for updating a redirect
 * @param options
 */
export function useRedirectUpdate(
	options?: Omit< UseMutationOptions< Redirect, Error, UpdateRedirectInput >, 'mutationFn' >
) {
	const queryClient = useQueryClient();
	const { incrementProgress, decrementProgress, addNotice, addError } = useMessageStore();
	const { addSaving, removeSaving } = useRedirectStore();

	return useMutation( {
		mutationFn: async ( data: UpdateRedirectInput ) => {
			const { id } = data;
			addSaving( id );
			incrementProgress();
			try {
				const { id: redirectId, ...updates } = data;
				const response = await apiFetch( RedirectionApi.redirect.update( redirectId, updates ) );
				const validated = RedirectItemResponseSchema.parse( response );
				return validated.item;
			} catch ( error ) {
				decrementProgress();
				removeSaving( id );
				throw handleApiError( error );
			}
		},
		onSuccess: ( data ) => {
			decrementProgress();
			removeSaving( data.id );
			addNotice( 'Redirect saved' );
			queryClient.invalidateQueries( { queryKey: queryKeys.redirects.lists() } );
			queryClient.invalidateQueries( { queryKey: queryKeys.redirects.detail( data.id ) } );
		},
		onError: ( error ) => {
			addError( error.message || 'Failed to update redirect' );
		},
		...options,
	} );
}

/**
 * Mutation hook for deleting redirects (bulk)
 * @param options
 */
export function useRedirectDelete(
	options?: Omit< UseMutationOptions< any, Error, { items: number[] } >, 'mutationFn' >
) {
	const queryClient = useQueryClient();
	const { incrementProgress, decrementProgress, addNotice, addError } = useMessageStore();
	const { clearSelected } = useRedirectStore();

	return useMutation( {
		mutationFn: async ( { items }: { items: number[] } ) => {
			incrementProgress();
			try {
				const response = await apiFetch( RedirectionApi.bulk.redirect( 'delete', { items }, {} ) );
				return response;
			} catch ( error ) {
				decrementProgress();
				throw handleApiError( error );
			}
		},
		onSuccess: () => {
			decrementProgress();
			addNotice( 'Redirects deleted' );
			clearSelected();
			queryClient.invalidateQueries( { queryKey: queryKeys.redirects.lists() } );
		},
		onError: ( error ) => {
			addError( error.message || 'Failed to delete redirects' );
		},
		...options,
	} );
}

/**
 * Mutation hook for bulk actions (enable, disable, reset)
 * @param action
 * @param options
 */
export function useRedirectBulkAction(
	action: 'enable' | 'disable' | 'reset',
	options?: Omit< UseMutationOptions< any, Error, { items: number[] } >, 'mutationFn' >
) {
	const queryClient = useQueryClient();
	const { incrementProgress, decrementProgress, addNotice, addError } = useMessageStore();
	const { clearSelected } = useRedirectStore();

	return useMutation( {
		mutationFn: async ( { items }: { items: number[] } ) => {
			incrementProgress();
			try {
				const response = await apiFetch( RedirectionApi.bulk.redirect( action, { items }, {} ) );
				return response;
			} catch ( error ) {
				decrementProgress();
				throw handleApiError( error );
			}
		},
		onSuccess: () => {
			decrementProgress();
			addNotice( `Redirects ${ action }d` );
			clearSelected();
			queryClient.invalidateQueries( { queryKey: queryKeys.redirects.lists() } );
		},
		onError: ( error ) => {
			addError( error.message || `Failed to ${ action } redirects` );
		},
		...options,
	} );
}
