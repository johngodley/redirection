/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import { connect } from 'react-redux';

/**
 * Internal dependencies
 */
import { saveSettings } from 'state/settings/action';
import { STATUS_IN_PROGRESS } from 'state/settings/type';
import { FormTable, TableRow } from 'component/form-table';
import Select from 'component/select';
import ExternalLink from 'component/external-link';

const timeToKeep = () => [
	{ value: -1, text: __( 'No logs' ) },
	{ value: 1, text: __( 'A day' ) },
	{ value: 7, text: __( 'A week' ) },
	{ value: 30, text: __( 'A month' ) },
	{ value: 60, text: __( 'Two months' ) },
	{ value: 0, text: __( 'Forever' ) },
];
const expireTimes = () => [
	{ value: -1, text: __( 'Never cache' ) },
	{ value: 1, text: __( 'An hour' ) },
	{ value: 24, text: __( 'A day' ) },
	{ value: 24 * 7, text: __( 'A week' ) },
	{ value: 0, text: __( 'Forever' ) },
];
const ipLogging = () => [
	{ value: 0, text: __( 'No IP logging' ) },
	{ value: 1, text: __( 'Full IP logging' ) },
	{ value: 2, text: __( 'Anonymize IP (mask last part)' ) },
];
export const restApi = () => [
	{ value: 0, text: __( 'Default REST API' ) },
	{ value: 1, text: __( 'Raw REST API' ) },
	{ value: 3, text: __( 'Relative REST API' ) },
];
export const queryMatch = () => [
	{ value: 'exact', text: __( 'Exact match' ) },
	{ value: 'ignore', text: __( 'Ignore all query parameters' ) },
	{ value: 'pass', text: __( 'Ignore and pass all query parameters' ) },
];

class OptionsForm extends React.Component {
	constructor( props ) {
		super( props );

		const modules = props.values.modules;

		this.state = props.values;
		this.state.location = modules[ 2 ] ? modules[ 2 ].location : '';
	}

	onChange = ev => {
		const { target } = ev;
		const value = target.type === 'checkbox' ? target.checked : target.value;

		this.setState( { [ target.name ]: value } );
	}

	onSubmit = ev => {
		ev.preventDefault();
		this.props.onSaveSettings( this.state );
	}

	getFirstGroup( groups ) {
		if ( groups.length > 0 ) {
			if ( groups[ 0 ].value.length !== undefined ) {
				return groups[ 0 ].value[ 0 ].value;
			}

			return groups[ 0 ].value;
		}

		return 0;
	}

	getMonitorPost( post ) {
		const { groups } = this.props;

		if ( parseInt( post, 10 ) === 0 && groups.length > 0 ) {
			return this.getFirstGroup( groups );
		}

		return post;
	}

	onMonitor = ev => {
		const type = ev.target.name.replace( 'monitor_type_', '' );
		const { monitor_post, associated_redirect } = this.state;
		const monitor_types = this.state.monitor_types.filter( item => item !== type );

		if ( ev.target.checked ) {
			monitor_types.push( type );
		}

		this.setState( {
			monitor_types,
			monitor_post: monitor_types.length > 0 ? this.getMonitorPost( monitor_post ) : 0,
			associated_redirect: monitor_types.length > 0 ? associated_redirect : '',
		} );
	}

	getSnapshotBeforeUpdate( prevProps ) {
		if ( prevProps.values.token !== this.props.values.token ) {
			this.setState( { token: this.props.values.token } );
		}

		if ( prevProps.values.auto_target !== this.props.values.auto_target ) {
			this.setState( { auto_target: this.props.values.auto_target } );
		}

		return null;
	}

	componentDidUpdate( prevProps ) {
		const newLocation = this.props.values.modules[ 2 ] ? this.props.values.modules[ 2 ].location : '';
		const oldLocation = prevProps.values.modules[ 2 ] ? prevProps.values.modules[ 2 ].location : '';

		if ( oldLocation !== newLocation ) {
			this.setState( { location: newLocation } );
		}
	}

