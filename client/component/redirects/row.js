/**
 * External dependencies
 */

import React from 'react';
import { translate as __, numberFormat } from 'lib/locale';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */

import RowActions from 'component/table/row-action';
import EditRedirect from './edit';
import Spinner from 'component/wordpress/spinner';
import { setSelected, performTableAction } from 'state/redirect/action';
import { STATUS_SAVING, STATUS_IN_PROGRESS } from 'state/settings/type';

const CODE_PASS = 'pass';
const CODE_NOTHING = 'nothing';
const MATCH_URL = 'url';

class RedirectRow extends React.Component {
	constructor( props ) {
		super( props );

		this.state = { editing: false };

		this.handleEdit = this.onEdit.bind( this );
		this.handleDelete = this.onDelete.bind( this );
		this.handleDisable = this.onDisable.bind( this );
		this.handleEnable = this.onEnable.bind( this );
		this.handleCancel = this.onCancel.bind( this );
		this.handleSelected = this.onSelected.bind( this );
	}

	componentWillUpdate( nextProps ) {
		if ( nextProps.item.id !== this.props.item.id && this.state.editing ) {
			this.setState( { editing: false } );
		}
	}

	onEdit( ev ) {
		ev.preventDefault();
		this.setState( { editing: true } );
	}

	onCancel() {
		this.setState( { editing: false } );
	}

	onDelete( ev ) {
		ev.preventDefault();
		this.props.onTableAction( 'delete', this.props.item.id );
	}

	onDisable( ev ) {
		ev.preventDefault();
		this.props.onTableAction( 'disable', this.props.item.id );
	}

	onEnable( ev ) {
		ev.preventDefault();
		this.props.onTableAction( 'enable', this.props.item.id );
	}

	onSelected() {
		this.props.onSetSelected( [ this.props.item.id ] );
	}

	getMenu() {
		const { enabled } = this.props.item;
		const menu = [];

		if ( enabled ) {
			menu.push( [ __( 'Edit' ), this.handleEdit ] );
		}

		menu.push( [ __( 'Delete' ), this.handleDelete ] );

		if ( enabled ) {
			menu.push( [ __( 'Disable' ), this.handleDisable ] );
		} else {
			menu.push( [ __( 'Enable' ), this.handleEnable ] );
		}

		return menu
			.map( ( item, pos ) => <a key={ pos } href="#" onClick={ item[ 1 ] }>{ item[ 0 ] }</a> )
			.reduce( ( prev, curr ) => [ prev, ' | ', curr ] );
	}

	getCode() {
		const { action_code, action_type } = this.props.item;

		if ( action_type === CODE_PASS ) {
			return __( 'pass' );
		}

		if ( action_type === CODE_NOTHING ) {
			return '-';
		}

		return action_code;
	}

	getTarget() {
		const { match_type, action_data } = this.props.item;

		if ( match_type === MATCH_URL ) {
			return action_data;
		}

		return null;
	}

	getUrl( url ) {
		if ( this.props.item.enabled ) {
			return url;
		}

		return <strike>{ url }</strike>;
	}

	renderSource( url, title, saving ) {
		const name = title ? title : <a href={ url } target="_blank" rel="noopener noreferrer">{ this.getUrl( url ) }</a>;

		return (
			<td>
				{ name }<br />
				<span className="target">{ this.getTarget() }</span>

				<RowActions disabled={ saving }>
					{ this.getMenu() }
				</RowActions>
			</td>
		);
	}

	render() {
		const { id, url, hits, last_access, enabled, title, position } = this.props.item;
		const { selected, status } = this.props;
		const isLoading = status === STATUS_IN_PROGRESS;
		const isSaving = status === STATUS_SAVING;
		const hideRow = ! enabled || isLoading || isSaving;

		return (
			<tr className={ hideRow ? 'disabled' : '' }>
				<th scope="row" className="check-column">
					{ ! isSaving && <input type="checkbox" name="item[]" value={ id } disabled={ isLoading } checked={ selected } onClick={ this.handleSelected } /> }
					{ isSaving && <Spinner size="small" /> }
				</th>
				<td>
					{ this.getCode() }
				</td>

				{ this.state.editing ? <td><EditRedirect item={ this.props.item } onCancel={ this.handleCancel } /></td> : this.renderSource( url, title, isSaving ) }

				<td>
					{ numberFormat( position ) }
				</td>
				<td>
					{ numberFormat( hits ) }
				</td>
				<td>
					{ last_access }
				</td>
			</tr>
		);
	}
}

RedirectRow.propTypes = {
	item: PropTypes.object.isRequired,
	selected: PropTypes.bool.isRequired,
	status: PropTypes.string.isRequired,
};

function mapDispatchToProps( dispatch ) {
	return {
		onSetSelected: items => {
			dispatch( setSelected( items ) );
		},
		onTableAction: ( action, ids ) => {
			dispatch( performTableAction( action, ids ) );
		},
	};
}

export default connect(
	null,
	mapDispatchToProps,
)( RedirectRow );
