/**
 * External dependencies
 */

import React from 'react';
import classnames from 'classnames';
import PropTypes from 'prop-types';

const SortableColumn = props => {
	const { name, text, table, primary } = props;
	const { direction, orderBy } = table;
	const click = ev => {
		ev.preventDefault();
		props.onSetOrderBy( name, orderBy === name && direction === 'desc' ? 'asc' : 'desc' );
	};
	const classes = classnames( {
		'manage-column': true,
		sortable: true,
		asc: orderBy === name && direction === 'asc',
		desc: orderBy === name && direction === 'desc' || orderBy !== name,
		'column-primary': primary,
		[ 'column-' + name ]: true,
	} );

	return (
		<th scope="col" className={ classes } onClick={ click }>
			<a href="#">
				<span>{ text }</span>
				<span className="sorting-indicator"></span>
			</a>
		</th>
	);
};

SortableColumn.propTypes = {
	table: PropTypes.object.isRequired,
	name: PropTypes.string.isRequired,
	text: PropTypes.string.isRequired,
	onSetOrderBy: PropTypes.func.isRequired,
};

export default SortableColumn;
