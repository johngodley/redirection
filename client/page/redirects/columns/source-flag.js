/**
 * External dependencies
 */

import React from 'react';
import classnames from 'classnames';

/**
 * Internal dependencies
 */

import Badge from 'component/badge';

/**
 * A redirect flag
 *
 * @param {object} props
 * @param {string} props.name - Name to display in the flag
 * @param {string} [props.className] - Extra class name
 */
function RedirectFlag( { name, className } ) {
	return <Badge className={ classnames( 'redirect-source__flag', className ) }>{ name }</Badge>;
}

export default RedirectFlag;
