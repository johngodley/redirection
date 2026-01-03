import { useEffect, useRef } from 'react';

interface PreventLeaveWarningProps {
	message: string;
	prevent?: boolean;
}

function PreventLeaveWarning( { message, prevent = true }: PreventLeaveWarningProps ) {
	const onWarningRef = useRef< ( event: BeforeUnloadEvent ) => string >();

	// Create stable reference to the warning handler
	useEffect( () => {
		onWarningRef.current = ( event: BeforeUnloadEvent ) => {
			event.returnValue = message;
			return event.returnValue;
		};
	}, [ message ] );

	// Manage event listener based on prevent prop
	useEffect( () => {
		if ( ! prevent || ! onWarningRef.current ) {
			return;
		}

		const handler = ( event: BeforeUnloadEvent ): string | undefined => {
			if ( onWarningRef.current ) {
				return onWarningRef.current( event );
			}
			return undefined;
		};

		window.addEventListener( 'beforeunload', handler );

		return () => {
			window.removeEventListener( 'beforeunload', handler );
		};
	}, [ prevent ] );

	return null;
}

export default PreventLeaveWarning;
