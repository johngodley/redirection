/**
 * External dependencies
 */

import React from 'react';
import classnames from 'classnames';

/** @typedef {import('../index.js').Table} Table */
/** @typedef {import('../index.js').SetOrderBy} SetOrderBy */

/**
 * Sortable column
 *
 * @param {object} props - Component props
 * @param {string} props.name - Column ID name
 * @param {string} props.title - Column title
 * @param {Table} props.table - Table params
 * @param {boolean} props.primary - Is a primary column
 * @param {SetOrderBy} props.onSetOrderBy - When clicking on a sortable header
 */
const SortableColumn = ( props ) => {
	const { name, title, table, primary, onSetOrderBy } = props;
	const { direction, orderby } = table;

	/** @param {Event} ev */
	const click = ( ev ) => {
		ev.preventDefault();
		onSetOrderBy( name, orderby === name && direction === 'desc' ? 'asc' : 'desc' );
	};
	const classes = classnames( {
		'manage-column': true,
		sortable: true,
		asc: orderby === name && direction === 'asc',
		desc: ( orderby === name && direction === 'desc' ) || orderby !== name,
		'column-primary': primary,
		[ 'column-' + name ]: true,
	} );

	return (
		<th scope="col" className={ classes } onClick={ click }>
			<a href="#">
				<span>{ title }</span>
				<span className="sorting-indicator" />
			</a>
		</th>
	);
};

export default SortableColumn;
