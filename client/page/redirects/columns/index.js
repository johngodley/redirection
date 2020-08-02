/**
 * External dependencies
 */

import React from 'react';
import { numberFormat } from 'wp-plugin-lib/locale';

/**
 * Internal dependencies
 */
import StatusColumn from './status';
import SourceColumn from './source';
import getCode from './code';
import GroupColumn from './group';
import EditRedirect from 'component/redirect-edit';
import Modal from 'wp-plugin-components/modal';
import getMatchType from './match-type';
import HttpCheck from 'component/http-check';
import { getMatches, getActions } from 'component/redirect-edit/constants';

export default function getColumns( row, rowParams, disabled, defaultFlags, group ) {
	const { last_access, hits, position, match_type, action_type } = row;
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
						<Modal onClose={ () => setRowMode( null ) } padding={ false }>
							<HttpCheck item={ row } />
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
			content: numberFormat( position ),
		},
		{
			name: 'last_count',
			content: numberFormat( hits ),
		},
		{
			name: 'last_access',
			content: last_access,
		},
	];
}
