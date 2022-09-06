/**
 * External dependencies
 */

import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { TableRow } from '../../../component/form-table';
import { Select, createInterpolateElement } from '@wp-plugin-components';

export const restApi = () => [
	{ value: 0, label: __( 'Default REST API', 'redirection' ) },
	{ value: 1, label: __( 'Raw REST API', 'redirection' ) },
	{ value: 3, label: __( 'Relative REST API', 'redirection' ) },
];

function getPluginUpdateOptions() {
	return [
		{
			value: 'prompt',
			label: __( 'Upgrade manually when prompted', 'redirection' ),
		},
		{
			value: 'admin',
			label: __( 'Automatically upgrade on admin pages', 'redirection' ),
		},
	];
}

function OtherOptions( props ) {
	const { settings, onChange, getLink, installed, warning } = props;
	const { location, token, rest_api, plugin_update } = settings;

	return (
		<>
			<tr className="redirect-option__row">
				<td colSpan={ 2 }>
					<h2 className="title">{ __( 'Advanced', 'redirection' ) }</h2>
				</td>
			</tr>
			<TableRow title={ __( 'RSS Token', 'redirection' ) + ':' } url={ getLink( 'options', 'rsstoken' ) }>
				<input className="regular-text" type="text" value={ token } name="token" onChange={ onChange } />
				<br />
				<span className="sub">
					{ __(
						'A unique token allowing feed readers access to Redirection log RSS (leave blank to auto-generate)',
						'redirection'
					) }
				</span>
			</TableRow>
			<TableRow title={ __( 'Apache .htaccess', 'redirection' ) } url={ getLink( 'options', 'apache' ) }>
				<label>
					<p>
						<input
							type="text"
							className="regular-text"
							name="location"
							value={ location }
							onChange={ onChange }
						/>
					</p>

					<p className="sub">
						{ createInterpolateElement(
							sprintf(
								__(
									'Redirects added to an Apache group can be saved to an {{code}}.htaccess{{/code}} file by adding the full path here. For reference, your WordPress is installed to {{code}}%(installed)s{{/code}}.',
									'redirection'
								),
								{ installed },
							),
							{
								code: <code />,
							},
						) }
					</p>

					{ warning && (
						<p className="inline-notice">
							{ __( 'Unable to save .htaccess file', 'redirection' ) } <code>{ warning }</code>
						</p>
					) }
				</label>
			</TableRow>
			<TableRow title={ __( 'REST API', 'redirection' ) } url={ getLink( 'options', 'restapi' ) }>
				<Select items={ restApi() } name="rest_api" value={ parseInt( rest_api, 10 ) } onChange={ onChange } />{' '}
				&nbsp;
				<span className="sub">
					{ __( "How Redirection uses the REST API - don't change unless necessary", 'redirection' ) }
				</span>
			</TableRow>
			<TableRow title={ __( 'Data Upgrade', 'redirection' ) }>
				<Select
					items={ getPluginUpdateOptions() }
					name="plugin_update"
					value={ plugin_update }
					onChange={ onChange }
				/>{' '}
				&nbsp;
				<span className="sub">
					{ __( "Decide how Redirection updates itself, if needed.", 'redirection' ) }
				</span>
			</TableRow>
		</>
	);
}

export default OtherOptions;
