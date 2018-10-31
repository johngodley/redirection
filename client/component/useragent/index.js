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
import { getAgent } from 'state/info/action';
import { STATUS_IN_PROGRESS, STATUS_FAILED, STATUS_COMPLETE } from 'state/settings/type';
import './style.scss';

class Useragent extends React.Component {
	constructor( props ) {
		super( props );

		this.props.onGet( props.agent );
	}

	renderError() {
		const { error } = this.props;

		return (
			<div className="modal-error">
				<h2>{ __( 'Useragent Error' ) }</h2>
				<p>{ __( 'Something went wrong obtaining this information' ) }</p>
				<p><code>{ error.message }</code></p>
			</div>
		);
	}

	renderUnknown() {
		const { agent } = this.props;

		return (
			<div className="agent-unknown">
				<h2>{ __( 'Unknown Useragent' ) }</h2>
				<br />
				<p>{ agent }</p>
			</div>
		);
	}

	getDetail( info ) {
		if ( info && info.name && info.version ) {
			return info.name + ' ' + info.version;
		}

		return false;
	}

	getDevice( device ) {
		const parts = [];

		if ( device.vendor ) {
			parts.push( device.vendor );
		}

		if ( device.name ) {
			parts.push( device.name );
		}

		return parts.join( ' ' );
	}

	getType( type, url ) {
		const name = type.slice( 0, 1 ).toUpperCase() + type.slice( 1 );

		if ( url ) {
			return <a href={ url } target="_blank">{ name }</a>;
		}

		return name;
	}

	renderDetails() {
		const { agents, agent } = this.props;
		const detail = agents[ agent ] ? agents[ agent ] : false;

		if ( ! detail ) {
			return this.renderUnknown();
		}

		const type = this.getType( detail.device.type, detail.url );
		const device = this.getDevice( detail.device );
		const os = this.getDetail( detail.os );
		const browser = this.getDetail( detail.browser );
		const engine = this.getDetail( detail.engine );
		const parts = [];

		if ( device ) {
			parts.push( [ __( 'Device' ), device ] );
		}

		if ( os ) {
			parts.push( [ __( 'Operating System' ), os ] );
		}

		if ( browser ) {
			parts.push( [ __( 'Browser' ), browser ] );
		}

		if ( engine ) {
			parts.push( [ __( 'Engine' ), engine ] );
		}

		return (
			<div>
				<h2>{ __( 'Useragent' ) }: { type }</h2>
				<table>
					<tbody>
						<tr>
							<th>{ __( 'Agent' ) }</th>
							<td className="useragent-agent">{ agent }</td>
						</tr>

						{ parts.map( ( item, key ) => {
							return (
								<tr key={ key }>
									<th>{ item[ 0 ] }</th>
									<td>{ item[ 1 ] }</td>
								</tr>
							);
						} ) }
					</tbody>
				</table>

				<div className="external">
					{ __( 'Powered by {{link}}redirect.li{{/link}}', {
						components: {
							link: <a href="https://redirect.li" target="_blank" rel="noopener noreferrer" />,
						},
					} ) }
				</div>
			</div>
		);
	}

	componentDidUpdate() {
		this.props.parent.resize();
	}

	render() {
		const { status } = this.props;
		const klass = classnames( {
			useragent: true,
			'modal-loading': status === STATUS_IN_PROGRESS,
		} );

		return (
			<div className={ klass }>
				{ status === STATUS_IN_PROGRESS && <Spinner /> }
				{ status === STATUS_FAILED && this.renderError() }
				{ status === STATUS_COMPLETE && this.renderDetails() }
			</div>
		);
	}
}

function mapDispatchToProps( dispatch ) {
	return {
		onGet: agent => {
			dispatch( getAgent( agent ) );
		},
	};
}

function mapStateToProps( state ) {
	const { status, error, agents } = state.info;

	return {
		status,
		error,
		agents,
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps
)( Useragent );
