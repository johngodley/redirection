/**
 * External dependencies
 */

import React, { useEffect, useState } from 'react';
import { translate as __, numberFormat } from 'lib/locale';

/**
 * Internal dependencies
 */

import NavigationButton from './navigation-button';

/**
 * @param {number} total
 * @param {number} perPage
 */
function getTotalPages( total, perPage ) {
	return Math.ceil( total / perPage );
}

function PaginationLinks( props ) {
	const { page, total, perPage, onChangePage } = props;
	const [ currentPage, setPage ] = useState( page );
	const max = getTotalPages( total, perPage );

	useEffect( () => {
		setPage( page );
	}, [ page ] );

	return (
		<span className="pagination-links">
			<NavigationButton
				title={ __( 'First page' ) }
				button="«"
				className="first-page"
				disabled={ page <= 0 }
				onClick={ () => onChangePage( 0 ) }
			/>

			<NavigationButton
				title={ __( 'Prev page' ) }
				button="‹"
				className="prev-page"
				disabled={ page <= 0 }
				onClick={ () => onChangePage( page - 1 ) }
			/>
			<span className="paging-input">
				<label htmlFor="current-page-selector" className="screen-reader-text">
					{ __( 'Current Page' ) }
				</label>

				<input
					className="current-page"
					type="number"
					min="1"
					max={ max }
					name="paged"
					value={ currentPage + 1 }
					size={ 2 }
					aria-describedby="table-paging"
					onBlur={ () => onChangePage( currentPage ) }
					onChange={ ( ev ) => setPage( ev.target.value ) }
				/>

				<span className="tablenav-paging-text">
					{ __( 'of %(page)s', {
						components: {
							total: <span className="total-pages" />,
						},
						args: {
							page: numberFormat( max ),
						},
					} ) }
				</span>
			</span>

			<NavigationButton
				title={ __( 'Next page' ) }
				button="›"
				className="next-page"
				disabled={ page >= max - 1 }
				onClick={ () => onChangePage( page + 1 ) }
			/>

			<NavigationButton
				title={ __( 'Last page' ) }
				button="»"
				className="last-page"
				disabled={ page >= max - 1 }
				onClick={ () => onChangePage( max - 1 ) }
			/>
		</span>
	);
}

export default PaginationLinks;
