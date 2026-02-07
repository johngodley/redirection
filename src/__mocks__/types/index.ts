import { z } from 'zod';

// Mock Settings schema and type
export const SettingsSchema = z.object( {
	installed: z.string().optional(),
	warning: z.string().optional(),
	postTypes: z.array( z.string() ).optional(),
} );

export type Settings = z.infer< typeof SettingsSchema >;
