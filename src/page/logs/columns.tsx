import { ExternalLink } from '@wp-plugin-components';
import { getServerUrl } from 'lib/wordpress-url';

interface Log {
	id: number;
	created: string;
	url: string;
	sent_to: string;
	agent: string;
	referrer: string;
	ip: string;
	module: string;
	redirection_id?: number;
	request_method: string;
	request_data?: Record< string, any >;
	http_code: number;
	domain?: string;
	redirect_by?: string;
	count?: number;
}

interface Column {
	name: string;
	content: JSX.Element | string | number;
	alwaysDisplay?: boolean;
}

function getUrl( row: Log ): string {
	const server = row.domain ? 'https://' + row.domain : document.location.origin;
	return getServerUrl( server, row.url );
}

function getTarget( row: Log ): string | JSX.Element {
	if ( ! row.sent_to ) {
		return '';
	}

	const server = row.domain ? 'https://' + row.domain : document.location.origin;
	const targetUrl = getServerUrl( server, row.sent_to );

	return <ExternalLink url={ targetUrl }>{ row.sent_to }</ExternalLink>;
}

export default function getColumns( row: Log ): Column[] {
	const { created, url, agent, referrer, ip, request_method, http_code, domain, redirect_by, count } = row;

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
			content: domain || '',
		},
		{
			name: 'url',
			content: <ExternalLink url={ getUrl( row ) }>{ url }</ExternalLink>,
			alwaysDisplay: true,
		},
		{
			name: 'target',
			content: getTarget( row ),
		},
		{
			name: 'redirect_by',
			content: redirect_by || '',
		},
		{
			name: 'code',
			content: http_code,
		},
		{
			name: 'referrer',
			content: referrer || '',
		},
		{
			name: 'agent',
			content: agent || '',
		},
		{
			name: 'ip',
			content: ip || '',
		},
		{
			name: 'count',
			content: count ? new Intl.NumberFormat( window.Redirectioni10n.locale ).format( count ) : '',
		},
	];
}
