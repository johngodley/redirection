/* global Redirectioni10n */
/**
 * External dependencies
 */

import React from 'react';
import { connect } from 'react-redux';
import { translate as __ } from 'lib/locale';

/**
 * Internal dependencies
 */

import { getApiNonce } from 'lib/api';
import { loadStatus, fixStatus } from 'state/settings/action';
import './status.scss';

const Fixit = () => {
	return (
		<div>
			<form action={ Redirectioni10n.pluginRoot + '&sub=support' } method="POST">
				<input type="hidden" name="_wpnonce" value={ getApiNonce() } />
				<input type="hidden" name="action" value="fixit" />

				<p>{ __( "If the magic button doesn't work then you should read the error and see if you can fix it manually, otherwise follow the 'Need help' section below." ) }</p>
				<p><input type="submit" className="button-primary" value={ __( '⚡️ Magic fix ⚡️' ) } /></p>
			</form>
		</div>
	);
};

const PluginStatusItem = ( props ) => {
	const { item } = props;

	return (
		<tr>
			<th>{ item.name }</th>
			<td><span className={ 'plugin-status-' + item.status }>{ item.status === 'good' ? __( 'Good' ) : __( 'Problem' ) }</span> { item.message }</td>
		</tr>
	);
};

const PluginStatus = ( props ) => {
	const { status } = props;
	const hasProblem = status.filter( item => item.status !== 'good' );

	return (
		<div>
			<table className="plugin-status">
				<tbody>
					{ status.map( ( item, pos ) => <PluginStatusItem item={ item } key={ pos } /> ) }
				</tbody>
			</table>

			{ hasProblem.length > 0 && <Fixit /> }
		</div>
	);
};

class Status extends React.Component {
	constructor( props ) {
		super( props );

		this.props.onLoadStatus();
	}

	render() {
		const { pluginStatus } = this.props;

		return (
			<div>
				<h2>{ __( 'Plugin Status' ) }</h2>

				{ pluginStatus.length > 0 && <PluginStatus status={ pluginStatus } /> }
				{ pluginStatus.length === 0 && <div className="placeholder-inline"><div className="placeholder-loading"></div></div> }
			</div>
		);
	}
}

function mapDispatchToProps( dispatch ) {
	return {
		onLoadStatus: () => {
			dispatch( loadStatus() );
		},
		onFix: () => {
			dispatch( fixStatus() );
		},
	};
}

function mapStateToProps( state ) {
	const { pluginStatus } = state.settings;

	return {
		pluginStatus,
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps
)( Status );
