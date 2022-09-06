/**
 * External dependencies
 */

import React from 'react';
import { connect } from 'react-redux';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */

import { RowActions, RowAction } from '../../component/table/row-action';
import { performTableAction } from '../../state/redirect/action';
import { has_capability, CAP_REDIRECT_ADD, CAP_REDIRECT_DELETE } from '../../lib/capabilities';

function RedirectRowActions( props ) {
	const { disabled, rowParams, onDelete, onEnable, onDisable, row } = props;
	const { setRowMode, rowMode } = rowParams;
	const { id, enabled, regex, action_type, match_type } = row;
	const menu = [];

	if ( rowMode === 'edit' ) {
		return null;
	}

	if ( enabled && has_capability( CAP_REDIRECT_ADD ) ) {
		menu.push( <RowAction key="1" onClick={ () => setRowMode( 'edit' ) }>{ __( 'Edit', 'redirection' ) }</RowAction> );
	}

	if ( has_capability( CAP_REDIRECT_DELETE ) ) {
		menu.push( <RowAction key="2" onClick={ () => onDelete( id ) }>{ __( 'Delete', 'redirection' ) }</RowAction> );
	}

	if ( has_capability( CAP_REDIRECT_ADD ) ) {
		if ( enabled ) {
			menu.push( <RowAction key="3" onClick={ () => onDisable( id ) }>{ __( 'Disable', 'redirection' ) }</RowAction> );
		} else {
			menu.push( <RowAction key="4" onClick={ () => onEnable( id ) }>{ __( 'Enable', 'redirection' ) }</RowAction> );
		}
	}

	if ( enabled && ! regex && action_type === 'url' && ( match_type === 'url' || match_type === 'server' ) ) {
		menu.push( <RowAction key="5" onClick={ () => setRowMode( 'check' ) }>{ __( 'Check Redirect', 'redirection' ) }</RowAction> );
	}

	return <RowActions disabled={ disabled } actions={ menu } />;
}

function mapDispatchToProps( dispatch ) {
	return {
		onDelete: ( id ) => {
			dispatch( performTableAction( 'delete', [ id ] ) );
		},
		onEnable: ( id ) => {
			dispatch( performTableAction( 'enable', [ id ] ) );
		},
		onDisable: ( id ) => {
			dispatch( performTableAction( 'disable', [ id ] ) );
		},
	};
}

export default connect(
	null,
	mapDispatchToProps
)( RedirectRowActions );
