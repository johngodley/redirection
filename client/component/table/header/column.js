/**
 * External dependencies
 */

import React from 'react';
import classnames from 'classnames';

const Column = props => {
	const { name, text, primary } = props;
	const classes = classnames( {
		'manage-column': true,
		'column-primary': primary,
		[ 'column-' + name ]: true,
	} );

	return (
		<th scope="col" className={ classes }>
			<span>{ text }</span>
		</th>
	);
};

export default Column;
