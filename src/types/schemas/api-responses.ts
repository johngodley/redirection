import { z } from 'zod';
import { RedirectSchema } from './redirect';
import { GroupSchema } from './group';
import { PaginatedResponseSchema } from './table';

/**
 * Generic success response wrapper
 */
export const SuccessResponseSchema = z.object( {
	item: z.unknown(),
	items: z.array( z.unknown() ).optional(),
	total: z.number().int().nonnegative().optional(),
} );

export type SuccessResponse = z.infer< typeof SuccessResponseSchema >;

/**
 * Error response schema
 */
export const ErrorResponseSchema = z.object( {
	code: z.union( [ z.string(), z.number() ] ).optional(),
	message: z.string().optional(),
	data: z.unknown().optional(),
	jsonData: z.unknown().optional(),
	request: z
		.object( {
			url: z.string(),
			method: z.string(),
		} )
		.optional(),
} );

export type ErrorResponse = z.infer< typeof ErrorResponseSchema >;

/**
 * Redirect list response
 */
export const RedirectListResponseSchema = PaginatedResponseSchema( RedirectSchema );

export type RedirectListResponse = z.infer< typeof RedirectListResponseSchema >;

/**
 * Redirect single item response
 */
export const RedirectItemResponseSchema = z.object( {
	item: RedirectSchema,
} );

export type RedirectItemResponse = z.infer< typeof RedirectItemResponseSchema >;

/**
 * Group list response
 */
export const GroupListResponseSchema = PaginatedResponseSchema( GroupSchema );

export type GroupListResponse = z.infer< typeof GroupListResponseSchema >;

/**
 * Group single item response
 */
export const GroupItemResponseSchema = z.object( {
	item: GroupSchema,
} );

export type GroupItemResponse = z.infer< typeof GroupItemResponseSchema >;

/**
 * Bulk action response
 */
export const BulkActionResponseSchema = z.object( {
	deleted: z.number().int().nonnegative().optional(),
	updated: z.number().int().nonnegative().optional(),
	items: z.array( z.unknown() ).optional(),
	total: z.number().int().nonnegative().optional(),
} );

export type BulkActionResponse = z.infer< typeof BulkActionResponseSchema >;
