/**
 * Internal dependencies
 */

import { translate as __ } from 'wp-plugin-lib/locale';
import { getAllHttpCodes } from 'component/redirect-edit/constants';

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
	}

	if ( groupBy === 'agent' ) {
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

	if ( groupBy === 'ip' ) {
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
	}

	return [
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

/**
 * Get bulk options
 *
 * @param {string} groupBy
 */
export const getBulk = ( groupBy ) => {
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

	if ( groupBy === 'agent' ) {
		return [
			{
				id: 'delete',
				name: __( 'Delete' ),
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

export function getDisplayGroups( groupBy ) {
	if ( groupBy ) {
		return [ { value: 'group', label: __( 'Group' ), grouping: [ groupBy, 'count' ] } ];
	}

	return [
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
		{
			value: 'all',
			label: __( 'Display All' ),
			grouping: getDisplayOptions( groupBy ).map( ( item ) => item.value ),
		},
	];
}

/**
 * Get display options
 * @param {string} groupBy
 */
export function getDisplayOptions( groupBy ) {
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
		{ value: 'code', label: __( 'HTTP code' ) },
		{ value: 'referrer', label: __( 'Referrer' ) },
		{ value: 'agent', label: __( 'User Agent' ) },
		{ value: 'ip', label: __( 'IP' ) },
	];
}

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
		label: __( 'HTTP Status Code' ),
		value: 'http',
		options: getAllHttpCodes().filter( ( code ) => code.value >= 400 && code.value < 500 ),
	},
];

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
		name: 'domain',
		title: __( 'Search domain' ),
	},
];
