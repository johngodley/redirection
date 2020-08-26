/**
 * External dependencies
 */

import React from 'react';
import classnames from 'classnames';

/**
 * Table header column
 *
 * @param {object} props - Component props
 * @param {string} props.name - Column ID name
 * @param {string} props.title - Column title
 * @param {boolean} props.primary - Is the column 'primary'?
 */
const Column = ( props ) => {
	const { name, title, primary } = props;
	const classes = classnames( {
		'manage-column': true,
		'column-primary': primary,
		[ 'column-' + name ]: true,
	} );

	return (
		<th scope="col" className={ classes }>
			<span>{ title }</span>
		</th>
	);
};

export default Column;
