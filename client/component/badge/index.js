/**
 * External dependencies
 */

import React from 'react';
import classnames from 'classnames';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import './style.scss';

const Badge = props => {
	const { children, className, onClick, title, onCancel } = props;
	const extra = {
		title,
		onClick,
	};

	return (
		<span className={ classnames( 'redirect-badge', className, onClick ? 'redirect-badge__click' : null ) } { ...extra }>
			{ children }
			{ onCancel && <button onClick={ onCancel }>⨯</button> }
		</span>
	);
};

Badge.propTypes = {
	className: PropTypes.string,
	onClick: PropTypes.func,
	onCancel: PropTypes.func,
	children: PropTypes.object.isRequired,
	title: PropTypes.string,
};

export default Badge;
