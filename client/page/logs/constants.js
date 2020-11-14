/**
 * External dependencies
 */

import { translate as __ } from 'i18n-calypso';

export const getHeaders = ( groupBy ) => {
	if ( groupBy === 'url' ) {
		return [
			{
				name: 'url',
				title: __( 'Source URL' ),
				primary: true,
				sortable: false,
			},
			{
				name: 'count',
				title: __( 'Count' ),
				sortable: true,
			},
		];
	} else if ( groupBy === 'ip' ) {
		return [
			{
				name: 'ip',
				title: __( 'IP' ),
				primary: true,
				sortable: false,
			},
			{
				name: 'count',
				title: __( 'Count' ),
				sortable: true,
			},
		];
	} else if ( groupBy === 'agent' ) {
		return [
			{
				name: 'agent',
				title: __( 'User Agent' ),
				primary: true,
				sortable: false,
			},
			{
				name: 'count',
				title: __( 'Count' ),
				sortable: true,
			},
		];
	}
	return [
		{
			name: 'date',
			title: __( 'Date' ),
		},
		{
			name: 'method',
			title: __( 'Method' ),
			sortable: false,
		},
		{
			name: 'domain',
			title: __( 'Domain' ),
			sortable: false,
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
			name: 'redirect_by',
			title: __( 'Redirect By' ),
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
	];
};

export const getBulk = () => [
	{
		id: 'delete',
		name: __( 'Delete' ),
	},
];

export const getDisplayGroups = ( groupBy ) => {
	if ( groupBy ) {
		return [ { value: 'group', label: __( 'Group' ), grouping: [ groupBy, 'count' ] } ];
	}
	return [
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
		{
			value: 'all',
			label: __( 'Display All' ),
			grouping: getDisplayOptions( groupBy ).map( ( item ) => item.value ),
		},
	];
};

export const getDisplayOptions = ( groupBy ) => {
	if ( groupBy === 'url' ) {
		return [ { value: 'url', label: __( 'URL' ) }, { value: 'count', label: __( 'Count' ) } ];
	}

	if ( groupBy === 'agent' ) {
		return [ { value: 'agent', label: __( 'User Agent' ) }, { value: 'count', label: __( 'Count' ) } ];
	}

	if ( groupBy === 'ip' ) {
		return [ { value: 'ip', label: __( 'IP' ) }, { value: 'count', label: __( 'Count' ) } ];
	}

	return [
		{ value: 'date', label: __( 'Date' ) },
		{ value: 'method', label: __( 'Method' ) },
		{ value: 'domain', label: __( 'Domain' ) },
		{ value: 'url', label: __( 'URL' ) },
		{ value: 'redirect_by', label: __( 'Redirect By' ) },
		{ value: 'code', label: __( 'HTTP code' ) },
		{ value: 'referrer', label: __( 'Referrer' ) },
		{ value: 'agent', label: __( 'User Agent' ) },
		{ value: 'target', label: __( 'Target' ) },
		{ value: 'ip', label: __( 'IP' ) },
	];
};

export const getSearchOptions = () => [
	{
		name: 'url',
		title: __( 'Search URL' ),
	},
	{
		name: 'url-exact',
		title: __( 'Search exact URL' ),
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
		{
			value: 'agent',
			label: __( 'Group by user agent' ),
		},
	];

	if ( ipLogging > 0 ) {
		values.push( {
			value: 'ip',
			label: __( 'Group by IP' ),
		} );
	}

	return values;
};

export const getFilterOptions = () => [
	{
		label: __( 'Method' ),
		value: 'method',
		options: [
			{
				label: 'GET',
				value: 'get',
			},
			{
				label: 'POST',
				value: 'post',
			},
			{
				label: 'HEAD',
				value: 'head',
			},
		],
	},
	{
		label: __( 'Redirect By' ),
		value: 'redirect_by',
		options: [
			{
				label: __( 'WordPress' ),
				value: 'wordpress',
			},
			{
				label: __( 'Redirection' ),
				value: 'redirection',
			},
		],
	},
];
