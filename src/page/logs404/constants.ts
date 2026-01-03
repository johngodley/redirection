import { __ } from '@wordpress/i18n';
import { getAllHttpCodes } from 'component/redirect-edit/constants';

interface Header {
	name: string;
	title: string;
	primary?: boolean;
	sortable?: boolean;
}

interface BulkOption {
	id: string;
	name: string;
	isEverything?: boolean;
}

interface GroupByOption {
	value: string;
	label: string;
}

interface DisplayGroup {
	value: string;
	label: string;
	grouping: string[];
}

interface DisplayOption {
	value: string;
	label: string;
}

interface FilterOption {
	label: string;
	value: string;
	options: Array< { label: string; value: string | number } >;
}

interface SearchOption {
	name: string;
	title: string;
}

export const getHeaders = ( groupBy?: string ): Header[] => {
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
	}

	if ( groupBy === 'agent' ) {
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

	if ( groupBy === 'ip' ) {
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
	}

	return [
		{
			name: 'date',
			title: __( 'Date', 'redirection' ),
		},
		{
			name: 'method',
			title: __( 'Method', 'redirection' ),
		},
		{
			name: 'domain',
			title: __( 'Domain', 'redirection' ),
		},
		{
			name: 'url',
			title: __( 'Source URL', 'redirection' ),
			primary: true,
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

export const getBulk = ( groupBy?: string ): BulkOption[] => {
	const bulk: BulkOption[] = [
		{
			id: 'delete',
			name: __( 'Delete', 'redirection' ),
		},
	];

	if ( groupBy === 'ip' ) {
		return bulk.concat( [
			{
				id: 'redirect-ip',
				name: __( 'Redirect All', 'redirection' ),
			},
			{
				id: 'block',
				name: __( 'Block IP', 'redirection' ),
			},
		] );
	}

	if ( groupBy === 'agent' ) {
		return bulk;
	}

	return bulk.concat( [
		{
			id: 'redirect-url',
			name: __( 'Redirect All', 'redirection' ),
			isEverything: false,
		},
		{
			id: 'ignore',
			name: __( 'Ignore URL', 'redirection' ),
			isEverything: false,
		},
	] );
};

export const getGroupBy = ( ipLogging: number ): GroupByOption[] => {
	const values: GroupByOption[] = [
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

export function getDisplayGroups( groupBy?: string ): DisplayGroup[] {
	if ( groupBy ) {
		return [ { value: 'group', label: __( 'Group', 'redirection' ), grouping: [ groupBy, 'count' ] } ];
	}

	return [
		{
			value: 'standard',
			label: __( 'Standard Display', 'redirection' ),
			grouping: [ 'date', 'url', 'agent', 'ip' ],
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
}

export function getDisplayOptions( groupBy?: string ): DisplayOption[] {
	if ( groupBy === 'url' ) {
		return [
			{ value: 'url', label: __( 'URL', 'redirection' ) },
			{ value: 'count', label: __( 'Count', 'redirection' ) },
		];
	}

	if ( groupBy === 'agent' ) {
		return [
			{ value: 'agent', label: __( 'User Agent', 'redirection' ) },
			{ value: 'count', label: __( 'Count', 'redirection' ) },
		];
	}

	if ( groupBy === 'ip' ) {
		return [
			{ value: 'ip', label: __( 'IP', 'redirection' ) },
			{ value: 'count', label: __( 'Count', 'redirection' ) },
		];
	}

	return [
		{ value: 'date', label: __( 'Date', 'redirection' ) },
		{ value: 'method', label: __( 'Method', 'redirection' ) },
		{ value: 'domain', label: __( 'Domain', 'redirection' ) },
		{ value: 'url', label: __( 'URL', 'redirection' ) },
		{ value: 'code', label: __( 'HTTP code', 'redirection' ) },
		{ value: 'referrer', label: __( 'Referrer', 'redirection' ) },
		{ value: 'agent', label: __( 'User Agent', 'redirection' ) },
		{ value: 'ip', label: __( 'IP', 'redirection' ) },
	];
}

export const getFilterOptions = (): FilterOption[] => [
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
		label: __( 'HTTP Status Code', 'redirection' ),
		value: 'http',
		options: getAllHttpCodes().filter(
			( code ) => parseInt( code.value, 10 ) >= 400 && parseInt( code.value, 10 ) < 500
		),
	},
];

export const getSearchOptions = (): SearchOption[] => [
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
		name: 'domain',
		title: __( 'Search domain', 'redirection' ),
	},
];
