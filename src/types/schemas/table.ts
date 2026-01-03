import { z } from 'zod';

/**
 * Table state schema - used across all paginated tables
 */
export const TableStateSchema = z.object( {
	page: z.number().int().nonnegative(),
	per_page: z.number().int().positive().max( 100 ),
	orderby: z.string(),
	direction: z.enum( [ 'asc', 'desc' ] ),
	selected: z.array( z.number() ),
	selectAll: z.boolean().optional(),
	displayType: z.string().optional(),
	displaySelected: z.array( z.string() ).optional(),
	filterBy: z.record( z.string(), z.unknown() ).optional(),
	groupBy: z.string().optional(),
} );

export type TableState = z.infer< typeof TableStateSchema >;

/**
 * Generic paginated response wrapper
 * @param itemSchema
 */
export const PaginatedResponseSchema = < T extends z.ZodTypeAny >( itemSchema: T ) =>
	z.object( {
		items: z.array( itemSchema ),
		total: z.number().int().nonnegative(),
		table: TableStateSchema.optional(),
	} );

export type PaginatedResponse< T > = {
	items: T[];
	total: number;
	table?: TableState;
};
