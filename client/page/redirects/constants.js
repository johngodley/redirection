/**
 * External dependencies
 */

import { translate as __ } from 'i18n-calypso';
import { getMatches, getActions, getAllHttpCodes } from 'component/redirect-edit/constants';

export const getHeaders = () => [
	{
		name: 'status',
		title: __( 'Status' ),
		sortable: false,
	},
	{
		name: 'source',
		title: __( 'URL' ),
		primary: true,
	},
	{
		name: 'match_type',
		title: __( 'Match Type' ),
		sortable: false,
	},
	{
		name: 'action_type',
		title: __( 'Action Type' ),
		sortable: false,
	},
	{
		name: 'code',
		title: __( 'Code' ),
		sortable: false,
	},
	{
		name: 'group',
		title: __( 'Group' ),
		sortable: false,
	},
	{
		name: 'position',
		title: __( 'Pos' ),
	},
	{
		name: 'last_count',
		title: __( 'Hits' ),
	},
	{
		name: 'last_access',
		title: __( 'Last Access' ),
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
	{
		id: 'reset',
		name: __( 'Reset hits' ),
	},
];

export const getDisplayOptions = () => [
	{ value: 'source', label: __( 'Source' ) },
	{ value: 'flags', label: __( 'URL options' ) },
	{ value: 'query', label: __( 'Query Parameters' ) },
	{ value: 'title', label: __( 'Title' ) },
	{ value: 'target', label: __( 'Target' ) },
	{ value: 'code', label: __( 'HTTP code' ) },
	{ value: 'match_type', label: __( 'Match Type' ) },
	{ value: 'position', label: __( 'Position' ) },
	{ value: 'last_count', label: __( 'Hits' ) },
	{ value: 'last_access', label: __( 'Last Access' ) },
	{ value: 'status', label: __( 'Status' ) },
	{ value: 'action_type', label: __( 'Action Type' ) },
	{ value: 'group', label: __( 'Group' ) },
];

export const getDisplayGroups = () => [
	{
		value: 'standard',
		label: __( 'Standard Display' ),
		grouping: [ 'last_count', 'last_access', 'source', 'target', 'code', 'title' ],
	},
	{
		value: 'minimal',
		label: __( 'Compact Display' ),
		grouping: [ 'source', 'last_count', 'last_access', 'target' ],
	},
	{
		value: 'all',
		label: __( 'Display All' ),
		grouping: getDisplayOptions().map( ( item ) => item.value ),
	},
];

export const getFilterOptions = () => [
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
		label: __( 'URL match' ),
		value: 'url-match',
		options: [
			{
				label: __( 'Regular Expression' ),
				value: 'regular',
			},
			{
				label: __( 'Plain' ),
				value: 'plain',
			},
		],
	},
	{
		label: __( 'Match Type' ),
		value: 'match',
		options: getMatches(),
	},
	{
		label: __( 'Action Type' ),
		value: 'action',
		options: getActions(),

	},
	{
		label: __( 'HTTP Status Code' ),
		value: 'http',
		options: getAllHttpCodes(),
	},
	{
		label: __( 'Last Accessed' ),
		value: 'access',
		options: [
			{
				label: __( 'Never accessed' ),
				value: 'never',
			},
			{
				label: __( 'Not accessed in last month' ),
				value: 'month',
			},
			{
				label: __( 'Not accessed in last year' ),
				value: 'year',
			},
		],
	},
];

export const getSearchOptions = () => [
	{
		name: 'url',
		title: __( 'Search URL' ),
	},
	{
		name: 'target',
		title: __( 'Search target URL' ),
	},
	{
		name: 'title',
		title: __( 'Search title' ),
	},
];
