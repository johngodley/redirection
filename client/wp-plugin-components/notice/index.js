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
 * Show an inline notice
 *
 * @param {object} props - Component props
 * @param {('warning'|'notice'|'error'|'general')} [props.level] - Error level
 * @param {string} [props.className] = Extra class name
 * @param {object} props.children
 */
function Notice( { level = 'notice', children, className } ) {
	return <div className={ classnames( `inline-notice inline-${ level }`, className ) }>{ children }</div>;
}

export default Notice;
