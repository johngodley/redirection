import { z } from 'zod';

/**
 * Group entity schema
 */
export const GroupSchema = z.object( {
	id: z.number().int(),
	name: z.string(),
	redirects: z.number().int().nonnegative(),
	module_id: z.number().int(),
	moduleName: z.string().optional(), // Name of the module
	enabled: z.boolean(),
} );

export type Group = z.infer< typeof GroupSchema >;

/**
 * Create group input schema
 */
export const CreateGroupInputSchema = z.object( {
	name: z.string().min( 1, 'Group name is required' ),
	moduleId: z.number().int().positive(),
	position: z.number().int().nonnegative().default( 0 ),
	enabled: z.boolean().optional(),
} );

export type CreateGroupInput = z.infer< typeof CreateGroupInputSchema >;

/**
 * Update group input schema
 */
export const UpdateGroupInputSchema = CreateGroupInputSchema.partial().extend( {
	id: z.number().int(),
} );

export type UpdateGroupInput = z.infer< typeof UpdateGroupInputSchema >;
