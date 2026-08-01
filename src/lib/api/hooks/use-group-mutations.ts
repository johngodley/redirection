import { useMutation, useQueryClient, UseMutationOptions } from '@tanstack/react-query';
import apiFetch from '@wp-plugin-lib/api-fetch';
import { RedirectionApi } from 'lib/api-request';
import {
	GroupItemResponseSchema,
	GroupListResponseSchema,
	type Group,
	type CreateGroupInput,
	type UpdateGroupInput,
} from 'types';
import { queryKeys } from '../query-keys';
import { handleApiError } from '../errors';
import { useTableStore, useMessageStore } from 'stores';

/**
 * Mutation hook for creating a group
 * @param options
 */
export function useGroupCreate(
	options?: Omit< UseMutationOptions< Group | null, Error, CreateGroupInput >, 'mutationFn' >
) {
	const queryClient = useQueryClient();
	const { incrementProgress, decrementProgress, addNotice, addError } = useMessageStore();

	return useMutation( {
		mutationFn: async ( data: CreateGroupInput ) => {
			incrementProgress();
			try {
				await queryClient.cancelQueries( { queryKey: queryKeys.groups.lists() } );
				const response = await apiFetch( RedirectionApi.group.create( data ) );
				// API returns a list response after creation
				const validated = GroupListResponseSchema.parse( response );
				return validated.items[ 0 ] ?? null;
			} catch ( error ) {
				decrementProgress();
				throw handleApiError( error );
			}
		},
		onSuccess: () => {
			decrementProgress();
			addNotice( 'Group created' );
			queryClient.invalidateQueries( { queryKey: queryKeys.groups.lists() } );
			queryClient.invalidateQueries( { queryKey: queryKeys.groups.dropdown() } );
		},
		onError: ( error ) => {
			addError( error.message || 'Failed to create group' );
		},
		...options,
	} );
}

/**
 * Mutation hook for updating a group
 * @param options
 */
export function useGroupUpdate( options?: Omit< UseMutationOptions< Group, Error, UpdateGroupInput >, 'mutationFn' > ) {
	const queryClient = useQueryClient();
	const { incrementProgress, decrementProgress, addNotice, addError } = useMessageStore();

	return useMutation( {
		mutationFn: async ( data: UpdateGroupInput ) => {
			incrementProgress();
			try {
				await queryClient.cancelQueries( { queryKey: queryKeys.groups.lists() } );
				const { id: groupId, ...updates } = data;
				const response = await apiFetch( RedirectionApi.group.update( groupId, updates ) );
				const validated = GroupItemResponseSchema.parse( response );
				return validated.item;
			} catch ( error ) {
				decrementProgress();
				throw handleApiError( error );
			}
		},
		onSuccess: ( data ) => {
			decrementProgress();
			addNotice( 'Group saved' );
			queryClient.invalidateQueries( { queryKey: queryKeys.groups.lists() } );
			queryClient.invalidateQueries( { queryKey: queryKeys.groups.dropdown() } );
			queryClient.invalidateQueries( { queryKey: queryKeys.groups.detail( data.id ) } );
		},
		onError: ( error ) => {
			addError( error.message || 'Failed to update group' );
		},
		...options,
	} );
}

/**
 * Mutation hook for deleting groups (bulk)
 * @param options
 */
export function useGroupDelete(
	options?: Omit< UseMutationOptions< any, Error, { items: number[] } >, 'mutationFn' >
) {
	const queryClient = useQueryClient();
	const { incrementProgress, decrementProgress, addNotice, addError } = useMessageStore();
	const { setGroupsSelected } = useTableStore();

	return useMutation( {
		mutationFn: async ( { items }: { items: number[] } ) => {
			incrementProgress();
			try {
				await queryClient.cancelQueries( { queryKey: queryKeys.groups.lists() } );
				const response = await apiFetch( RedirectionApi.bulk.group( 'delete', { items }, {} ) );
				return response;
			} catch ( error ) {
				decrementProgress();
				throw handleApiError( error );
			}
		},
		onSuccess: () => {
			decrementProgress();
			addNotice( 'Groups deleted' );
			setGroupsSelected( [] );
			queryClient.invalidateQueries( { queryKey: queryKeys.groups.lists() } );
			queryClient.invalidateQueries( { queryKey: queryKeys.groups.dropdown() } );
		},
		onError: ( error ) => {
			addError( error.message || 'Failed to delete groups' );
		},
		...options,
	} );
}

/**
 * Mutation hook for bulk group actions (enable/disable)
 * @param options
 */
export function useGroupBulkAction(
	options?: Omit< UseMutationOptions< any, Error, { action: string; items: number[]; params?: any } >, 'mutationFn' >
) {
	const queryClient = useQueryClient();
	const { incrementProgress, decrementProgress, addNotice, addError } = useMessageStore();
	const { setGroupsTable } = useTableStore();

	return useMutation( {
		mutationFn: async ( { action, items, params = {} }: { action: string; items: number[]; params?: any } ) => {
			incrementProgress();
			try {
				await queryClient.cancelQueries( { queryKey: queryKeys.groups.lists() } );
				const response = await apiFetch( RedirectionApi.bulk.group( action, { items }, params ) );
				return response;
			} catch ( error ) {
				decrementProgress();
				throw handleApiError( error );
			}
		},
		onSuccess: ( _, variables ) => {
			decrementProgress();
			let actionName = variables.action;
			if ( variables.action === 'enable' ) {
				actionName = 'enabled';
			} else if ( variables.action === 'disable' ) {
				actionName = 'disabled';
			}
			addNotice( `Groups ${ actionName }` );
			// Reset to first page and clear selections after any bulk action
			setGroupsTable( { page: 0, selected: [], selectAll: false } );
			queryClient.invalidateQueries( { queryKey: queryKeys.groups.lists() } );
			queryClient.invalidateQueries( { queryKey: queryKeys.groups.dropdown() } );
		},
		onError: ( error ) => {
			addError( error.message || 'Failed to perform group action' );
		},
		...options,
	} );
}
