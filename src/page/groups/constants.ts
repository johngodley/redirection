import { __ } from '@wordpress/i18n';

interface DisplayOption {
	value: string;
	label: string;
}

interface DisplayGroup {
	value: string;
	label: string;
	grouping: string[];
}

interface FilterOption {
	label: string;
	value: string;
	options: Array< { label: string; value: string } >;
}

interface Header {
	name: string;
	title: string;
	primary?: boolean;
	sortable?: boolean;
}

interface BulkOption {
	id: string;
	name: string;
}

interface SearchOption {
	name: string;
	title: string;
}

export const getDisplayOptions = (): DisplayOption[] => [
	{ value: 'name', label: __( 'Name', 'redirection' ) },
	{ value: 'module', label: __( 'Module', 'redirection' ) },
	{ value: 'status', label: __( 'Status', 'redirection' ) },
	{ value: 'redirects', label: __( 'Redirects', 'redirection' ) },
];

export const getDisplayGroups = (): DisplayGroup[] => [
	{
		value: 'standard',
		label: __( 'Standard Display', 'redirection' ),
		grouping: [ 'name', 'module', 'redirects' ],
	},
	{
		value: 'minimal',
		label: __( 'Compact Display', 'redirection' ),
		grouping: [ 'name' ],
	},
	{
		value: 'all',
		label: __( 'Display All', 'redirection' ),
		grouping: getDisplayOptions().map( ( item ) => item.value ),
	},
];

export const getFilterOptions = ( options: Array< { label: string; value: string } > ): FilterOption[] => [
	{
		label: __( 'Status', 'redirection' ),
		value: 'status',
		options: [
			{
				label: __( 'Enabled', 'redirection' ),
				value: 'enabled',
			},
			{
				label: __( 'Disabled', 'redirection' ),
				value: 'disabled',
			},
		],
	},
	{
		label: __( 'Module', 'redirection' ),
		value: 'module',
		options,
	},
];

export const getHeaders = (): Header[] => [
	{
		name: 'status',
		title: __( 'Status', 'redirection' ),
		sortable: false,
	},
	{
		name: 'name',
		title: __( 'Name', 'redirection' ),
		primary: true,
	},
	{
		name: 'redirects',
		title: __( 'Redirects', 'redirection' ),
		sortable: false,
	},
	{
		name: 'module',
		title: __( 'Module', 'redirection' ),
		sortable: false,
	},
];

export const getBulk = (): BulkOption[] => [
	{
		id: 'delete',
		name: __( 'Delete', 'redirection' ),
	},
	{
		id: 'enable',
		name: __( 'Enable', 'redirection' ),
	},
	{
		id: 'disable',
		name: __( 'Disable', 'redirection' ),
	},
	{
		id: 'export-csv',
		name: __( 'Export as CSV', 'redirection' ),
	},
	{
		id: 'export-json',
		name: __( 'Export as JSON', 'redirection' ),
	},
];

export const getSearchOptions = (): SearchOption[] => [
	{
		name: 'name',
		title: __( 'Search', 'redirection' ),
	},
];
