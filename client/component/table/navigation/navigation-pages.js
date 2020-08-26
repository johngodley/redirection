/**
 * External dependencies
 */

import React from 'react';
import { translate as __, numberFormat } from 'i18n-calypso';
import classnames from 'classnames';

/**
 * Internal dependencies
 */

import PaginationLinks from './pagination-links';

function NavigationPages( props ) {
	const { total, perPage, page, onChangePage, disabled } = props;
	const classes = classnames( {
		'tablenav-pages': true,
	} );

	return (
		<div className={ classes }>
			<span className="displaying-num">
				{ __( '%s item', '%s items', { count: total, args: numberFormat( total, 0 ) } ) }
			</span>

			<span className="pagination-links">
				<PaginationLinks
					onChangePage={ onChangePage }
					total={ total }
					perPage={ perPage }
					page={ page }
					disabled={ disabled }
					key={ page }
				/>
			</span>
		</div>
	);
}

export default NavigationPages;
