import Highlighter from 'react-highlight-words';
import { __ } from '@wordpress/i18n';
import ColumnIp from './column-ip';
import ColumnUrl from './column-url';
import ColumnDate from './column-date';
import ColumnTarget from './column-target';
import ColumnReferrer from './column-referrer';

interface FilterBy {
	domain?: string;
	referrer?: string;
	agent?: string;
	url?: string;
	'url-exact'?: string;
	ip?: string;
	target?: string;
}

interface Table {
	filterBy: FilterBy;
	groupBy: string;
}

interface RowParams {
	table: Table;
}

interface Row {
	created: string;
	created_time: string;
	referrer?: string;
	agent?: string;
	request_method: string;
	http_code: number;
	domain?: string;
	redirect_by?: string;
	url?: string;
	ip: string;
	count: number;
	sent_to?: string;
}

interface Actions {
	onCreate: () => void;
	onDelete: () => void;
	onFilter: ( filter: any ) => void;
}

interface RenderedRow {
	name: string;
	content: JSX.Element | string | number;
}

function getColumns( row: Row, rowParams: RowParams, actions: Actions, isSaving: boolean ): RenderedRow[] {
	const { created, created_time, referrer, agent, request_method, http_code, domain = '', redirect_by } = row;
	const { table } = rowParams;
	const { onFilter } = actions;

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
					searchWords={ [ table.filterBy.domain || '' ] }
					textToHighlight={ domain ? domain : '' }
					autoEscape
				/>
			),
		},
		{
			name: 'url',
			content: <ColumnUrl row={ row } table={ table } />,
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
				<ColumnReferrer
					{ ...( referrer !== undefined ? { url: referrer } : {} ) }
					{ ...( table.filterBy.referrer !== undefined ? { search: table.filterBy.referrer } : {} ) }
				/>
			),
		},
		{
			name: 'agent',
			content: (
				<Highlighter
					searchWords={ [ table.filterBy.agent || '' ] }
					textToHighlight={ agent || '' }
					autoEscape
				/>
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
