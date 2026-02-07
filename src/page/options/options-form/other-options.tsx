import { __, sprintf } from '@wordpress/i18n';
import { TableRow } from 'component/form-table';
import { Select, createInterpolateElement } from '@wp-plugin-components';

interface Option {
	value: number | string;
	label: string;
}

interface Settings {
	location: string;
	token: string;
	rest_api: number;
	plugin_update: string;
}

interface OtherOptionsProps {
	settings: Settings;
	onChange: ( ev: React.ChangeEvent< HTMLInputElement > ) => void;
	getLink: ( rel: string, anchor?: string ) => string;
	installed: string;
	warning: string;
}

export const restApi = (): Option[] => [
	{ value: 0, label: __( 'Default REST API', 'redirection' ) },
	{ value: 1, label: __( 'Raw REST API', 'redirection' ) },
	{ value: 3, label: __( 'Relative REST API', 'redirection' ) },
];

function getPluginUpdateOptions(): Option[] {
	return [
		{
			value: 'prompt',
			// translators: Upgrade manually when prompted
			label: __( 'Upgrade manually when prompted', 'redirection' ),
		},
		{
			value: 'admin',
			// translators: Automatically upgrade on admin pages
			label: __( 'Automatically upgrade on admin pages', 'redirection' ),
		},
	];
}

function OtherOptions( props: OtherOptionsProps ) {
	const { settings, onChange, getLink, installed, warning } = props;
	const { location, token, rest_api, plugin_update } = settings;

	return (
		<>
			<tr className="redirect-option__row">
				<th colSpan={ 2 }>
					<h2 className="title">{ __( 'Advanced', 'redirection' ) }</h2>
				</th>
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
				<p>
					<label className="screen-reader-text" htmlFor="apache-htaccess-location">
						{ __( 'Apache .htaccess location', 'redirection' ) }
					</label>
					<input
						id="apache-htaccess-location"
						type="text"
						className="regular-text"
						name="location"
						value={ location }
						onChange={ onChange }
					/>
				</p>

				<p className="sub">
					{ /* translators: %(installed)s is the path where WordPress is installed */ }
					{ createInterpolateElement(
						sprintf(
							// translators: %(installed)s is the path where WordPress is installed
							__(
								'Redirects added to an Apache group can be saved to an {{code}}.htaccess{{/code}} file by adding the full path here. For reference, your WordPress is installed to {{code}}%(installed)s{{/code}}.',
								'redirection'
							),
							{ installed }
						),
						{
							code: <code />,
						}
					) }
				</p>

				{ warning && (
					<p className="inline-notice">
						{ __( 'Unable to save .htaccess file', 'redirection' ) } <code>{ warning }</code>
					</p>
				) }
			</TableRow>
			<TableRow title={ __( 'REST API', 'redirection' ) } url={ getLink( 'options', 'restapi' ) }>
				<Select
					items={ restApi() as any }
					name="rest_api"
					value={ String( rest_api ) }
					onChange={ onChange as any }
				/>{ ' ' }
				&nbsp;
				<span className="sub">
					{ __( "How Redirection uses the REST API - don't change unless necessary", 'redirection' ) }
				</span>
			</TableRow>
			<TableRow title={ __( 'Data Upgrade', 'redirection' ) }>
				<Select
					items={ getPluginUpdateOptions() as any }
					name="plugin_update"
					value={ plugin_update }
					onChange={ onChange as any }
				/>{ ' ' }
				&nbsp;
				<span className="sub">
					{ __( 'Decide how Redirection updates itself, if needed.', 'redirection' ) }
				</span>
			</TableRow>
		</>
	);
}

export default OtherOptions;
