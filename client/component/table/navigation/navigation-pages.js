/**
 * External dependencies
 */

import { sprintf, __, _n } from '@wordpress/i18n';
import classnames from 'classnames';

/**
 * Internal dependencies
 */

import PaginationLinks from './pagination-links';
import { createInterpolateElement } from 'wp-plugin-components';

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
				{ selected === 0 && sprintf( _n( '%s item', '%s items', total, 'redirection' ), new Intl.NumberFormat( window.Redirectioni10n.locale ).format( total ) ) }
				{ selected > 0 &&
					!isEverything &&
					createInterpolateElement(
						sprintf( __( '%1d of %1d selected. {{all}}Select All.{{/all}}' ), selected, total ),
						{
							all: <a href="#" onClick={ selectAll } />,
						},
					) }
				{ isEverything &&
					createInterpolateElement(
						sprintf( __( '%1d of %1d selected. {{all}}Clear All.{{/all}}' ), selected, total ),
						{
							all: <a href="#" onClick={ clearAll } />,
						},
					) }
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
