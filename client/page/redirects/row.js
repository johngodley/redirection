/**
 * External dependencies
 */

import React from 'react';
import { translate as __, numberFormat } from 'lib/locale';
import { connect } from 'react-redux';
import classnames from 'classnames';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */

import RowActions from 'component/table/row-action';
import EditRedirect from 'component/redirect-edit';
import Modal from 'component/modal';
import Spinner from 'component/spinner';
import HttpCheck from 'component/http-check';
import { setSelected, performTableAction } from 'state/redirect/action';
import { MATCH_URL, MATCH_SERVER, CODE_PASS, CODE_NOTHING } from 'state/redirect/selector';
import { STATUS_SAVING, STATUS_IN_PROGRESS } from 'state/settings/type';

class RedirectRow extends React.Component {
	static propTypes = {
		item: PropTypes.object.isRequired,
		selected: PropTypes.bool.isRequired,
		status: PropTypes.string.isRequired,
	};

	constructor( props ) {
		super( props );

		this.state = {
			editing: false,
			showCheck: false,
		};
	}

	onEdit = ev => {
		ev.preventDefault();
		this.setState( { editing: true } );
	}

	onCancel = ev => {
		ev.preventDefault();
		this.setState( { editing: false } );
	}

	onDelete = ev => {
		ev.preventDefault();
		this.props.onTableAction( 'delete', this.props.item.id );
	}

	onDisable = ev => {
		ev.preventDefault();
		this.props.onTableAction( 'disable', this.props.item.id );
	}

	onEnable = ev => {
		ev.preventDefault();
		this.props.onTableAction( 'enable', this.props.item.id );
	}

	onSelected = () => {
		this.props.onSetSelected( [ this.props.item.id ] );
	}

	onCheck = ev => {
		ev.preventDefault();
		this.setState( { showCheck: true } );
	}

	getMenu() {
		const { enabled, regex, action_type } = this.props.item;
		const menu = [];

		if ( enabled ) {
			menu.push( [ __( 'Edit' ), this.onEdit ] );
		}

		menu.push( [ __( 'Delete' ), this.onDelete ] );

		if ( enabled ) {
			menu.push( [ __( 'Disable' ), this.onDisable ] );

			if ( ! regex && action_type === 'url' ) {
				menu.push( [ __( 'Check Redirect' ), this.onCheck ] );
			}
		} else {
			menu.push( [ __( 'Enable' ), this.onEnable ] );
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
			return action_data.url;
		}

		return null;
	}

	getServerUrl( url, matchType ) {
		if ( matchType === MATCH_SERVER ) {
			const { action_data } = this.props.item;

			return action_data.server + url;
		}

		return url;
	}

	getUrl( url, matchType ) {
		url = this.getServerUrl( url, matchType );

		if ( this.props.item.enabled ) {
			return url;
		}

		return <strike>{ url }</strike>;
	}

	getName( url, title ) {
		const { regex, match_type } = this.props.item;

		if ( title ) {
			return title;
		}

		const fullUrl = this.getUrl( url, match_type );
		if ( regex ) {
			return fullUrl;
		}

		return <a href={ fullUrl } target="_blank" rel="noopener noreferrer">{ fullUrl }</a>;
	}

	renderSource( url, title, saving ) {
		const name = this.getName( url, title );

		return (
			<td className="column-primary column-url has-row-actions">
				{ name }<br />
				<span className="target">{ this.getTarget() }</span>

				<RowActions disabled={ saving }>
					{ this.getMenu() }
				</RowActions>
			</td>
		);
	}

	renderEditColumns() {
		return (
			<td className="column-primary column-url" colSpan="4">
				<EditRedirect item={ this.props.item } onCancel={ this.onCancel } />
			</td>
		);
	}

	closeCheck = () => {
		this.setState( { showCheck: false } );
	}

	renderCheck() {
		return (
			<Modal onClose={ this.closeCheck } padding={ false }>
				<HttpCheck item={ this.props.item } />
			</Modal>
		);
	}

	renderViewColumns( isSaving ) {
		const { url, hits, last_access, title, position } = this.props.item;

		return (
			<React.Fragment>
				{ this.renderSource( url, title, isSaving ) }

				<td className="column-position">
					{ numberFormat( position ) }
					{ this.state.showCheck && this.renderCheck() }
				</td>
				<td className="column-last_count">
					{ numberFormat( hits ) }
				</td>
				<td className="column_last_access">
					{ last_access }
				</td>
			</React.Fragment>
		);
	}

	render() {
		const { id, enabled } = this.props.item;
		const { selected, status } = this.props;
		const isLoading = status === STATUS_IN_PROGRESS;
		const isSaving = status === STATUS_SAVING;
		const hideRow = ! enabled || isLoading || isSaving;
		const classes = classnames( {
			disabled: hideRow,
		} );

		return (
			<tr className={ classes }>
				<th scope="row" className="check-column">
					{ ! isSaving && <input type="checkbox" name="item[]" value={ id } disabled={ isLoading } checked={ selected } onChange={ this.onSelected } /> }
					{ isSaving && <Spinner size="small" /> }
				</th>
				<td className="column-code">
					{ this.getCode() }
				</td>

				{ this.state.editing ? this.renderEditColumns() : this.renderViewColumns( isSaving ) }
			</tr>
		);
	}
}

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
