/**
 * External dependencies
 */

import Highlighter from 'react-highlight-words';
import { _n, __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */

import ColumnIp from './column-ip';
import ColumnUrl from './column-url';
import ColumnDate from './column-date';
import ColumnTarget from './column-target';
import ColumnReferrer from './column-referrer';

/** @typedef {import('component/table').Table} Table */
/** @typedef {import('component/table').TableRow} TableRow */
/** @typedef {import('component/table').RenderedRow} RenderedRow */

/**
 * @typedef Row404
 * @property {string} domain
 * @property {string} url
 * @property {string} ip
 * @property {string} agent
 * @property {string} request_method
 * @property {number} http_code
 * @property {string} referrer
 * @property {string} created
 * @property {string} created_time
 */

/**
 * Return all the columns associated with a 404 log
 *
 * @param {TableRow & Row404} row
 * @param {{table: Table}} rowParams
 * @returns {RenderedRow[]}
 */
function getColumns( row, rowParams, actions, isSaving ) {
	const { created, created_time, referrer, agent, request_method, http_code, domain = '', redirect_by } = row;
	const { table } = rowParams;
	const { onCreate, onDelete, onFilter } = actions;

	return [
		{
			name: 'date',
			content: <ColumnDate created={ created } createdTime={ created_time } />,
		},
		{
			name: 'method',
			content: request_method,
		},
		{
			name: 'domain',
			content: (
				<Highlighter
					searchWords={ [ table.filterBy.domain ] }
					textToHighlight={ domain ? domain : '' }
					autoEscape
				/>
			),
		},
		{
			name: 'url',
			content: <ColumnUrl row={ row } table={ table } onDelete={ onDelete } onCreate={ onCreate } />,
		},
		{
			name: 'target',
			content: <ColumnTarget row={ row } filters={ table.filterBy } />,
		},
		{
			name: 'redirect_by',
			content: redirect_by ? redirect_by : __( 'Redirection', 'redirection' ),
		},
		{
			name: 'code',
			content: http_code > 0 ? http_code.toString() : '',
		},
		{
			name: 'referrer',
			content: (
				<ColumnReferrer url={ referrer } search={ table.filterBy.referrer ? table.filterBy.referrer : '' } />
			),
		},
		{
			name: 'agent',
			content: (
				<Highlighter searchWords={ [ table.filterBy.agent ] } textToHighlight={ agent || '' } autoEscape />
			),
		},
		{
			name: 'ip',
			content: <ColumnIp row={ row } table={ table } onFilter={ onFilter } isSaving={ isSaving } />,
		},
		{
			name: 'count',
			content: new Intl.NumberFormat( window.Redirectioni10n.locale ).format( row.count ),
		},
	];
}

export default getColumns;
