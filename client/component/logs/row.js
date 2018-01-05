/**
 * External dependencies
 */

import React from 'react';
import * as parseUrl from 'url';
import { connect } from 'react-redux';
import { translate as __ } from 'lib/locale';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */

import RowActions from 'component/table/row-action';
import { setFilter, setSelected, performTableAction } from 'state/log/action';
import Spinner from 'component/wordpress/spinner';
import { STATUS_IN_PROGRESS, STATUS_SAVING } from 'state/settings/type';
import Modal from 'component/modal';
import GeoMap from 'component/geo-map';

const Referrer = props => {
	const { url } = props;

	if ( url ) {
		const domain = parseUrl.parse( url ).hostname;

		return (
			<a href={ url } rel="noreferrer noopener" target="_blank">{ domain }</a>
		);
	}

	return null;
};

class LogRow extends React.Component {
	static propTypes = {
		item: PropTypes.object.isRequired,
		selected: PropTypes.bool.isRequired,
		status: PropTypes.string.isRequired,
	};

	constructor( props ) {
		super( props );

		this.state = {
			showMap: false,
		};
	}

	handleShow = ev => {
		ev.preventDefault();
		props.onShowIP( ip );
	}

	handleSelected = () => {
		props.onSetSelected( [ id ] );
	}

	handleDelete = ev => {
		ev.preventDefault();
		props.onDelete( id );
	}

	renderIp = ipStr => {
		if ( ipStr ) {
			return (
				<a href={ 'https://redirect.li/map/?ip=' + encodeURIComponent( ipStr ) } onClick={ this.showMap }>{ ipStr }</a>
			);
		}

		return '-';
	}

	renderMap() {
		return (
			<Modal show={ this.state.showMap } onClose={ this.closeMap } width="800" padding={ false }>
				<GeoMap ip={ this.props.item.ip } />
			</Modal>
		);
	}

	showMap = ev => {
		ev.preventDefault();
		this.setState( { showMap: true } );
	}

	closeMap = () => {
		this.setState( { showMap: false } );
	}

	render() {
		const { created, ip, referrer, url, agent, sent_to, id } = this.props.item;
		const { selected, status } = this.props;
		const isLoading = status === STATUS_IN_PROGRESS;
		const isSaving = status === STATUS_SAVING;
		const hideRow = isLoading || isSaving;

		return (
			<tr className={ hideRow ? 'disabled' : '' }>
				<th scope="row" className="check-column">
					{ ! isSaving && <input type="checkbox" name="item[]" value={ id } disabled={ isLoading } checked={ selected } onClick={ this.handleSelected } /> }
					{ isSaving && <Spinner size="small" /> }
				</th>
				<td className="column-date">
					{ created }
				</td>
				<td className="column-primary column-url">
					<a href={ url } rel="noreferrer noopener" target="_blank">{ url.substring( 0, 100 ) }</a><br />
					{ sent_to ? sent_to.substring( 0, 100 ) : '' }

					<RowActions disabled={ isSaving }>
						{ ip && <a href={ 'https://redirect.li/map/?ip=' + encodeURIComponent( ip ) } onClick={ this.showMap }>{ __( 'Show Geo IP' ) }</a> }
						{ ip && <span> | </span> }
						<a href="#" onClick={ this.handleDelete }>{ __( 'Delete' ) }</a>
					</RowActions>

					{ this.state.showMap && this.renderMap() }
				</td>
				<td className="column-referrer">
					<Referrer url={ referrer } /><br />
					{ agent }
				</td>
				<td className="column-ip">
					{ this.renderIp( ip ) }

					<RowActions>
						{ ip && <a href="#" onClick={ this.handleShow }>{ __( 'Filter by IP' ) }</a> }
					</RowActions>
				</td>
			</tr>
		);
	}
};

function mapDispatchToProps( dispatch ) {
	return {
		onShowIP: ip => {
			dispatch( setFilter( 'ip', ip ) );
		},
		onSetSelected: items => {
			dispatch( setSelected( items ) );
		},
		onDelete: item => {
			dispatch( performTableAction( 'delete', item ) );
		},
	};
}

export default connect(
	null,
	mapDispatchToProps
)( LogRow );
