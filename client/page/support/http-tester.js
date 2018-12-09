/**
 * External dependencies
 *
 * @format
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import { connect } from 'react-redux';

/**
 * Internal dependencies
 */

import { getHttp } from 'state/info/action';
import { STATUS_IN_PROGRESS, STATUS_FAILED } from 'state/settings/type';
import Spinner from 'component/spinner';
import './style.scss';

class HttpTester extends React.Component {
	constructor( props ) {
		super( props );

		this.state = { url: '' };
	}

	onChange = ev => {
		this.setState( { url: ev.target.value } );
	}

	onSubmit = () => {
		this.props.onRequest( this.state.url );
	}

	renderResults( http ) {
		const { status, statusMessage, statusDescription, headers } = http;

		if ( status === 500 || ! statusMessage ) {
			return (
				<div className="inline-notice">
					<p>{ __( 'Unable to load details' ) }</p>
				</div>
			);
		}

		const location = headers.find( item => item.name === 'location' );
		const xredirection = headers.find( item => item.name === 'x-redirect-agent' );
		return (
			<div className="inline-notice">
				<p><strong>HTTP { status + ' ' + statusMessage }</strong> - { statusDescription }</p>
				{ xredirection && <p>{ __( 'URL is being redirected with Redirection' ) }</p> }
				{ location && ! xredirection && <p>{ __( 'URL is not being redirected with Redirection' ) }</p> }
				{ location && <p>{ __( 'Target' ) }: <code>{ location.value }</code></p> }
			</div>
		);
	}

	render() {
		const { url } = this.state;
		const { http, status } = this.props;

		return (
			<div className="http-tester">
				<h3>{ __( 'Redirect Tester' ) }</h3>

				<p>
					{ __( "Sometimes your browser can cache a URL, making it hard to know if it's working as expected. Use this to check a URL to see how it is really redirecting." ) }
				</p>
				<p>
					{ __( 'URL' ) }: <input type="text" value={ url } onChange={ this.onChange } disabled={ status === STATUS_IN_PROGRESS } placeholder={ __( 'Enter full URL, including http:// or https://' ) } />
					<input type="submit" className="button-secondary" onClick={ this.onSubmit } disabled={ status === STATUS_IN_PROGRESS } value={ __( 'Check' ) } />
				</p>

				{ status === STATUS_IN_PROGRESS && <Spinner /> }
				{ status === STATUS_FAILED && <div className="inline-notice"><p>{ __( 'Unable to load details' ) }</p></div> }

				{ http && this.renderResults( http ) }
			</div>
		);
	}
}

function mapDispatchToProps( dispatch ) {
	return {
		onRequest: url => {
			dispatch( getHttp( url ) );
		},
	};
}

function mapStateToProps( state ) {
	const { http, status } = state.info;

	return {
		http,
		status,
	};
}

export default connect( mapStateToProps, mapDispatchToProps )( HttpTester );
