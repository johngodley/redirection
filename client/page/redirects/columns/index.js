/**
 * External dependencies
 */

import React from 'react';
import { numberFormat } from 'i18n-calypso';

/**
 * Internal dependencies
 */
import StatusColumn from './status';
import SourceColumn from './source';
import getCode from './code';
import GroupColumn from './group';
import EditRedirect from 'component/redirect-edit';
import { Modal } from 'wp-plugin-components';
import getMatchType from './match-type';
import HttpCheck from 'component/http-check';
import { getMatches, getActions } from 'component/redirect-edit/constants';

function getServer( item ) {
	if ( item.match_type === 'server' ) {
		return item.action_data.server;
	}

	return document.location.origin;
}

export default function getColumns( row, rowParams, disabled, defaultFlags, group ) {
	const { last_access, hits, position, match_type, action_type, action_code, action_data } = row;
	const { rowMode, setRowMode } = rowParams;

	if ( rowMode === 'edit' ) {
		return <EditRedirect item={ row } onCancel={ () => setRowMode( false ) } />;
	}

	return [
		{
			name: 'status',
			content: <StatusColumn row={ row } />,
		},
		{
			name: 'source',
			content: (
				<>
					<SourceColumn
						row={ row }
						table={ rowParams.table }
						filters={ rowParams.table.filterBy }
						defaultFlags={ defaultFlags }
					/>
					{ rowMode === 'check' && (
						<Modal onClose={ () => setRowMode( null ) }>
							<HttpCheck url={ getServer( row ) } desiredCode={ action_code } desiredTarget={ action_data } />
						</Modal>
					) }
				</>
			),
			alwaysDisplay: true,
		},
		{
			name: 'match_type',
			content: getMatchType( match_type, getMatches() ),
		},
		{
			name: 'action_type',
			content: getMatchType( action_type, getActions() ),
		},
		{
			name: 'code',
			content: getCode( row ),
		},
		{
			name: 'group',
			content: <GroupColumn row={ row } group={ group } />,
		},
		{
			name: 'position',
			content: numberFormat( position, 0 ),
		},
		{
			name: 'last_count',
			content: numberFormat( hits, 0 ),
		},
		{
			name: 'last_access',
			content: last_access,
		},
	];
}
