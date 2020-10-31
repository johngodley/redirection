/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'i18n-calypso';

/**
 * Internal dependencies
 */
import { TableRow } from 'component/form-table';
import { Select } from 'wp-plugin-components';
import UrlMonitoring from './url-monitor';

export const queryMatch = () => [
	{ value: 'exact', label: __( 'Exact match in any order' ) },
	{ value: 'ignore', label: __( 'Ignore all query parameters' ) },
	{ value: 'pass', label: __( 'Ignore and pass all query parameters' ) },
];
const expireTimes = () => [
	{ value: -1, label: __( 'Never cache' ) },
	{ value: 1, label: __( 'An hour' ) },
	{ value: 24, label: __( 'A day' ) },
	{ value: 24 * 7, label: __( 'A week' ) },
	{ value: 0, label: __( 'Forever' ) },
];

function UrlOptions( props ) {
	const { settings, onChange, getLink, groups, postTypes } = props;
	const { flag_case, flag_trailing, flag_query, auto_target, redirect_cache, cache_key } = settings;

	return (
		<>
			<tr className="redirect-option__row">
				<td colSpan={ 2 }>
					<h2 className="title">{ __( 'URL' ) }</h2>
				</td>
			</tr>
			<UrlMonitoring
				settings={ settings }
				onChange={ onChange }
				groups={ groups }
				getLink={ getLink }
				postTypes={ postTypes }
			/>
			<TableRow title={ __( 'Default URL settings' ) + ':' } url={ getLink( 'options', 'urlsettings' ) }>
				<p>{ __( 'Applies to all redirections unless you configure them otherwise.' ) }</p>
				<label>
					<p>
						<input type="checkbox" name="flag_case" onChange={ onChange } checked={ flag_case } />
						{ __(
							'Case insensitive matches (i.e. {{code}}/Exciting-Post{{/code}} will match {{code}}/exciting-post{{/code}})',
							{
								components: {
									code: <code />,
								},
							}
						) }
					</p>
				</label>

				<label>
					<p>
						<input
							type="checkbox"
							name="flag_trailing"
							onChange={ onChange }
							checked={ flag_trailing }
						/>
						{ __(
							'Ignore trailing slashes (i.e. {{code}}/exciting-post/{{/code}} will match {{code}}/exciting-post{{/code}})',
							{
								components: {
									code: <code />,
								},
							}
						) }
					</p>
				</label>
			</TableRow>
			<TableRow title={ __( 'Default query matching' ) + ':' } url={ getLink( 'options', 'querysettings' ) }>
				<p>{ __( 'Applies to all redirections unless you configure them otherwise.' ) }</p>
				<p>
					<Select items={ queryMatch() } name="flag_query" value={ flag_query } onChange={ onChange } />
				</p>
				<ul>
					<li>
						{ __(
							'Exact - matches the query parameters exactly defined in your source, in any order'
						) }
					</li>
					<li>{ __( 'Ignore - as exact, but ignores any query parameters not in your source' ) }</li>
					<li>{ __( 'Pass - as ignore, but also copies the query parameters to the target' ) }</li>
				</ul>
			</TableRow>
			<TableRow title={ __( 'Auto-generate URL' ) + ':' } url={ getLink( 'options', 'autogenerate' ) }>
				<input
					className="regular-text"
					type="text"
					value={ auto_target }
					name="auto_target"
					onChange={ onChange }
				/>
				<br />
				<span className="sub">
					{ __(
						'Used to auto-generate a URL if no URL is given. Use the special tags {{code}}$dec${{/code}} or {{code}}$hex${{/code}} to insert a unique ID instead',
						{
							components: {
								code: <code />,
							},
						}
					) }
				</span>
			</TableRow>

			<TableRow title={ __( 'HTTP Cache Header' ) } url={ getLink( 'options', 'cache' ) }>
				<Select
					items={ expireTimes() }
					name="redirect_cache"
					value={ parseInt( redirect_cache, 10 ) }
					onChange={ onChange }
				/>{' '}
				&nbsp;
				<span className="sub">
					{ __( 'How long to cache redirected 301 URLs (via "Expires" HTTP header)' ) }
				</span>
			</TableRow>
			<TableRow title={ __( 'Redirect Caching' ) } url={ getLink( 'options', 'cache' ) }>
				<label>
					<input type="checkbox" name="cache_key" onChange={ onChange } checked={ cache_key !== 0 && cache_key !== false } />
					&nbsp;
					<span className="sub">
						{ __(
							'Enable caching of redirects via WordPress object cache. Can improve performance, but requires a working object cache.'
						) }
					</span>
				</label>
			</TableRow>
		</>
	);
}

export default UrlOptions;
