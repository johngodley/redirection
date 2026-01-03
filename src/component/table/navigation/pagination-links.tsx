import { useEffect, useState } from 'react';
import { __, sprintf } from '@wordpress/i18n';
import NavigationButton from './navigation-button';

function getTotalPages( total: number, perPage: number ): number {
	return Math.ceil( total / perPage );
}

interface PaginationLinksProps {
	page: number;
	total: number;
	perPage: number;
	onChangePage: ( page: number ) => void;
}

function PaginationLinks( props: PaginationLinksProps ) {
	const { page, total, perPage, onChangePage } = props;
	const onePage = total <= perPage;
	const [ currentPage, setPage ] = useState( page + 1 );

	useEffect( () => {
		setPage( page + 1 );
	}, [ page ] );

	if ( onePage ) {
		return null;
	}
	const max = getTotalPages( total, perPage );

	return (
		<>
			<NavigationButton
				title={ __( 'First page', 'redirection' ) }
				button="«"
				className="first-page"
				disabled={ page <= 0 }
				onClick={ () => onChangePage( 0 ) }
			/>

			<NavigationButton
				title={ __( 'Prev page', 'redirection' ) }
				button="‹"
				className="prev-page"
				disabled={ page <= 0 }
				onClick={ () => onChangePage( page - 1 ) }
			/>
			<span className="paging-input">
				<label htmlFor="current-page-selector" className="screen-reader-text">
					{ __( 'Current Page', 'redirection' ) }
				</label>

				<input
					className="current-page"
					type="number"
					min="1"
					max={ max }
					name="paged"
					value={ currentPage }
					size={ 2 }
					aria-describedby="table-paging"
					onBlur={ () => onChangePage( Math.min( max - 1, Math.max( 0, currentPage - 1 ) ) ) }
					onChange={ ( ev ) => setPage( parseInt( ev.target.value, 10 ) ) }
				/>

				<span className="tablenav-paging-text">
					{ sprintf(
						// translators: %s is the total number of pages
						__( 'of %s', 'redirection' ),
						new Intl.NumberFormat( window.Redirectioni10n.locale ).format( max )
					) }
				</span>
			</span>

			<NavigationButton
				title={ __( 'Next page', 'redirection' ) }
				button="›"
				className="next-page"
				disabled={ page >= max - 1 }
				onClick={ () => onChangePage( page + 1 ) }
			/>

			<NavigationButton
				title={ __( 'Last page', 'redirection' ) }
				button="»"
				className="last-page"
				disabled={ page >= max - 1 }
				onClick={ () => onChangePage( max - 1 ) }
			/>
		</>
	);
}

export default PaginationLinks;
