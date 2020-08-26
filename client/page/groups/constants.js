/**
 * External dependencies
 */

import { translate as __ } from 'i18n-calypso';

export const getDisplayOptions = () => [
	{ value: 'name', label: __( 'Name' ) },
	{ value: 'module', label: __( 'Module' ) },
	{ value: 'status', label: __( 'Status' ) },
	{ value: 'redirects', label: __( 'Redirects' ) },
];

export const getDisplayGroups = () => [
	{
		value: 'standard',
		label: __( 'Standard Display' ),
		grouping: [ 'name', 'module', 'redirects' ],
	},
	{
		value: 'minimal',
		label: __( 'Compact Display' ),
		grouping: [ 'name' ],
	},
		{
		value: 'all',
		label: __( 'Display All' ),
		grouping: getDisplayOptions().map( ( item ) => item.value ),
	},
];

export const getFilterOptions = ( options ) => [
	{
		label: __( 'Status' ),
		value: 'status',
		options: [
			{
				label: __( 'Enabled' ),
				value: 'enabled',
			},
			{
				label: __( 'Disabled' ),
				value: 'disabled',
			},
		],
	},
	{
		label: __( 'Module' ),
		value: 'module',
		options,
	},
];

export const getHeaders = () => [
	{
		name: 'status',
		title: __( 'Status' ),
		sortable: false,
	},
	{
		name: 'name',
		title: __( 'Name' ),
		primary: true,
	},
	{
		name: 'redirects',
		title: __( 'Redirects' ),
		sortable: false,
	},
	{
		name: 'module',
		title: __( 'Module' ),
		sortable: false,
	},
];

export const getBulk = () => [
	{
		id: 'delete',
		name: __( 'Delete' ),
	},
	{
		id: 'enable',
		name: __( 'Enable' ),
	},
	{
		id: 'disable',
		name: __( 'Disable' ),
	},
];

export const getSearchOptions = () => [
	{
		name: 'name',
		title: __( 'Search' ),
	},
];
