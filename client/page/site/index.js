/**
 * External dependencies
 */

import React from 'react';
import { connect } from 'react-redux';
import { translate as __ } from 'lib/locale';

/**
 * Internal dependencies
 */
import { loadSettings, saveSettings } from 'state/settings/action';
import { STATUS_IN_PROGRESS } from 'state/settings/type';
import Placeholder from 'component/placeholder';

class Site extends React.Component {
	constructor( props ) {
		super( props );

		props.onLoadSettings();

		this.state = {
			https: false,
		};
	}

	onChange = ev => {
		this.setState( { https: ev.target.checked } );
	}

	onSubmit = ev => {
		ev.preventDefault();
		this.props.onSaveSettings( this.state );
	}

	render() {
		const { loadStatus, values, saveStatus } = this.props;

		if ( loadStatus === STATUS_IN_PROGRESS || ! values ) {
			return <Placeholder />;
		}

		return (
			<form onSubmit={ this.onSubmit }>
				<h3>{ __( 'Site Settings' ) }</h3>
				<p><label><input type="checkbox" name="https" onChange={ this.onChange } checked={ this.state.https } /> { __( 'Force a redirect from HTTP to HTTPS' ) }</label></p>

				<div className="inline-notice inline-warning">
					<p>{ __( '{{strong}}Warning{{/strong}}: ensure your HTTPS is working otherwise you can break your site.', {
						components: {
							strong: <strong />,
						},
					} ) } { __( 'Also ensure that you update your site URL settings.' ) }</p>
					<p>{ __( 'If your site stops working you will need to do an emergency revert. This will disable all the HTTPS option and all redirects, allowing you to login and make changes.' ) }</p>
				</div>

				<input className="button-primary" type="submit" name="update" value={ __( 'Update' ) } disabled={ saveStatus === STATUS_IN_PROGRESS } />
			</form>
		);
	}
}

function mapDispatchToProps( dispatch ) {
	return {
		onLoadSettings: () => {
			dispatch( loadSettings() );
		},
		onSaveSettings: settings => {
			dispatch( saveSettings( settings ) );
		},
	};
}

function mapStateToProps( state ) {
	const { loadStatus, saveStatus, values } = state.settings;

	return {
		loadStatus,
		saveStatus,
		values,
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps
)( Site );
