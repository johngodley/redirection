/**
 * External dependencies
 */

import { translate as __ } from 'lib/locale';

export const getHeaders = groupBy => {
	if ( groupBy === 'url' ) {
		return [
			{
				name: 'cb',
				check: true,
			},
			{
				name: 'url',
				title: __( 'Source URL' ),
				primary: true,
				sortable: false,
			},
			{
				name: 'total',
				title: __( 'Count' ),
				sortable: true,
			},
		];
	} else if ( groupBy === 'ip' ) {
		return [
			{
				name: 'cb',
				check: true,
			},
			{
				name: 'ipx',
				title: __( 'IP' ),
				primary: true,
				sortable: false,
			},
			{
				name: 'total',
				title: __( 'Count' ),
				sortable: true,
			},
		];
	}

	return [
		{
			name: 'cb',
			check: true,
		},
		{
			name: 'date',
			title: __( 'Date' ),
		},
		{
			name: 'method',
			title: __( 'Method' ),
		},
		{
			name: 'domain',
			title: __( 'Domain' ),
		},
		{
			name: 'url',
			title: __( 'Source URL' ),
			primary: true,
		},
		{
			name: 'target',
			title: __( 'Target URL' ),
			sortable: false,
		},
		{
			name: 'code',
			title: __( 'HTTP code' ),
		},
		{
			name: 'referrer',
			title: __( 'Referrer' ),
			sortable: false,
		},
		{
			name: 'agent',
			title: __( 'User Agent' ),
			sortable: false,
		},
		{
			name: 'ip',
			title: __( 'IP' ),
			sortable: false,
		},
	]
};

export const getBulk = () => [
	{
		id: 'delete',
		name: __( 'Delete' ),
	},
];

export const getDisplayGroups = () => [
	{
		value: 'standard',
		label: __( 'Standard Display' ),
		grouping: [ 'date', 'url', 'target', 'agent', 'ip' ],
	},
	{
		value: 'minimal',
		label: __( 'Compact Display' ),
		grouping: [ 'date', 'url' ],
	},
];

export const getDisplayOptions = () => [
	{ value: 'date', label: __( 'Date' ) },
	{ value: 'method', label: __( 'Method' ) },
	{ value: 'domain', label: __( 'Domain' ) },
	{ value: 'url', label: __( 'URL' ) },
	{ value: 'code', label: __( 'HTTP code' ) },
	{ value: 'referrer', label: __( 'Referrer' ) },
	{ value: 'agent', label: __( 'User Agent' ) },
	{ value: 'target', label: __( 'Target' ) },
	{ value: 'ip', label: __( 'IP' ) },
];

export const getFilterOptions = () => [];

export const getSearchOptions = () => [
	{
		name: 'url',
		title: __( 'Search URL' ),
	},
	{
		name: 'referrer',
		title: __( 'Search referrer' ),
	},
	{
		name: 'agent',
		title: __( 'Search user agent' ),
	},
	{
		name: 'ip',
		title: __( 'Search IP' ),
	},
	{
		name: 'target',
		title: __( 'Search target URL' ),
	},
	{
		name: 'domain',
		title: __( 'Search domain' ),
	},
];

export const getGroupBy = ( ipLogging ) => {
	const values = [
		{
			value: '',
			label: __( 'No grouping' ),
		},
		{
			value: 'url',
			label: __( 'Group by URL' ),
		},
	];

	if ( ipLogging > 0 ) {
		values.push( 	{
			value: 'ip',
			label: __( 'Group by IP' ),
		} );
	}

	return values;
};
