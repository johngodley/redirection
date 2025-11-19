/**
 * External dependencies
 */

import { __ } from '@wordpress/i18n';
import Highlighter from 'react-highlight-words';

/**
 * Internal dependencies
 */
import { RowActions, RowAction } from 'component/table/row-action';

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

			{ table.groupBy === '' && (
				<RowActions
					disabled={ isSaving }
					actions={ [ <RowAction onClick={ () => onFilter( { ip } ) }>{ __( 'Filter by IP', 'redirection' ) }</RowAction> ] }
				/>
			) }
		</>
	);
}
