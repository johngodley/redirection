type ExportFormat = 'json' | 'csv' | 'apache' | 'nginx';

function getMimeType( format: ExportFormat ) {
	if ( format === 'json' ) {
		return 'application/json;charset=utf-8';
	}

	if ( format === 'csv' ) {
		return 'text/csv;charset=utf-8';
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

export async function copyText( data: string ) {
	if ( navigator.clipboard?.writeText ) {
		await navigator.clipboard.writeText( data );
		return;
	}

	const textArea = document.createElement( 'textarea' );

	textArea.value = data;
	textArea.setAttribute( 'readonly', '' );
	textArea.style.position = 'absolute';
	textArea.style.left = '-9999px';
	document.body.appendChild( textArea );
	textArea.select();
	const copied = document.execCommand( 'copy' );
	textArea.remove();

	if ( ! copied ) {
		throw new Error( 'Unable to copy export data' );
	}
}
