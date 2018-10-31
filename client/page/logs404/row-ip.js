/**
 * External dependencies
 */

import React from 'react';
import { connect } from 'react-redux';
import { translate as __, numberFormat } from 'lib/locale';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */

import RowActions from 'component/table/row-action';
import Spinner from 'component/spinner';
import Modal from 'component/modal';
import GeoMap from 'component/geo-map';
import { setUngroupedSearch, setSelected, performTableAction } from 'state/error/action';
import { STATUS_IN_PROGRESS, STATUS_SAVING } from 'state/settings/type';
import { ACTION_URL, MATCH_IP, ACTION_ERROR } from 'state/redirect/selector';

class LogRowIp extends React.Component {
	static propTypes = {
		item: PropTypes.object.isRequired,
		onCreate: PropTypes.func.isRequired,
		selected: PropTypes.oneOfType( [
			PropTypes.bool,
			PropTypes.array,
		] ).isRequired,
	};

	constructor( props ) {
		super( props );

		this.state = {
			showMap: false,
			showAgent: false,
		};
	}

	onSelect = () => {
		this.props.onSetSelected( [ this.props.item.id ] );
	}

	onDelete = ev => {
		ev.preventDefault();
		this.props.onDelete( this.props.item.id );
	}

	onShow = ev => {
		ev.preventDefault();
		this.props.onShow( this.props.item.ip );
	}

	onAdd = ev => {
		const redirect = { regex: true, match_type: MATCH_IP, action_type: ACTION_URL, action_data: { ip: [ this.props.item.ip ] } };

		ev.preventDefault();
		this.props.onCreate( [ this.props.item.ip ], redirect );
	}

	onBlock = ev => {
		const block = { regex: true, match_type: MATCH_IP, action_type: ACTION_ERROR, action_data: { ip: [ this.props.item.ip ] }, action_code: 403 };

		ev.preventDefault();
		this.props.onCreate( [ this.props.item.ip ], block );
	}

	renderMap() {
		return (
			<Modal onClose={ this.closeMap } padding={ false }>
				<GeoMap ip={ this.props.item.ip } />
			</Modal>
		);
	}

	onGeo = ev => {
		ev.preventDefault();
		this.setState( { showMap: true } );
	}

	closeMap = () => {
		this.setState( { showMap: false } );
	}

	render() {
		const { ip, id, count } = this.props.item;
		const { selected, status } = this.props;
		const isLoading = status === STATUS_IN_PROGRESS;
		const isSaving = status === STATUS_SAVING;
		const hideRow = isLoading || isSaving;
		const menu = [
			<a href="#" onClick={ this.onDelete } key="0">{ __( 'Delete All' ) }</a>,
			<a href="#" onClick={ this.onAdd } key="1">{ __( 'Redirect All' ) }</a>,
			<a href="#" onClick={ this.onShow } key="2">{ __( 'Show All' ) }</a>,
			<a href="#" onClick={ this.onGeo } key="3">{ __( 'Geo Info' ) }</a>,
			<a href="#" onClick={ this.onBlock } key="3">{ __( 'Block IP' ) }</a>,
		];

		return (
			<tr className={ hideRow ? 'disabled' : '' }>
				<th scope="row" className="check-column">
					{ ! isSaving && <input type="checkbox" name="item[]" value={ id } disabled={ isLoading } checked={ selected } onChange={ this.onSelect } /> }
					{ isSaving && <Spinner size="small" /> }
				</th>
				<td className="column-ipx column-primary">
					<a href="#" onClick={ this.onGeo }>{ ip }</a>

					<RowActions disabled={ isSaving }>
						{ menu.reduce( ( prev, curr ) => [ prev, ' | ', curr ] ) }
					</RowActions>

					{ this.state.showMap && this.renderMap() }
				</td>
				<td className="column-total">
					{ numberFormat( count ) }
				</td>
			</tr>
		);
	}
}

function mapDispatchToProps( dispatch ) {
	return {
		onShow: ip => {
			dispatch( setUngroupedSearch( ip, 'ip' ) );
		},
		onSetSelected: items => {
			dispatch( setSelected( items ) );
		},
		onDelete: item => {
			dispatch( performTableAction( 'delete', item ) );
		},
	};
}

function mapStateToProps( state ) {
	const { status: infoStatus } = state.info;

	return {
		infoStatus,
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps
)( LogRowIp );
