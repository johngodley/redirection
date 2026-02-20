import { z } from 'zod';
import { PaginatedResponseSchema } from './table';

/**
 * Log entry schema — handles both individual and grouped results.
 * Grouped results have a string id (the group value: url/ip/agent).
 * Individual results have a numeric id.
 *
 * Many fields are nullable because they come from outer-joined DB columns
 * or may be absent for grouped/aggregated rows.
 */
export const LogSchema = z.object( {
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

export type Log = z.infer< typeof LogSchema >;

/**
 * 404 error log schema — handles both individual and grouped results.
 */
export const Error404Schema = z.object( {
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

export type Error404 = z.infer< typeof Error404Schema >;

export const LogListResponseSchema = PaginatedResponseSchema( LogSchema );
export type LogListResponse = z.infer< typeof LogListResponseSchema >;

export const Error404ListResponseSchema = PaginatedResponseSchema( Error404Schema );
export type Error404ListResponse = z.infer< typeof Error404ListResponseSchema >;
