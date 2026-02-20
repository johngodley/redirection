import Highlighter from 'react-highlight-words';
import { ExternalLink } from '@wp-plugin-components';
import { getServerUrl, getOrigin } from 'lib/wordpress-url';
import { __ } from '@wordpress/i18n';
import { RowActions, RowAction } from 'component/table/row-action';

// Interface for logs - all fields optional to support grouped results
interface Log {
	id: number | string;
	created?: string;
	url?: string;
	sent_to?: string | null;
	agent?: string | null;
	referrer?: string | null;
	ip?: string | null;
	module?: string;
	redirection_id?: number;
	request_method?: string | null;
	request_data?: Record< string, unknown >;
	http_code?: number;
	domain?: string | null;
	redirect_by?: string | null;
	count?: number;
}

interface Column {
	name: string;
	content: JSX.Element | string | number;
	alwaysDisplay?: boolean;
}

interface FilterBy {
	[ key: string ]: string;
}

interface RowParams {
	table: {
		filterBy: FilterBy;
		groupBy?: string;
	};
	onFilter?: ( filter: FilterBy ) => void;
}

export function getUrl( row: Log ): string {
	if ( ! row.url ) {
		return '';
	}
	return getServerUrl( getOrigin( row.domain ), row.url );
}

export function getTarget( row: Log, filterBy: FilterBy ): string | JSX.Element {
	if ( ! row.sent_to ) {
		return '';
	}

	const searchWord = filterBy.target || '';
	const isAbsolute = /^https?:\/\//i.test( row.sent_to );
	const targetUrl = isAbsolute ? row.sent_to : getServerUrl( getOrigin( row.domain ), row.sent_to );

	return (
		<ExternalLink url={ targetUrl }>
			<Highlighter searchWords={ [ searchWord ] } textToHighlight={ row.sent_to } autoEscape />
		</ExternalLink>
	);
}

export default function getColumns( row: Log, rowParams?: RowParams ): Column[] {
	const { created, url, agent, referrer, ip, request_method, http_code, domain, redirect_by, count } = row;
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
			content: domain || '',
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
			name: 'target',
			content: getTarget( row, filterBy ),
		},
		{
			name: 'redirect_by',
			content: redirect_by || '',
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
				<>
					<a href={ 'https://redirect.li/ip/?ip=' + encodeURIComponent( ip ) }>
						<Highlighter searchWords={ [ filterBy.ip || '' ] } textToHighlight={ ip } autoEscape />
					</a>
					{ rowParams?.table?.groupBy === '' && rowParams?.onFilter && (
						<RowActions
							actions={ [
								<RowAction
									key="filter-ip"
									onClick={ () => {
										rowParams.onFilter?.( { ip } );
									} }
								>
									{ __( 'Filter by IP', 'redirection' ) }
								</RowAction>,
							] }
						/>
					) }
				</>
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
