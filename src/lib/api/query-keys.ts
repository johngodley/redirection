/**
 * Query key factory for TanStack Query
 *
 * Provides type-safe, hierarchical query keys for all resources.
 * Pattern: [resource, operation, ...params]
 *
 * @see https://tkdodo.eu/blog/effective-react-query-keys
 */

export const queryKeys = {
	redirects: {
		all: [ 'redirects' ] as const,
		lists: () => [ ...queryKeys.redirects.all, 'list' ] as const,
		list: ( params: any ) => [ ...queryKeys.redirects.lists(), params ] as const,
		detail: ( id: number ) => [ ...queryKeys.redirects.all, 'detail', id ] as const,
	},
	groups: {
		all: [ 'groups' ] as const,
		lists: () => [ ...queryKeys.groups.all, 'list' ] as const,
		list: ( params: any ) => [ ...queryKeys.groups.lists(), params ] as const,
		detail: ( id: number ) => [ ...queryKeys.groups.all, 'detail', id ] as const,
		dropdown: () => [ ...queryKeys.groups.all, 'dropdown' ] as const,
	},
	logs: {
		all: [ 'logs' ] as const,
		lists: () => [ ...queryKeys.logs.all, 'list' ] as const,
		list: ( params: any ) => [ ...queryKeys.logs.lists(), params ] as const,
	},
	errors: {
		all: [ 'errors' ] as const,
		lists: () => [ ...queryKeys.errors.all, 'list' ] as const,
		list: ( params: any ) => [ ...queryKeys.errors.lists(), params ] as const,
	},
	settings: {
		all: [ 'settings' ] as const,
		get: () => [ ...queryKeys.settings.all, 'get' ] as const,
		status: () => [ ...queryKeys.settings.all, 'status' ] as const,
	},
	pluginInfo: {
		all: [ 'pluginInfo' ] as const,
		get: () => [ ...queryKeys.pluginInfo.all, 'get' ] as const,
	},
	io: {
		all: [ 'io' ] as const,
		importers: () => [ ...queryKeys.io.all, 'importers' ] as const,
	},
	info: {
		all: [ 'info' ] as const,
		ip: ( ip: string ) => [ ...queryKeys.info.all, 'ip', ip ] as const,
		agent: ( agent: string ) => [ ...queryKeys.info.all, 'agent', agent ] as const,
		http: ( url: string ) => [ ...queryKeys.info.all, 'http', url ] as const,
	},
} as const;
