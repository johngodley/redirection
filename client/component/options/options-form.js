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

const notifySchedule = [
	{ value: 'never', text: __( 'Never' ) },
	{ value: 'daily', text: __( 'Each day' ) },
	{ value: 'weekly', text: __( 'Once a week' ) },
	{ value: 'monthly', text: __( 'Once a month' ) }
];

const dontMonitor = { value: 0, text: __( "Don't monitor" ) };

class OptionsForm extends React.Component {
	constructor( props ) {
		super( props );

		this.state = props.values;
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
		const { groups, saveStatus } = this.props;
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
							{ __( 'This will be used to auto-generate a URL if no URL is given. You can use the special tags {{code}}$dec${{/code}} or {{code}}$hex${{/code}} to have a unique ID inserted (either decimal or hex)', {
								components: {
									code: <code />
								}
							} ) }
						</span>
					</TableRow>
					<TableRow title={ __( 'Notify' ) + ':' }>
						<input className="regular-text" type="text" value={ this.state.notify_emails } name="notify_emails" onChange={ this.onChange } /><br />
						<span className="sub">{ __( 'Comma seperated list of email addresses to notify concerning 404s.' ) }</span>
					</TableRow>
					<TableRow title={ __( 'Notification Schedule' ) + ':' }>
						<Select items={ notifySchedule } name="notify_schedule" value={ this.state.notify_schedule } onChange={ this.onChange } /> { __( '(How often to send email notifications about new 404s.)' ) }
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
	const { groups, values, saveStatus } = state.settings;

	return {
		groups,
		values,
		saveStatus,
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps
)( OptionsForm );
