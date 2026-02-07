import { __ } from '@wordpress/i18n';
import { TableRow } from 'component/form-table';
import { Select, createInterpolateElement } from '@wp-plugin-components';
import UrlMonitoring from './url-monitor';

interface Option {
	value: number | string;
	label: string;
}

interface Settings {
	flag_case: boolean;
	flag_trailing: boolean;
	flag_query: string;
	auto_target: string;
	redirect_cache: number;
	cache_key: number | boolean;
	associated_redirect: string;
	monitor_post: number;
	monitor_types: string[];
}

interface GroupOption {
	value: number | Array< { value: number } >;
	label: string;
}

interface PostTypes {
	[ key: string ]: string;
}

interface UrlOptionsProps {
	settings: Settings;
	onChange: ( ev: React.ChangeEvent< HTMLInputElement > | { [ key: string ]: any } ) => void;
	getLink: ( rel: string, anchor?: string ) => string;
	groups: GroupOption[];
	postTypes: PostTypes;
}

export const queryMatch = (): Option[] => [
	{ value: 'exact', label: __( 'Exact match in any order', 'redirection' ) },
	{ value: 'ignore', label: __( 'Ignore all query parameters', 'redirection' ) },
	{ value: 'pass', label: __( 'Ignore and pass all query parameters', 'redirection' ) },
];
const expireTimes = (): Option[] => [
	{ value: -1, label: __( 'Never cache', 'redirection' ) },
	{ value: 1, label: __( 'An hour', 'redirection' ) },
	{ value: 24, label: __( 'A day', 'redirection' ) },
	{ value: 24 * 7, label: __( 'A week', 'redirection' ) },
	{ value: 0, label: __( 'Forever', 'redirection' ) },
];

function UrlOptions( props: UrlOptionsProps ) {
	const { settings, onChange, getLink, groups, postTypes } = props;
	const { flag_case, flag_trailing, flag_query, auto_target, redirect_cache, cache_key } = settings;

	return (
		<>
			<tr className="redirect-option__row">
				<th colSpan={ 2 }>
					<h2 className="title">{ __( 'URL', 'redirection' ) }</h2>
				</th>
			</tr>
			<UrlMonitoring
				settings={ settings }
				onChange={ onChange }
				groups={ groups }
				getLink={ getLink }
				postTypes={ postTypes }
			/>
			<TableRow
				title={ __( 'Default URL settings', 'redirection' ) + ':' }
				url={ getLink( 'options', 'urlsettings' ) }
			>
				<p>{ __( 'Applies to all redirections unless you configure them otherwise.', 'redirection' ) }</p>
				<p>
					<input
						type="checkbox"
						id="url-options-flag-case"
						name="flag_case"
						onChange={ onChange }
						checked={ flag_case }
					/>
					<label htmlFor="url-options-flag-case">
						{ createInterpolateElement(
							__(
								'Case insensitive matches (i.e. {{code}}/Exciting-Post{{/code}} will match {{code}}/exciting-post{{/code}})',
								'redirection'
							),
							{
								code: <code />,
							}
						) }
					</label>
				</p>

				<p>
					<input
						type="checkbox"
						id="url-options-flag-trailing"
						name="flag_trailing"
						onChange={ onChange }
						checked={ flag_trailing }
					/>
					<label htmlFor="url-options-flag-trailing">
						{ createInterpolateElement(
							__(
								'Ignore trailing slashes (i.e. {{code}}/exciting-post/{{/code}} will match {{code}}/exciting-post{{/code}})',
								'redirection'
							),
							{
								code: <code />,
							}
						) }
					</label>
				</p>
			</TableRow>
			<TableRow
				title={ __( 'Default query matching', 'redirection' ) + ':' }
				url={ getLink( 'options', 'querysettings' ) }
			>
				<p>{ __( 'Applies to all redirections unless you configure them otherwise.', 'redirection' ) }</p>
				<p>
					<Select
						items={ queryMatch() as any }
						name="flag_query"
						value={ flag_query }
						onChange={ onChange as any }
					/>
				</p>
				<ul>
					<li>
						{ __(
							'Exact - matches the query parameters exactly defined in your source, in any order',
							'redirection'
						) }
					</li>
					<li>
						{ __(
							'Ignore - as exact, but ignores any query parameters not in your source',
							'redirection'
						) }
					</li>
					<li>
						{ __( 'Pass - as ignore, but also copies the query parameters to the target', 'redirection' ) }
					</li>
				</ul>
			</TableRow>
			<TableRow
				title={ __( 'Auto-generate URL', 'redirection' ) + ':' }
				url={ getLink( 'options', 'autogenerate' ) }
			>
				<input
					className="regular-text"
					type="text"
					value={ auto_target }
					name="auto_target"
					onChange={ onChange }
				/>
				<br />
				<span className="sub">
					{ createInterpolateElement(
						__(
							'Used to auto-generate a URL if no URL is given. Use the special tags {{code}}$dec${{/code}} or {{code}}$hex${{/code}} to insert a unique ID instead',
							'redirection'
						),
						{
							code: <code />,
						}
					) }
				</span>
			</TableRow>

			<TableRow title={ __( 'HTTP Cache Header', 'redirection' ) } url={ getLink( 'options', 'cache' ) }>
				<Select
					items={ expireTimes() as any }
					name="redirect_cache"
					value={ String( redirect_cache ) }
					onChange={ onChange as any }
				/>{ ' ' }
				&nbsp;
				<span className="sub">
					{ __( 'How long to cache redirected 301 URLs (via "Expires" HTTP header)', 'redirection' ) }
				</span>
			</TableRow>
			<TableRow title={ __( 'Redirect Caching', 'redirection' ) } url={ getLink( 'options', 'cache' ) }>
				<input
					id="url-options-cache-key"
					type="checkbox"
					name="cache_key"
					onChange={ onChange }
					checked={ cache_key !== 0 && cache_key !== false }
				/>
				&nbsp;
				<label htmlFor="url-options-cache-key">
					<span className="sub">
						{ __(
							'(beta) Enable caching of redirects via WordPress object cache. Can improve performance. Requires an object cache.',
							'redirection'
						) }
					</span>
				</label>
			</TableRow>
		</>
	);
}

export default UrlOptions;
