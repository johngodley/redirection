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
import PoweredBy from 'component/powered-by';
import HttpDetails from './details';
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
			<div className="redirection-modal_error">
				<h2>{ __( 'Error' ) }</h2>
				<p>{ __( 'Something went wrong obtaining this information' ) }</p>
				<p><code>{ error.message }</code></p>
			</div>
		);
	}

	componentDidUpdate() {
		this.props.parent.resize();
	}

	render() {
		const { status, http, item } = this.props;
		const klass = classnames( {
			'redirection-httpcheck': true,
			'redirection-modal_loading': status === STATUS_IN_PROGRESS,
			'redirection-httpcheck_small': status === STATUS_FAILED,
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

						<HttpDetails http={ http } item={ item } />
						<PoweredBy />
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
