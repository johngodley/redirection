/**
 * External dependencies
 */

import React from 'react';
import { connect } from 'react-redux';
import { translate as __ } from 'i18n-calypso';

/**
 * Internal dependencies
 */

import { RowActions, RowAction } from 'component/table/row-action';
import { setFilter } from 'state/error/action';
import { CAP_REDIRECT_MANAGE, CAP_404_DELETE, CAP_REDIRECT_ADD } from 'lib/capabilities';
import GeoMapAction from 'component/log-page/log-actions/geo-map';
import UseragentAction from 'component/log-page/log-actions/user-agent';
import getCreateAction from './create-action';

function getShowFilter( groupBy, row ) {
	const { ip, agent, url } = row;

	if ( groupBy === 'ip' ) {
		return { ip };
	}

	if ( groupBy === 'agent' ) {
		return { agent };
	}

	return { 'url-exact': url };
}

function ErrorRowActions( props ) {
	const { row, onDelete, onCreate, table, disabled, onFilter } = props;
	const { url, ip, agent, id } = row;
	const { groupBy } = table;
	const menu = [];

	menu.push(
		<RowAction onClick={ () => onDelete( id ) } capability={ CAP_404_DELETE } key="0">
			{ __( 'Delete' ) }
		</RowAction>
	);

	menu.push(
		<RowAction
			onClick={ () => onCreate( getCreateAction( groupBy, groupBy === 'ip' ? [ id ] : ( groupBy === '' ? url : id ) ) ) }
			capability={ CAP_REDIRECT_ADD }
			key="1"
		>
			{ __( 'Add Redirect' ) }
		</RowAction>
	);

	if ( ip ) {
		menu.unshift( <GeoMapAction key="2" ip={ ip } /> );
	}

	if ( agent ) {
		menu.unshift( <UseragentAction key="3" agent={ agent } /> );
	}

	menu.push(
		<RowAction
			onClick={ () => onFilter( getShowFilter( groupBy, row ) ) }
			capability={ CAP_REDIRECT_MANAGE }
			key="4"
		>
			{ __( 'Show All' ) }
		</RowAction>
	);

	if ( groupBy === 'ip' ) {
		menu.push(
			<RowAction onClick={ () => onCreate( getCreateAction( 'block', [ ip ] ) ) } capability={ CAP_REDIRECT_ADD } key="5">
				{ __( 'Block IP' ) }
			</RowAction>
		);
	} else if ( groupBy !== 'agent' ) {
		menu.push(
			<RowAction
				onClick={ () => onCreate( getCreateAction( 'ignore', url ) ) }
				capability={ CAP_REDIRECT_ADD }
				key="6"
			>
				{ __( 'Ignore URL' ) }
			</RowAction>
		);
	}

	return <RowActions disabled={ disabled } actions={ menu } />;
}

function mapDispatchToProps( dispatch ) {
	return {
		onFilter: ( filterBy ) => {
			dispatch( setFilter( filterBy ) );
		},
	};
}

export default connect(
	null,
	mapDispatchToProps
)( ErrorRowActions );
