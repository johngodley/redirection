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
import { FormTable, TableRow } from 'component/wordpress/form-table';
import Select from 'component/wordpress/select';

const timeToKeep = [
	{ value: -1, text: __( 'No logs' ) },
	{ value: 1, text: __( 'A day' ) },
	{ value: 7, text: __( 'A week' ) },
	{ value: 30, text: __( 'A month' ) },
	{ value: 60, text: __( 'Two months' ) },
	{ value: 0, text: __( 'Forever' ) },
];

const dontMonitor = { value: 0, text: __( "Don't monitor" ) };

class OptionsForm extends React.Component {
	constructor( props ) {
		super( props );

		const modules = props.values.modules;

		this.state = props.values;
		this.state.location = modules[ 2 ] ? modules[ 2 ].location : '',
		this.state.canonical = modules[ 2 ] ? modules[ 2 ].canonical : '',

		this.onChange = this.handleInput.bind( this );
		this.onSubmit = this.handleSubmit.bind( this );
	}

	handleInput( event ) {
		const { target } = event;
		const value = target.type === 'checkbox' ? target.checked : target.value;

		this.setState( { [ target.name ]: value } );
	}

	handleSubmit( event ) {
		event.preventDefault();
		this.props.onSaveSettings( this.state );
	}

	componentWillUpdate( nextProps ) {
		if ( nextProps.values.token !== this.props.values.token ) {
			this.setState( { token: nextProps.values.token } );
		}

		if ( nextProps.values.auto_target !== this.props.values.auto_target ) {
			this.setState( { auto_target: nextProps.values.auto_target } );
		}
	}

	render() {
		const { groups, saveStatus, installed } = this.props;
		const monitor = [ dontMonitor ].concat( groups );

		return (
			<form onSubmit={ this.onSubmit }>
				<FormTable>
					<TableRow title={ __( 'Plugin support' ) + ':' }>
						<label>
							<input type="checkbox" checked={ this.state.support } name="support" onChange={ this.onChange } />
							<span className="sub">{ __( "I'm a nice person and I have helped support the author of this plugin" ) }</span>
						</label>
					</TableRow>

					<TableRow title={ __( 'Redirect Logs' ) + ':' }>
						<Select items={ timeToKeep } name="expire_redirect" value={ parseInt( this.state.expire_redirect, 10 ) } onChange={ this.onChange } /> { __( '(time to keep logs for)' ) }
					</TableRow>

					<TableRow title={ __( '404 Logs' ) + ':' }>
						<Select items={ timeToKeep } name="expire_404" value={ parseInt( this.state.expire_404, 10 ) } onChange={ this.onChange } /> { __( '(time to keep logs for)' ) }
					</TableRow>

					<TableRow title={ __( 'Monitor changes to posts' ) + ':' }>
						<Select items={ monitor } name="monitor_post" value={ parseInt( this.state.monitor_post, 10 ) } onChange={ this.onChange } />
					</TableRow>

					<TableRow title={ __( 'RSS Token' ) + ':' }>
						<input className="regular-text" type="text" value={ this.state.token } name="token" onChange={ this.onChange } /><br />
						<span className="sub">{ __( 'A unique token allowing feed readers access to Redirection log RSS (leave blank to auto-generate)' ) }</span>
					</TableRow>

					<TableRow title={ __( 'Auto-generate URL' ) + ':' }>
						<input className="regular-text" type="text" value={ this.state.auto_target } name="auto_target" onChange={ this.onChange } /><br />
						<span className="sub">
							{ __( 'Used to auto-generate a URL if no URL is given. Use the special tags {{code}}$dec${{/code}} or {{code}}$hex${{/code}} to insert a unique ID inserted', {
								components: {
									code: <code />
								}
							} ) }
						</span>
					</TableRow>

					<TableRow title={ __( 'Apache Module' ) }>
						<label>
							<p><input type="text" className="regular-text" name="location" value={ this.state.location } onChange={ this.onChange } placeholder={ installed } /></p>

							<p className="sub">
								{ __( 'Enter the full path and filename if you want Redirection to automatically update your {{code}}.htaccess{{/code}}.', {
									components: {
										code: <code />,
									}
								} ) }
							</p>

							<p>
								<label>
									<select name="canonical" value={ this.state.canonical } onChange={ this.onChange }>
										<option value="">{ __( 'Default server' ) }</option>
										<option value="nowww">{ __( 'Remove WWW' ) }</option>
										<option value="www">{ __( 'Add WWW' ) }</option>
									</select>
									&nbsp;
									{ __( 'Automatically remove or add www to your site.' ) }
								</label>
							</p>
						</label>
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
		}
	};
}

function mapStateToProps( state ) {
	const { groups, values, saveStatus, installed } = state.settings;

	return {
		groups,
		values,
		saveStatus,
		installed,
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps
)( OptionsForm );
