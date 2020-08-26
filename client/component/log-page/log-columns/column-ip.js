/**
 * External dependencies
 */

import React, { useState } from 'react';
import { translate as __ } from 'i18n-calypso';
import Highlighter from 'react-highlight-words';

/**
 * Internal dependencies
 */
import { RowActions, RowAction } from 'component/table/row-action';
import Modal from 'wp-plugin-components/modal';
import GeoMap from 'component/geo-map';

/** @typedef {import('component/table').Table} Table */
/** @typedef {import('component/table').TableRow} TableRow */

/**
 * @callback onFilter
 * @param {object} filter
 **/

/**
 * @param {object} props - Component props
 * @param {TableRow & {ip: string}} props.row - Table rows
 * @param {Table} props.table
 * @param {onFilter} props.onFilter
 */
export default function ColumnIp( props ) {
	const [ showMap, setShowMap ] = useState( false );
	const { row, table, onFilter, isSaving } = props;
	const { ip } = row;

	if ( ! ip ) {
		return null;
	}

	return (
		<>
			<a
				href={ 'https://redirect.li/map/?ip=' + encodeURIComponent( ip ) }
				onClick={ ( ev ) => {
					ev.preventDefault();
					setShowMap( true );
				} }
			>
				<Highlighter searchWords={ [ table.filterBy.ip ] } textToHighlight={ ip } autoEscape />
			</a>

			{ showMap && (
				<Modal onClose={ () => setShowMap( false ) } padding={ false }>
					<GeoMap ip={ ip } />
				</Modal>
			) }

			{ table.groupBy === '' && (
				<RowActions
					disabled={ isSaving }
					actions={ [ <RowAction onClick={ () => onFilter( { ip } ) }>{ __( 'Filter by IP' ) }</RowAction> ] }
				/>
			) }
		</>
	);
}
