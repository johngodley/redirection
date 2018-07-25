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
import Spinner from 'component/wordpress/spinner';
import { getMap } from 'state/info/action';
import { STATUS_IN_PROGRESS, STATUS_FAILED, STATUS_COMPLETE } from 'state/settings/type';

class GeoMap extends React.Component {
	constructor( props ) {
		super( props );

		this.props.onGet( props.ip );
	}

	renderError() {
		const { error } = this.props;

		return (
			<div className="modal-error">
				<h2>{ __( 'Geo IP Error' ) }</h2>
				<p>{ __( 'Something went wrong obtaining this information' ) }</p>
				<p><code>{ error.message }</code></p>
			</div>
		);
	}

	showPrivate( details ) {
		const { ip, ipType } = details;

		return (
			<div className="geo-simple">
				<h2>{ __( 'Geo IP' ) }: { ip } - IPv{ ipType }</h2>

				<p>
					{ __( 'This is an IP from a private network. This means it is located inside a home or business network and no more information can be displayed.' ) }
				</p>
			</div>
		);
	}

	showUnknown( details ) {
		const { ip, ipType } = details;

		return (
			<div className="geo-simple">
				<h2>{ __( 'Geo IP' ) }: { ip } - IPv{ ipType }</h2>

				<p>
					{ __( 'No details are known for this address.' ) }
				</p>
			</div>
		);
	}

	showMap( details ) {
		const { countryName, regionName, city, postCode, timeZone, accuracyRadius, latitude, longitude, ip, ipType } = details;
		const map = 'https://www.google.com/maps/embed/v1/place?key=AIzaSyDPHZn9iAyI6l-2Qv5-1IPXsLUENVtQc3A&q=' + encodeURIComponent( latitude + ',' + longitude );
		const area = [ regionName, countryName, postCode ].filter( item => item );

		return (
			<div className="geo-full">
				<table>
					<tbody>
						<tr>
							<th colSpan="2">
								<h2>{ __( 'Geo IP' ) }: <a href={ 'https://redirect.li/map/?ip=' + encodeURIComponent( ip ) } target="_blank" rel="noopener noreferrer">{ ip }</a> - IPv{ ipType }
								</h2>
							</th>
						</tr>
						<tr>
							<th>{ __( 'City' ) }</th>
							<td>{ city }</td>
						</tr>
						<tr>
							<th>{ __( 'Area' ) }</th>
							<td>{ area.join( ', ' ) }</td>
						</tr>
						<tr>
							<th>{ __( 'Timezone' ) }</th>
							<td>{ timeZone }</td>
						</tr>
						<tr>
							<th>{ __( 'Geo Location' ) }</th>
							<td>{ latitude + ',' + longitude + ' (~' + accuracyRadius + 'm)' }</td>
						</tr>
					</tbody>
				</table>

				<iframe frameBorder="0" src={ map } allowFullScreen></iframe>
			</div>
		);
	}

	renderDetails() {
		const { maps, ip } = this.props;
		const detail = maps[ ip ] ? maps[ ip ] : false;

		if ( detail ) {
			const { code } = detail;

			if ( code === 'private' ) {
				return this.showPrivate( detail );
			}

			if ( code === 'geoip' ) {
				return this.showMap( detail );
			}

			return this.showUnknown( detail );
		}

		return null;
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
		const { status } = this.props;
		const isPrivate = ( status === STATUS_COMPLETE && this.props.maps[ this.props.ip ] && this.props.maps[ this.props.ip ].code !== 'geoip' );
		const klass = classnames( {
			'geo-map': true,
			'modal-loading': status === STATUS_IN_PROGRESS,
			'geo-map-small': status === STATUS_FAILED || isPrivate,
		} );

		return (
			<div className={ klass }>
				{ status === STATUS_IN_PROGRESS && <Spinner /> }
				{ status === STATUS_FAILED && this.renderError() }
				{ status === STATUS_COMPLETE && this.renderDetails() }
				{ status === STATUS_COMPLETE && this.renderLink() }
			</div>
		);
	}
}

function mapDispatchToProps( dispatch ) {
	return {
		onGet: ip => {
			dispatch( getMap( ip ) );
		},
	};
}

function mapStateToProps( state ) {
	const { status, error, maps } = state.info;

	return {
		status,
		error,
		maps,
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps
)( GeoMap );
