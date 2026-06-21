import { z } from 'zod';

/**
 * Plugin settings schema
 */
export const SettingsSchema = z
	.object( {
		support: z.boolean().optional(),
		token: z.string().optional(),
		ip_logging: z.number().int().optional(),
		expire_redirect: z.number().int().optional(),
		expire_404: z.number().int().optional(),
		auto_target: z.string().optional(),
		location: z.string().optional(),
		monitor_post: z.number().int().optional(),
		monitor_types: z.array( z.string() ).optional(),
		associated_redirect: z.string().optional(),
		monitor_keep_domain: z.boolean().optional(),
		redirect_cache: z.number().int().optional(),
		rest_api: z.number().int().optional(),
		https: z.boolean().optional(),
		headers: z.array( z.unknown() ).optional(),
		flag_regex: z.boolean().optional(),
		flag_query: z.string().optional(),
		flag_trailing: z.boolean().optional(),
		flag_case: z.boolean().optional(),
		postTypes: z.record( z.string(), z.string() ).optional(),
		installed: z.string().optional(),
		warning: z.string().optional(),
		plugin_update: z.string().optional(),
	} )
	.passthrough();

export type Settings = z.infer< typeof SettingsSchema >;

/**
 * Database status schema
 */
export const DatabaseStatusSchema = z.object( {
	status: z.enum( [ 'ok', 'need-update', 'error' ] ),
	version: z.string().optional(),
	required: z.string().optional(),
	reason: z.string().optional(),
} );

export type DatabaseStatus = z.infer< typeof DatabaseStatusSchema >;

/**
 * Plugin info schema
 */
export const PluginInfoSchema = z.object( {
	version: z.string(),
	database: DatabaseStatusSchema,
	groups: z.array( z.unknown() ).optional(),
	postTypes: z.array( z.string() ).optional(),
	canDelete: z.boolean().optional(),
	autoTarget: z.string().optional(),
} );

export type PluginInfo = z.infer< typeof PluginInfoSchema >;
