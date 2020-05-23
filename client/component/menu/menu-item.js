/* global Redirectioni10n */
/**
 * External dependencies
 */

import React from 'react';
import PropTypes from 'prop-types';

const MenuItem = props => {
	const { item, isCurrent, onClick, isLast } = props;
	const url = Redirectioni10n.pluginRoot + ( item.value === '' ? '' : '&sub=' + item.value );
	const clicker = ev => {
		ev.preventDefault();
		onClick( item.value, url );
	};

	return (
		<li>
			<a className={ isCurrent ? 'current' : '' } href={ url } onClick={ clicker }>
				{ item.name }
			</a> { ! isLast && '|'  }&nbsp;
		</li>
	);
};

MenuItem.propTypes = {
	item: PropTypes.object.isRequired,
	isCurrent: PropTypes.bool.isRequired,
	onClick: PropTypes.func.isRequired,
};

export default MenuItem;
