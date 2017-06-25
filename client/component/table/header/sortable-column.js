/**
 * External dependencies
 */

import React from 'react';
import { connect } from 'react-redux';
import classnames from 'classnames';

import { setOrderBy } from 'state/log/action';

const SortableColumn = props => {
	const { direction, orderBy, name, text } = props;
	const click = ev => {
		ev.preventDefault();
		props.onChange( name, orderBy === name && direction === 'desc' ? 'asc' : 'desc' );
	};
	const classes = classnames( {
		'manage-column': true,
		sortable: true,
		asc: orderBy === name && direction === 'asc',
		desc: orderBy === name && direction === 'desc' || orderBy !== name,
		'column-primary': orderBy === name,
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

function mapDispatchToProps( dispatch ) {
	return {
		onChange: ( column, direction ) => {
			dispatch( setOrderBy( column, direction ) );
		},
	};
}

export default connect(
	null,
	mapDispatchToProps
)( SortableColumn );
