import { copyText } from './download';

describe( 'copyText', () => {
	const originalClipboard = navigator.clipboard;
	const originalExecCommand = document.execCommand;

	afterEach( () => {
		Object.defineProperty( navigator, 'clipboard', {
			configurable: true,
			value: originalClipboard,
		} );
		Object.defineProperty( document, 'execCommand', {
			configurable: true,
			value: originalExecCommand,
		} );
		document.body.innerHTML = '';
		jest.restoreAllMocks();
	} );

	it( 'uses the clipboard api when available', async () => {
		const writeText = jest.fn().mockResolvedValue( undefined );

		Object.defineProperty( navigator, 'clipboard', {
			configurable: true,
			value: { writeText },
		} );

		await copyText( 'redirect data' );

		expect( writeText ).toHaveBeenCalledWith( 'redirect data' );
	} );

	it( 'falls back to execCommand when the clipboard api is unavailable', async () => {
		const execCommand = jest.fn().mockReturnValue( true );

		Object.defineProperty( navigator, 'clipboard', {
			configurable: true,
			value: undefined,
		} );
		Object.defineProperty( document, 'execCommand', {
			configurable: true,
			value: execCommand,
		} );

		await copyText( 'redirect data' );

		expect( execCommand ).toHaveBeenCalledWith( 'copy' );
		expect( document.querySelector( 'textarea' ) ).toBeNull();
	} );
} );
