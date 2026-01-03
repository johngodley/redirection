/**
 * Clean API parameters by removing empty objects and arrays
 * This prevents sending empty filterBy={} which the API rejects
 * @param params
 */
export function cleanApiParams( params: Record< string, any > ): Record< string, any > {
	const cleaned: Record< string, any > = {};

	for ( const key in params ) {
		const value = params[ key ];

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
