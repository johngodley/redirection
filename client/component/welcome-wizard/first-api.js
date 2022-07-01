// Returns the best API route (the one with a valid GET and POST), in order they are defined. If nothing is valid, return the default
export default function getFirstApi( apiTest ) {
	const keys = Object.keys( apiTest );

	for ( let index = 0; index < keys.length; index++ ) {
		const key = keys[ index ];

		if ( apiTest[ key ] && apiTest[ key ].GET.status === 'ok' && apiTest[ key ].POST.status === 'ok' ) {
			return key;
		}
	}

	return 0;
}