	renderMonitor( groups ) {
		return (
			<TableRow title={ __( 'URL Monitor Changes' ) + ':' } url={ this.supportLink( 'options', 'monitor' ) }>
				<Select items={ groups } name="monitor_post" value={ parseInt( this.state.monitor_post, 10 ) } onChange={ this.onChange } />
				&nbsp;
				{ __( 'Save changes to this group' ) }

				<p><input type="text" className="regular-text" name="associated_redirect" onChange={ this.onChange } placeholder={ __( 'For example "/amp"' ) } value={ this.state.associated_redirect } /> { __( 'Create associated redirect (added to end of URL)' ) }</p>
			</TableRow>
		);
	}

	renderPostTypes() {
		const { postTypes } = this.props;
		const { monitor_types } = this.state;
		const types = [];

		for ( const key in postTypes ) {
			const label = postTypes[ key ];
			const existing = monitor_types.find( item => item === key );
			const value = existing ? true : false;

			types.push(
				<p key={ key }>
					<label>
						<input type="checkbox" name={ 'monitor_type_' + key } onChange={ this.onMonitor } checked={ value } />

						{ __( 'Monitor changes to %(type)s', {
							args: {
								type: label.toLowerCase(),
							},
						} ) }
					</label>
				</p>
			);
		}

		return types;
	}

	supportLink( rel, anchor ) {
		return 'https://redirection.me/support/' + rel + '/?utm_source=redirection&utm_medium=plugin&utm_campaign=support' + ( anchor ? '&utm_term=' + anchor + '#' + anchor : '' );
	}

