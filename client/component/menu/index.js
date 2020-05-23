/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import { getPluginPage } from 'lib/wordpress-url';
import MenuItem from './menu-item';
import { has_page_access } from 'lib/capabilities';
import './style.scss';

const getMenu = () => [
	{
		name: __( 'Redirects' ),
		value: '',
	},
	{
		name: __( 'Groups' ),
		value: 'groups',
	},
	{
		name: __( 'Site' ),
		value: 'site',
	},
	{
		name: __( 'Log' ),
		value: 'log',
	},
	{
		name: __( '404s' ),
		value: '404s',
	},
	{
		name: __( 'Import/Export' ),
		value: 'io',
	},
	{
		name: __( 'Options' ),
		value: 'options',
	},
	{
		name: __( 'Support' ),
		value: 'support',
	},
];

const isCurrent = ( page, item ) => page === item.value || page === 'redirect' && item.value === '';

const Menu = props => {
	const { onChangePage } = props;
	const page = getPluginPage();
	const menu = getMenu().filter( option => has_page_access( option.value ) || option.value === '' && has_page_access( 'redirect' ) );

	if ( menu.length < 2 ) {
		return null;
	}

	return (
		<div className="subsubsub-container">
			<ul className="subsubsub">
				{ menu.map( ( item, pos ) => (
					<MenuItem
						key={ pos }
						item={ item }
						isCurrent={ isCurrent( page, item ) }
						isLast={ pos === menu.length - 1 }
						onClick={ onChangePage } />
				) ) }
			</ul>
		</div>
	);
};

Menu.propTypes = {
	onChangePage: PropTypes.func.isRequired,
};

export default Menu;
