/**
 * External dependencies
 */

import React from 'react';

import { has_capability } from '../../lib/capabilities';

/**
 * @param {object} props - Component props
 * @param {RowAction[]} props.actions - Array of actions
 * @param {boolean} props.disabled
 */
export function RowActions( props ) {
	const { actions, disabled = false } = props;

	return (
		<div className="row-actions">
			{ disabled ? (
				<span>&nbsp;</span>
			) : (
				actions.length > 0 && actions.filter( ( item ) => item ).reduce( ( prev, curr ) => [ prev, ' | ', curr ] )
			) }
		</div>
	);
}

/**
 *
 * @param {object} props - Component props
 * @param {} [props.onClick]
 * @param {} props.children
 * @param {string} [props.href='']
 * @param {string} [props.capability='']
 */
export function RowAction( props ) {
	const { onClick, children, href = '', capability = '' } = props;

	function click( ev ) {
		if ( onClick ) {
			ev.preventDefault();
			onClick();
		}
	}

	if ( capability && ! has_capability( capability ) ) {
		return null;
	}

	return (
		<a href={ href ? href : '#' } onClick={ click }>
			{ children }
		</a>
	);
}
