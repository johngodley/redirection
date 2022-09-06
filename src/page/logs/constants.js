/**
 * External dependencies
 */

import { __ } from '@wordpress/i18n';

export const getHeaders = ( groupBy ) => {
	if ( groupBy === 'url' ) {
		return [
			{
				name: 'url',
				title: __( 'Source URL', 'redirection' ),
				primary: true,
				sortable: false,
			},
			{
				name: 'count',
				title: __( 'Count', 'redirection' ),
				sortable: true,
			},
		];
	} else if ( groupBy === 'ip' ) {
		return [
			{
				name: 'ip',
				title: __( 'IP', 'redirection' ),
				primary: true,
				sortable: false,
			},
			{
				name: 'count',
				title: __( 'Count', 'redirection' ),
				sortable: true,
			},
		];
	} else if ( groupBy === 'agent' ) {
		return [
			{
				name: 'agent',
				title: __( 'User Agent', 'redirection' ),
				primary: true,
				sortable: false,
			},
			{
				name: 'count',
				title: __( 'Count', 'redirection' ),
				sortable: true,
			},
		];
	}
	return [
		{
			name: 'date',
			title: __( 'Date', 'redirection' ),
		},
		{
			name: 'method',
			title: __( 'Method', 'redirection' ),
			sortable: false,
		},
		{
			name: 'domain',
			title: __( 'Domain', 'redirection' ),
			sortable: false,
		},
		{
			name: 'url',
			title: __( 'Source URL', 'redirection' ),
			primary: true,
		},
		{
			name: 'target',
			title: __( 'Target URL', 'redirection' ),
			sortable: false,
		},
		{
			name: 'redirect_by',
			title: __( 'Redirect By', 'redirection' ),
			sortable: false,
		},
		{
			name: 'code',
			title: __( 'HTTP code', 'redirection' ),
			sortable: false,
		},
		{
			name: 'referrer',
			title: __( 'Referrer', 'redirection' ),
			sortable: false,
		},
		{
			name: 'agent',
			title: __( 'User Agent', 'redirection' ),
			sortable: false,
		},
		{
			name: 'ip',
			title: __( 'IP', 'redirection' ),
			sortable: false,
		},
	];
};

export const getBulk = () => [
	{
		id: 'delete',
		name: __( 'Delete', 'redirection' ),
	},
];

export const getDisplayGroups = ( groupBy ) => {
	if ( groupBy ) {
		return [ { value: 'group', label: __( 'Group', 'redirection' ), grouping: [ groupBy, 'count' ] } ];
	}
	return [
		{
			value: 'standard',
			label: __( 'Standard Display', 'redirection' ),
			grouping: [ 'date', 'url', 'target', 'agent', 'ip' ],
		},
		{
			value: 'minimal',
			label: __( 'Compact Display', 'redirection' ),
			grouping: [ 'date', 'url' ],
		},
		{
			value: 'all',
			label: __( 'Display All', 'redirection' ),
			grouping: getDisplayOptions( groupBy ).map( ( item ) => item.value ),
		},
	];
};

export const getDisplayOptions = ( groupBy ) => {
	if ( groupBy === 'url' ) {
		return [ { value: 'url', label: __( 'URL', 'redirection' ) }, { value: 'count', label: __( 'Count', 'redirection' ) } ];
	}

	if ( groupBy === 'agent' ) {
		return [ { value: 'agent', label: __( 'User Agent', 'redirection' ) }, { value: 'count', label: __( 'Count', 'redirection' ) } ];
	}

	if ( groupBy === 'ip' ) {
		return [ { value: 'ip', label: __( 'IP', 'redirection' ) }, { value: 'count', label: __( 'Count', 'redirection' ) } ];
	}

	return [
		{ value: 'date', label: __( 'Date', 'redirection' ) },
		{ value: 'method', label: __( 'Method', 'redirection' ) },
		{ value: 'domain', label: __( 'Domain', 'redirection' ) },
		{ value: 'url', label: __( 'URL', 'redirection' ) },
		{ value: 'redirect_by', label: __( 'Redirect By', 'redirection' ) },
		{ value: 'code', label: __( 'HTTP code', 'redirection' ) },
		{ value: 'referrer', label: __( 'Referrer', 'redirection' ) },
		{ value: 'agent', label: __( 'User Agent', 'redirection' ) },
		{ value: 'target', label: __( 'Target', 'redirection' ) },
		{ value: 'ip', label: __( 'IP', 'redirection' ) },
	];
};

export const getSearchOptions = () => [
	{
		name: 'url',
		title: __( 'Search URL', 'redirection' ),
	},
	{
		name: 'url-exact',
		title: __( 'Search exact URL', 'redirection' ),
	},
	{
		name: 'referrer',
		title: __( 'Search referrer', 'redirection' ),
	},
	{
		name: 'agent',
		title: __( 'Search user agent', 'redirection' ),
	},
	{
		name: 'ip',
		title: __( 'Search IP', 'redirection' ),
	},
	{
		name: 'target',
		title: __( 'Search target URL', 'redirection' ),
	},
	{
		name: 'domain',
		title: __( 'Search domain', 'redirection' ),
	},
];

export const getGroupBy = ( ipLogging ) => {
	const values = [
		{
			value: '',
			label: __( 'No grouping', 'redirection' ),
		},
		{
			value: 'url',
			label: __( 'Group by URL', 'redirection' ),
		},
		{
			value: 'agent',
			label: __( 'Group by user agent', 'redirection' ),
		},
	];

	if ( ipLogging > 0 ) {
		values.push( {
			value: 'ip',
			label: __( 'Group by IP', 'redirection' ),
		} );
	}

	return values;
};

export const getFilterOptions = () => [
	{
		label: __( 'Method', 'redirection' ),
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
		label: __( 'Redirect By', 'redirection' ),
		value: 'redirect_by',
		options: [
			{
				label: __( 'WordPress', 'redirection' ),
				value: 'wordpress',
			},
			{
				label: __( 'Redirection', 'redirection' ),
				value: 'redirection',
			},
		],
	},
];
