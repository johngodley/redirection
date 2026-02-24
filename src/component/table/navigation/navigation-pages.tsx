import { sprintf, __, _n } from '@wordpress/i18n';
import clsx from 'clsx';
import PaginationLinks from './pagination-links';
import { createInterpolateElement } from '@wp-plugin-components';

interface NavigationPagesProps {
	total: number;
	perPage: number;
	page: number;
	onChangePage: ( page: number ) => void;
	disabled: boolean;
	selected: number;
	onSelectAll: ( selectAll: boolean ) => void;
	isEverything: boolean;
}

function NavigationPages( props: NavigationPagesProps ) {
	const { total, perPage, page, onChangePage, selected, onSelectAll, isEverything } = props;
	const classes = clsx( {
		'tablenav-pages': true,
	} );

	function selectAll() {
		onSelectAll( true );
	}
	function clearAll() {
		onSelectAll( false );
	}

	return (
		<div className={ classes }>
			<span className={ clsx( 'displaying-num', isEverything ? 'displaying-num-all' : null ) }>
				{ /* translators: %s is the number of items */ }
				{ ( selected === 0 || ( selected < perPage && ! isEverything ) ) &&
					sprintf(
						// translators: %s is the number of items
						_n( '%s item', '%s items', total, 'redirection' ),
						new Intl.NumberFormat( window.Redirectioni10n.locale ).format( total )
					) }
				{ /* translators: 1: number of selected items, 2: total number of items */ }
				{ selected > 0 &&
					selected >= perPage &&
					! isEverything &&
					createInterpolateElement(
						sprintf(
							// translators: 1: number of selected items, 2: total number of items
							__( '%1$d of %2$d selected. {{all}}Select All.{{/all}}', 'redirection' ),
							selected,
							total
						),
						{
							all: <button type="button" onClick={ selectAll } />,
						}
					) }
				{ /* translators: 1: number of selected items, 2: total number of items */ }
				{ isEverything &&
					createInterpolateElement(
						sprintf(
							// translators: 1: number of selected items, 2: total number of items
							__( '%1$d of %2$d selected. {{all}}Clear All.{{/all}}', 'redirection' ),
							selected,
							total
						),
						{
							all: <button type="button" onClick={ clearAll } />,
						}
					) }
			</span>

			<span className="pagination-links">
				<PaginationLinks
					onChangePage={ onChangePage }
					total={ total }
					perPage={ perPage }
					page={ page }
					key={ page }
				/>
			</span>
		</div>
	);
}

export default NavigationPages;