	render() {
		const { groups, saveStatus, installed, warning } = this.props;
		const canMonitor = this.state.monitor_types.length > 0;

		return (
			<form onSubmit={ this.onSubmit }>
				<FormTable>
					<TableRow title="">
						<label>
							<input type="checkbox" checked={ this.state.support } name="support" onChange={ this.onChange } />
							<span className="sub">{ __( "I'm a nice person and I have helped support the author of this plugin" ) }</span>
						</label>
					</TableRow>

					<TableRow title={ __( 'Redirect Logs' ) + ':' } url={ this.supportLink( 'logs' ) }>
						<Select items={ timeToKeep() } name="expire_redirect" value={ parseInt( this.state.expire_redirect, 10 ) } onChange={ this.onChange } /> { __( '(time to keep logs for)' ) }
					</TableRow>

					<TableRow title={ __( '404 Logs' ) + ':' } url={ this.supportLink( 'tracking-404-errors' ) }>
						<Select items={ timeToKeep() } name="expire_404" value={ parseInt( this.state.expire_404, 10 ) } onChange={ this.onChange } /> { __( '(time to keep logs for)' ) }
					</TableRow>

					<TableRow title={ __( 'IP Logging' ) + ':' } url={ this.supportLink( 'options', 'iplogging' ) }>
						<Select items={ ipLogging() } name="ip_logging" value={ parseInt( this.state.ip_logging, 10 ) } onChange={ this.onChange } /> { __( '(select IP logging level)' ) }

						&nbsp;- <ExternalLink url={ this.supportLink( 'privacy-gdpr' ) }>{ __( 'GDPR / Privacy information' ) }</ExternalLink>
					</TableRow>

					<TableRow title={ __( 'URL Monitor' ) + ':' } url={ this.supportLink( 'options', 'monitor' ) }>
						{ this.renderPostTypes() }
					</TableRow>

					{ canMonitor && this.renderMonitor( groups ) }

					<TableRow title={ __( 'RSS Token' ) + ':' } url={ this.supportLink( 'options', 'rsstoken' ) }>
						<input className="regular-text" type="text" value={ this.state.token } name="token" onChange={ this.onChange } /><br />
						<span className="sub">{ __( 'A unique token allowing feed readers access to Redirection log RSS (leave blank to auto-generate)' ) }</span>
					</TableRow>

					<TableRow title={ __( 'Default URL settings' ) + ':' } url={ this.supportLink( 'options', 'urlsettings' ) }>
						<p>{ __( 'Applies to all redirections unless you configure them otherwise.' ) }</p>
						<label>
							<p>
								<input type="checkbox" name="flag_case" onChange={ this.onChange } checked={ this.state.flag_case } />
								{ __( 'Case insensitive matches (i.e. {{code}}/Exciting-Post{{/code}} will match {{code}}/exciting-post{{/code}})', {
									components: {
										code: <code />,
									},
								} ) }
							</p>
						</label>

						<label>
							<p>
								<input type="checkbox" name="flag_trailing" onChange={ this.onChange } checked={ this.state.flag_trailing } />
								{ __( 'Ignore trailing slashes (i.e. {{code}}/exciting-post/{{/code}} will match {{code}}/exciting-post{{/code}})', {
									components: {
										code: <code />,
									},
								} ) }
							</p>
						</label>
					</TableRow>

					<TableRow title={ __( 'Default query matching' ) + ':' } url={ this.supportLink( 'options', 'querysettings' ) }>
						<p>{ __( 'Applies to all redirections unless you configure them otherwise.' ) }</p>
						<p>
							<Select items={ queryMatch() } name="flag_query" value={ this.state.flag_query } onChange={ this.onChange } />
						</p>
						<ul>
							<li>{ __( 'Exact - matches the query parameters exactly defined in your source, in any order' ) }</li>
							<li>{ __( 'Ignore - as exact, but ignores any query parameters not in your source' ) }</li>
							<li>{ __( 'Pass - as ignore, but also copies the query parameters to the target' ) }</li>
						</ul>
					</TableRow>

					<TableRow title={ __( 'Auto-generate URL' ) + ':' } url={ this.supportLink( 'options', 'autogenerate' ) }>
						<input className="regular-text" type="text" value={ this.state.auto_target } name="auto_target" onChange={ this.onChange } /><br />
						<span className="sub">
							{ __( 'Used to auto-generate a URL if no URL is given. Use the special tags {{code}}$dec${{/code}} or {{code}}$hex${{/code}} to insert a unique ID instead', {
								components: {
									code: <code />,
								},
							} ) }
						</span>
					</TableRow>

					<TableRow title={ __( 'Apache .htaccess' ) } url={ this.supportLink( 'options', 'apache' ) }>
						<label>
							<p><input type="text" className="regular-text" name="location" value={ this.state.location } onChange={ this.onChange } /></p>

							<p className="sub">
								{ __( 'Redirects added to an Apache group can be saved to an {{code}}.htaccess{{/code}} file by adding the full path here. For reference, your WordPress is installed to {{code}}%(installed)s{{/code}}.', {
									components: {
										code: <code />,
									},
									args: {
										installed,
									},
								} ) }
							</p>

							{ warning && <p className="inline-notice">{ __( 'Unable to save .htaccess file' ) } <code>{ warning }</code></p>}
						</label>
					</TableRow>

					<TableRow title={ __( 'Force HTTPS' ) } url={ this.supportLink( 'options', 'force-https' ) }>
						<label>
							<p>
								<input type="checkbox" name="https" onChange={ this.onChange } checked={ this.state.https } />
								{ __( 'Force a redirect from HTTP to the HTTPS version of your WordPress site domain. Please ensure your HTTPS is working before enabling.' ) }
								&nbsp; { __( '(beta)' ) }
							</p>
						</label>
					</TableRow>

					<TableRow title={ __( 'Redirect Cache' ) } url={ this.supportLink( 'options', 'cache' ) }>
						<Select items={ expireTimes() } name="redirect_cache" value={ parseInt( this.state.redirect_cache, 10 ) } onChange={ this.onChange } /> &nbsp;
						<span className="sub">{ __( 'How long to cache redirected 301 URLs (via "Expires" HTTP header)' ) }</span>
					</TableRow>

					<TableRow title={ __( 'REST API' ) } url={ this.supportLink( 'options', 'restapi' ) }>
						<Select items={ restApi() } name="rest_api" value={ parseInt( this.state.rest_api, 10 ) } onChange={ this.onChange } /> &nbsp;
						<span className="sub">{ __( "How Redirection uses the REST API - don't change unless necessary" ) }</span>
					</TableRow>
				</FormTable>

				<input className="button-primary" type="submit" name="update" value={ __( 'Update' ) } disabled={ saveStatus === STATUS_IN_PROGRESS } />
			</form>
		);
	}
}

function mapDispatchToProps( dispatch ) {
	return {
		onSaveSettings: settings => {
			dispatch( saveSettings( settings ) );
		},
	};
}

function mapStateToProps( state ) {
	const { groups, values, saveStatus, installed, postTypes, warning } = state.settings;

	return {
		groups,
		values,
		saveStatus,
		installed,
		postTypes,
		warning,
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps
)( OptionsForm );
