/**
 * External dependencies
 */

import React from 'react';
import { connect } from 'react-redux';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */

import RestApiStatus from 'component/rest-api-status';
import PluginStatus from './plugin-status';
import Debug from './debug';
import { loadStatus } from 'state/settings/action';
import './status.scss';

class Status extends React.Component {
	componentDidMount() {
		this.props.onLoadStatus();
	}

	render() {
		const { status = [], debug = false } = this.props;

		return (
			<>
				<h2>{ __( 'WordPress REST API', 'redirection' ) }</h2>
				<p>{ __( 'Redirection communicates with WordPress through the WordPress REST API. This is a standard part of WordPress, and you will experience problems if you cannot use it.', 'redirection' ) }</p>
				<RestApiStatus />

				<h2>{ __( 'Plugin Status', 'redirection' ) }</h2>

				{ status.length > 0 && <PluginStatus status={ status } /> }
				{ status.length === 0 && <div className="placeholder-inline"><div className="wpl-placeholder__loading"></div></div> }

				<h2>{ __( 'Plugin Debug', 'redirection' ) }</h2>
				<p>{ __( 'This information is provided for debugging purposes. Be careful making any changes.', 'redirection' ) }</p>

				{ debug && <Debug debug={ debug } /> }
				{ ! debug === 0 && <div className="placeholder-inline"><div className="wpl-placeholder__loading"></div></div> }
			</>
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
