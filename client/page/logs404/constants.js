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
			title: __( 'Referrer / User Agent' ),
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
			text: __( 'No grouping' ),
		},
		{
			value: 'url',
			text: __( 'Group by URL' ),
		},
	];

	if ( ipLogging > 0 ) {
		values.push( 	{
			value: 'ip',
			text: __( 'Group by IP' ),
		} );
	}

	return values;
};
