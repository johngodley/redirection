/**
 * External dependencies
 */

import React from 'react';
import classnames from 'classnames';

/**
 * Internal dependencies
 */

import './style.scss';

/**
 * A spinner.
 *
 * @param {Object} props - Spinner props.
 * @param {String} [props.size] - Define a spinner size (`small`), otherwise uses default size.
 */
const Spinner = ( props ) => {
	const { size = '' } = props;
	const classes = classnames( 'wpl-spinner__container', size && ' spinner-' + size );

	return (
		<div className={ classes }>
			<span className="wpl-spinner__item" />
		</div>
	);
};

export default Spinner;
