/* global Redirectioni10n */
/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import { connect } from 'react-redux';
import { Line } from 'rc-progress';
import PropTypes from 'prop-types';
import TextareaAutosize from 'react-textarea-autosize';

/**
 * Internal dependencies
 */

import PreventLeaveWarning from 'component/prevent-leave';
import Spinner from 'component/spinner';
import { upgradeDatabase, finishUpgrade, fixStatus } from 'state/settings/action';
import { STATUS_FAILED } from 'state/settings/type';
import './style.scss';

class Database extends React.Component {
	static propTypes = {
		onFinished: PropTypes.func,
		manual: PropTypes.bool,
	};

	static defaultProps = {
		manual: false,
	}

	constructor( props ) {
		super( props );

		if ( this.hasWork( props ) && ! props.manual ) {
			props.onUpgrade();
		}

		this.state = { looped: false };
	}

	hasWork( props ) {
		const { status, result } = props;

		if ( result === 'error' ) {
			return false;
		}

		return status === 'need-install' || status === 'need-update';
	}

	hasFinished( status ) {
		return status === 'finish-install' || status === 'finish-update';
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

		if ( this.props.onFinished ) {
			this.props.onFinished();
		}

		this.props.onFinish();
	}

	componentDidUpdate( prevProps ) {
		if ( prevProps.time !== this.props.time && this.hasWork( this.props ) ) {
			if ( prevProps.complete === this.props.complete && this.props.status !== 'error' ) {
				this.setState( { looped: true } );
			} else {
				// Start next call, after a slight pause to allow the server a bit of breathing room
				setTimeout( () => {
					this.props.onUpgrade();
				}, 1000 );
			}
		}
	}

	getErrorMessage() {
		const { debug = [], reason, current, next } = this.props;
		const message = [
			reason ? 'Message: ' + reason : null,
			'Installed: ' + current,
			'Next: ' + next,
			debug.length > 0 ? 'Debug: ' + debug.join( '\n' ) : null,
		];

		return message.filter( item => item ).join( '\n' );
	}

	renderLoopError() {
		return this.renderError(
			__( 'A loop was detected and the upgrade has been stopped. This usually indicates {{support}}your site is cached{{/support}} and database changes are not being saved.', {
				components: {
					support: <a href="https://redirection.me/support/problems/data-is-not-saved/" />,
				},
			} ), false );
	}

	renderError( error ) {
		const { current } = this.props;
		const recovery = this.getErrorMessage();

		return (
			<div className="redirection-database_error red-error">
				<h3>{ __( 'Database problem' ) }</h3>
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

				<TextareaAutosize readOnly value={ recovery } rows="15" />
			</div>
		);
	}

	renderIntro() {
		if ( ! this.hasWork( this.props ) ) {
			return null;
		}

		return (
			<p>{ __( 'Please remain on this page until complete.' ) }</p>
		);
	}

	getTitle() {
		const { status } = this.props;

		if ( status === 'need-update' ) {
			return __( 'Upgrading Redirection' );
		}

		return __( 'Setting up Redirection' );
	}

	onComplete = () => {
		this.props.onComplete( Redirectioni10n.database.next );
	}

	render() {
		const { status, complete = 0, reason, result, manual } = this.props;
		const { looped } = this.state;
		const showLoading = result === 'ok' && ! this.hasFinished( status ) && ! looped;

		if ( manual ) {
			return (
				<div className="redirection-database">
					<h1>{ __( 'Manual Install' ) }</h1>

					<p>{ __( 'If your site needs special database permissions, or you would rather do it yourself, you can manually run the following SQL.' ) } { __( 'Click "Finished! 🎉" when finished.' ) }</p>
					<p><TextareaAutosize readOnly cols="120" value={ Redirectioni10n.database.manual.join( ';\n\n' ) + ';' } spellCheck={ false } /></p>
					<button className="button button-primary" onClick={ this.onComplete }>{ __( 'Finished! 🎉' ) }</button>
					<p>{ __( 'If you do not complete the manual install you will be returned here.' ) }</p>
				</div>
			);
		}

		return (
			<div className="redirection-database">
				<PreventLeaveWarning message={ __( 'Leaving before the process has completed may cause problems.' ) } prevent={ result !== 'error' && result !== STATUS_FAILED } />

				<h1>{ this.getTitle() }</h1>

				{ result !== 'error' && this.renderIntro() }

				<div className="redirection-database_progress">
					<h3>
						{ __( 'Progress: %(complete)d%%', {
							args: {
								complete,
							},
						} ) }
					</h3>

					<Line percent={ complete } strokeWidth="4" trailWidth="4" strokeLinecap="square" />

					{ reason && result === 'ok' && <p>{ reason }</p> }

					{ showLoading && <div className="redirection-database_spinner"><Spinner /></div> }
					{ result === 'error' && this.renderError( reason ) }
					{ looped && this.renderLoopError() }
					{ this.hasFinished( status ) && <button className="button button-primary" onClick={ this.onFinish }>{ __( 'Finished! 🎉' ) }</button> }
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
		onComplete: ( version ) => {
			dispatch( fixStatus( 'database', version ) );
		},
	};
}

function mapStateToProps( state ) {
	const { settings } = state;
	const { status, complete, result, reason, debug, time, current, next } = settings.database;

	return {
		status,
		result,
		complete,
		reason,
		debug,
		time,
		current,
		next,
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps,
)( Database );
