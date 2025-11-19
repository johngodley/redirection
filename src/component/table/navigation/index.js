/**
 * External dependencies
 */

import React from 'react';
import classnames from 'classnames';

/**
 * Internal dependencies
 */
import NavigationPages from './navigation-pages';

function TableNav( props ) {
	const { total, table, children = null, onChangePage, disabled, className, onSelectAll } = props;

	return (
		<div className={ classnames( 'tablenav', className ) }>
			<div className="redirect-table__actions">{ children }</div>

			{ total > 0 && (
				<NavigationPages
					perPage={ table.per_page }
					page={ table.page }
					total={ total }
					onChangePage={ onChangePage }
					onSelectAll={ onSelectAll }
					disabled={ disabled }
					selected={ table.selectAll ? total : table.selected.length }
					isEverything={ table.selectAll }
				/>
			) }
		</div>
	);
}


export default TableNav;
