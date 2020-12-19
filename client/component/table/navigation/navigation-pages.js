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
	const { total, perPage, page, onChangePage, disabled, selected, onSelectAll, isEverything } = props;
	const classes = classnames( {
		'tablenav-pages': true,
	} );

	function selectAll( ev ) {
		ev.preventDefault();
		onSelectAll( true );
	}
	function clearAll( ev ) {
		ev.preventDefault();
		onSelectAll( false );
	}

	return (
		<div className={ classes }>
			<span className={ classnames( 'displaying-num', isEverything ? 'displaying-num-all' : null ) }>
				{ selected === 0 && __( '%s item', '%s items', { count: total, args: numberFormat( total, 0 ) } ) }
				{ selected > 0 &&
					! isEverything &&
					__( '%1d of %1d selected. {{all}}Select All.{{/all}}', {
						args: [ selected, total ],
						components: {
							all: <a href="#" onClick={ selectAll } />,
						},
					} ) }
				{ isEverything &&
					__( '%1d of %1d selected. {{all}}Clear All.{{/all}}', {
						args: [ selected, total ],
						components: {
							all: <a href="#" onClick={ clearAll } />,
						},
					} ) }
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
