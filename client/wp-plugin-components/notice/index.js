/**
 * External dependencies
 */

import React from 'react';

/**
 * Internal dependencies
 */

import './style.scss';

/**
 * Show an inline notice
 *
 * @param {object} props - Component props
 * @param {('warning'|'notice'|'error'|'general')} props.level - Error level
 * @param {object} props.children
 */
function Notice( { level, children } ) {
	return <div className={ `inline-notice inline-${ level }` }>{ children }</div>;
}

export default Notice;
