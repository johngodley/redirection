import { ExternalLink } from '@wp-plugin-components';
import { getServerUrl } from 'lib/wordpress-url';

interface Error404 {
	id: number;
	created: string;
	url: string;
	agent: string;
	referrer: string;
	ip: string;
	domain: string;
	request_method?: string;
	http_code?: number;
	count?: number;
}

interface Column {
	name: string;
	content: JSX.Element | string | number;
	alwaysDisplay?: boolean;
}

function getUrl( row: Error404 ): string {
	const server = row.domain ? 'https://' + row.domain : document.location.origin;
	return getServerUrl( server, row.url );
}

export default function getColumns( row: Error404 ): Column[] {
	const { created, url, agent, referrer, ip, domain, request_method, http_code, count } = row;

	return [
		{
			name: 'date',
			content: created,
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
			content: <ExternalLink url={ getUrl( row ) }>{ url }</ExternalLink>,
			alwaysDisplay: true,
		},
		{
			name: 'code',
			content: http_code ?? '',
		},
		{
			name: 'referrer',
			content: referrer ?? '',
		},
		{
			name: 'agent',
			content: agent ?? '',
		},
		{
			name: 'ip',
			content: ip ?? '',
		},
		{
			name: 'count',
			content: count ? new Intl.NumberFormat( window.Redirectioni10n.locale ).format( count ) : '',
		},
	];
}
