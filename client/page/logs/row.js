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
import Spinner from 'component/spinner';
import { STATUS_IN_PROGRESS, STATUS_SAVING } from 'state/settings/type';
import Modal from 'component/modal';
import GeoMap from 'component/geo-map';
import Useragent from 'component/useragent';

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
			showAgent: false,
		};
	}

	onShow = ev => {
		ev.preventDefault();
		this.props.onShowIP( this.props.item.ip );
	}

	onSelected = () => {
		this.props.onSetSelected( [ this.props.item.id ] );
	}

	onDelete = ev => {
		ev.preventDefault();
		this.props.onDelete( this.props.item.id );
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
			<Modal onClose={ this.closeMap } padding={ false }>
				<GeoMap ip={ this.props.item.ip } />
			</Modal>
		);
	}

	renderAgent() {
		return (
			<Modal onClose={ this.closeAgent } width="800">
				<Useragent agent={ this.props.item.agent } />
			</Modal>
		);
	}

	showMap = ev => {
		ev.preventDefault();
		this.setState( { showMap: true } );
	}

	showAgent = ev => {
		ev.preventDefault();
		this.setState( { showAgent: true } );
	}

	closeMap = () => {
		this.setState( { showMap: false } );
	}

	closeAgent = () => {
		this.setState( { showAgent: false } );
	}

	render() {
		const { created, created_time, ip, referrer, url, agent, sent_to, id } = this.props.item;
		const { selected, status } = this.props;
		const isLoading = status === STATUS_IN_PROGRESS;
		const isSaving = status === STATUS_SAVING;
		const hideRow = isLoading || isSaving;
		const menu = [
			<a href="#" onClick={ this.onDelete } key="0">{ __( 'Delete' ) }</a>,
		];

		if ( ip ) {
			menu.unshift( <a href={ 'https://redirect.li/map/?ip=' + encodeURIComponent( ip ) } onClick={ this.showMap } key="2">{ __( 'Geo Info' ) }</a> );
		}

		if ( agent ) {
			menu.unshift( <a href={ 'https://redirect.li/agent/?ip=' + encodeURIComponent( agent ) } onClick={ this.showAgent } key="3">{ __( 'Agent Info' ) }</a> );
		}

		return (
			<tr className={ hideRow ? 'disabled' : '' }>
				<th scope="row" className="check-column">
					{ ! isSaving && <input type="checkbox" name="item[]" value={ id } disabled={ isLoading } checked={ selected } onChange={ this.onSelected } /> }
					{ isSaving && <Spinner size="small" /> }
				</th>
				<td className="column-date">
					{ created }<br />{ created_time }
				</td>
				<td className="column-primary column-url">
					<a href={ url } rel="noreferrer noopener" target="_blank">{ url.substring( 0, 100 ) }</a><br />
					{ sent_to ? sent_to.substring( 0, 100 ) : '' }

					<RowActions disabled={ isSaving }>
						{ menu.reduce( ( prev, curr ) => [ prev, ' | ', curr ] ) }
					</RowActions>

					{ this.state.showMap && this.renderMap() }
					{ this.state.showAgent && this.renderAgent() }
				</td>
				<td className="column-referrer">
					<Referrer url={ referrer } />
					{ referrer && <br /> }
					{ agent }
				</td>
				<td className="column-ip">
					{ this.renderIp( ip ) }

					<RowActions>
						{ ip && <a href="#" onClick={ this.onShow }>{ __( 'Filter by IP' ) }</a> }
					</RowActions>
				</td>
			</tr>
		);
	}
}

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
