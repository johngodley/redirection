/**
 * External dependencies
 */

import React from 'react';
import { connect } from 'react-redux';
import { translate as __ } from 'lib/locale';
import classnames from 'classnames';

/**
 * Internal dependencies
 */
import Spinner from 'component/spinner';
import { getHttp, clearHttp } from 'state/info/action';
import { getServerUrl } from 'lib/wordpress-url';
import { STATUS_IN_PROGRESS, STATUS_FAILED, STATUS_COMPLETE } from 'state/settings/type';
import './style.scss';

class HttpCheck extends React.Component {
	constructor( props ) {
		super( props );

		this.props.onGet( getServerUrl( this.getServer( props.item ), props.item.url ) );
	}

	getServer( item ) {
		if ( item.match_type === 'server' ) {
			return item.action_data.server;
		}

		return document.location.origin;
	}

	componentWillUnmount() {
		this.props.onClearHttp();
	}

	renderError() {
		const { error } = this.props;

		return (
			<div className="modal-error">
				<h2>{ __( 'Error' ) }</h2>
				<p>{ __( 'Something went wrong obtaining this information' ) }</p>
				<p><code>{ error.message }</code></p>
			</div>
		);
	}

	renderDetails() {
		const { action_code, action_data } = this.props.item;
		const { status, headers = [] } = this.props.http;
		const location = headers.find( item => item.name === 'location' );
		const redirection = headers.find( item => item.name === 'x-redirect-agent' );
		const matches = action_code === status && location && location.value === action_data.url && redirection;

		return (
			<div className="http-check-results">
				<div className="http-status">
					{ matches && <span className="dashicons dashicons-yes"></span> }
					{ ! matches && <span className="dashicons dashicons-no"></span> }
				</div>
				<div className="http-result">
					<p>
						<strong>{ __( 'Expected' ) }: </strong>

						<span>
							{ __( '{{code}}%(status)d{{/code}} to {{code}}%(url)s{{/code}}', {
								args: {
									status: action_code,
									url: action_data.url,
								},
								components: {
									code: <code />,
								},
							} ) }
						</span>
					</p>
					<p>
						<strong>{ __( 'Found' ) }: </strong>

						<span>
							{
								location
									? __( '{{code}}%(status)d{{/code}} to {{code}}%(url)s{{/code}}', {
										args: {
											status,
											url: location.value,
										},
										components: {
											code: <code />,
										},
									} )
									: status
							}
						</span>
					</p>
					<p>
						<strong>{ __( 'Agent' ) }: </strong>

						<span>{ redirection ? __( 'Using Redirection' ) : __( 'Not using Redirection' ) }</span>
					</p>
					{ location && ! redirection && <p><a href="https://redirection.me/support/problems/url-not-redirecting/" target="_blank" rel="noopener noreferrer">{ __( 'What does this mean?' ) }</a></p> }
				</div>
			</div>
		);
	}

	renderLink() {
		return (
			<div className="external">
				{ __( 'Powered by {{link}}redirect.li{{/link}}', {
					components: {
						link: <a href="https://redirect.li" target="_blank" rel="noopener noreferrer" />,
					},
				} ) }
			</div>
		);
	}

	componentDidUpdate() {
		this.props.parent.resize();
	}

	render() {
		const { status, http } = this.props;
		const klass = classnames( {
			'http-check': true,
			'modal-loading': status === STATUS_IN_PROGRESS,
			'http-check-small': status === STATUS_FAILED,
		} );

		return (
			<div className={ klass }>
				{ status === STATUS_IN_PROGRESS && <Spinner /> }

				{ status === STATUS_FAILED && this.renderError() }

				{ status === STATUS_COMPLETE && http &&
					<React.Fragment>
						<h2>
							{
								__( 'Check redirect for: {{code}}%s{{/code}}', {
									args: [ http.url ],
									components: {
										code: <code />,
									},
								} )
							}
						</h2>

						{ this.renderDetails() }
						{ this.renderLink() }
					</React.Fragment>
				}
			</div>
		);
	}
}

function mapDispatchToProps( dispatch ) {
	return {
		onGet: url => {
			dispatch( getHttp( url ) );
		},
		onClearHttp: () => {
			dispatch( clearHttp() );
		},
	};
}

function mapStateToProps( state ) {
	const { status, error, http } = state.info;

	return {
		status,
		error,
		http,
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps
)( HttpCheck );
