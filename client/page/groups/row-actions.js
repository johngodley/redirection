/**
 * External dependencies
 */

import React from 'react';
import { connect } from 'react-redux';
import { translate as __ } from 'lib/locale';

/**
 * Internal dependencies
 */

import { RowActions, RowAction } from 'component/table/row-action';
import { performTableAction } from 'state/group/action';
import { has_capability, CAP_GROUP_ADD, CAP_GROUP_DELETE, CAP_REDIRECT_MANAGE } from 'lib/capabilities';

function GroupRowActions( props ) {
	const { disabled, rowParams, onDelete, onEnable, onDisable, row } = props;
	const { setRowMode, rowMode } = rowParams;
	const { id, enabled } = row;
	const menu = [];

	if ( rowMode === 'edit' ) {
		return null;
	}

	if ( has_capability( CAP_GROUP_ADD ) ) {
		menu.push(
			<RowAction onClick={ () => setRowMode( rowMode === 'edit' ? false : 'edit' ) } key="0">
				{ __( 'Edit' ) }
			</RowAction>
		);
	}

	if ( has_capability( CAP_GROUP_DELETE ) ) {
		menu.push(
			<RowAction onClick={ () => onDelete( id ) } key="1">
				{ __( 'Delete' ) }
			</RowAction>
		);
	}

	if ( has_capability( CAP_REDIRECT_MANAGE ) ) {
		menu.push(
			<RowAction
				key="2"
				href={ Redirectioni10n.pluginRoot + '&' + encodeURIComponent( 'filterby[group]' ) + '=' + id }
			>
				{ __( 'View Redirects' ) }
			</RowAction>
		);
	}

	if ( has_capability( CAP_GROUP_ADD ) ) {
		if ( enabled ) {
			menu.push(
				<RowAction key="3" onClick={ () => onDisable( id ) }>
					{ __( 'Disable' ) }
				</RowAction>
			);
		} else {
			menu.push(
				<RowAction key="3" onClick={ () => onEnable( id ) }>
					{ __( 'Enable' ) }
				</RowAction>
			);
		}
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
)( GroupRowActions );
