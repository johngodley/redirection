import type { ExportFormat } from './types';

function getMimeType( format: ExportFormat ) {
	if ( format === 'json' ) {
		return 'application/json;charset=utf-8';
	}

	return 'text/plain;charset=utf-8';
}

export function downloadText( filename: string, data: string, format: ExportFormat ) {
	const blob = new Blob( [ data ], { type: getMimeType( format ) } );
	const url = window.URL.createObjectURL( blob );
	const link = document.createElement( 'a' );

	link.href = url;
	link.download = filename;
	document.body.appendChild( link );
	link.click();
	link.remove();
	window.URL.revokeObjectURL( url );
}
