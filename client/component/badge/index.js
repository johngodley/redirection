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
		<div className={ classnames( 'redirect-badge', className, onClick ? 'redirect-badge__click' : null ) } { ...extra }>
			<div>
				{ children }
				{ onCancel && <span onClick={ onCancel }>⨯</span> }
			</div>
		</div>
	);
};

Badge.propTypes = {
	className: PropTypes.string,
	onClick: PropTypes.func,
	onCancel: PropTypes.func,
	children: PropTypes.oneOfType( [
		PropTypes.object,
		PropTypes.string,
	] ).isRequired,
	title: PropTypes.string,
};

export default Badge;
