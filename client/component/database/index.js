/* global Redirectioni10n */
/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import { connect } from 'react-redux';
import { Line } from 'rc-progress';

/**
 * Internal dependencies
 */

import PreventLeaveWarning from 'component/prevent-leave';
import Error from 'component/error';
import Spinner from 'component/spinner';
import { STATUS_FAILED } from 'state/settings/type';
import { upgradeDatabase, finishUpgrade } from 'state/settings/action';
import './style.scss';

class Database extends React.Component {
	constructor( props ) {
		super( props );

		if ( props.needUpgrade || props.needInstall ) {
			props.onUpgrade();
		}
	}

	onRetry = ev => {
		ev.preventDefault();
		this.props.onUpgrade();
	}

	onSkip = ev => {
		ev.preventDefault();
		this.props.onUpgrade( 'skip' );
	}

	onStop = ev => {
		ev.preventDefault();
		this.props.onUpgrade( 'stop' );
	}

	onFinish = ev => {
		ev.preventDefault();
		this.props.onFinish();
	}

	componentDidUpdate( prevProps ) {
		if ( prevProps.time !== this.props.time && this.props.status === 'ok' ) {
			// Start next call, after a slight pause to allow the server a bit of breathing room
			setTimeout( () => {
				this.props.onUpgrade();
			}, 1000 );
		}
	}

	getErrorMessage() {
		const { debug, reason, current, next } = this.props;
		const message = [
			'Message: ' + reason,
			'Installed: ' + current,
			'Next: ' + next,
			'Debug:\n\n' + debug.join( '\n' ),
		];

		return message.join( '\n' );
	}

	renderError( error ) {
		const { current } = this.props;
		const recovery = this.getErrorMessage();

		return (
			<div className="redirection-database_error notice notice-error">
				<h2>{ __( 'Database problem' ) }</h2>
				<p>{ error }</p>
				<p>
					<button className="button button-primary" onClick={ this.onRetry }>{ __( 'Try again' ) }</button>&nbsp;
					{ current !== '-' && <button className="button button-secondary" onClick={ this.onSkip }>{ __( 'Skip this stage' ) }</button> }&nbsp;
					{ current !== '-' && <button className="button button-secondary" onClick={ this.onStop }>{ __( 'Stop upgrade' ) }</button> }
				</p>

				<p>
					{ __( 'If you want to {{support}}ask for support{{/support}} please include these details:', {
						components: {
							support: <a href={ 'mailto:john@redirection.me?subject=Redirection%20Database&body=' + encodeURIComponent( 'Redirection: ' + Redirectioni10n.versions ) } />,
						},
					} ) }
				</p>

				<textarea value={ recovery } rows="15" readOnly />
			</div>
		);
	}

	renderIntro() {
		const { needInstall, needUpgrade } = this.props;

		if ( ! needInstall && ! needUpgrade ) {
			return null;
		}

		return (
			<React.Fragment>
				{ needInstall && <p>{ __( 'Your database is being installed.' ) }</p> }
				{ needUpgrade && ! needInstall && <p>{ __( 'Your database is being upgraded.' ) }</p> }

				<p>{ __( 'Please remain on this page until complete.' ) }</p>
			</React.Fragment>
		);
	}

	render() {
		const { status, complete = 0, reason } = this.props;
		const noLoading = status === 'error' || status === STATUS_FAILED || ( status === 'ok' && complete === 100 );

		return (
			<div className="redirection-database">
				<PreventLeaveWarning message={ __( 'Leaving before the process has completed may cause problems.' ) } prevent={ status !== STATUS_FAILED && status !== 'error' } />

				<h1>{ __( 'Redirection Database' ) }</h1>

				{ status === STATUS_FAILED && <Error /> }
				{ status !== STATUS_FAILED && this.renderIntro() }

				<div className="redirection-database_progress">
					<h3>
						{ __( 'Progress: %(complete)d%%', {
							args: {
								complete,
							},
						} ) }
					</h3>

					<Line percent={ complete } strokeWidth="4" trailWidth="2" />

					{ reason && status !== 'error' && <p>{ reason }</p> }

					{ ! noLoading && <Spinner /> }
					{ status === 'error' && this.renderError( reason ) }
					{ status === 'ok' && complete === 100 && <button className="button button-primary" onClick={ this.onFinish }>{ __( 'Finished! 🎉' ) }</button> }
				</div>
			</div>
		);
	}
}

function mapDispatchToProps( dispatch ) {
	return {
		onUpgrade: ( arg ) => {
			dispatch( upgradeDatabase( arg ) );
		},
		onFinish: () => {
			dispatch( finishUpgrade() );
		},
	};
}

function mapStateToProps( state ) {
	const { settings } = state;
	const { status, complete, reason, debug, time, current, needUpgrade, needInstall, next } = settings.database;

	return {
		status,
		complete,
		reason,
		debug,
		time,
		current,
		needUpgrade,
		needInstall,
		next,
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps,
)( Database );
