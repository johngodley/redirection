/**
 * External dependencies
 */

import React from 'react';
import { connect } from 'react-redux';
import { translate as __ } from 'lib/locale';

/**
 * Internal dependencies
 */

import RestApiStatus from 'component/rest-api-status';
import PluginStatus from './plugin-status';
import Debug from './debug';
import { loadStatus } from 'state/settings/action';
import './status.scss';

class Status extends React.Component {
	constructor( props ) {
		super( props );

		this.props.onLoadStatus();
	}

	render() {
		const { status = [], debug = false } = this.props;

		return (
			<React.Fragment>
				<h2>{ __( 'WordPress REST API' ) }</h2>
				<p>{ __( 'Redirection communicates with WordPress through the WordPress REST API. This is a standard part of WordPress, and you will experience problems if you cannot use it.' ) }</p>
				<RestApiStatus />

				<h2>{ __( 'Plugin Status' ) }</h2>

				{ status.length > 0 && <PluginStatus status={ status } /> }
				{ status.length === 0 && <div className="placeholder-inline"><div className="placeholder-loading"></div></div> }

				<h2>{ __( 'Plugin Debug' ) }</h2>
				<p>{ __( 'This information is provided for debugging purposes. Be careful making any changes.' ) }</p>

				{ debug && <Debug debug={ debug } /> }
				{ ! debug === 0 && <div className="placeholder-inline"><div className="placeholder-loading"></div></div> }
			</React.Fragment>
		);
	}
}

function mapDispatchToProps( dispatch ) {
	return {
		onLoadStatus: () => {
			dispatch( loadStatus() );
		},
	};
}

function mapStateToProps( state ) {
	const { debug, status } = state.settings.pluginStatus;

	return {
		debug,
		status,
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps
)( Status );
