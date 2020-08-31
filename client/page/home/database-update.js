/* global Redirectioni10n */
/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'i18n-calypso';
import TextareaAutosize from 'react-textarea-autosize';
import { connect } from 'react-redux';

/**
 * Internal dependencies
 */
import { getExportUrl } from 'state/io/selector';
import Database from 'component/database';
import { ExternalLink, Error } from 'wp-plugin-components';
import { STATUS_FAILED } from 'state/settings/type';
import { fixStatus } from 'state/settings/action';
import { getErrorLinks, getErrorDetails } from 'lib/error-links';
import DebugReport from 'page/home/debug';

function getUpgradeNotice() {
	const { current, next } = Redirectioni10n.database;

	if ( current === next ) {
		return <p>{ __( 'A database upgrade is in progress. Please continue to finish.' ) }</p>;
	}

	return (
		<>
			<p>{ __( 'Redirection stores data in your database and sometimes this needs upgrading. Your database is at version {{strong}}%(current)s{{/strong}} and the latest is {{strong}}%(latest)s{{/strong}}.', {
				args: {
					current: Redirectioni10n.database.current,
					latest: Redirectioni10n.database.next,
				},
				components: {
					strong: <strong />,
				},
			} ) }
			</p>
		</>
	);
}

class NeedUpdate extends React.Component {
	constructor( props ) {
		super( props );

		this.state = { showManual: false };
	}

	onToggle = ev => {
		ev.preventDefault();
		this.setState( { showManual: ! this.state.showManual } );
	}

	onComplete = () => {
		this.props.onComplete( Redirectioni10n.database.next );
	}

	renderManual() {
		return (
			<>
				<p>{ __( 'If your site needs special database permissions, or you would rather do it yourself, you can manually run the following SQL.' ) } { __( 'Click "Complete Upgrade" when finished.' ) }</p>
				<p><TextareaAutosize readOnly={ true } cols="120" value={ Redirectioni10n.database.manual.join( ';\n' ) + ';' } spellCheck={ false } /></p>
				<p><button className="button-primary" onClick={ this.onComplete }>{ __( 'Complete Upgrade' ) }</button></p>
			</>
		);
	}

	renderStandard() {
		const { onShowUpgrade } = this.props;

		return (
			<>
				<p>{ __( 'Click the "Upgrade Database" button to automatically upgrade the database.' ) }</p>
				<p>
					<input className="button-primary" type="submit" value={ __( 'Upgrade Database' ) } onClick={ onShowUpgrade } />
				</p>
			</>
		);
	}

	render() {
		const { showDatabase, result, reason } = this.props;
		const { showManual } = this.state;

		if ( showDatabase ) {
			return (
				<>
					{ result === STATUS_FAILED && (
						<Error
							details={ getErrorDetails() }
							errors={ reason }
							renderDebug={ DebugReport }
							links={ getErrorLinks() }
						>
							{ __( 'Something went wrong when upgrading Redirection.' ) }
						</Error>
					) }

					<div className="wizard-wrapper">
						<div className="wizard">
							<Database />
						</div>
					</div>
				</>
			);
		}

		return (
			<div className="wrap redirection">
				<h1 className="wp-heading-inline">{ __( 'Upgrade Required' ) }</h1>

				<div className="wpl-error">
					<h3>{ __( 'Redirection database needs upgrading' ) }</h3>
					{ getUpgradeNotice() }

					<p>{ __( 'Please make a backup of your Redirection data: {{download}}downloading a backup{{/download}}. If you experience any issues you can import this back into Redirection.', {
						components: {
							download: <ExternalLink url={ getExportUrl( 'all', 'json' ) } />,
							import: <ExternalLink url="https://redirection.me/support/import-export-redirects/" />,
						},
					} ) }</p>

					{ showManual && this.renderManual() }
					{ ! showManual && this.renderStandard() }
				</div>

				<div className="database-switch">
					{ ! showManual && <a href="#" onClick={ this.onToggle }>{ __( 'Manual Upgrade' ) }</a> }
					{ showManual && <a href="#" onClick={ this.onToggle }>{ __( 'Automatic Upgrade' ) }</a> }
				</div>
			</div>
		);
	}
}

function mapDispatchToProps( dispatch ) {
	return {
		onComplete: ( version ) => {
			dispatch( fixStatus( 'database', version ) );
		},
	};
}

function mapStateToProps( state ) {
	const { database } = state.settings;

	return {
		reason: database.reason,
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps
)( NeedUpdate );
