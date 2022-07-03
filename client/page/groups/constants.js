/**
 * External dependencies
 */

import { __ } from '@wordpress/i18n';

export const getDisplayOptions = () => [
	{ value: 'name', label: __( 'Name', 'redirection' ) },
	{ value: 'module', label: __( 'Module', 'redirection' ) },
	{ value: 'status', label: __( 'Status', 'redirection' ) },
	{ value: 'redirects', label: __( 'Redirects', 'redirection' ) },
];

export const getDisplayGroups = () => [
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

export const getFilterOptions = ( options ) => [
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

export const getHeaders = () => [
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

export const getBulk = () => [
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
];

export const getSearchOptions = () => [
	{
		name: 'name',
		title: __( 'Search', 'redirection' ),
	},
];
