/**
 * External dependencies
 */

import React from 'react';
import { __ } from '@wordpress/i18n';

const RAW_HIDE_LENGTH = 500;

class ApiResultRaw extends React.Component {
	constructor( props ) {
		super( props );

		const { request } = this.props.error;

		this.state = { hide: this.doesNeedHiding( request ) };
	}

	doesNeedHiding( request ) {
		return request && request.raw && request.raw.length > RAW_HIDE_LENGTH;
	}

	onShow = ev => {
		ev.preventDefault();
		this.setState( { hide: false } );
	}

	onHide = ev => {
		ev.preventDefault();
		this.setState( { hide: true } );
	}

	render() {
		const { request } = this.props.error;
		const { hide } = this.state;
		const needToHide = this.doesNeedHiding( request );

		if ( request && request.raw ) {
			return (
				<>
					{ needToHide && hide && <a className="api-result-hide" onClick={ this.onShow } href="#">{ __( 'Show Full', 'redirection' ) }</a> }
					{ needToHide && ! hide && <a className="api-result-hide" onClick={ this.onHide } href="#">{ __( 'Hide', 'redirection' ) }</a> }
					<pre>{ hide ? request.raw.substr( 0, RAW_HIDE_LENGTH ) + ' ...' : request.raw }</pre>
				</>
			);
		}

		return null;
	}
}

export default ApiResultRaw;
