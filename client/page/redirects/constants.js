/**
 * External dependencies
 */

import { __ } from '@wordpress/i18n';
import { getMatches, getActions, getAllHttpCodes } from 'component/redirect-edit/constants';

export const getHeaders = () => [
	{
		name: 'status',
		title: __( 'Status', 'redirection' ),
		sortable: false,
	},
	{
		name: 'source',
		title: __( 'URL', 'redirection' ),
		primary: true,
	},
	{
		name: 'match_type',
		title: __( 'Match Type', 'redirection' ),
		sortable: false,
	},
	{
		name: 'action_type',
		title: __( 'Action Type', 'redirection' ),
		sortable: false,
	},
	{
		name: 'code',
		title: __( 'Code', 'redirection' ),
		sortable: false,
	},
	{
		name: 'group',
		title: __( 'Group', 'redirection' ),
		sortable: false,
	},
	{
		name: 'position',
		title: __( 'Pos', 'redirection' ),
	},
	{
		name: 'last_count',
		title: __( 'Hits', 'redirection' ),
	},
	{
		name: 'last_access',
		title: __( 'Last Access', 'redirection' ),
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
	{
		id: 'reset',
		name: __( 'Reset hits', 'redirection' ),
	},
];

export const getDisplayOptions = () => [
	{ value: 'source', label: __( 'Source', 'redirection' ) },
	{ value: 'flags', label: __( 'URL options', 'redirection' ) },
	{ value: 'query', label: __( 'Query Parameters', 'redirection' ) },
	{ value: 'title', label: __( 'Title', 'redirection' ) },
	{ value: 'target', label: __( 'Target', 'redirection' ) },
	{ value: 'code', label: __( 'HTTP code', 'redirection' ) },
	{ value: 'match_type', label: __( 'Match Type', 'redirection' ) },
	{ value: 'position', label: __( 'Position', 'redirection' ) },
	{ value: 'last_count', label: __( 'Hits', 'redirection' ) },
	{ value: 'last_access', label: __( 'Last Access', 'redirection' ) },
	{ value: 'status', label: __( 'Status', 'redirection' ) },
	{ value: 'action_type', label: __( 'Action Type', 'redirection' ) },
	{ value: 'group', label: __( 'Group', 'redirection' ) },
];

export const getDisplayGroups = () => [
	{
		value: 'standard',
		label: __( 'Standard Display', 'redirection' ),
		grouping: [ 'last_count', 'last_access', 'source', 'target', 'code', 'title' ],
	},
	{
		value: 'minimal',
		label: __( 'Compact Display', 'redirection' ),
		grouping: [ 'source', 'last_count', 'last_access', 'target' ],
	},
	{
		value: 'all',
		label: __( 'Display All', 'redirection' ),
		grouping: getDisplayOptions().map( ( item ) => item.value ),
	},
];

export const getFilterOptions = () => [
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
		label: __( 'URL match', 'redirection' ),
		value: 'url-match',
		options: [
			{
				label: __( 'Regular Expression', 'redirection' ),
				value: 'regular',
			},
			{
				label: __( 'Plain', 'redirection' ),
				value: 'plain',
			},
		],
	},
	{
		label: __( 'Match Type', 'redirection' ),
		value: 'match',
		options: getMatches(),
	},
	{
		label: __( 'Action Type', 'redirection' ),
		value: 'action',
		options: getActions(),

	},
	{
		label: __( 'HTTP Status Code', 'redirection' ),
		value: 'http',
		options: getAllHttpCodes(),
	},
	{
		label: __( 'Last Accessed', 'redirection' ),
		value: 'access',
		options: [
			{
				label: __( 'Never accessed', 'redirection' ),
				value: 'never',
			},
			{
				label: __( 'Not accessed in last month', 'redirection' ),
				value: 'month',
			},
			{
				label: __( 'Not accessed in last year', 'redirection' ),
				value: 'year',
			},
		],
	},
];

export const getSearchOptions = () => [
	{
		name: 'url',
		title: __( 'Search URL', 'redirection' ),
	},
	{
		name: 'target',
		title: __( 'Search target URL', 'redirection' ),
	},
	{
		name: 'title',
		title: __( 'Search title', 'redirection' ),
	},
];
