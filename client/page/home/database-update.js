/* global Redirectioni10n */
/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import TextareaAutosize from 'react-textarea-autosize';
import { connect } from 'react-redux';

/**
 * Internal dependencies
 */
import { getExportUrl } from 'state/io/selector';
import Database from 'component/database';
import ExternalLink from 'component/external-link';
import Error from 'component/error';
import { STATUS_FAILED } from 'state/settings/type';
import { fixStatus } from 'state/settings/action';

function getUpgradeNotice() {
	const { current, next } = Redirectioni10n.database;

	if ( current === next ) {
		return <p>{ __( 'A database upgrade is in progress. Please continue to finish.' ) }</p>;
	}

	return (
		<React.Fragment>
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
		</React.Fragment>
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
			<React.Fragment>
				<p>{ __( 'If your site needs special database permissions, or you would rather do it yourself, you can manually run the following SQL.' ) } { __( 'Click "Complete Upgrade" when finished.' ) }</p>
				<p><TextareaAutosize readOnly={ true } cols="120" value={ Redirectioni10n.database.manual.join( ';\n' ) + ';' } spellCheck={ false } /></p>
				<p><button className="button-primary" onClick={ this.onComplete }>{ __( 'Complete Upgrade' ) }</button></p>
			</React.Fragment>
		);
	}

	renderStandard() {
		const { onShowUpgrade } = this.props;

		return (
			<React.Fragment>
				<p>{ __( 'Click the "Upgrade Database" button to automatically upgrade the database.' ) }</p>
				<p>
					<input className="button-primary" type="submit" value={ __( 'Upgrade Database' ) } onClick={ onShowUpgrade } />
				</p>
			</React.Fragment>
		);
	}

	render() {
		const { showDatabase, result } = this.props;
		const { showManual } = this.state;

		if ( showDatabase ) {
			return (
				<React.Fragment>
					{ result === STATUS_FAILED && <Error /> }

					<div className="wizard-wrapper">
						<div className="wizard">
							<Database />
						</div>
					</div>
				</React.Fragment>
			);
		}

		return (
			<div className="wrap redirection">
				<h1 className="wp-heading-inline">{ __( 'Upgrade Required' ) }</h1>

				<div className="red-error">
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
export default connect(
	null,
	mapDispatchToProps,
)( NeedUpdate );
