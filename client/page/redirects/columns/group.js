/**
 * External dependencies
 */

import React from 'react';

/**
 * Internal dependencies
 */
import Badge from 'wp-plugin-components/badge';

function GroupColumnn( props ) {
	const { row, group } = props;
	const foundGroup = group.rows.find( ( found ) => found.id === row.group_id );

	if ( foundGroup ) {
		return (
			<div className="redirect-column-wrap">
				{ foundGroup.name } <Badge>{ foundGroup.moduleName }</Badge>
			</div>
		);
	}

	return null;
}

export default GroupColumnn;
