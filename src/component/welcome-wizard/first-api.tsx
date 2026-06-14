interface ApiResult {
	GET: {
		status: string;
	};
	POST: {
		status: string;
	};
}

interface ApiTest {
	[ key: string ]: ApiResult;
}

export function hasWorkingApi( apiTest: ApiTest ): boolean {
	const keys = Object.keys( apiTest );

	for ( let index = 0; index < keys.length; index++ ) {
		const key = keys[ index ];

		if ( key && apiTest[ key ] && apiTest[ key ].GET.status === 'ok' && apiTest[ key ].POST.status === 'ok' ) {
			return true;
		}
	}

	return false;
}

export default function getFirstApi( apiTest: ApiTest ): string | null {
	const keys = Object.keys( apiTest );

	for ( let index = 0; index < keys.length; index++ ) {
		const key = keys[ index ];

		if ( key && apiTest[ key ] && apiTest[ key ].GET.status === 'ok' && apiTest[ key ].POST.status === 'ok' ) {
			return key;
		}
	}

	return null;
}
