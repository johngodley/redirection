/**
 * Internal dependencies
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
			name: 'url',
			title: __( 'Source URL' ),
			primary: true,
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
	];
};

export const getBulk = groupBy => {
	if ( groupBy === 'ip' ) {
		return [
			{
				id: 'delete',
				name: __( 'Delete' ),
			},
			{
				id: 'redirect-ip',
				name: __( 'Redirect All' ),
			},
			{
				id: 'block',
				name: __( 'Block IP' ),
			},
		];
	}

	return [
		{
			id: 'delete',
			name: __( 'Delete' ),
		},
		{
			id: 'redirect-url',
			name: __( 'Redirect All' ),
		},
		{
			id: 'ignore',
			name: __( 'Ignore URL' ),
		},
	];
};

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

export const getDisplayGroups = () => [
	{
		value: 'standard',
		label: __( 'Standard Display' ),
		grouping: [ 'date', 'url', 'agent', 'ip' ],
	},
	{
		value: 'minimal',
		label: __( 'Compact Display' ),
		grouping: [ 'date', 'url' ],
	},
];

export const getDisplayOptions = () => [
	{ value: 'date', label: __( 'Date' ) },
	{ value: 'url', label: __( 'URL' ) },
	{ value: 'referrer', label: __( 'Referrer' ) },
	{ value: 'agent', label: __( 'User Agent' ) },
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
];
