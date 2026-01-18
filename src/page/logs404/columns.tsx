import Highlighter from 'react-highlight-words';
import { ExternalLink } from '@wp-plugin-components';
import { getServerUrl } from 'lib/wordpress-url';

// Interface for 404 errors - all fields optional to support grouped results
interface Error404 {
	id: number | string;
	created?: string;
	url?: string;
	agent?: string;
	referrer?: string;
	ip?: string;
	domain?: string;
	request_method?: string;
	http_code?: number;
	count?: number;
}

interface Column {
	name: string;
	content: JSX.Element | string | number;
	alwaysDisplay?: boolean;
}

interface FilterBy {
	url?: string;
	'url-exact'?: string;
	ip?: string;
	referrer?: string;
	agent?: string;
}

interface RowParams {
	table: {
		filterBy: FilterBy;
	};
}

function getUrl( row: Error404 ): string {
	if ( ! row.url ) {
		return '';
	}
	const server = row.domain ? 'https://' + row.domain : document.location.origin;
	return getServerUrl( server, row.url );
}

export default function getColumns( row: Error404, rowParams?: RowParams ): Column[] {
	const { created, url, agent, referrer, ip, domain, request_method, http_code, count } = row;
	const filterBy = rowParams?.table?.filterBy || {};
	const urlSearch = filterBy.url || filterBy[ 'url-exact' ] || '';

	return [
		{
			name: 'date',
			content: created ?? '',
		},
		{
			name: 'method',
			content: request_method ? request_method.toUpperCase() : '',
		},
		{
			name: 'domain',
			content: domain ?? '',
		},
		{
			name: 'url',
			content: url ? (
				<ExternalLink url={ getUrl( row ) }>
					<Highlighter searchWords={ [ urlSearch ] } textToHighlight={ url } autoEscape />
				</ExternalLink>
			) : (
				''
			),
		},
		{
			name: 'code',
			content: http_code ?? '',
		},
		{
			name: 'referrer',
			content: referrer ? (
				<Highlighter searchWords={ [ filterBy.referrer || '' ] } textToHighlight={ referrer } autoEscape />
			) : (
				''
			),
		},
		{
			name: 'agent',
			content: agent ? (
				<Highlighter searchWords={ [ filterBy.agent || '' ] } textToHighlight={ agent } autoEscape />
			) : (
				''
			),
		},
		{
			name: 'ip',
			content: ip ? (
				<Highlighter searchWords={ [ filterBy.ip || '' ] } textToHighlight={ ip } autoEscape />
			) : (
				''
			),
		},
		{
			name: 'count',
			content: count ? new Intl.NumberFormat( window.Redirectioni10n.locale ).format( count ) : '',
		},
	];
}
