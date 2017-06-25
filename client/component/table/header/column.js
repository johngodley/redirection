/**
 * External dependencies
 */

import React from 'react';
import classnames from 'classnames';

const Column = props => {
	const { name, text } = props;
	const classes = classnames( {
		'manage-column': true,
		[ 'column-' + name ]: true,
	} );

	return (
		<th scope="col" className={ classes }>
			<span>{ text }</span>
		</th>
	);
};

export default Column;
