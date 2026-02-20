/**
 * Returns the table state update to apply after a bulk delete completes.
 *
 * For global (select-all) deletes we reset to page 0, because items from
 * the current page no longer exist. For single-item or non-global bulk
 * deletes we stay on the current page so the user doesn't lose their place.
 *
 * @param params The mutation variables' params object (may be undefined)
 */
export function getTableResetOnDelete( params: { global?: boolean } | undefined ): {
	page?: number;
	selected: never[];
	selectAll: boolean;
} {
	const isGlobal = params?.global === true;
	return {
		...( isGlobal ? { page: 0 } : {} ),
		selected: [],
		selectAll: false,
	};
}

/**
 * Maps a groupBy value to the correct filterBy key for "Show All" actions.
 *
 * When viewing grouped log/404 results, each row's id is the group value
 * (the URL, IP, or agent string). Clicking "Show All" should switch to
 * ungrouped view filtered to that value, using the correct filter key.
 *
 * @param groupBy The current groupBy field ('ip', 'agent', or 'url')
 * @param id      The group value (row.id cast to string)
 */
export function getShowFilter( groupBy: string, id: string ): Record< string, string > {
	if ( groupBy === 'ip' ) {
		return { ip: id };
	}
	if ( groupBy === 'agent' ) {
		return { agent: id };
	}
	return { 'url-exact': id };
}

// UI-only fields that should not be sent to the API or affect query keys
const UI_ONLY_FIELDS = [ 'selected', 'selectAll', 'displayType', 'displaySelected' ];

/**
 * Clean API parameters by removing empty objects and arrays
 * This prevents sending empty filterBy={} which the API rejects
 * @param params
 */
export function cleanApiParams( params: Record< string, any > ): Record< string, any > {
	const cleaned: Record< string, any > = {};

	for ( const key in params ) {
		const value = params[ key ];

		// Skip UI-only fields that shouldn't affect API queries
		if ( UI_ONLY_FIELDS.includes( key ) ) {
			continue;
		}

		// Skip undefined and null
		if ( value === undefined || value === null ) {
			continue;
		}

		// Skip empty objects
		if ( typeof value === 'object' && ! Array.isArray( value ) && Object.keys( value ).length === 0 ) {
			continue;
		}

		// Skip empty arrays
		if ( Array.isArray( value ) && value.length === 0 ) {
			continue;
		}

		// Skip empty strings
		if ( typeof value === 'string' && value.trim() === '' ) {
			continue;
		}

		cleaned[ key ] = value;
	}

	return cleaned;
}
